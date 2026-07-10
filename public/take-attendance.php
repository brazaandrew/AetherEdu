<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/AttendanceController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'teacher'])) {
    header('Location: dashboard.php');
    exit;
}

$attendanceController = new AttendanceController();

// Get subjects
if ($role === 'admin') {
    $subjects = $attendanceController->getAllSubjects();
} else {
    $subjects = $attendanceController->getSubjectsForTeacher($user['empidno']);
}

$selectedSubjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$selectedDate = $_GET['date'] ?? date('Y-m-d');

$students = [];
$existingAttendance = [];

if ($selectedSubjectId) {
    $students = $attendanceController->getStudentsForSubject($selectedSubjectId);
    $existingAttendance = $attendanceController->getAttendanceForSubject($selectedSubjectId, $selectedDate);
    // Index by student_id
    $attendanceMap = [];
    foreach ($existingAttendance as $att) {
        $attendanceMap[$att['student_id']] = $att;
    }
    $existingAttendance = $attendanceMap;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $subjectId = (int)$_POST['subject_id'];
    $date = $_POST['date'];
    $result = $attendanceController->saveAttendance($subjectId, $date, $_POST['attendance'], $user['id']);
    if ($result['success']) {
        $_SESSION['attendance_message'] = $result['message'];
    } else {
        $_SESSION['attendance_error'] = $result['error'];
    }
    header("Location: take-attendance.php?subject_id=$subjectId&date=$date");
    exit;
}

$message = $_SESSION['attendance_message'] ?? '';
$error = $_SESSION['attendance_error'] ?? '';
unset($_SESSION['attendance_message'], $_SESSION['attendance_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4"><i class="bi bi-clipboard-check me-2"></i>Take Attendance</h2>
            
            <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            
            <!-- Selection Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-select" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subj): ?>
                                <option value="<?= $subj['id'] ?>" <?= $selectedSubjectId == $subj['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subj['code'] . ' - ' . $subj['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" class="form-control" value="<?= $selectedDate ?>" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Load Students</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($selectedSubjectId && !empty($students)): ?>
            <form method="POST">
                <input type="hidden" name="subject_id" value="<?= $selectedSubjectId ?>">
                <input type="hidden" name="date" value="<?= $selectedDate ?>">
                
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Mark Attendance - <?= date('F d, Y', strtotime($selectedDate)) ?></h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')"><i class="bi bi-check-all"></i> All Present</button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')"><i class="bi bi-x-circle"></i> All Absent</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th style="width: 350px">Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $i => $student): 
                                        $existing = $existingAttendance[$student['id']] ?? null;
                                        $currentStatus = $existing['status'] ?? 'present';
                                    ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($student['empidno']) ?></td>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check" name="attendance[<?= $student['id'] ?>][status]" id="present_<?= $student['id'] ?>" value="present" <?= $currentStatus === 'present' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-success btn-sm" for="present_<?= $student['id'] ?>"><i class="bi bi-check"></i> Present</label>
                                                
                                                <input type="radio" class="btn-check" name="attendance[<?= $student['id'] ?>][status]" id="absent_<?= $student['id'] ?>" value="absent" <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-danger btn-sm" for="absent_<?= $student['id'] ?>"><i class="bi bi-x"></i> Absent</label>
                                                
                                                <input type="radio" class="btn-check" name="attendance[<?= $student['id'] ?>][status]" id="late_<?= $student['id'] ?>" value="late" <?= $currentStatus === 'late' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-warning btn-sm" for="late_<?= $student['id'] ?>"><i class="bi bi-clock"></i> Late</label>
                                                
                                                <input type="radio" class="btn-check" name="attendance[<?= $student['id'] ?>][status]" id="excused_<?= $student['id'] ?>" value="excused" <?= $currentStatus === 'excused' ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-info btn-sm" for="excused_<?= $student['id'] ?>"><i class="bi bi-file-text"></i> Excused</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[<?= $student['id'] ?>][remarks]" class="form-control form-control-sm" value="<?= htmlspecialchars($existing['remarks'] ?? '') ?>" placeholder="Optional remarks">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Attendance</button>
                        <a href="attendance.php" class="btn btn-secondary"><i class="bi bi-list"></i> View Records</a>
                    </div>
                </div>
            </form>
            <?php elseif ($selectedSubjectId): ?>
                <div class="alert alert-info">No students enrolled in this subject.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAll(status) {
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                if (radio.value === status) {
                    radio.checked = true;
                }
            });
        }
    </script>
</body>
</html>
