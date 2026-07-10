<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/student.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();

$studentId = (int) ($_GET['id'] ?? 0);
if ($studentId <= 0) {
    http_response_code(400);
    exit('Invalid student id');
}

// Allow registrar, admin, cashier, and the student themselves
if (!in_array($user['role'], ['registrar', 'admin', 'cashier']) && (int)$user['id'] !== $studentId) {
    header('Location: dashboard.php');
    exit;
}

// Fetch student
$stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'student' LIMIT 1");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
if (!$student) {
    http_response_code(404);
    exit('Student not found');
}

// Student derived fields
$fullName = studentFullName($student);
$lrn         = $student['lrn_number'] ?? '';
$prevSchool  = $student['last_school_attended'] ?? '';
$address     = $student['home_address'] ?? '';
$fatherName  = $student['father_name'] ?? '';
$fatherCon   = $student['father_contact'] ?? '';
$motherName  = $student['mother_name'] ?? '';
$motherCon   = $student['mother_contact'] ?? '';
$gradeLevel  = $student['grade_level'] ?? '';
$section     = $student['section'] ?? '';
$gender      = $student['gender'] ?? '';
$dob         = !empty($student['date_of_birth']) ? date('m/d/Y', strtotime($student['date_of_birth'])) : '';
$age         = $student['age'] ?? '';

// School year
$currentMonth = (int) date('n');
$currentYear  = (int) date('Y');
if ($currentMonth >= 6) {
    $sy = $currentYear . '-' . ($currentYear + 1);
} else {
    $sy = ($currentYear - 1) . '-' . $currentYear;
}

// Date enrolled
$enrolledOn = !empty($student['created_at']) ? date('d-M-y', strtotime($student['created_at'])) : '';

// Fetch student fees (graceful fallback if tables don't exist)
$studentFees = [];
$totalPaid   = 0.0;
try {
    $fs = db()->prepare("SELECT sf.*, ft.name AS fee_name FROM student_fees sf JOIN fee_types ft ON ft.id = sf.fee_type_id WHERE sf.student_id = ? ORDER BY ft.name");
    $fs->execute([$studentId]);
    $studentFees = $fs->fetchAll();
} catch (Exception $e) {
    // tables may not exist — leave blank
}

// Build map keyed by lowercased fee name
$feeMap = [];
foreach ($studentFees as $f) {
    $key = strtolower($f['fee_name']);
    if (!isset($feeMap[$key])) $feeMap[$key] = 0.0;
    $feeMap[$key] += (float) $f['amount'];
}
function feeVal(array $map, array $needles): float {
    foreach ($needles as $n) {
        foreach ($map as $k => $v) {
            if (strpos($k, $n) !== false) return abs((float) $v);
        }
    }
    return 0.0;
}

$tuitionFee        = feeVal($feeMap, ['tuition']);
$miscellaneousFee  = feeVal($feeMap, ['miscellaneous', 'misc']);
$otherFee          = feeVal($feeMap, ['book', 'others']);

$earlyBirdDiscount = feeVal($feeMap, ['early bird']);
$academicScholar   = feeVal($feeMap, ['academic scholar']);
$sportsDiscount    = feeVal($feeMap, ['sports']);
$escGrant          = feeVal($feeMap, ['esc grant', 'esc']);
$shsVoucher        = feeVal($feeMap, ['shs voucher', 'shs']);
$tlcaScholar       = feeVal($feeMap, ['tlca scholar', 'tlcm']);
$fullScholar       = feeVal($feeMap, ['full scholar']);

$subTotalGross     = $tuitionFee + $miscellaneousFee + $otherFee;
$scholarshipTotal  = $earlyBirdDiscount + $academicScholar + $sportsDiscount
                   + $escGrant + $shsVoucher + $tlcaScholar + $fullScholar;

// Retrieve previous account
$previousAccount   = 0.0;
$currentMonth      = (int) date('n');
$currentYear       = (int) date('Y');
$sy = $currentMonth >= 6 ? ($currentYear . '-' . ($currentYear + 1)) : (($currentYear - 1) . '-' . $currentYear);
try {
    $pb = db()->prepare("SELECT COALESCE(SUM(balance),0) FROM student_fees WHERE student_id = ? AND (school_year IS NOT NULL AND school_year <> '' AND school_year <> ?) AND status <> 'paid'");
    $pb->execute([$studentId, $sy]);
    $previousAccount = max(0.0, (float) $pb->fetchColumn());
} catch (Exception $e) { /* ignore */ }
$payableOfYear     = max(0.0, ($subTotalGross - $scholarshipTotal) + $previousAccount);
$subTotalNet       = max(0.0, $subTotalGross - $scholarshipTotal);

function feeFmt(float $v): string { return $v > 0 ? number_format($v, 2) : ''; }

$tuition   = feeFmt($tuitionFee);
$misc      = feeFmt($miscellaneousFee);
$books     = feeFmt($otherFee);
$earlyBird = feeFmt($earlyBirdDiscount);
$academic  = feeFmt($academicScholar);
$sports    = feeFmt($sportsDiscount);
$esc       = feeFmt($escGrant);
$shs       = feeFmt($shsVoucher);
$tlcm      = feeFmt($tlcaScholar);
$full      = feeFmt($fullScholar);

$subtotal  = $subTotalGross;
$payable   = $payableOfYear;

// Scholarship label
$scholarshipAvail = '';
if ($escGrant > 0)        $scholarshipAvail = 'ESC GRANTEE';
elseif ($shsVoucher > 0)  $scholarshipAvail = 'SHS VOUCHER';
elseif ($academicScholar > 0) $scholarshipAvail = 'ACADEMIC';
elseif ($fullScholar > 0) $scholarshipAvail = 'FULL SCHOLAR';
elseif ($tlcaScholar > 0) $scholarshipAvail = 'TLCA SCHOLAR';

$studentType = ($student['is_transfer_in'] ?? 0) ? 'TRANSFEREE' : 'REGULAR';

// Logo
$logoFile = __DIR__ . '/image/new.png';
$logoSrc  = file_exists($logoFile) ? 'image/new.png' : '';
$months = ['JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER','JANUARY','FEBRUARY','MARCH'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Registration - <?= htmlspecialchars($fullName) ?></title>
    <style>
        @page { size: Letter; margin: 0.4in; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 20px;
            background: #f0f0f0;
        }
        .page {
            width: 8.5in;
            min-height: 11in;
            background: #fff;
            margin: 0 auto;
            padding: 0.4in 0.5in;
            box-shadow: 0 0 8px rgba(0,0,0,0.15);
            position: relative;
        }
        .school-header {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .school-header img { width: 64px; height: 64px; object-fit: contain; }
        .school-header .mid { margin: 0 14px; text-align: center; line-height: 1.2; }
        .school-header .mid .name { font-weight: bold; font-size: 13pt; letter-spacing: 0.5px; }
        .school-header .mid .sub { font-size: 9pt; }
        h1.doc-title {
            text-align: center;
            margin: 6px 0 14px 0;
            font-size: 14pt;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        table.info { width: 100%; border-collapse: collapse; }
        table.info td {
            padding: 3px 4px;
            vertical-align: bottom;
            font-size: 10pt;
        }
        .label { font-weight: bold; white-space: nowrap; }
        .fill {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 140px;
            padding: 0 4px;
            font-weight: bold;
        }
        .fill-wide { min-width: 240px; }
        .fill-sm   { min-width: 60px; }
        .fill-md   { min-width: 110px; }

        .cred-table { width: 100%; border-collapse: collapse; margin: 8px 0 4px 0; }
        .cred-table td { padding: 3px 6px; }
        .cred-table .cred-label { font-weight: bold; }
        .cred-table .check { width: 30px; text-align: center; border-bottom: 1px solid #000; }

        .section-title {
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 12pt;
            text-transform: uppercase;
            margin: 14px 0 8px 0;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 4px 0;
        }

        .soa-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .soa-row > div { flex: 1; }

        .fee-wrap { display: flex; gap: 16px; margin-top: 6px; }
        .fee-wrap .fee-left, .fee-wrap .fee-right { flex: 1; }

        table.fee-list { width: 100%; border-collapse: collapse; }
        table.fee-list td { padding: 3px 4px; font-size: 10pt; }
        table.fee-list td.amt {
            width: 100px;
            text-align: right;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        table.fee-list tr.emph td { font-weight: bold; border-top: 1px solid #000; }

        table.schedule { width: 100%; border-collapse: collapse; }
        table.schedule th, table.schedule td { padding: 3px 6px; font-size: 10pt; }
        table.schedule th {
            background: #f7dc6f; /* yellow like the template */
            font-weight: bold;
            text-align: center;
            border-bottom: 1px solid #000;
        }
        table.schedule td.month { font-weight: bold; }
        table.schedule td.amt { text-align: right; border-bottom: 1px dotted #000; min-width: 80px; }

        .payable-box {
            border: 1.5px solid #000;
            background: #fff9c4;
            padding: 6px 10px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 12pt;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-row .sig {
            width: 45%;
            text-align: center;
        }
        .signature-row .sig .line {
            border-top: 1px solid #000;
            padding-top: 3px;
            font-size: 9pt;
        }

        .foot-note {
            margin-top: 20px;
            font-size: 8.5pt;
            font-style: italic;
            text-align: center;
        }

        .print-bar {
            max-width: 8.5in;
            margin: 0 auto 10px auto;
            text-align: right;
        }
        .btn {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            margin-left: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .btn.secondary { background: #6c757d; }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; margin: 0; padding: 0.4in 0.5in; }
            .print-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <a href="javascript:window.print()" class="btn">🖨 Print</a>
    <a href="javascript:history.back()" class="btn secondary">← Back</a>
</div>

<div class="page">
    <!-- ===== HEADER ===== -->
    <div class="school-header">
        <?php if ($logoSrc): ?><img src="<?= $logoSrc ?>" alt="logo"><?php endif; ?>
        <div class="mid">
            <div class="sub">Department of Education</div>
            <div class="name">THE LIGHT CHRISTIAN ACADEMY</div>
            <div class="sub">Private Educational Institution</div>
            <div class="sub">Philippines</div>
        </div>
        <?php if ($logoSrc): ?><img src="<?= $logoSrc ?>" alt="logo"><?php endif; ?>
    </div>

    <h1 class="doc-title">Certificate of Registration</h1>

    <!-- ===== STUDENT INFO ===== -->
    <table class="info">
        <tr>
            <td style="width:70%">
                <span class="label">NAME:</span>
                <span class="fill fill-wide"><?= htmlspecialchars(strtoupper($fullName)) ?></span>
            </td>
            <td>
                <span class="label">LRN:</span>
                <span class="fill"><?= htmlspecialchars($lrn) ?></span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">PRESCHOOL GRADUATED:</span>
                <span class="fill fill-wide" style="min-width:380px"><?= htmlspecialchars(strtoupper($prevSchool)) ?></span>
            </td>
        </tr>
        <tr>
            <td style="width:40%">
                <span class="label">AGE:</span>
                <span class="fill fill-sm"><?= htmlspecialchars((string)$age) ?></span>
                <span class="label" style="margin-left:10px">BIRTHDATE:</span>
                <span class="fill fill-md"><?= htmlspecialchars($dob) ?></span>
            </td>
            <td>
                <span class="label">GENDER:</span>
                <span class="fill fill-md"><?= htmlspecialchars(strtoupper((string)$gender)) ?></span>
                <span class="label" style="margin-left:10px">SCHOOL TYPE:</span>
                <span class="fill fill-md">PUBLIC</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">ADDRESS:</span>
                <span class="fill fill-wide" style="min-width:560px"><?= htmlspecialchars(strtoupper($address)) ?></span>
            </td>
        </tr>
        <tr>
            <td style="width:50%">
                <span class="label">Father's Name:</span>
                <span class="fill fill-md"><?= htmlspecialchars(strtoupper($fatherName)) ?></span>
            </td>
            <td>
                <span class="label">Mother's Name:</span>
                <span class="fill fill-md"><?= htmlspecialchars(strtoupper($motherName)) ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Contact Number:</span>
                <span class="fill fill-md"><?= htmlspecialchars($fatherCon) ?></span>
            </td>
            <td>
                <span class="label">Contact Number:</span>
                <span class="fill fill-md"><?= htmlspecialchars($motherCon) ?></span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">GRADE LEVEL ENROLLED:</span>
                <span class="fill fill-sm"><?= htmlspecialchars((string)$gradeLevel) ?></span>
                <span class="label" style="margin-left:10px">SCHOOL YEAR:</span>
                <span class="fill fill-md"><?= htmlspecialchars($sy) ?></span>
            </td>
            <td></td>
        </tr>
    </table>

    <!-- ===== CREDENTIALS PRESENTED ===== -->
    <div style="margin-top:6px">
        <span class="label">CREDENTIALS PRESENTED:</span>
        <span style="float:right">
            <span class="label">Date Enrolled:</span>
            <span class="fill fill-md"><?= htmlspecialchars($enrolledOn) ?></span>
        </span>
    </div>
    <table class="cred-table">
        <tr>
            <td class="cred-label">221D</td><td class="check">&nbsp;</td>
            <td class="cred-label">SF9</td><td class="check">&nbsp;</td>
        </tr>
        <tr>
            <td class="cred-label">GOOD MORAL</td><td class="check">&nbsp;</td>
            <td class="cred-label">PSA</td><td class="check">&nbsp;</td>
        </tr>
        <tr>
            <td class="cred-label">INDIGENCY</td><td class="check">&nbsp;</td>
            <td></td><td></td>
        </tr>
    </table>

    <!-- ===== STATEMENT OF ACCOUNT ===== -->
    <div class="section-title">Statement of Account</div>

    <div class="soa-row">
        <div>
            <span class="label">SCHOOL YEAR:</span>
            <span class="fill fill-md"><?= htmlspecialchars($sy) ?></span>
        </div>
        <div style="text-align:right">
            <span class="label">GRADE LEVEL:</span>
            <span class="fill fill-sm"><?= htmlspecialchars((string)$gradeLevel) ?></span>
        </div>
    </div>
    <div class="soa-row">
        <div>
            <span class="label">STUDENT TYPE:</span>
            <span class="fill fill-md"><?= htmlspecialchars($studentType) ?></span>
        </div>
        <div style="text-align:right">
            <span class="label">SCHOLARSHIP AVAIL:</span>
            <span class="fill fill-md"><?= htmlspecialchars($scholarshipAvail) ?></span>
        </div>
    </div>

    <div class="label" style="margin-top:6px">FEE DETAILS:</div>

    <div class="fee-wrap">
        <div class="fee-left">
            <table class="fee-list">
                <tr><td>Tuition Fee</td><td class="amt"><?= $tuition ?: '&nbsp;' ?></td></tr>
                <tr><td>Miscellaneous</td><td class="amt"><?= $misc ?: '&nbsp;' ?></td></tr>
                <tr><td>Books</td><td class="amt"><?= $books ?: '&nbsp;' ?></td></tr>
                <tr class="emph"><td>Sub-Total</td><td class="amt"><?= $subtotal > 0 ? number_format($subtotal,2) : '&nbsp;' ?></td></tr>
                <tr><td colspan="2" style="font-weight:bold;padding-top:6px">Less:</td></tr>
                <tr><td>Early Bird</td><td class="amt"><?= $earlyBird ?: '&nbsp;' ?></td></tr>
                <tr><td>Academic Scholar</td><td class="amt"><?= $academic ?: '&nbsp;' ?></td></tr>
                <tr><td>Sports Discount</td><td class="amt"><?= $sports ?: '&nbsp;' ?></td></tr>
                <tr><td>ESC Grant</td><td class="amt"><?= $esc ?: '&nbsp;' ?></td></tr>
                <tr><td>SHS Voucher</td><td class="amt"><?= $shs ?: '&nbsp;' ?></td></tr>
                <tr><td>TLCA Scholar</td><td class="amt"><?= $tlcm ?: '&nbsp;' ?></td></tr>
                <tr><td>Full Scholar</td><td class="amt"><?= $full ?: '&nbsp;' ?></td></tr>
                <tr class="emph"><td>Sub-Total</td><td class="amt"><?= $subTotalNet > 0 ? number_format($subTotalNet,2) : '&nbsp;' ?></td></tr>
                <tr><td>Add: Previous Account</td><td class="amt"><?= $previousAccount > 0 ? number_format($previousAccount,2) : '&nbsp;' ?></td></tr>
            </table>
        </div>
        <div class="fee-right">
            <div style="font-weight:bold;text-align:center;margin-bottom:4px;">MONTHLY PAYMENT SCHEDULE</div>
            <table class="schedule">
                <thead>
                    <tr><th>MONTH</th><th>AMOUNT</th></tr>
                </thead>
                <tbody>
                    <?php
                    $totalPaidSoFar = 0.0;
                    $installmentList = [];
                    for ($i = 0; $i < 9; $i++) {
                        if ($i === 8) {
                            $installmentList[] = max(0.0, $payable - $totalPaidSoFar);
                        } else {
                            $inst = round($payable / 9, 2);
                            $installmentList[] = $inst;
                            $totalPaidSoFar += $inst;
                        }
                    }
                    ?>
                    <?php foreach ($months as $idx => $m): ?>
                        <tr>
                            <td class="month"><?= $m ?></td>
                            <td class="amt"><?= $installmentList[$idx] > 0 ? number_format($installmentList[$idx], 2) : '&nbsp;' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="payable-box">
        <span>PAYABLE OF THE YEAR</span>
        <span><?= $payable > 0 ? '₱ '.number_format($payable,2) : '' ?></span>
    </div>

    <!-- ===== SIGNATURES ===== -->
    <div class="signature-row">
        <div class="sig">
            <div style="height:40px"></div>
            <div class="line">Enrolled by (Signature over Printed Name)</div>
        </div>
        <div class="sig">
            <div style="height:40px"></div>
            <div class="line">Parent / Guardian (Signature over Printed Name)</div>
        </div>
    </div>

    <div class="foot-note">
        Note: 1 copy for school file, 1 copy for the student, 1 copy for the registrar
    </div>
</div>

<script>
const params = new URLSearchParams(window.location.search);
if (params.get('print') === '1') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
}
</script>

</body>
</html>