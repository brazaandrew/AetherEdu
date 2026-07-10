<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();
$user = requireRole(['admin','registrar']);

$studentId = (int)($_GET['id'] ?? 0);
if (!$studentId) { header('Location: school-forms.php'); exit; }

$db = db();

// Self-healing: create SF tables if missing
$db->exec("CREATE TABLE IF NOT EXISTS student_behavior (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    school_year VARCHAR(20) NOT NULL DEFAULT '2024-2025',
    quarter TINYINT NOT NULL,
    maka_diyos VARCHAR(5) DEFAULT NULL,
    makatao VARCHAR(5) DEFAULT NULL,
    makakalikasan VARCHAR(5) DEFAULT NULL,
    makabansa VARCHAR(5) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_behavior (student_id, school_year, quarter)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$db->exec("CREATE TABLE IF NOT EXISTS generated_school_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    form_type ENUM('SF9','SF10') NOT NULL,
    school_year VARCHAR(20),
    generated_by INT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    verification_code VARCHAR(64) UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Seed school settings defaults
$db->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('school_name',      'The Light Christian Academy'),
    ('school_id',        ''),
    ('school_division',  ''),
    ('school_region',    'Region IV-A (CALABARZON)'),
    ('school_year',      '2024-2025'),
    ('school_head_name', ''),
    ('registrar_name',   ''),
    ('school_logo',      'assets/images/school-logo.png')");

// --- Fetch student ---
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
if (!$student) { header('Location: school-forms.php'); exit; }

// --- School settings ---
$settingsStmt = $db->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
foreach ($settingsStmt->fetchAll() as $r) { $settings[$r['setting_key']] = $r['setting_value']; }
$schoolName     = $settings['school_name']      ?? 'The Light Christian Academy';
$schoolId       = $settings['school_id']        ?? '';
$schoolDivision = $settings['school_division']  ?? '';
$schoolRegion   = $settings['school_region']    ?? '';
$schoolYear     = $settings['school_year']      ?? '2024-2025';
$schoolHead     = $settings['school_head_name'] ?? '';
$registrarName  = $settings['registrar_name']   ?? '';
$schoolLogo     = $settings['school_logo']      ?? 'assets/images/school-logo.png';

// --- Grades per subject ---
$gradesStmt = $db->prepare("
    SELECT s.name AS subject_name, s.code,
           g.q1_grade, g.q2_grade, g.q3_grade, g.q4_grade, g.average_grade, g.final_grade
    FROM grades g
    JOIN subjects s ON s.id = g.subject_id
    WHERE g.student_id = ?
    ORDER BY s.name ASC
");
$gradesStmt->execute([$studentId]);
$grades = $gradesStmt->fetchAll();

// General Average
$totalFinal = 0; $countSubjects = 0;
foreach ($grades as $g) {
    if ($g['average_grade'] !== null) { $totalFinal += (float)$g['average_grade']; $countSubjects++; }
}
$genAvg = $countSubjects > 0 ? round($totalFinal / $countSubjects, 2) : null;
$promoted = $genAvg !== null ? ($genAvg >= 75 ? 'PROMOTED' : 'RETAINED') : '—';

// --- Attendance summary ---
try {
    $attendStmt = $db->prepare("
        SELECT
            COUNT(DISTINCT date) AS total_days,
            SUM(status='present') AS present_days,
            SUM(status='absent')  AS absent_days,
            SUM(status='late')    AS late_days
        FROM student_attendance
        WHERE student_id = ?
    ");
    $attendStmt->execute([$studentId]);
    $attend = $attendStmt->fetch();
} catch (Exception $e) {
    $attend = ['total_days' => 0, 'present_days' => 0, 'absent_days' => 0, 'late_days' => 0];
}

// --- Behavior / Core Values ---
try {
    $behaviorStmt = $db->prepare("SELECT * FROM student_behavior WHERE student_id = ? ORDER BY quarter ASC");
    $behaviorStmt->execute([$studentId]);
    $behaviorRows = $behaviorStmt->fetchAll();
} catch (Exception $e) {
    $behaviorRows = [];
}
$behavior = [];
foreach ($behaviorRows as $b) { $behavior[$b['quarter']] = $b; }

// --- Adviser (first teacher assigned to any subject student is enrolled in) ---
$adviserStmt = $db->prepare("
    SELECT u.name FROM users u
    JOIN folder_teacher ft ON ft.teacher_empidno = u.empidno
    JOIN enrollments e ON e.subject_id = ft.subject_id
    WHERE e.student_id = ?
    LIMIT 1
");
$adviserStmt->execute([$studentId]);
$adviserName = $adviserStmt->fetchColumn() ?: '';

// Audit log
saveAudit($user['id'], 'generate', 'sf9', $studentId, ['school_year' => $schoolYear, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);

// Verification code
$verCode = strtoupper(substr(md5($studentId . 'SF9' . $schoolYear . time()), 0, 12));

// Helper
function rating($r) { return htmlspecialchars($r ?? '—'); }
function gradeCell($v) {
    if ($v === null) return '<td>—</td>';
    $cls = (float)$v >= 75 ? 'text-success fw-bold' : 'text-danger fw-bold';
    return '<td class="' . $cls . '">' . number_format((float)$v, 0) . '</td>';
}

$nameParts = explode(',', $student['name'], 2);
$lastName = trim($nameParts[0] ?? $student['name']);
$firstName = trim($nameParts[1] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SF9 – <?= htmlspecialchars($student['name']) ?> – eLMS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/style.css?v=2028">
<link rel="stylesheet" href="assets/css/school-forms.css?v=1">
</head>
<body>
<?php include __DIR__.'/includes/sidebar.php'; ?>
<div class="main-content">
<?php $pageTitle='SF9 – Progress Report Card'; include __DIR__.'/includes/topbar.php'; ?>
<div class="sf-preview-wrapper">

    <!-- Toolbar (hidden on print) -->
    <div class="sf-toolbar d-print-none">
        <a href="school-forms.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <button class="btn btn-primary btn-sm ms-auto" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print / Save PDF</button>
        <a href="sf10.php?id=<?= $studentId ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-journal-richtext me-1"></i>View SF10</a>
    </div>

    <!-- ========================================================
         SF9 FORM — Official DepEd Layout
         ======================================================== -->
    <div class="sf-page" id="sf9">

        <!-- DepEd Header -->
        <div class="sf-deped-header">
            <img src="<?= htmlspecialchars($schoolLogo) ?>" class="logo" alt="School Logo" onerror="this.style.display='none'">
            <div class="center-text">
                <p style="font-size:7pt;">Republic of the Philippines</p>
                <p style="font-size:7pt;">Department of Education</p>
                <p style="font-weight:bold;font-size:8pt;"><?= htmlspecialchars($schoolRegion) ?></p>
                <p style="font-size:7pt;">Division of <?= htmlspecialchars($schoolDivision) ?></p>
                <p style="font-weight:bold;font-size:9pt;"><?= htmlspecialchars($schoolName) ?></p>
            </div>
            <div style="text-align:right; min-width:80px;">
                <p style="font-size:6.5pt; margin:0;">Form No. SF9</p>
                <p style="font-size:6.5pt; margin:0; color:#555;">Generated: <?= date('m/d/Y') ?></p>
                <p style="font-size:6pt; margin:0; color:#777;word-break:break-all;"><?= $verCode ?></p>
            </div>
        </div>

        <div class="sf-form-title">SCHOOL FORM 9 (SF9)</div>
        <div class="sf-form-subtitle">Learner's Progress Report Card</div>

        <!-- ── LEARNER INFORMATION ── -->
        <div class="sf-section-title">I. LEARNER'S INFORMATION</div>
        <table class="sf-info-table">
            <tr>
                <td class="label">LRN</td>
                <td class="value" colspan="3"><?= htmlspecialchars($student['lrn_number'] ?: '—') ?></td>
                <td class="label">School Year</td>
                <td class="value"><?= htmlspecialchars($schoolYear) ?></td>
            </tr>
            <tr>
                <td class="label">Last Name</td>
                <td class="value"><?= htmlspecialchars($lastName) ?></td>
                <td class="label">First Name</td>
                <td class="value"><?= htmlspecialchars($firstName) ?></td>
                <td class="label">Middle Name</td>
                <td class="value"><?= htmlspecialchars($student['middle_name'] ?? '—') ?></td>
            </tr>
            <tr>
                <td class="label">Date of Birth</td>
                <td class="value"><?= $student['date_of_birth'] ? date('m/d/Y', strtotime($student['date_of_birth'])) : '—' ?></td>
                <td class="label">Sex</td>
                <td class="value"><?= htmlspecialchars($student['gender'] ?? '—') ?></td>
                <td class="label">Age</td>
                <td class="value"><?= $student['date_of_birth'] ? (int)date_diff(date_create($student['date_of_birth']), date_create('today'))->y : '—' ?></td>
            </tr>
            <tr>
                <td class="label">Grade Level</td>
                <td class="value">—</td>
                <td class="label">Section</td>
                <td class="value">—</td>
                <td class="label">Adviser</td>
                <td class="value"><?= htmlspecialchars($adviserName) ?></td>
            </tr>
            <tr>
                <td class="label">School</td>
                <td class="value" colspan="3"><?= htmlspecialchars($schoolName) ?></td>
                <td class="label">School ID</td>
                <td class="value"><?= htmlspecialchars($schoolId) ?></td>
            </tr>
        </table>

        <!-- ── QUARTERLY GRADES ── -->
        <div class="sf-section-title">II. QUARTERLY GRADES</div>
        <?php if(empty($grades)): ?>
        <p style="font-size:7.5pt; color:#555; padding:4px;">No grade records found for this learner.</p>
        <?php else: ?>
        <table class="sf-grades-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width:38%; text-align:left; padding-left:4px;">Learning Areas / Subjects</th>
                    <th colspan="4">Quarterly Rating</th>
                    <th rowspan="2">Final Rating</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr>
                    <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($grades as $g):
                $final = $g['average_grade'] ?? $g['final_grade'];
                $passed = ($final !== null && (float)$final >= 75);
                $rowClass = $final !== null ? ($passed ? 'passed' : 'failed') : '';
            ?>
            <tr class="<?= $rowClass ?>">
                <td class="subject-name"><?= htmlspecialchars($g['subject_name']) ?></td>
                <?= gradeCell($g['q1_grade']) ?>
                <?= gradeCell($g['q2_grade']) ?>
                <?= gradeCell($g['q3_grade']) ?>
                <?= gradeCell($g['q4_grade']) ?>
                <?= gradeCell($final) ?>
                <td><?= $final !== null ? ($passed ? 'PASSED' : 'FAILED') : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td class="subject-name" colspan="5"><strong>GENERAL AVERAGE</strong></td>
                    <td><strong><?= $genAvg !== null ? number_format($genAvg, 2) : '—' ?></strong></td>
                    <td><strong><?= $promoted ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>

        <!-- ── ATTENDANCE ── -->
        <div class="sf-section-title">III. ATTENDANCE SUMMARY</div>
        <table class="sf-attend-table">
            <thead>
                <tr><th>School Days</th><th>No. of Days Present</th><th>No. of Days Absent</th><th>No. of Times Tardy/Late</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= (int)($attend['total_days'] ?? 0) ?></td>
                    <td><?= (int)($attend['present_days'] ?? 0) ?></td>
                    <td><?= (int)($attend['absent_days'] ?? 0) ?></td>
                    <td><?= (int)($attend['late_days'] ?? 0) ?></td>
                </tr>
            </tbody>
        </table>

        <!-- ── CORE VALUES ── -->
        <div class="sf-section-title">IV. CORE VALUES (Behavioral Rating)</div>
        <p class="sf-rating-legend"><strong>Rating Scale:</strong> O – Outstanding | VS – Very Satisfactory | S – Satisfactory | F – Fairly Satisfactory | D – Did Not Meet Expectations</p>
        <table class="sf-behavior-table">
            <thead>
                <tr><th style="text-align:left; padding-left:4px;">Core Values</th><th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th></tr>
            </thead>
            <tbody>
                <?php
                $coreValues = [
                    'maka_diyos'    => 'Maka-Diyos (Spirituality)',
                    'makatao'       => 'Makatao (Humanity)',
                    'makakalikasan' => 'Makakalikasan (Environment)',
                    'makabansa'     => 'Makabansa (Patriotism)',
                ];
                foreach($coreValues as $key => $label):
                ?>
                <tr>
                    <td class="core-label"><?= $label ?></td>
                    <?php for($q=1;$q<=4;$q++): ?>
                    <td><?= rating($behavior[$q][$key] ?? null) ?></td>
                    <?php endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ── REMARKS ── -->
        <div class="sf-section-title">V. REMARKS</div>
        <div class="sf-remarks">
            <?php if($genAvg !== null): ?>
                This learner has achieved a General Average of <strong><?= number_format($genAvg,2) ?></strong> for School Year <?= htmlspecialchars($schoolYear) ?> and is <strong><?= $promoted ?></strong> to the next grade level.
            <?php else: ?>
                &nbsp;
            <?php endif; ?>
        </div>

        <!-- ── SIGNATORIES ── -->
        <div class="sf-section-title">VI. CERTIFICATION</div>
        <div class="sf-signatories">
            <div class="sf-signatory-box">
                <div class="sf-signatory-line"></div>
                <div class="sf-signatory-label"><?= htmlspecialchars($adviserName ?: 'Class Adviser') ?></div>
                <div class="sf-signatory-role">Class Adviser</div>
                <div style="margin-top:4px; font-size:6.5pt;">Date: _______________</div>
            </div>
            <div class="sf-signatory-box">
                <div class="sf-signatory-line"></div>
                <div class="sf-signatory-label"><?= htmlspecialchars($registrarName ?: 'School Registrar') ?></div>
                <div class="sf-signatory-role">Registrar</div>
                <div style="margin-top:4px; font-size:6.5pt;">Date: _______________</div>
            </div>
            <div class="sf-signatory-box">
                <div class="sf-signatory-line"></div>
                <div class="sf-signatory-label"><?= htmlspecialchars($schoolHead ?: 'School Head') ?></div>
                <div class="sf-signatory-role">School Head / Principal</div>
                <div style="margin-top:4px; font-size:6.5pt;">Date: _______________</div>
            </div>
        </div>

        <!-- ── FOOTER ── -->
        <div class="sf-footer">
            <span><?= htmlspecialchars($schoolName) ?> | School ID: <?= htmlspecialchars($schoolId) ?></span>
            <span>Verification Code: <strong><?= $verCode ?></strong> | Generated: <?= date('F d, Y \a\t h:i A') ?></span>
        </div>

    </div><!-- end .sf-page -->
</div><!-- end .sf-preview-wrapper -->
</div><!-- end .main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Keyboard shortcut: Ctrl+P or Cmd+P auto-triggers browser print
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') { /* browser handles it */ }
});
</script>
</body>
</html>
