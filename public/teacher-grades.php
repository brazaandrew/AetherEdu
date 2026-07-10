<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireTeacher();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// Handle Save Single Quarter Grade (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_single_grade'])) {
    header('Content-Type: application/json');
    
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if ($csrfToken !== $sessionToken) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    $studentId = (int)($_POST['student_id'] ?? 0);
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $quarter = $_POST['quarter'] ?? '';
    $gradeValue = isset($_POST['grade']) && $_POST['grade'] !== '' ? (float)$_POST['grade'] : null;
    
    if (!$studentId || !$subjectId || !in_array($quarter, ['q1', 'q2', 'q3', 'q4'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }
    
    // Verify student is enrolled
    $stmt = db()->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND subject_id = ?');
    $stmt->execute([$studentId, $subjectId]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Student not enrolled in this subject']);
        exit;
    }
    
    try {
        // Check if grade record exists
        $stmt = db()->prepare('SELECT id, q1_grade, q2_grade, q3_grade, q4_grade FROM grades WHERE student_id = ? AND subject_id = ?');
        $stmt->execute([$studentId, $subjectId]);
        $gradeRecord = $stmt->fetch();
        
        $column = $quarter . '_grade';
        
        if ($gradeRecord) {
            // Update specific quarter
            $stmt = db()->prepare("UPDATE grades SET {$column} = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$gradeValue, $gradeRecord['id']]);
            $gradeId = $gradeRecord['id'];
        } else {
            // Insert new record with specific quarter
            $stmt = db()->prepare("INSERT INTO grades (student_id, subject_id, {$column}, updated_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$studentId, $subjectId, $gradeValue]);
            $gradeId = (int)db()->lastInsertId();
        }
        
        // Fetch updated grades to calculate average
        $stmt = db()->prepare('SELECT q1_grade, q2_grade, q3_grade, q4_grade FROM grades WHERE id = ?');
        $stmt->execute([$gradeId]);
        $grades = $stmt->fetch();
        
        // Calculate average
        $quarters = array_filter([
            $grades['q1_grade'],
            $grades['q2_grade'],
            $grades['q3_grade'],
            $grades['q4_grade']
        ], function($v) { return $v !== null; });
        
        $average = count($quarters) > 0 ? array_sum($quarters) / 4 : null;
        
        // Update average
        $stmt = db()->prepare('UPDATE grades SET average_grade = ? WHERE id = ?');
        $stmt->execute([$average, $gradeId]);
        
        saveAudit($user['id'], 'update', 'grade', $gradeId, ['student_id' => $studentId, 'subject_id' => $subjectId, 'quarter' => $quarter, 'grade' => $gradeValue]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Grade saved successfully',
            'average' => $average !== null ? number_format($average, 2) : null
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

// Handle Save All Grades
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_grades'])) {
    error_log('Save grades triggered');
    error_log('POST data: ' . print_r($_POST, true));
    
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if ($csrfToken !== $sessionToken) {
        $error = 'Invalid CSRF token';
        error_log('CSRF token mismatch');
    } else {
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $grades = $_POST['grades'] ?? [];
        
        error_log('Subject ID: ' . $subjectId);
        error_log('Grades array: ' . print_r($grades, true));
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($grades as $studentId => $studentGrades) {
            $studentId = (int)$studentId;
            
            // Verify student is enrolled
            $stmt = db()->prepare('SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND subject_id = ?');
            $stmt->execute([$studentId, $subjectId]);
            $enrollmentCount = $stmt->fetchColumn();
            error_log("Student $studentId enrollment check: $enrollmentCount");
            
            if ($enrollmentCount == 0) {
                $errorCount++;
                error_log("Student $studentId not enrolled, skipping");
                continue;
            }
            
            try {
                // Check if grade record exists
                $stmt = db()->prepare('SELECT id FROM grades WHERE student_id = ? AND subject_id = ?');
                $stmt->execute([$studentId, $subjectId]);
                $gradeRecord = $stmt->fetch();
                
                $q1 = isset($studentGrades['q1']) && $studentGrades['q1'] !== '' ? (float)$studentGrades['q1'] : null;
                $q2 = isset($studentGrades['q2']) && $studentGrades['q2'] !== '' ? (float)$studentGrades['q2'] : null;
                $q3 = isset($studentGrades['q3']) && $studentGrades['q3'] !== '' ? (float)$studentGrades['q3'] : null;
                $q4 = isset($studentGrades['q4']) && $studentGrades['q4'] !== '' ? (float)$studentGrades['q4'] : null;
                
                error_log("Student $studentId grades: Q1=$q1, Q2=$q2, Q3=$q3, Q4=$q4");
                
                // Calculate average
                $quarters = array_filter([$q1, $q2, $q3, $q4], function($v) { return $v !== null; });
                $average = count($quarters) > 0 ? array_sum($quarters) / 4 : null;
                
                error_log("Student $studentId average: $average");
                
                if ($gradeRecord) {
                    // Update
                    error_log("Updating grade record ID: " . $gradeRecord['id']);
                    $stmt = db()->prepare('UPDATE grades SET q1_grade = ?, q2_grade = ?, q3_grade = ?, q4_grade = ?, average_grade = ?, updated_at = NOW() WHERE id = ?');
                    $result = $stmt->execute([$q1, $q2, $q3, $q4, $average, $gradeRecord['id']]);
                    error_log('Update result: ' . ($result ? 'success' : 'failed'));
                } else {
                    // Insert
                    error_log("Inserting new grade record for student $studentId");
                    $stmt = db()->prepare('INSERT INTO grades (student_id, subject_id, q1_grade, q2_grade, q3_grade, q4_grade, average_grade, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                    $result = $stmt->execute([$studentId, $subjectId, $q1, $q2, $q3, $q4, $average]);
                    error_log('Insert result: ' . ($result ? 'success' : 'failed'));
                    error_log('Last insert ID: ' . db()->lastInsertId());
                }
                
                $successCount++;
                saveAudit($user['id'], 'update', 'grade', $gradeRecord['id'] ?? (int)db()->lastInsertId(), ['student_id' => $studentId, 'subject_id' => $subjectId]);
            } catch (Exception $e) {
                $errorCount++;
                error_log('Exception for student ' . $studentId . ': ' . $e->getMessage());
            }
        }
        
        error_log("Save completed: $successCount success, $errorCount errors");
        
        if ($successCount > 0) {
            $message = "Successfully saved grades for $successCount student(s).";
            if ($errorCount > 0) {
                $message .= " Failed to save $errorCount student(s).";
            }
            
            // Reload grades to show updated data
            if ($selectedSubjectId) {
                $stmt = db()->prepare('
                    SELECT u.id, u.name, u.empidno
                    FROM enrollments e
                    JOIN users u ON e.student_id = u.id
                    WHERE e.subject_id = ?
                    ORDER BY u.name ASC
                ');
                $stmt->execute([$selectedSubjectId]);
                $enrolledStudents = $stmt->fetchAll();
                
                if (!empty($enrolledStudents)) {
                    $studentIds = array_column($enrolledStudents, 'id');
                    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                    $stmt = db()->prepare("SELECT student_id, q1_grade, q2_grade, q3_grade, q4_grade, average_grade FROM grades WHERE subject_id = ? AND student_id IN ($placeholders)");
                    $stmt->execute(array_merge([$selectedSubjectId], $studentIds));
                    $studentGrades = [];
                    foreach ($stmt->fetchAll() as $grade) {
                        $studentGrades[$grade['student_id']] = $grade;
                    }
                }
            }
        } else {
            $error = 'Failed to save grades.';
        }
    }
}

// Fetch teacher's subjects
$stmt = db()->prepare('SELECT DISTINCT s.id, s.code, s.name FROM subjects s JOIN folder_teacher ft ON s.id = ft.subject_id WHERE ft.teacher_empidno = ? AND s.archived = 0 ORDER BY s.name');
$stmt->execute([$user['empidno']]);
$teacherSubjects = $stmt->fetchAll();

// Fetch grade periods configuration
$stmt = db()->query('SELECT * FROM grade_periods ORDER BY quarter');
$gradePeriods = [];
foreach ($stmt->fetchAll() as $period) {
    $gradePeriods[$period['quarter']] = [
        'is_enabled' => $period['is_enabled'] == 1,
        'deadline' => $period['deadline'],
        'is_expired' => $period['deadline'] && strtotime($period['deadline']) < time()
    ];
}

// Selected subject filter
$selectedSubjectId = (int)($_GET['subject_id'] ?? 0);

// Fetch enrolled students with quarterly grades
$enrolledStudents = [];
$studentGrades = [];
if ($selectedSubjectId) {
    // Fetch enrolled students
    $stmt = db()->prepare('
        SELECT u.id, u.name, u.empidno
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.subject_id = ?
        ORDER BY u.name ASC
    ');
    $stmt->execute([$selectedSubjectId]);
    $enrolledStudents = $stmt->fetchAll();
    
    // Fetch quarterly grades for enrolled students
    if (!empty($enrolledStudents)) {
        $studentIds = array_column($enrolledStudents, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = db()->prepare("SELECT student_id, q1_grade, q2_grade, q3_grade, q4_grade, average_grade FROM grades WHERE subject_id = ? AND student_id IN ($placeholders)");
        $stmt->execute(array_merge([$selectedSubjectId], $studentIds));
        foreach ($stmt->fetchAll() as $grade) {
            $studentGrades[$grade['student_id']] = $grade;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Grade Management'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2>
                        <i class="bi bi-clipboard-data"></i>
                        Quarterly Grade Management
                    </h2>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Subject Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label for="subject_id" class="form-label">Select Subject</label>
                                <select class="form-select" id="subject_id" name="subject_id" onchange="this.form.submit()">
                                    <option value="">-- Choose Subject --</option>
                                    <?php foreach ($teacherSubjects as $subj): ?>
                                    <option value="<?= $subj['id'] ?>" <?= $subj['id'] == $selectedSubjectId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subj['name']) ?> (<?= htmlspecialchars($subj['code']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selectedSubjectId): ?>
            <!-- Quarterly Grades Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-data me-2"></i>Quarterly Grades
                        <span class="badge bg-primary ms-2"><?= count($enrolledStudents) ?> Students</span>
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-info" onclick="printGradeReport()">
                            <i class="bi bi-printer me-2"></i>Print Report
                        </button>
                        <button type="button" class="btn btn-success" onclick="document.getElementById('gradesForm').submit();">
                            <i class="bi bi-save me-2"></i>Save All Grades
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="gradesForm">
                        <?= csrfField() ?>
                        <input type="hidden" name="subject_id" value="<?= $selectedSubjectId ?>">
                        <?php if (empty($enrolledStudents)): ?>
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No students enrolled in this subject yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="gradesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="min-width: 120px;">STUDENT ID</th>
                                            <th style="min-width: 200px;">FULLNAME</th>
                                            <th class="text-center" style="width: 120px;">
                                                Q1
                                                <?php if (!empty($gradePeriods['Q1'])): ?>
                                                    <?php if (!$gradePeriods['Q1']['is_enabled']): ?>
                                                        <br><span class="badge bg-danger text-white">Locked</span>
                                                    <?php elseif ($gradePeriods['Q1']['is_expired']): ?>
                                                        <br><span class="badge bg-warning text-dark">Expired</span>
                                                    <?php else: ?>
                                                        <br><span class="badge bg-success text-white">Open</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </th>
                                            <th class="text-center" style="width: 120px;">
                                                Q2
                                                <?php if (!empty($gradePeriods['Q2'])): ?>
                                                    <?php if (!$gradePeriods['Q2']['is_enabled']): ?>
                                                        <br><span class="badge bg-danger text-white">Locked</span>
                                                    <?php elseif ($gradePeriods['Q2']['is_expired']): ?>
                                                        <br><span class="badge bg-warning text-dark">Expired</span>
                                                    <?php else: ?>
                                                        <br><span class="badge bg-success text-white">Open</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </th>
                                            <th class="text-center" style="width: 120px;">
                                                Q3
                                                <?php if (!empty($gradePeriods['Q3'])): ?>
                                                    <?php if (!$gradePeriods['Q3']['is_enabled']): ?>
                                                        <br><span class="badge bg-danger text-white">Locked</span>
                                                    <?php elseif ($gradePeriods['Q3']['is_expired']): ?>
                                                        <br><span class="badge bg-warning text-dark">Expired</span>
                                                    <?php else: ?>
                                                        <br><span class="badge bg-success text-white">Open</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </th>
                                            <th class="text-center" style="width: 120px;">
                                                Q4
                                                <?php if (!empty($gradePeriods['Q4'])): ?>
                                                    <?php if (!$gradePeriods['Q4']['is_enabled']): ?>
                                                        <br><span class="badge bg-danger text-white">Locked</span>
                                                    <?php elseif ($gradePeriods['Q4']['is_expired']): ?>
                                                        <br><span class="badge bg-warning text-dark">Expired</span>
                                                    <?php else: ?>
                                                        <br><span class="badge bg-success text-white">Open</span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </th>
                                            <th class="text-center bg-light" style="width: 130px;"><strong>AVERAGE</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($enrolledStudents as $student): 
                                            $grades = $studentGrades[$student['id']] ?? [];
                                            $q1 = $grades['q1_grade'] ?? null;
                                            $q2 = $grades['q2_grade'] ?? null;
                                            $q3 = $grades['q3_grade'] ?? null;
                                            $q4 = $grades['q4_grade'] ?? null;
                                            $avg = $grades['average_grade'] ?? null;
                                            
                                            // Calculate average on the fly
                                            $tempQuarters = array_filter([$q1, $q2, $q3, $q4], function($v) { return $v !== null; });
                                            $displayAvg = count($tempQuarters) > 0 ? array_sum($tempQuarters) / 4 : null;
                                            
                                            // Check if quarters are enabled
                                            $q1Enabled = !empty($gradePeriods['Q1']) && $gradePeriods['Q1']['is_enabled'] && !$gradePeriods['Q1']['is_expired'];
                                            $q2Enabled = !empty($gradePeriods['Q2']) && $gradePeriods['Q2']['is_enabled'] && !$gradePeriods['Q2']['is_expired'];
                                            $q3Enabled = !empty($gradePeriods['Q3']) && $gradePeriods['Q3']['is_enabled'] && !$gradePeriods['Q3']['is_expired'];
                                            $q4Enabled = !empty($gradePeriods['Q4']) && $gradePeriods['Q4']['is_enabled'] && !$gradePeriods['Q4']['is_expired'];
                                        ?>
                                        <tr data-student-id="<?= $student['id'] ?>">
                                            <td><strong><?= htmlspecialchars($student['empidno']) ?></strong></td>
                                            <td><?= htmlspecialchars($student['name']) ?></td>
                                            <td class="text-center grade-cell" data-value="<?= $q1 !== null ? number_format((float)$q1, 2) : '--' ?>">
                                                <input type="number" 
                                                       class="form-control form-control-sm grade-input" 
                                                       name="grades[<?= $student['id'] ?>][q1]"
                                                       value="<?= $q1 !== null ? number_format((float)$q1, 2, '.', '') : '' ?>"
                                                       placeholder="--"
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01"
                                                       <?= !$q1Enabled ? 'disabled readonly' : '' ?>
                                                       <?= !$q1Enabled ? 'title="Quarter is locked"' : 'title="Enter grade for Q1"' ?>>
                                            </td>
                                            <td class="text-center grade-cell" data-value="<?= $q2 !== null ? number_format((float)$q2, 2) : '--' ?>">
                                                <input type="number" 
                                                       class="form-control form-control-sm grade-input" 
                                                       name="grades[<?= $student['id'] ?>][q2]"
                                                       value="<?= $q2 !== null ? number_format((float)$q2, 2, '.', '') : '' ?>"
                                                       placeholder="--"
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01"
                                                       <?= !$q2Enabled ? 'disabled readonly' : '' ?>
                                                       <?= !$q2Enabled ? 'title="Quarter is locked"' : 'title="Enter grade for Q2"' ?>>
                                            </td>
                                            <td class="text-center grade-cell" data-value="<?= $q3 !== null ? number_format((float)$q3, 2) : '--' ?>">
                                                <input type="number" 
                                                       class="form-control form-control-sm grade-input" 
                                                       name="grades[<?= $student['id'] ?>][q3]"
                                                       value="<?= $q3 !== null ? number_format((float)$q3, 2, '.', '') : '' ?>"
                                                       placeholder="--"
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01"
                                                       <?= !$q3Enabled ? 'disabled readonly' : '' ?>
                                                       <?= !$q3Enabled ? 'title="Quarter is locked"' : 'title="Enter grade for Q3"' ?>>
                                            </td>
                                            <td class="text-center grade-cell" data-value="<?= $q4 !== null ? number_format((float)$q4, 2) : '--' ?>">
                                                <input type="number" 
                                                       class="form-control form-control-sm grade-input" 
                                                       name="grades[<?= $student['id'] ?>][q4]"
                                                       value="<?= $q4 !== null ? number_format((float)$q4, 2, '.', '') : '' ?>"
                                                       placeholder="--"
                                                       min="0" 
                                                       max="100" 
                                                       step="0.01"
                                                       <?= !$q4Enabled ? 'disabled readonly' : '' ?>
                                                       <?= !$q4Enabled ? 'title="Quarter is locked"' : 'title="Enter grade for Q4"' ?>>
                                            </td>
                                            <td class="text-center bg-light average-cell">
                                                <strong class="<?= $displayAvg !== null && $displayAvg >= 75 ? 'text-success' : ($displayAvg !== null ? 'text-danger' : 'text-muted') ?>">
                                                    <?= $displayAvg !== null ? number_format((float)$displayAvg, 2) : '--' ?>
                                                </strong>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Instructions:</strong> Enter grades in the table cells and click "Save All Grades" button to save all changes. 
                                    <strong>Formula:</strong> AVERAGE = (Q1 + Q2 + Q3 + Q4) ÷ 4
                                </small>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="position-fixed top-50 start-50 translate-middle d-none" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Saving...</span>
        </div>
    </div>

    <!-- Edit Grade Modal -->
    <div class="modal fade" id="editGradeModal" tabindex="-1" aria-labelledby="editGradeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editGradeModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit Student Grades
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editGradeForm">
                        <input type="hidden" id="editStudentId" name="student_id" value="">
                        <input type="hidden" name="save_single_grade" value="1">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="subject_id" value="<?= $selectedSubjectId ?? 0 ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Student Name:</label>
                            <h5 id="editStudentName" class="mb-0"></h5>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="editQ1" class="form-label">Q1 Grade</label>
                                <input type="number" class="form-control" id="editQ1" name="q1" min="0" max="100" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label for="editQ2" class="form-label">Q2 Grade</label>
                                <input type="number" class="form-control" id="editQ2" name="q2" min="0" max="100" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label for="editQ3" class="form-label">Q3 Grade</label>
                                <input type="number" class="form-control" id="editQ3" name="q3" min="0" max="100" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label for="editQ4" class="form-label">Q4 Grade</label>
                                <input type="number" class="form-control" id="editQ4" name="q4" min="0" max="100" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-4">
                            <strong>Average:</strong> <span id="editAverage">--</span> | 
                            <strong>Status:</strong> <span id="editStatus">No Grade</span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveGradeBtn">
                        <i class="bi bi-save me-2"></i>Save Grades
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reportModalLabel">
                        <i class="bi bi-file-earmark-text me-2"></i>Quarterly Grade Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($selectedSubjectId && !empty($enrolledStudents)): 
                        $selectedSubject = null;
                        foreach ($teacherSubjects as $subj) {
                            if ($subj['id'] == $selectedSubjectId) {
                                $selectedSubject = $subj;
                                break;
                            }
                        }
                    ?>
                        <div class="mb-4 text-center">
                            <h3><?= htmlspecialchars($selectedSubject['name'] ?? 'Subject Report') ?></h3>
                            <p class="text-muted">Subject Code: <?= htmlspecialchars($selectedSubject['code'] ?? 'N/A') ?></p>
                            <p class="text-muted">Report Generated: <?= date('F d, Y - h:i A') ?></p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="reportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 80px;">#</th>
                                        <th style="min-width: 120px;">Student ID</th>
                                        <th style="min-width: 200px;">Student Name</th>
                                        <th class="text-center" style="width: 100px;">Q1</th>
                                        <th class="text-center" style="width: 100px;">Q2</th>
                                        <th class="text-center" style="width: 100px;">Q3</th>
                                        <th class="text-center" style="width: 100px;">Q4</th>
                                        <th class="text-center bg-light" style="width: 120px;">AVERAGE</th>
                                        <th class="text-center" style="width: 100px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    $passCount = 0;
                                    $failCount = 0;
                                    $noGradeCount = 0;
                                    
                                    foreach ($enrolledStudents as $student): 
                                        $grades = $studentGrades[$student['id']] ?? [];
                                        $q1 = $grades['q1_grade'] ?? null;
                                        $q2 = $grades['q2_grade'] ?? null;
                                        $q3 = $grades['q3_grade'] ?? null;
                                        $q4 = $grades['q4_grade'] ?? null;
                                        $avg = $grades['average_grade'] ?? null;
                                        
                                        if ($avg !== null) {
                                            if ((float)$avg >= 75) {
                                                $passCount++;
                                                $status = 'PASSED';
                                                $statusClass = 'text-success';
                                                $statusBadge = 'bg-success';
                                            } else {
                                                $failCount++;
                                                $status = 'FAILED';
                                                $statusClass = 'text-danger';
                                                $statusBadge = 'bg-danger';
                                            }
                                        } else {
                                            $noGradeCount++;
                                            $status = 'NO GRADE';
                                            $statusClass = 'text-muted';
                                            $statusBadge = 'bg-secondary';
                                        }
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $counter++ ?></td>
                                        <td><strong><?= htmlspecialchars($student['empidno']) ?></strong></td>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <td class="text-center"><?= $q1 !== null ? number_format((float)$q1, 2) : '--' ?></td>
                                        <td class="text-center"><?= $q2 !== null ? number_format((float)$q2, 2) : '--' ?></td>
                                        <td class="text-center"><?= $q3 !== null ? number_format((float)$q3, 2) : '--' ?></td>
                                        <td class="text-center"><?= $q4 !== null ? number_format((float)$q4, 2) : '--' ?></td>
                                        <td class="text-center bg-light">
                                            <strong class="<?= $statusClass ?>">
                                                <?= $avg !== null ? number_format((float)$avg, 2) : '--' ?>
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $statusBadge ?>"><?= $status ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <strong>Summary:</strong> 
                                            <span class="badge bg-success ms-2">Passed: <?= $passCount ?></span>
                                            <span class="badge bg-danger ms-2">Failed: <?= $failCount ?></span>
                                            <span class="badge bg-secondary ms-2">No Grade: <?= $noGradeCount ?></span>
                                            <span class="badge bg-info ms-2">Total: <?= count($enrolledStudents) ?></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p>No data available. Please select a subject and ensure students are enrolled.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printReport()">
                        <i class="bi bi-printer me-2"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const subjectId = <?= $selectedSubjectId ?? 0 ?>;
        const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';
        
        // Grade periods configuration (pass to JavaScript)
        const gradePeriods = {
            Q1: <?= json_encode($gradePeriods['Q1'] ?? null) ?>,
            Q2: <?= json_encode($gradePeriods['Q2'] ?? null) ?>,
            Q3: <?= json_encode($gradePeriods['Q3'] ?? null) ?>,
            Q4: <?= json_encode($gradePeriods['Q4'] ?? null) ?>
        };
        
        console.log('Subject ID:', subjectId);
        console.log('CSRF Token:', csrfToken);
        console.log('Grade periods:', gradePeriods);
        console.log('Grade inputs found:', document.querySelectorAll('.grade-input').length);
        
        // Auto-save grade per quarter when input changes
        document.querySelectorAll('.grade-input').forEach(input => {
            console.log('Attaching listener to input:', input.name);
            let saveTimeout = null;
            
            input.addEventListener('input', function() {
                console.log('Input event triggered for:', this.name, 'Value:', this.value);
                
                const row = this.closest('tr');
                const studentId = row.dataset.studentId;
                const inputName = this.name;
                // Fix regex to properly match q1, q2, q3, q4
                const quarterMatch = inputName.match(/\[q([1-4])\]/);
                const quarter = quarterMatch ? 'q' + quarterMatch[1] : null;
                const gradeValue = this.value;
                
                console.log('Parsed - Student ID:', studentId, 'Quarter:', quarter, 'Grade:', gradeValue);
                
                if (!studentId || !quarter) {
                    console.error('Missing student ID or quarter!', 'StudentID:', studentId, 'Quarter:', quarter, 'Row:', row);
                    return;
                }
                
                if (!subjectId) {
                    console.error('No subject selected!');
                    showNotification('Please select a subject first', 'error');
                    return;
                }
                
                // Check if quarter is enabled
                const quarterUpper = 'Q' + quarter.charAt(1); // Convert q1 to Q1, etc.
                const quarterEnabled = gradePeriods[quarterUpper]?.is_enabled && !gradePeriods[quarterUpper]?.is_expired;
                
                if (!quarterEnabled) {
                    console.error('Quarter is not enabled:', quarterUpper);
                    showNotification('This quarter is currently locked', 'error');
                    return;
                }
                
                // Visual feedback - saving state
                this.classList.remove('saved', 'error');
                this.classList.add('saving');
                
                // Clear previous timeout
                if (saveTimeout) clearTimeout(saveTimeout);
                
                // Debounce: wait 1 second after user stops typing
                saveTimeout = setTimeout(() => {
                    saveGradePerQuarter(studentId, quarter, gradeValue, this, row);
                }, 1000);
                
                // Update average display in real-time
                updateAverageDisplay(row);
            });
            
            // Also save on blur (when user clicks away)
            input.addEventListener('blur', function() {
                if (saveTimeout) {
                    clearTimeout(saveTimeout);
                    const row = this.closest('tr');
                    const studentId = row.dataset.studentId;
                    const inputName = this.name;
                    const quarterMatch = inputName.match(/\[q([1-4])\]/);
                    const quarter = quarterMatch ? 'q' + quarterMatch[1] : null;
                    const gradeValue = this.value;
                    
                    // Check if quarter is enabled
                    const quarterUpper = 'Q' + quarter.charAt(1); // Convert q1 to Q1, etc.
                    const quarterEnabled = gradePeriods[quarterUpper]?.is_enabled && !gradePeriods[quarterUpper]?.is_expired;
                    
                    if (this.classList.contains('saving') && studentId && quarter && subjectId && quarterEnabled) {
                        saveGradePerQuarter(studentId, quarter, gradeValue, this, row);
                    }
                }
            });
        });
        
        // Auto-save grade per quarter via AJAX
        function saveGradePerQuarter(studentId, quarter, gradeValue, inputElement, row) {
            console.log('Saving grade - Student:', studentId, 'Quarter:', quarter, 'Grade:', gradeValue, 'Subject:', subjectId);
            
            const formData = new FormData();
            formData.append('save_single_grade', '1');
            formData.append('csrf_token', csrfToken);
            formData.append('student_id', studentId);
            formData.append('subject_id', subjectId);
            formData.append('quarter', quarter);
            formData.append('grade', gradeValue);
            
            console.log('Sending AJAX request...');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Success feedback
                    inputElement.classList.remove('saving', 'error');
                    inputElement.classList.add('saved');
                    
                    // Update average in the table
                    if (data.average !== null) {
                        const averageCell = row.querySelector('.average-cell strong');
                        averageCell.textContent = data.average;
                        const avgValue = parseFloat(data.average);
                        averageCell.classList.remove('text-muted', 'text-success', 'text-danger');
                        averageCell.classList.add(avgValue >= 75 ? 'text-success' : 'text-danger');
                    }
                    
                    // Show success notification
                    showNotification('Grade saved successfully', 'success');
                    
                    // Remove saved state after 2 seconds
                    setTimeout(() => {
                        inputElement.classList.remove('saved');
                    }, 2000);
                } else {
                    // Error feedback
                    console.error('Save failed:', data.message);
                    inputElement.classList.remove('saving', 'saved');
                    inputElement.classList.add('error');
                    showNotification(data.message || 'Failed to save grade', 'error');
                }
            })
            .catch(error => {
                console.error('Network error:', error);
                inputElement.classList.remove('saving', 'saved');
                inputElement.classList.add('error');
                showNotification('Network error: ' + error.message, 'error');
            });
        }
        
        // Edit grade button functionality
        document.querySelectorAll('.edit-grade-btn').forEach(button => {
            button.addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                const studentName = this.dataset.studentName;
                const q1 = this.dataset.q1;
                const q2 = this.dataset.q2;
                const q3 = this.dataset.q3;
                const q4 = this.dataset.q4;
                const avg = this.dataset.avg;
                
                // Determine which quarters are enabled
                const q1Enabled = gradePeriods['Q1']?.is_enabled && !gradePeriods['Q1']?.is_expired;
                const q2Enabled = gradePeriods['Q2']?.is_enabled && !gradePeriods['Q2']?.is_expired;
                const q3Enabled = gradePeriods['Q3']?.is_enabled && !gradePeriods['Q3']?.is_expired;
                const q4Enabled = gradePeriods['Q4']?.is_enabled && !gradePeriods['Q4']?.is_expired;
                
                // Populate modal fields
                document.getElementById('editStudentId').value = studentId;
                document.getElementById('editStudentName').textContent = studentName;
                document.getElementById('editQ1').value = q1;
                document.getElementById('editQ2').value = q2;
                document.getElementById('editQ3').value = q3;
                document.getElementById('editQ4').value = q4;
                document.getElementById('editAverage').textContent = avg || '--';
                
                // Set status based on average
                const avgValue = parseFloat(avg);
                if (avgValue) {
                    document.getElementById('editStatus').textContent = avgValue >= 75 ? 'PASSED' : 'FAILED';
                    document.getElementById('editStatus').className = avgValue >= 75 ? 'text-success' : 'text-danger';
                } else {
                    document.getElementById('editStatus').textContent = 'NO GRADE';
                    document.getElementById('editStatus').className = 'text-muted';
                }
                
                // Enable/disable inputs based on quarter status
                document.getElementById('editQ1').disabled = !q1Enabled;
                document.getElementById('editQ1').placeholder = q1Enabled ? '0.00' : 'Quarter Locked';
                document.getElementById('editQ2').disabled = !q2Enabled;
                document.getElementById('editQ2').placeholder = q2Enabled ? '0.00' : 'Quarter Locked';
                document.getElementById('editQ3').disabled = !q3Enabled;
                document.getElementById('editQ3').placeholder = q3Enabled ? '0.00' : 'Quarter Locked';
                document.getElementById('editQ4').disabled = !q4Enabled;
                document.getElementById('editQ4').placeholder = q4Enabled ? '0.00' : 'Quarter Locked';
                
                // Add help text for disabled quarters
                document.getElementById('editQ1').title = q1Enabled ? 'Enter Q1 grade' : 'Quarter is locked';
                document.getElementById('editQ2').title = q2Enabled ? 'Enter Q2 grade' : 'Quarter is locked';
                document.getElementById('editQ3').title = q3Enabled ? 'Enter Q3 grade' : 'Quarter is locked';
                document.getElementById('editQ4').title = q4Enabled ? 'Enter Q4 grade' : 'Quarter is locked';
                
                // Update button text based on availability
                const saveBtn = document.getElementById('saveGradeBtn');
                const anyEnabled = q1Enabled || q2Enabled || q3Enabled || q4Enabled;
                saveBtn.disabled = !anyEnabled;
                
                if (!anyEnabled) {
                    saveBtn.innerHTML = '<i class="bi bi-lock me-2"></i>All Quarters Locked';
                } else {
                    saveBtn.innerHTML = '<i class="bi bi-save me-2"></i>Save Grades';
                }
                
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('editGradeModal'));
                editModal.show();
            });
        });
        
        // Update average when editing grades in modal
        document.querySelectorAll('#editQ1, #editQ2, #editQ3, #editQ4').forEach(input => {
            input.addEventListener('input', function() {
                updateEditModalAverage();
            });
        });
        
        // Save grade from modal
        document.getElementById('saveGradeBtn').addEventListener('click', function() {
            const form = document.getElementById('editGradeForm');
            const formData = new FormData(form);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Grades saved successfully', 'success');
                    
                    // Update the main table
                    const studentId = document.getElementById('editStudentId').value;
                    const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
                    if (row) {
                        // Update Q1
                        const q1Input = row.querySelector('input[name*="[q1]"]');
                        if (q1Input) q1Input.value = formData.get('q1');
                        
                        // Update Q2
                        const q2Input = row.querySelector('input[name*="[q2]"]');
                        if (q2Input) q2Input.value = formData.get('q2');
                        
                        // Update Q3
                        const q3Input = row.querySelector('input[name*="[q3]"]');
                        if (q3Input) q3Input.value = formData.get('q3');
                        
                        // Update Q4
                        const q4Input = row.querySelector('input[name*="[q4]"]');
                        if (q4Input) q4Input.value = formData.get('q4');
                        
                        // Update average display
                        const avgCell = row.querySelector('.average-cell strong');
                        if (avgCell) {
                            avgCell.textContent = data.average || '--';
                            avgCell.className = 'text-muted';
                            if (data.average) {
                                const avgValue = parseFloat(data.average);
                                avgCell.classList.remove('text-muted');
                                avgCell.classList.add(avgValue >= 75 ? 'text-success' : 'text-danger');
                            }
                        }
                    }
                    
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editGradeModal')).hide();
                } else {
                    showNotification(data.message || 'Failed to save grades', 'error');
                }
            })
            .catch(error => {
                showNotification('Network error: ' + error.message, 'error');
            });
        });
        
        // Update average in edit modal
        function updateEditModalAverage() {
            const q1 = parseFloat(document.getElementById('editQ1').value) || 0;
            const q2 = parseFloat(document.getElementById('editQ2').value) || 0;
            const q3 = parseFloat(document.getElementById('editQ3').value) || 0;
            const q4 = parseFloat(document.getElementById('editQ4').value) || 0;
            
            const grades = [q1, q2, q3, q4].filter(val => val > 0);
            if (grades.length > 0) {
                const avg = grades.reduce((a, b) => a + b, 0) / 4;
                document.getElementById('editAverage').textContent = avg.toFixed(2);
                
                // Update status
                if (avg >= 75) {
                    document.getElementById('editStatus').textContent = 'PASSED';
                    document.getElementById('editStatus').className = 'text-success';
                } else {
                    document.getElementById('editStatus').textContent = 'FAILED';
                    document.getElementById('editStatus').className = 'text-danger';
                }
            } else {
                document.getElementById('editAverage').textContent = '--';
                document.getElementById('editStatus').textContent = 'NO GRADE';
                document.getElementById('editStatus').className = 'text-muted';
            }
        }
        
        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3`;
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.innerHTML = `
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.5s';
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
        
        // Print grade report function
        function printGradeReport() {
            window.print();
        }
    </script>
    <style>
        @media print {
            /* Hide elements not needed in print */
            .sidebar,
            .topbar,
            .btn-group,
            .page-header,
            .alert,
            .form-select,
            label[for="subject_id"],
            .grade-input {
                display: none !important;
            }
            
            /* Show grade values in print */
            .grade-cell::after {
                content: attr(data-value);
            }
            
            /* Print layout adjustments */
            body {
                padding: 20px;
                background: white;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            /* Add print header */
            .card::before {
                content: "Quarterly Grade Report";
                display: block;
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 20px;
            }
        }
        
        .grade-input {
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .grade-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .grade-input.saving {
            background-color: #fff3cd;
            border-color: #ffc107;
        }
        .grade-input.saved {
            background-color: #d1e7dd;
            border-color: #198754;
        }
        .grade-input.error {
            background-color: #f8d7da;
            border-color: #dc3545;
        }
        .table-bordered th,
        .table-bordered td {
            vertical-align: middle;
        }
        .average-cell strong {
            font-size: 1.1rem;
        }
    </style>

    <!-- Grade Report Modal - Placed before closing body tag -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reportModalLabel">
                        <i class="bi bi-file-earmark-text me-2"></i>Quarterly Grade Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($selectedSubjectId && !empty($enrolledStudents)): 
                        $selectedSubject = null;
                        foreach ($teacherSubjects as $subj) {
                            if ($subj['id'] == $selectedSubjectId) {
                                $selectedSubject = $subj;
                                break;
                            }
                        }
                    ?>
                        <div class="mb-4 text-center">
                            <h3><?= htmlspecialchars($selectedSubject['name'] ?? 'Subject Report') ?></h3>
                            <p class="text-muted">Subject Code: <?= htmlspecialchars($selectedSubject['code'] ?? 'N/A') ?></p>
                            <p class="text-muted">Report Generated: <?= date('F d, Y - h:i A') ?></p>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="reportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 80px;">#</th>
                                        <th style="min-width: 120px;">Student ID</th>
                                        <th style="min-width: 200px;">Student Name</th>
                                        <th class="text-center" style="width: 100px;">Q1</th>
                                        <th class="text-center" style="width: 100px;">Q2</th>
                                        <th class="text-center" style="width: 100px;">Q3</th>
                                        <th class="text-center" style="width: 100px;">Q4</th>
                                        <th class="text-center bg-light" style="width: 120px;">AVERAGE</th>
                                        <th class="text-center" style="width: 100px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $counter = 1;
                                    $passCount = 0;
                                    $failCount = 0;
                                    $noGradeCount = 0;
                                    
                                    foreach ($enrolledStudents as $student): 
                                        $grades = $studentGrades[$student['id']] ?? [];
                                        $q1 = $grades['q1_grade'] ?? null;
                                        $q2 = $grades['q2_grade'] ?? null;
                                        $q3 = $grades['q3_grade'] ?? null;
                                        $q4 = $grades['q4_grade'] ?? null;
                                        $avg = $grades['average_grade'] ?? null;
                                        
                                        if ($avg !== null) {
                                            if ((float)$avg >= 75) {
                                                $passCount++;
                                                $status = 'PASSED';
                                                $statusClass = 'text-success';
                                                $statusBadge = 'bg-success';
                                            } else {
                                                $failCount++;
                                                $status = 'FAILED';
                                                $statusClass = 'text-danger';
                                                $statusBadge = 'bg-danger';
                                            }
                                        } else {
                                            $noGradeCount++;
                                            $status = 'NO GRADE';
                                            $statusClass = 'text-muted';
                                            $statusBadge = 'bg-secondary';
                                        }
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $counter++ ?></td>
                                        <td><strong><?= htmlspecialchars($student['empidno']) ?></strong></td>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <td class="text-center"><?= $q1 !== null ? number_format((float)$q1, 2) : '--' ?></td>
                                        <td class="text-center"><?= $q2 !== null ? number_format((float)$q2, 2) : '--' ?></td>
                                        <td class="text-center"><?= $q3 !== null ? number_format((float)$q3, 2) : '--' ?></td>
                                        <td class="text-center"><?= $q4 !== null ? number_format((float)$q4, 2) : '--' ?></td>
                                        <td class="text-center bg-light">
                                            <strong class="<?= $statusClass ?>">
                                                <?= $avg !== null ? number_format((float)$avg, 2) : '--' ?>
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $statusBadge ?>"><?= $status ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <strong>Summary:</strong> 
                                            <span class="badge bg-success ms-2">Passed: <?= $passCount ?></span>
                                            <span class="badge bg-danger ms-2">Failed: <?= $failCount ?></span>
                                            <span class="badge bg-secondary ms-2">No Grade: <?= $noGradeCount ?></span>
                                            <span class="badge bg-info ms-2">Total: <?= count($enrolledStudents) ?></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p>No data available. Please select a subject and ensure students are enrolled.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="printReport()">
                        <i class="bi bi-printer me-2"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
