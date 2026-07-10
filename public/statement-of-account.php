<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'cashier', 'student'])) {
    header('Location: dashboard.php');
    exit;
}

$studentId = $role === 'student' ? (int) $user['id'] : (int) ($_GET['id'] ?? 0);

$allStudents = [];
if ($role !== 'student') {
    $stmt = db()->query("SELECT id, name, grade_level, empidno FROM users WHERE role = 'student' AND archived = 0 ORDER BY name");
    $allStudents = $stmt->fetchAll();
}

$student = null;
if ($studentId > 0) {
    $stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'student' LIMIT 1");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();
}

$currentMonth = (int) date('n');
$currentYear  = (int) date('Y');
$sy = $currentMonth >= 6 ? ($currentYear . '-' . ($currentYear + 1)) : (($currentYear - 1) . '-' . $currentYear);

$feeMap = [];
$previousAccount = 0.0;
$payments = [];
$totalPaidAmount = 0.0;
$paidMonths = [];
$monthPayments = [];

if ($student) {
    try {
        // Only fees for the CURRENT school year are part of this SOA
        $fs = db()->prepare("SELECT sf.*, ft.name AS fee_name FROM student_fees sf JOIN fee_types ft ON ft.id = sf.fee_type_id WHERE sf.student_id = ? AND (sf.school_year = ? OR sf.school_year IS NULL OR sf.school_year = '')");
        $fs->execute([$studentId, $sy]);
        foreach ($fs->fetchAll() as $f) {
            $k = strtolower($f['fee_name']);
            if (!isset($feeMap[$k])) $feeMap[$k] = 0.0;
            $feeMap[$k] += (float) $f['amount'];
        }
        $pb = db()->prepare("SELECT COALESCE(SUM(balance),0) FROM student_fees WHERE student_id = ? AND (school_year IS NOT NULL AND school_year <> '' AND school_year <> ?) AND status <> 'paid'");
        $pb->execute([$studentId, $sy]);
        $previousAccount = max(0.0, (float) $pb->fetchColumn());

        // Fetch all payments made by this student
        $stmtPay = db()->prepare("SELECT p.*, ft.name AS fee_name 
                                  FROM payments p 
                                  JOIN student_fees sf ON p.student_fee_id = sf.id 
                                  JOIN fee_types ft ON sf.fee_type_id = ft.id 
                                  WHERE p.student_id = ? 
                                  ORDER BY p.received_at ASC");
        $stmtPay->execute([$studentId]);
        $payments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($payments as $p) {
            $totalPaidAmount += (float)$p['amount'];
            if ($p['notes'] && preg_match('/Month:\s*([A-Za-z]+)/i', $p['notes'], $matches)) {
                $mName = strtoupper($matches[1]);
                $paidMonths[] = $mName;
                $monthPayments[$mName] = $p;
            }
        }
    } catch (Exception $e) { /* tables missing */ }
}

function soaValue(array $map, array $needles): float {
    foreach ($needles as $n) {
        foreach ($map as $k => $v) {
            if (strpos($k, $n) !== false) return abs((float) $v);
        }
    }
    return 0.0;
}
function soaFmt(float $v): string { return $v > 0 ? number_format($v, 2) : ''; }

// ===== FEES =====
$tuitionFee        = soaValue($feeMap, ['tuition']);
$miscellaneousFee  = soaValue($feeMap, ['miscellaneous', 'misc']);
$otherFee          = soaValue($feeMap, ['others', 'book']);

// ===== DISCOUNTS / SCHOLARSHIPS =====
$earlyBirdDiscount = soaValue($feeMap, ['early bird']);
$academicScholar   = soaValue($feeMap, ['academic scholar']);
$sportsDiscount    = soaValue($feeMap, ['sports']);
$escGrant          = soaValue($feeMap, ['esc grant', 'esc']);
$shsVoucher        = soaValue($feeMap, ['shs voucher', 'shs']);
$tlcaScholar       = soaValue($feeMap, ['tlca scholar', 'tlcm']);
$fullScholar       = soaValue($feeMap, ['full scholar']);

// ===== FORMULA =====
// SUBTOTAL = tuition + miscellaneous + others
$subtotal          = $tuitionFee + $miscellaneousFee + $otherFee;

// SCHOLARSHIP TOTAL = sum of all 7 discounts/grants
$scholarshipTotal  = $earlyBirdDiscount + $academicScholar + $sportsDiscount
                   + $escGrant + $shsVoucher + $tlcaScholar + $fullScholar;

// PAYABLE OF THE YEAR = subtotal - scholarship_total + previous_account
$payableOfYear     = max(0.0, ($subtotal - $scholarshipTotal) + $previousAccount);

// SUB-TOTAL AFTER DISCOUNTS (before adding previous account)
$subTotalNet       = max(0.0, $subtotal - $scholarshipTotal);

// MONTHLY PAYMENT (9 months Jul-Mar)
$monthlyPayment    = $payableOfYear > 0 ? round($payableOfYear / 9, 2) : 0.0;

// Back-compat aliases used in the template
$tuition   = soaFmt($tuitionFee);
$misc      = soaFmt($miscellaneousFee);
$others    = soaFmt($otherFee);
$earlyBird = soaFmt($earlyBirdDiscount);
$academic  = soaFmt($academicScholar);
$sports    = soaFmt($sportsDiscount);
$esc       = soaFmt($escGrant);
$shs       = soaFmt($shsVoucher);
$tlcm      = soaFmt($tlcaScholar);
$full      = soaFmt($fullScholar);
$subTotalGross = $subtotal;
$payable       = $payableOfYear;
$monthlyShare  = $monthlyPayment;

$studentType = $student ? (($student['is_transfer_in'] ?? 0) ? 'Transferee' : 'New') : '';
$scholarshipAvail = '';
if ($escGrant > 0)        $scholarshipAvail = 'ESC GRANTEE';
elseif ($shsVoucher > 0)  $scholarshipAvail = 'SHS VOUCHER';
elseif ($academicScholar > 0) $scholarshipAvail = 'ACADEMIC';
elseif ($fullScholar > 0) $scholarshipAvail = 'FULL SCHOLAR';
elseif ($tlcaScholar > 0) $scholarshipAvail = 'TLCA SCHOLAR';

$months = ['JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER','JANUARY','FEBRUARY','MARCH'];
$gradeLevel = $student['grade_level'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        .soa-page {
            background: #fff;
            padding: 25px 30px;
            max-width: 8.5in;
            width: 100%;
            margin: 20px auto;
            box-shadow: 0 0 8px rgba(0,0,0,0.12);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            box-sizing: border-box;
        }
        .soa-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            letter-spacing: 3px;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .meta-table td { padding: 4px 6px; font-size: 11pt; vertical-align: top; }
        .meta-table .label { font-weight: bold; white-space: nowrap; }
        .meta-table .fill {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 140px;
            padding: 0 6px;
            font-weight: bold;
        }
        .fee-wrap { display: flex; gap: 24px; flex-wrap: wrap; }
        .fee-wrap > div { flex: 1 1 300px; min-width: 280px; }
        table.fee-list { width: 100%; border-collapse: collapse; }
        table.fee-list td { padding: 3px 5px; font-size: 11pt; }
        table.fee-list td.amt {
            width: 110px;
            text-align: right;
            border-bottom: 1px solid #000;
            font-weight: bold;
            white-space: nowrap;
        }
        table.fee-list tr.sub td { font-weight: bold; border-top: 1px solid #000; }
        table.fee-list tr.sub td.amt { background: #fff59d; }
        table.sched { width: 100%; border-collapse: collapse; }
        table.sched th { background: #f7dc6f; font-weight: bold; text-align: center; padding: 4px 6px; border-bottom: 1px solid #000; }
        table.sched td { padding: 3px 6px; font-size: 11pt; }
        table.sched td.month { font-weight: bold; }
        table.sched td.amt { text-align: right; border-bottom: 1px dotted #000; white-space: nowrap; }
        .payable-box {
            background: #fff59d;
            padding: 10px 16px;
            border: 1.5px solid #000;
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr auto 1fr auto;
            gap: 10px 20px;
            align-items: center;
            font-weight: bold;
            font-size: 12pt;
        }
        .payable-box .val { text-align: right; min-width: 100px; }
        .sig-row { display: flex; justify-content: space-between; gap: 30px; margin-top: 50px; flex-wrap: wrap; }
        .sig-row .box { flex: 1 1 250px; text-align: center; }
        .sig-row .box .line { border-top: 1px solid #000; padding-top: 4px; font-size: 10pt; }
        .foot-note { text-align: center; font-style: italic; font-size: 9pt; margin-top: 18px; }

        table.ledger-list { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.ledger-list th { background: #f2f2f2; font-weight: bold; text-align: left; padding: 6px; border: 1px solid #000; font-size: 10pt; }
        table.ledger-list td { padding: 5px 6px; border: 1px solid #000; font-size: 9.5pt; }
        table.ledger-list td.amt { text-align: right; font-weight: bold; }

        @media (max-width: 600px) {
            .soa-page { padding: 15px; }
            .payable-box { grid-template-columns: 1fr auto; font-size: 11pt; }
            .meta-table td { display: block; text-align: left !important; }
        }

        @media print {
            @page { size: Letter; margin: 0.4in; }
            body { background: #fff !important; }
            .no-print, .sidebar, #mobileToggle, .topbar, .mobile-toggle { display: none !important; }
            .soa-page { box-shadow: none; margin: 0; max-width: 100%; padding: 0; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .container-fluid { padding: 0 !important; }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<div class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>
    <div class="container-fluid p-4">

        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <div>
                <h2 class="mb-0"><i class="bi bi-receipt me-2"></i>Statement of Account</h2>
                <small class="text-muted">View and print student statement of account</small>
            </div>
            <div>
                <?php if ($role !== 'student'): ?>
                    <a href="finance.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Finance
                    </a>
                <?php endif; ?>
                <?php if ($student): ?>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Print
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($role !== 'student'): ?>
        <div class="card mb-3 no-print">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-bold">Select Student</label>
                        <select name="id" class="form-select" required>
                            <option value="">-- Choose a student --</option>
                            <?php foreach ($allStudents as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $studentId === (int)$s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?><?= !empty($s['grade_level']) ? ' — Grade '.htmlspecialchars((string)$s['grade_level']) : '' ?><?= !empty($s['empidno']) ? ' ('.htmlspecialchars($s['empidno']).')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> View SOA
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$student): ?>
            <?php if ($role !== 'student'): ?>
                <div class="alert alert-info no-print">Please select a student to view the Statement of Account.</div>
            <?php else: ?>
                <div class="alert alert-warning no-print">Student record not found.</div>
            <?php endif; ?>
        <?php else: ?>

        <!-- ========== SOA PAGE ========== -->
        <div class="soa-page">
            <div class="soa-title">STATEMENT OF ACCOUNT</div>

            <table class="meta-table">
                <tr>
                    <td><span class="label">NAME:</span> <span class="fill" style="min-width:260px"><?= htmlspecialchars(strtoupper(trim(($student['name'] ?? '') . ' ' . ($student['middle_name'] ?? '')))) ?></span></td>
                    <td style="text-align:right">
                        <span class="label">GRADE LEVEL:</span>
                        <span class="fill" style="min-width:60px"><?= htmlspecialchars((string)$gradeLevel) ?></span>
                    </td>
                </tr>
                <tr>
                    <td><span class="label">SCHOOL YEAR:</span> <span class="fill"><?= htmlspecialchars($sy) ?></span></td>
                    <td style="text-align:right">
                        <span class="label">SCHOLARSHIP AVAIL:</span>
                        <span class="fill"><?= htmlspecialchars($scholarshipAvail) ?></span>
                    </td>
                </tr>
                <tr>
                    <td><span class="label">STUDENT TYPE:</span> <span class="fill"><?= htmlspecialchars($studentType) ?></span></td>
                    <td></td>
                </tr>
            </table>

            <div class="fw-bold mt-2">FEE DETAILS:</div>

            <div class="fee-wrap">
                <div>
                    <table class="fee-list">
                        <tr><td>Tuition Fee</td><td class="amt"><?= $tuition ?: '&nbsp;' ?></td></tr>
                        <tr><td>Miscellaneous</td><td class="amt"><?= $misc ?: '&nbsp;' ?></td></tr>
                        <tr><td>Others</td><td class="amt"><?= $others ?: '&nbsp;' ?></td></tr>
                        <tr class="sub"><td>Sub_total</td><td class="amt"><?= $subTotalGross > 0 ? number_format($subTotalGross,2) : '&nbsp;' ?></td></tr>
                        <tr><td colspan="2" style="font-weight:bold;padding-top:4px">Less:</td></tr>
                        <tr><td>Early Bird Discount</td><td class="amt"><?= $earlyBird ?: '&nbsp;' ?></td></tr>
                        <tr><td>Academic Scholar</td><td class="amt"><?= $academic ?: '&nbsp;' ?></td></tr>
                        <tr><td>Sports Discount</td><td class="amt"><?= $sports ?: '&nbsp;' ?></td></tr>
                        <tr><td>ESC GRANT</td><td class="amt"><?= $esc ?: '&nbsp;' ?></td></tr>
                        <tr><td>SHS VOUCHER</td><td class="amt"><?= $shs ?: '&nbsp;' ?></td></tr>
                        <tr><td>TLCA SCHOLAR</td><td class="amt"><?= $tlcm ?: '&nbsp;' ?></td></tr>
                        <tr><td>FULL SCHOLAR</td><td class="amt"><?= $full ?: '&nbsp;' ?></td></tr>
                        <tr class="sub"><td>SUB-TOTAL</td><td class="amt"><?= $subTotalNet > 0 ? number_format($subTotalNet,2) : '&nbsp;' ?></td></tr>
                        <tr><td>ADD: PREVIOUS ACCOUNT</td><td class="amt"><?= $previousAccount > 0 ? number_format($previousAccount,2) : '&nbsp;' ?></td></tr>
                    </table>
                </div>
                <div>
                    <table class="sched">
                        <thead><tr><th colspan="2">MONTHLY PAYMENT SCHEDULE</th></tr></thead>
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
                                    <td class="amt" style="font-size: 9.5pt; font-weight: normal; text-align: right;">
                                         <?php if (in_array($m, $paidMonths)): ?>
                                             <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> PAID</span>
                                             <div style="font-size: 7.5pt; color: #555; line-height: 1.1; margin-top: 2px;">
                                                 OR: <?= htmlspecialchars($monthPayments[$m]['or_number'] ?? 'N/A') ?><br>
                                                 Ref: <?= htmlspecialchars($monthPayments[$m]['reference_number']) ?><br>
                                                 <?= date('Y-m-d', strtotime($monthPayments[$m]['received_at'])) ?>
                                             </div>
                                         <?php else: ?>
                                             ₱<?= number_format($installmentList[$idx], 2) ?>
                                         <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========== PAYMENT HISTORY LEDGER ========== -->
            <div class="fw-bold mt-4" style="font-size: 11pt; border-bottom: 2px solid #000; padding-bottom: 4px; margin-bottom: 8px;">
                PAYMENT HISTORY / LEDGER:
            </div>
            <?php if (!empty($payments)): ?>
            <table class="ledger-list">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference Number</th>
                        <th>OR Number</th>
                        <th>Particulars/Fee</th>
                        <th>Method</th>
                        <th style="text-align:right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?= date('Y-m-d h:i A', strtotime($p['received_at'])) ?></td>
                            <td style="font-family: monospace; color: #4b5563;"><?= htmlspecialchars($p['reference_number'] ?? 'N/A') ?></td>
                            <td style="font-weight: bold; color: #2563eb;"><?= htmlspecialchars($p['or_number'] ?? 'N/A') ?></td>
                            <td>
                                <?= htmlspecialchars($p['fee_name']) ?>
                                <?php if (!empty($p['notes'])): ?>
                                    <br><small style="color: #6b7280; font-style: italic; font-size: 8.5pt;">(<?= htmlspecialchars($p['notes']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td style="text-transform: uppercase; font-size: 8.5pt; font-weight: bold;"><?= htmlspecialchars($p['payment_method']) ?></td>
                            <td class="amt">₱<?= number_format((float)$p['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="border: 1px dashed #000; text-align: center; color: #666; padding: 12px; font-size: 10pt; margin-top: 10px; margin-bottom: 15px;">
                <i class="bi bi-info-circle me-1"></i> No payments have been recorded for this school year yet.
            </div>
            <?php endif; ?>

            <div class="payable-box" style="margin-top: 16px;">
                <span>PAYABLE OF THE YEAR</span>
                <span class="val">₱<?= number_format($payable, 2) ?></span>
                <span>TOTAL PAID TO DATE</span>
                <span class="val" style="color: #198754;">- ₱<?= number_format($totalPaidAmount, 2) ?></span>
                <span>NET BALANCE DUE</span>
                <span class="val text-danger" style="font-size: 13pt; font-weight: bold;">
                    ₱<?= number_format(max(0.0, $payable - $totalPaidAmount), 2) ?>
                </span>
                <span>&nbsp;</span>
                <span class="val">&nbsp;</span>
            </div>

            <div class="sig-row">
                <div class="box">
                    <div style="height:30px"></div>
                    <div class="line">Enrolled by (Signature over Printed Name)</div>
                </div>
                <div class="box">
                    <div style="height:30px"></div>
                    <div class="line">Parent / Guardian (Signature over Printed Name)</div>
                </div>
            </div>

            <div class="foot-note">
                Note: 1 copy for school file, 1 copy for the student, 1 copy for the registrar
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
