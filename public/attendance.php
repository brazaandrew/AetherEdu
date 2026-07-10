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

$attendanceController = new AttendanceController();

// Handle filters
$filterSubjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$filterDateFrom = $_GET['date_from'] ?? date('Y-m-01');
$filterDateTo = $_GET['date_to'] ?? date('Y-m-d');

$subjects = [];
if (in_array($role, ['admin', 'teacher'])) {
    $subjects = $role === 'admin' ? $attendanceController->getAllSubjects() : $attendanceController->getSubjectsForTeacher($user['empidno']);
}

// Load attendance data based on role
$attendanceRecords = [];
$summary = [];

if ($role === 'student') {
    $attendanceRecords = $attendanceController->getStudentAttendance($user['id'], $filterSubjectId ?: null, $filterDateFrom, $filterDateTo);
    $summary = $attendanceController->getStudentAttendanceSummary($user['id'], $filterSubjectId ?: null);
} elseif ($role === 'teacher') {
    if ($filterSubjectId) {
        $attendanceRecords = $attendanceController->getStudentAttendance(0, $filterSubjectId, $filterDateFrom, $filterDateTo);
    }
} elseif ($role === 'admin') {
    if ($filterSubjectId) {
        $attendanceRecords = $attendanceController->getStudentAttendance(0, $filterSubjectId, $filterDateFrom, $filterDateTo);
    }
}

// For admin/teacher, we need student names - query all records for the subject
if (in_array($role, ['admin', 'teacher']) && $filterSubjectId) {
    $attendanceRecords = [];
    $stmt = db()->prepare("SELECT sa.*, u.name as student_name, u.empidno as student_idno, s.name as subject_name, s.code as subject_code 
        FROM student_attendance sa 
        JOIN users u ON sa.student_id = u.id 
        JOIN subjects s ON sa.subject_id = s.id 
        WHERE sa.subject_id = ? AND sa.date >= ? AND sa.date <= ? 
        ORDER BY sa.date DESC, u.name");
    $stmt->execute([$filterSubjectId, $filterDateFrom, $filterDateTo]);
    $attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4"><i class="bi bi-list-check me-2"></i>Attendance Records</h2>
            
            <?php if ($role === 'student'): ?>
            <!-- Student Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <h6>Present</h6>
                            <h3><?= $summary['present'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white text-center">
                        <div class="card-body">
                            <h6>Absent</h6>
                            <h3><?= $summary['absent'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark text-center">
                        <div class="card-body">
                            <h6>Late</h6>
                            <h3><?= $summary['late'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h6>Excused</h6>
                            <h3><?= $summary['excused'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="alert alert-light border mb-4">
                <strong>Attendance Rate:</strong> 
                <?php if ($summary['total'] > 0): ?>
                    <?= round(($summary['present'] / $summary['total']) * 100, 1) ?>% 
                    (<?= $summary['present'] ?> out of <?= $summary['total'] ?> days)
                <?php else: ?>
                    No attendance records yet.
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <?php if (!empty($subjects)): ?>
                        <div class="col-md-4">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" class="form-select">
                                <option value="">All Subjects</option>
                                <?php foreach ($subjects as $subj): ?>
                                <option value="<?= $subj['id'] ?>" <?= $filterSubjectId == $subj['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subj['code'] . ' - ' . $subj['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?= $filterDateFrom ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?= $filterDateTo ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Attendance Table -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attendance Records</h5>
                    <?php if (in_array($role, ['admin', 'teacher'])): ?>
                    <a href="take-attendance.php" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> Take Attendance</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <?php if ($role !== 'student'): ?><th>Student</th><?php endif; ?>
                                    <?php if (in_array($role, ['admin', 'teacher'])): ?><th>Subject</th><?php endif; ?>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendanceRecords as $record): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                                    <?php if ($role !== 'student'): ?><td><?= htmlspecialchars($record['student_name'] ?? '-') ?> (<?= htmlspecialchars($record['student_idno'] ?? '') ?>)</td><?php endif; ?>
                                    <?php if (in_array($role, ['admin', 'teacher'])): ?><td><?= htmlspecialchars(($record['subject_code'] ?? '') . ' - ' . ($record['subject_name'] ?? '')) ?></td><?php endif; ?>
                                    <td>
                                        <span class="badge bg-<?= match($record['status']) {
                                            'present' => 'success',
                                            'absent' => 'danger',
                                            'late' => 'warning',
                                            'excused' => 'info',
                                            default => 'secondary'
                                        } ?>">
                                            <?= ucfirst($record['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($record['remarks'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($attendanceRecords)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No attendance records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
