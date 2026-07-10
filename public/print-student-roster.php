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

if (!in_array($user['role'], ['registrar', 'admin'], true)) {
    header('Location: dashboard.php');
    exit;
}

$gradeFilter = trim((string) ($_GET['grade'] ?? ''));

$where = ["role = 'student'", 'archived = 0'];
$params = [];
if ($gradeFilter !== '') {
    if ($gradeFilter === 'Unassigned') {
        $where[] = "(grade_level IS NULL OR TRIM(grade_level) = '')";
    } else {
        $where[] = 'grade_level = ?';
        $params[] = $gradeFilter;
    }
}
$whereSql = implode(' AND ', $where);

$stmt = db()->prepare(
    "SELECT id, empidno, name, middle_name, grade_level, gender, lrn_number, contact_number, email, created_at
     FROM users WHERE {$whereSql}
     ORDER BY
       CASE
         WHEN grade_level IS NULL OR TRIM(grade_level) = '' THEN 999
         ELSE CAST(grade_level AS UNSIGNED)
       END,
       name"
);
$stmt->execute($params);
$allStudents = $stmt->fetchAll();

$gradeOrder = ['7', '8', '9', '10', '11', '12', 'Unassigned'];
$studentsByGrade = [];
foreach ($allStudents as $row) {
    $grade = trim((string) ($row['grade_level'] ?? ''));
    $gradeKey = $grade === '' ? 'Unassigned' : $grade;
    $studentsByGrade[$gradeKey][] = $row;
}

$orderedGrades = [];
foreach ($gradeOrder as $g) {
    if (!empty($studentsByGrade[$g])) {
        $orderedGrades[] = $g;
    }
}
foreach (array_keys($studentsByGrade) as $g) {
    $g = (string) $g;
    if (!in_array($g, $orderedGrades, true)) {
        $orderedGrades[] = $g;
    }
}

$totalStudents = count($allStudents);

$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$schoolYear = $currentMonth >= 6
    ? $currentYear . '-' . ($currentYear + 1)
    : ($currentYear - 1) . '-' . $currentYear;

$schoolName = 'THE LIGHT CHRISTIAN ACADEMY';
$schoolSub = 'Department of Education · Private Educational Institution';

$logoFile = __DIR__ . '/image/new.png';
$logoSrc = file_exists($logoFile) ? 'image/new.png' : '';

function rosterGradeLabel(string|int $grade): string
{
    $g = (string) $grade;
    return $g === 'Unassigned' ? 'Unassigned / No Grade' : 'Grade ' . $g;
}

/** @return array{Male: list<array>, Female: list<array>, Other: list<array>} */
function rosterSplitByGender(array $students): array
{
    $groups = ['Male' => [], 'Female' => [], 'Other' => []];
    foreach ($students as $student) {
        $gender = strtolower(trim((string) ($student['gender'] ?? '')));
        if ($gender === 'male' || $gender === 'm') {
            $groups['Male'][] = $student;
        } elseif ($gender === 'female' || $gender === 'f') {
            $groups['Female'][] = $student;
        } else {
            $groups['Other'][] = $student;
        }
    }
    foreach ($groups as &$list) {
        usort($list, static fn (array $a, array $b): int => strcasecmp(studentFullName($a), studentFullName($b)));
    }
    unset($list);

    return $groups;
}

function rosterGradeStudents(array $studentsByGrade, string|int $grade): array
{
    $key = (string) $grade;
    if (isset($studentsByGrade[$key])) {
        return $studentsByGrade[$key];
    }
    if (is_numeric($key) && isset($studentsByGrade[(int) $key])) {
        return $studentsByGrade[(int) $key];
    }

    return [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Roster by Grade - <?= htmlspecialchars($schoolName) ?></title>
    <style>
        @page { size: Letter; margin: 0.5in; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 16px;
            background: #f0f0f0;
        }
        .no-print {
            text-align: center;
            margin-bottom: 16px;
        }
        .no-print a,
        .no-print button {
            display: inline-block;
            margin: 4px 6px;
            padding: 10px 18px;
            font-size: 11pt;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-back { background: #6c757d; color: #fff; }
        .btn-print { background: #3182ce; color: #fff; }
        .page {
            max-width: 8.5in;
            margin: 0 auto;
            background: #fff;
            padding: 0.45in 0.5in;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.12);
        }
        @media print {
            body { background: #fff; padding: 0; }
            .page { box-shadow: none; max-width: none; padding: 0; }
            .no-print { display: none !important; }
            .grade-section { page-break-inside: avoid; }
        }
        .school-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .school-header img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .school-header .mid { text-align: center; line-height: 1.25; }
        .school-header .name {
            font-weight: bold;
            font-size: 14pt;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .school-header .sub { font-size: 9pt; color: #333; }
        h1.doc-title {
            text-align: center;
            font-size: 13pt;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0 0 10px;
            text-decoration: underline;
        }
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px 16px;
            font-size: 9pt;
            margin-bottom: 14px;
            padding: 6px 10px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-row strong { font-weight: 700; }
        .grade-section { margin-bottom: 18px; }
        .grade-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1a365d;
            color: #fff;
            padding: 6px 10px;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 0;
        }
        .grade-heading .count {
            font-weight: normal;
            font-size: 9pt;
            opacity: 0.9;
        }
        table.roster {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        table.roster th,
        table.roster td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9pt;
            vertical-align: top;
        }
        table.roster th {
            background: #edf2f7;
            font-weight: bold;
            text-align: left;
        }
        table.roster td.num,
        table.roster th.num { width: 32px; text-align: center; }
        table.roster td.id { width: 100px; white-space: nowrap; }
        table.roster td.lrn { width: 95px; }
        .gender-subheading {
            background: #e2e8f0;
            color: #1a365d;
            padding: 5px 10px;
            font-size: 9pt;
            font-weight: bold;
            margin: 10px 0 4px;
            border-left: 4px solid #3182ce;
        }
        .gender-subheading:first-of-type { margin-top: 6px; }
        table.roster + table.roster { margin-top: 0; }
        .empty-msg {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            font-size: 11pt;
        }
        .signatures {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            gap: 24px;
            font-size: 9pt;
        }
        .signatures .sig-block {
            flex: 1;
            text-align: center;
        }
        .signatures .line {
            border-top: 1px solid #000;
            margin-top: 36px;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="student-list.php" class="btn-back">← Back to Student List</a>
        <button type="button" class="btn-print" onclick="window.print()">Print Roster</button>
    </div>

    <div class="page">
        <header class="school-header">
            <?php if ($logoSrc): ?>
            <img src="<?= htmlspecialchars($logoSrc) ?>" alt="School Logo">
            <?php endif; ?>
            <div class="mid">
                <div class="name"><?= htmlspecialchars($schoolName) ?></div>
                <div class="sub"><?= htmlspecialchars($schoolSub) ?></div>
            </div>
            <?php if ($logoSrc): ?>
            <img src="<?= htmlspecialchars($logoSrc) ?>" alt="" aria-hidden="true" style="visibility:hidden;width:72px;height:72px;">
            <?php endif; ?>
        </header>

        <h1 class="doc-title">Masterlist of Enrolled Students</h1>

        <div class="meta-row">
            <span><strong>School Year:</strong> <?= htmlspecialchars($schoolYear) ?></span>
            <span><strong>Date Printed:</strong> <?= date('F j, Y') ?></span>
            <span><strong>Total Students:</strong> <?= $totalStudents ?></span>
            <?php if ($gradeFilter !== ''): ?>
            <span><strong>Filter:</strong> <?= htmlspecialchars(rosterGradeLabel($gradeFilter === 'Unassigned' ? 'Unassigned' : $gradeFilter)) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($totalStudents === 0): ?>
        <p class="empty-msg">No students found for this report.</p>
        <?php else: ?>
            <?php
            $genderSectionTitles = [
                'Male' => 'Male (Alphabetical List)',
                'Female' => 'Female (Alphabetical List)',
                'Other' => 'Not Specified (Alphabetical List)',
            ];
            foreach ($orderedGrades as $grade):
                $gradeStudents = rosterGradeStudents($studentsByGrade, $grade);
                $count = count($gradeStudents);
                $genderGroups = rosterSplitByGender($gradeStudents);
            ?>
            <section class="grade-section">
                <div class="grade-heading">
                    <span><?= htmlspecialchars(rosterGradeLabel($grade)) ?></span>
                    <span class="count"><?= $count ?> student<?= $count === 1 ? '' : 's' ?></span>
                </div>
                <?php foreach (['Male', 'Female', 'Other'] as $genderKey):
                    $list = $genderGroups[$genderKey];
                    if ($list === []) {
                        continue;
                    }
                ?>
                <div class="gender-subheading">
                    <?= htmlspecialchars($genderSectionTitles[$genderKey]) ?>
                    (<?= count($list) ?>)
                </div>
                <table class="roster">
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th class="id">Student ID</th>
                            <th>Full Name</th>
                            <th class="lrn">LRN</th>
                            <th>Contact No.</th>
                            <th>Date Enrolled</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $i => $s): ?>
                        <tr>
                            <td class="num"><?= $i + 1 ?></td>
                            <td class="id"><?= htmlspecialchars((string) $s['empidno']) ?></td>
                            <td><?= htmlspecialchars(studentFullName($s)) ?></td>
                            <td class="lrn"><?= htmlspecialchars((string) ($s['lrn_number'] ?? '—')) ?></td>
                            <td><?= htmlspecialchars((string) ($s['contact_number'] ?? '—')) ?></td>
                            <td><?= !empty($s['created_at']) ? date('M j, Y', strtotime($s['created_at'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endforeach; ?>
            </section>
            <?php endforeach; ?>

            <div class="signatures">
                <div class="sig-block">
                    <div class="line">Prepared by</div>
                </div>
                <div class="sig-block">
                    <div class="line">Verified by (Registrar)</div>
                </div>
                <div class="sig-block">
                    <div class="line">Approved by (School Head)</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
