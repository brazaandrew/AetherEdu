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

// Allow registrar, admin, and the student themselves
$studentId = (int) ($_GET['id'] ?? 0);
if ($studentId <= 0) {
    http_response_code(400);
    exit('Invalid student id');
}

if (!in_array($user['role'], ['registrar', 'admin']) && (int)$user['id'] !== $studentId) {
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

// Build name
$fullName = studentFullName($student);

$gradeLevel = $student['grade_level'] ?? '';
$section = $student['section'] ?? '';

// School year — try to derive from current month (June starts new SY in PH)
$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
if ($currentMonth >= 6) {
    $sy = $currentYear . '-' . ($currentYear + 1);
} else {
    $sy = ($currentYear - 1) . '-' . $currentYear;
}

// Date enrolled
$enrolledOn = !empty($student['created_at']) ? date('F j, Y', strtotime($student['created_at'])) : '';

// Today's date
$today = date('F j, Y');

// School details (TLCA branding)
$schoolName  = 'THE LIGHT CHRISTIAN ACADEMY';
$schoolDiv   = 'Department of Education';
$schoolDist  = 'Private Educational Institution';
$schoolAddr  = 'Philippines';

// Logo path
$logoFile = __DIR__ . '/image/new.png';
$logoSrc = file_exists($logoFile) ? 'image/new.png' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certification of Enrollment - <?= htmlspecialchars($fullName) ?></title>
    <style>
        @page { size: Letter; margin: 0.75in; }
        * { box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 13pt;
            color: #000;
            margin: 0;
            padding: 30px;
            background: #f0f0f0;
        }
        .page {
            width: 8.5in;
            min-height: 11in;
            background: #fff;
            margin: 0 auto;
            padding: 0.75in;
            box-shadow: 0 0 8px rgba(0,0,0,0.15);
            position: relative;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 30px;
        }
        .header .logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }
        .header .school-info {
            margin: 0 25px;
            line-height: 1.3;
        }
        .header .school-info .div { font-size: 12pt; }
        .header .school-info .school-name {
            font-weight: bold;
            font-size: 14pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .school-info .addr { font-size: 11pt; font-style: italic; }
        .date-line {
            text-align: right;
            margin-top: 40px;
            margin-bottom: 30px;
        }
        .date-line .underline {
            display: inline-block;
            min-width: 180px;
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 0 8px;
        }
        h1.title {
            text-align: center;
            font-size: 22pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 30px 0 40px 0;
        }
        .body-text {
            line-height: 2.2;
            text-align: justify;
            text-indent: 0.5in;
            font-size: 13pt;
        }
        .body-text .fill {
            display: inline-block;
            min-width: 240px;
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 0 6px;
            font-weight: bold;
        }
        .body-text .fill-sm {
            display: inline-block;
            min-width: 70px;
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 0 6px;
            font-weight: bold;
        }
        .body-text .fill-md {
            display: inline-block;
            min-width: 160px;
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 0 6px;
            font-weight: bold;
        }
        .signature-block {
            margin-top: 70px;
            text-align: right;
            padding-right: 40px;
        }
        .signature-block .truly { margin-bottom: 60px; }
        .signature-line {
            display: inline-block;
            min-width: 280px;
            border-top: 1px solid #000;
            padding-top: 4px;
            text-align: center;
        }
        .noted-block {
            margin-top: 80px;
            font-size: 12pt;
        }
        .noted-block .name {
            font-weight: bold;
            text-transform: uppercase;
        }
        .noted-block .role { padding-left: 20px; font-style: italic; }

        .print-bar {
            max-width: 8.5in;
            margin: 0 auto 12px auto;
            text-align: right;
        }
        .btn {
            background: #0d6efd;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 6px;
            text-decoration: none;
            display: inline-block;
        }
        .btn.secondary { background: #6c757d; }

        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; margin: 0; padding: 0.75in; }
            .print-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <a href="javascript:window.print()" class="btn">🖨 Print</a>
    <a href="student-list.php" class="btn secondary">← Back</a>
</div>

<div class="page">
    <div class="header">
        <?php if ($logoSrc): ?>
            <img src="<?= $logoSrc ?>" alt="School Logo" class="logo">
        <?php endif; ?>
        <div class="school-info">
            <div class="div"><?= htmlspecialchars($schoolDiv) ?></div>
            <div class="school-name"><?= htmlspecialchars($schoolName) ?></div>
            <div class="addr"><?= htmlspecialchars($schoolDist) ?></div>
            <div class="addr"><?= htmlspecialchars($schoolAddr) ?></div>
        </div>
        <?php if ($logoSrc): ?>
            <img src="<?= $logoSrc ?>" alt="School Logo" class="logo">
        <?php endif; ?>
    </div>

    <div class="date-line">
        Date : <span class="underline"><?= htmlspecialchars($today) ?></span>
    </div>

    <h1 class="title">CERTIFICATION</h1>

    <div class="body-text">
        This is to certify that
        <span class="fill"><?= htmlspecialchars(strtoupper($fullName)) ?></span>
        of Grade <span class="fill-sm"><?= htmlspecialchars((string)$gradeLevel) ?></span>
        section <span class="fill-sm"><?= htmlspecialchars((string)$section) ?></span>
        was enrolled in this school from
        <span class="fill-md"><?= htmlspecialchars($enrolledOn) ?></span>
        SY <?= htmlspecialchars($sy) ?>.
    </div>

    <div class="body-text" style="margin-top: 25px;">
        This certification is issued upon request for enrolment purposes.
    </div>

    <div class="signature-block">
        <div class="truly">Very truly yours,</div>
        <div class="signature-line">Academic &ndash;in&ndash;Charge</div>
    </div>

    <div class="noted-block">
        <div>Noted:</div>
        <div style="margin-top: 30px;">
            <div class="name">FLORENCE A. LIMASAC, PhD</div>
            <div class="role">School Principal</div>
          
        </div>
    </div>
</div>

<script>
// Optional: auto-open print dialog if ?print=1
const params = new URLSearchParams(window.location.search);
if (params.get('print') === '1') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
}
</script>

</body>
</html>
