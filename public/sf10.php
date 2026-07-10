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
$db->exec("CREATE TABLE IF NOT EXISTS student_behavior (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, school_year VARCHAR(20) NOT NULL DEFAULT '2024-2025', quarter TINYINT NOT NULL, maka_diyos VARCHAR(5), makatao VARCHAR(5), makakalikasan VARCHAR(5), makabansa VARCHAR(5), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_beh (student_id, school_year, quarter)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("CREATE TABLE IF NOT EXISTS generated_school_forms (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, form_type ENUM('SF9','SF10') NOT NULL, school_year VARCHAR(20), generated_by INT NOT NULL, generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ip_address VARCHAR(45), verification_code VARCHAR(64) UNIQUE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('school_name','The Light Christian Academy'),('school_id',''),('school_division',''),('school_region','Region IV-A (CALABARZON)'),('school_year','2024-2025'),('school_head_name',''),('registrar_name',''),('school_logo','assets/images/school-logo.png')");
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$studentId]);
$student = $stmt->fetch();
if (!$student) { header('Location: school-forms.php'); exit; }
$sr = $db->query("SELECT setting_key, setting_value FROM settings");
$s = [];
foreach ($sr->fetchAll() as $r) { $s[$r['setting_key']] = $r['setting_value']; }
$schoolName   = $s['school_name']      ?? 'The Light Christian Academy';
$schoolId2    = $s['school_id']        ?? '';
$schoolDiv    = $s['school_division']  ?? '';
$schoolReg    = $s['school_region']    ?? '';
$schoolYear   = $s['school_year']      ?? '2024-2025';
$schoolHead   = $s['school_head_name'] ?? '';
$registrar    = $s['registrar_name']   ?? '';
$schoolLogo   = $s['school_logo']      ?? 'assets/images/school-logo.png';

$gradesStmt = $db->prepare("SELECT s.name AS subject_name, s.grade_level, g.q1_grade, g.q2_grade, g.q3_grade, g.q4_grade, g.average_grade FROM grades g JOIN subjects s ON s.id = g.subject_id WHERE g.student_id = ? ORDER BY s.grade_level, s.name ASC");
$gradesStmt->execute([$studentId]);
$allGrades = $gradesStmt->fetchAll();
$gradesByLevel = [];
foreach ($allGrades as $g) { $lvl = $g['grade_level'] ?: 'Unknown'; $gradesByLevel[$lvl][] = $g; }

try {
    $attStmt = $db->prepare("SELECT COUNT(DISTINCT date) AS td, SUM(status='present') AS pr, SUM(status='absent') AS ab, SUM(status='late') AS lt FROM student_attendance WHERE student_id=?");
    $attStmt->execute([$studentId]);
    $att = $attStmt->fetch();
} catch(Exception $e) { $att = ['td'=>0,'pr'=>0,'ab'=>0,'lt'=>0]; }

$advStmt = $db->prepare("SELECT u.name FROM users u JOIN folder_teacher ft ON ft.teacher_empidno=u.empidno JOIN enrollments e ON e.subject_id=ft.subject_id WHERE e.student_id=? LIMIT 1");
$advStmt->execute([$studentId]);
$adviser = $advStmt->fetchColumn() ?: '';

saveAudit($user['id'],'generate','sf10',$studentId,['ip'=>$_SERVER['REMOTE_ADDR']??'']);
$verCode = strtoupper(substr(md5($studentId.'SF10'.$schoolYear.time()),0,12));
$np = explode(',', $student['name'], 2);
$lastName = trim($np[0]??$student['name']);
$fn2 = explode(' ', trim($np[1]??''));
$firstName = trim($fn2[0]??'');
$midInit = isset($fn2[1]) ? strtoupper(substr($fn2[1],0,1)).'.': '';

function fmtGrade($v) { return $v !== null ? number_format((float)$v, 0) : '' ; }

$jhsGrades = ['Grade 7'=>[],'Grade 8'=>[],'Grade 9'=>[],'Grade 10'=>[]];
foreach($gradesByLevel as $lvl=>$rows) {
    if(isset($jhsGrades[$lvl])) $jhsGrades[$lvl] = $rows;
}
$jhsSubjects = [
    ['key'=>'Filipino',   'label'=>'Filipino'],
    ['key'=>'English',    'label'=>'English'],
    ['key'=>'Mathematics','label'=>'Mathematics'],
    ['key'=>'Science',    'label'=>'Science'],
    ['key'=>'Araling',    'label'=>'Araling Panlipunan (AP)'],
    ['key'=>'ESP',        'label'=>'Edukasyon sa Pagpapakatao (EsP)'],
    ['key'=>'TLE',        'label'=>'Technology and Livelihood Education (TLE)'],
    ['key'=>'Music',      'label'=>'Music',     'mapeh'=>true],
    ['key'=>'Arts',       'label'=>'Arts',      'mapeh'=>true],
    ['key'=>'PE',         'label'=>'Physical Education', 'mapeh'=>true],
    ['key'=>'Health',     'label'=>'Health',    'mapeh'=>true],
];

function matchSubject($key, $grades) {
    $keywords = [
        'Filipino'  => ['filipino','fil'],
        'English'   => ['english','eng'],
        'Mathematics'=> ['math','mathematics'],
        'Science'   => ['science','sci'],
        'Araling'   => ['araling','ap','social'],
        'TLE'       => ['tle','livelihood','technology','tech'],
        'Music'     => ['music'],
        'Arts'      => ['arts','art'],
        'PE'        => ['physical education','pe','mapeh'],
        'Health'    => ['health'],
        'ESP'       => ['esp','edukasyon','values','pagpapakatao'],
    ];
    $kws = $keywords[$key] ?? [strtolower($key)];
    foreach($grades as $g) {
        $n = strtolower($g['subject_name']);
        foreach($kws as $kw) { if(stripos($n,$kw)!==false) return $g; }
    }
    return null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SF10-JHS – <?= htmlspecialchars($student['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/style.css?v=2028">
<style>
    @page { size: legal portrait; margin: 12mm; }
    .page-sf10 {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10.5px;
        color: #111;
        background: #fff;
        max-width: 850px;
        margin: 0 auto 30px;
        padding: 18px 22px;
        border: 1px solid #999;
        box-sizing: border-box;
    }
    .page-sf10 *, .page-sf10 *::before, .page-sf10 *::after {
        box-sizing: border-box;
    }
    .page-sf10 .form-code { font-size: 10px; text-align: left; }
    .page-sf10 .header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        position: relative;
        text-align: center;
        margin-bottom: 6px;
    }
    .page-sf10 .header img { height: 55px; }
    .page-sf10 .header .logo-left { position: absolute; left: 0; }
    .page-sf10 .header .logo-right { position: absolute; right: 0; }
    .page-sf10 .header .title-block { margin: 0 70px; }
    .page-sf10 .header .title-block p { margin: 0; line-height: 1.3; }
    .page-sf10 .header h1 { font-size: 13px; margin: 2px 0; font-weight:bold; }
    .page-sf10 .header .subtitle { font-size: 9.5px; font-style: italic; }

    .page-sf10 .section-title {
        background: #d8d3c4;
        text-align: center;
        font-weight: bold;
        padding: 3px 0;
        border: 1px solid #333;
        margin-top: 10px;
        font-size: 11px;
    }

    .page-sf10 .info-box, .page-sf10 .elig-box, .page-sf10 .cert-box {
        border: 1px solid #333;
        border-top: none;
        padding: 6px 10px;
    }

    .page-sf10 .field-row { display: flex; flex-wrap: wrap; gap: 4px 18px; margin: 4px 0; align-items: baseline; }
    .page-sf10 .lbl { font-weight: bold; white-space: nowrap; margin-right: 2px; }
    .page-sf10 .fill {
        display: inline-block;
        border-bottom: 1px solid #333;
        min-width: 90px;
        height: 14px;
        margin-right: 8px;
        font-weight: normal;
        text-align: center;
    }
    .page-sf10 .fill.wide { min-width: 170px; }
    .page-sf10 .fill.narrow { min-width: 40px; }

    .page-sf10 .checkbox-row { display: flex; align-items: center; gap: 6px; margin: 4px 0; flex-wrap: wrap; }
    .page-sf10 .checkbox { width: 11px; height: 11px; border: 1px solid #333; display: inline-block; text-align:center; font-size:9px; line-height:11px; font-weight:bold; }

    .page-sf10 .scholastic-block {
        border: 1px solid #333;
        border-top: none;
        padding: 8px 10px 12px;
        margin-bottom: 4px;
    }
    .page-sf10 .school-row { margin-bottom: 6px; }
    .page-sf10 .school-line { display: flex; flex-wrap: wrap; gap: 4px 14px; margin: 3px 0; align-items: baseline; font-size: 10px; }

    .page-sf10 table.grades-table, .page-sf10 table.remedial-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
        font-size: 10px;
    }
    .page-sf10 table.grades-table th, .page-sf10 table.grades-table td,
    .page-sf10 table.remedial-table th, .page-sf10 table.remedial-table td {
        border: 1px solid #333;
        padding: 3px 4px;
        text-align: center;
        height: 16px;
    }
    .page-sf10 table.grades-table .learning-area-col { width: 30%; text-align: center; }
    .page-sf10 table.grades-table .area-name { text-align: left; font-weight: 500; }
    .page-sf10 table.grades-table .q-col { width: 6%; }
    .page-sf10 table.grades-table .final-col { width: 9%; }
    .page-sf10 table.grades-table .remarks-col { width: 20%; }
    .page-sf10 table.grades-table .ga-label { font-style: italic; text-align: center; font-weight: bold; }

    .page-sf10 table.remedial-table .remedial-label { font-weight: bold; text-align: center; width: 10%; }
    .page-sf10 table.remedial-table td:not(.remedial-label) { text-align: left; }
    .page-sf10 table.remedial-table th { background: #f2f2f2; text-align:center; }

    .page-sf10 .cert-box p { margin: 6px 0; }
    .page-sf10 .cert-box .sig-line {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
    }
    .page-sf10 .cert-box .sig-line .sig {
        border-top: 1px solid #333;
        width: 260px;
        text-align: center;
        padding-top: 2px;
        font-size: 9.5px;
        position: relative;
    }
    .page-sf10 .cert-box .sig-line .sig-val {
        position: absolute;
        bottom: 100%;
        left: 0; right: 0;
        text-align: center;
        font-weight: bold;
        font-size: 11px;
    }
    .page-sf10 .cert-box .seal {
        width: 180px;
        text-align: center;
        font-size: 9.5px;
        align-self: flex-end;
        border: 1px dashed #999;
        padding: 18px 4px 4px;
    }
    .sf-preview-wrapper { padding: 20px; background: #e8e8e8; }
    .sf-toolbar { max-width: 850px; margin: 0 auto 10px; display: flex; gap: 8px; }

    @media print {
        body { background: #fff !important; padding: 0 !important; }
        .page-sf10 { border: none !important; margin: 0 !important; max-width: 100% !important; padding: 0 !important; box-shadow: none !important; }
        .sidebar, .main-content > .top-bar, .sf-toolbar, nav, header, footer { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .sf-preview-wrapper { padding: 0 !important; background: transparent !important; }
        @page { size: legal portrait; margin: 12mm; }
    }
</style>
</head>
<body>
<?php include __DIR__.'/includes/sidebar.php'; ?>
<div class="main-content">
<?php $pageTitle='SF10 – Permanent Academic Record'; include __DIR__.'/includes/topbar.php'; ?>
<div class="sf-preview-wrapper">
    <div class="sf-toolbar d-print-none">
        <a href="school-forms.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <button class="btn btn-primary btn-sm ms-auto" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print / Save as PDF</button>
        <a href="sf9.php?id=<?= $studentId ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-person me-1"></i>View SF9</a>
    </div>

    <div class="page-sf10" id="sf10">

        <div class="form-code">SF 10 - JHS</div>

        <div class="header">
            <img class="logo-left" src="<?= htmlspecialchars($schoolLogo) ?>" alt="School Logo" onerror="this.style.display='none'">
            <div class="title-block">
                <p>Republic of the Philippines</p>
                <p>Department of Education</p>
                <h1>Learner's Permanent Academic Record for Junior High School (SF10-JHS)</h1>
                <p class="subtitle">(Formerly Form 137)</p>
            </div>
            <img class="logo-right" src="assets/images/deped-logo.png" alt="DepEd Logo" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/6/6e/DepEd_Official_Logo.png'">
        </div>

        <div class="section-title">LEARNER'S INFORMATION</div>
        <div class="info-box">
            <div class="field-row">
                <span class="lbl">LAST NAME:</span><span class="fill wide"><?= htmlspecialchars($lastName) ?></span>
                <span class="lbl">FIRST NAME:</span><span class="fill wide"><?= htmlspecialchars($firstName) ?></span>
                <span class="lbl">NAME EXTN. (Jr,I,II):</span><span class="fill narrow"></span>
                <span class="lbl">MIDDLE NAME:</span><span class="fill wide"><?= htmlspecialchars($student['middle_name']??'') ?></span>
            </div>
            <div class="field-row">
                <span class="lbl">Learner Reference Number (LRN):</span><span class="fill wide"><?= htmlspecialchars($student['lrn_number']??'') ?></span>
                <span class="lbl">Birthdate (mm/dd/yyyy):</span><span class="fill"><?= $student['date_of_birth']?date('m/d/Y',strtotime($student['date_of_birth'])):' ' ?></span>
                <span class="lbl">Sex:</span><span class="fill narrow"><?= htmlspecialchars($student['gender']??'') ?></span>
            </div>
        </div>

        <div class="section-title">ELIGIBILITY FOR JHS ENROLMENT</div>
        <div class="elig-box">
            <div class="checkbox-row">
                <span class="checkbox">&#10003;</span>
                <span class="lbl">Elementary School Completer</span>
                <span class="lbl">General Average:</span><span class="fill"><?= htmlspecialchars($student['general_average']??'') ?></span>
                <span class="lbl">Citation: (If Any)</span><span class="fill wide"></span>
            </div>
            <div class="field-row">
                <span class="lbl">Name of Elementary School:</span><span class="fill wide"><?= htmlspecialchars($student['last_school_attended']??'') ?></span>
                <span class="lbl">School ID:</span><span class="fill"></span>
                <span class="lbl">Address of School:</span><span class="fill wide"></span>
            </div>
            <p style="font-weight:bold; margin: 8px 0 2px;">Other Credential Presented</p>
            <div class="checkbox-row">
                <span class="checkbox"></span><span class="lbl">PEPT Passer</span>
                <span class="lbl">Rating:</span><span class="fill narrow"></span>
                <span class="checkbox"></span><span class="lbl">ALS A &amp; E Passer</span>
                <span class="lbl">Rating:</span><span class="fill narrow"></span>
                <span class="checkbox"></span><span class="lbl">Others (Pls. Specify):</span><span class="fill wide"></span>
            </div>
            <div class="field-row">
                <span class="lbl">Date of Examination/Assessment (mm/dd/yyyy):</span><span class="fill"></span>
                <span class="lbl">Name and Address of Testing Center:</span><span class="fill wide"></span>
            </div>
        </div>

        <div class="section-title">SCHOLASTIC RECORD</div>
        <?php
        $gradeLevelsToDisplay = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
        foreach($gradeLevelsToDisplay as $idx => $gLevel):
            $lvlGrades = $jhsGrades[$gLevel] ?? [];
            $totalQ = 0; $cntQ = 0;
            foreach($lvlGrades as $gg) { if($gg['average_grade']!==null){$totalQ+=(float)$gg['average_grade'];$cntQ++;} }
            $genAvg = $cntQ>0 ? round($totalQ/$cntQ,2) : null;
            
            $failedSubjects = [];
            foreach($jhsSubjects as $subj) {
                $g = matchSubject($subj['key'],$lvlGrades);
                if($g && $g['average_grade']!==null && (float)$g['average_grade']<75) {
                    $failedSubjects[] = ['label'=>$subj['label'],'final'=>$g['average_grade']];
                }
            }
        ?>
        <div class="scholastic-block">
            <div class="school-row">
                <div class="school-line">
                    <span class="lbl">School:</span><span class="fill wide"><?= htmlspecialchars($schoolName) ?></span>
                    <span class="lbl">School ID:</span><span class="fill"><?= htmlspecialchars($schoolId2) ?></span>
                    <span class="lbl">District:</span><span class="fill"></span>
                    <span class="lbl">Division:</span><span class="fill"><?= htmlspecialchars($schoolDiv) ?></span>
                    <span class="lbl">Region:</span><span class="fill narrow"><?= htmlspecialchars(preg_replace('/[^A-Za-z0-9\-]/', '', explode(' ', $schoolReg)[0] ?? '')) ?></span>
                </div>
                <div class="school-line">
                    <span class="lbl">Classified as Grade:</span><span class="fill narrow"><?= htmlspecialchars(str_replace('Grade ', '', $gLevel)) ?></span>
                    <span class="lbl">Section:</span><span class="fill"></span>
                    <span class="lbl">School Year:</span><span class="fill"><?= htmlspecialchars($schoolYear) ?></span>
                    <span class="lbl">Name of Adviser/Teacher:</span><span class="fill wide"><?= htmlspecialchars($adviser) ?></span>
                    <span class="lbl">Signature:</span><span class="fill"></span>
                </div>
            </div>

            <table class="grades-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="learning-area-col">LEARNING AREAS</th>
                        <th colspan="4">Quarterly Rating</th>
                        <th rowspan="2" class="final-col">FINAL<br>RATING</th>
                        <th rowspan="2" class="remarks-col">REMARKS</th>
                    </tr>
                    <tr>
                        <th class="q-col">1</th>
                        <th class="q-col">2</th>
                        <th class="q-col">3</th>
                        <th class="q-col">4</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($jhsSubjects as $subj): 
                        $g = matchSubject($subj['key'], $lvlGrades);
                        $isMAPEH = isset($subj['mapeh']) && $subj['mapeh'];
                        $q1 = $g['q1_grade'] ?? null;
                        $q2 = $g['q2_grade'] ?? null;
                        $q3 = $g['q3_grade'] ?? null;
                        $q4 = $g['q4_grade'] ?? null;
                        $fin = $g['average_grade'] ?? null;
                        $passed = $fin !== null && (float)$fin >= 75;
                        
                        if ($subj['key'] == 'Music') {
                            echo '<tr><td class="area-name" style="font-weight:bold;">MAPEH</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
                        }
                    ?>
                    <tr>
                        <td class="area-name" <?= $isMAPEH ? 'style="padding-left:15px; font-style:italic;"' : '' ?>><?= htmlspecialchars($subj['label']) ?></td>
                        <td><?= fmtGrade($q1) ?></td>
                        <td><?= fmtGrade($q2) ?></td>
                        <td><?= fmtGrade($q3) ?></td>
                        <td><?= fmtGrade($q4) ?></td>
                        <td style="<?= $fin!==null?((float)$fin>=75?'color:green;':'color:red;'):'' ?> font-weight:bold;"><?= fmtGrade($fin) ?></td>
                        <td><?= $fin !== null ? ($passed ? 'Passed' : 'Failed') : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="5" class="ga-label">General Average</td>
                        <td style="font-weight:bold;"><?= $genAvg !== null ? number_format($genAvg, 2) : '' ?></td>
                        <td style="font-weight:bold;"><?= $genAvg !== null ? ($genAvg >= 75 ? 'Promoted' : 'Retained') : '' ?></td>
                    </tr>
                </tbody>
            </table>

            <table class="remedial-table">
                <tr>
                    <td class="remedial-label" rowspan="2">Remedial Classes</td>
                    <td colspan="4">
                        Conducted from (mm/dd/yyyy) <span class="fill"></span>
                        &nbsp;&nbsp;to (mm/dd/yyyy) <span class="fill"></span>
                    </td>
                </tr>
                <tr>
                    <th style="text-align:center;">Learning Areas</th>
                    <th style="text-align:center;">Final Rating</th>
                    <th style="text-align:center;">Remedial Class Mark</th>
                    <th style="text-align:center;">Recomputed Final Grade</th>
                </tr>
                <?php 
                $failedCount = count($failedSubjects);
                $remedialRows = max($failedCount, 2);
                for ($ri = 0; $ri < $remedialRows; $ri++):
                    $fs = $failedSubjects[$ri] ?? null;
                ?>
                <tr>
                    <td style="text-align:left;"><?= $fs ? htmlspecialchars($fs['label']) : '' ?></td>
                    <td style="text-align:center;"><?= $fs ? number_format((float)$fs['final'], 0) : '' ?></td>
                    <td></td>
                    <td></td>
                    <?php if($ri == 0): ?>
                    <td class="remarks-col" rowspan="<?= $remedialRows ?>" style="vertical-align:top; text-align:left;">Remarks:</td>
                    <?php endif; ?>
                </tr>
                <?php endfor; ?>
            </table>
        </div>
        <?php if($idx == 1): // After Grade 8 ?>
        <div style="page-break-after: always; height: 20px;"></div>
        <?php endif; ?>
        <?php endforeach; ?>

        <div class="section-title">CERTIFICATION</div>
        <div class="cert-box">
            <p>
                I CERTIFY that this is a true record of <span class="fill wide"><?= htmlspecialchars($lastName.', '.$firstName) ?></span>
                with LRN <span class="fill"><?= htmlspecialchars($student['lrn_number']??'') ?></span>
                and that he/she is eligible for admission to Grade <span class="fill narrow"></span>.
            </p>
            <p>
                Name of School: <span class="fill wide"><?= htmlspecialchars($schoolName) ?></span>
                School ID: <span class="fill"><?= htmlspecialchars($schoolId2) ?></span>
                Last School Year Attended: <span class="fill"><?= htmlspecialchars($schoolYear) ?></span>
            </p>
            <div class="sig-line">
                <div class="sig">
                    <span class="sig-val"><?= date('M d, Y') ?></span>
                    Date
                </div>
                <div class="sig">
                    <span class="sig-val"><?= htmlspecialchars($schoolHead ?: '____________________________') ?></span>
                    Name of Principal/School Head over Printed Name
                </div>
                <div class="seal">(Affix School Seal here)</div>
            </div>
        </div>
        
        <div style="text-align:center;font-size:8px;color:#888;margin-top:10px;">
            Document Verification Code: <strong><?= $verCode ?></strong> &nbsp;|&nbsp; Generated: <?= date('F d, Y \a\t h:i A') ?>
        </div>

    </div><!-- page-sf10 -->
</div><!-- sf-preview-wrapper -->
</div><!-- main-content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>