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

// Determine what to show based on role
$isTeacher = in_array($role, ['teacher', 'admin']);
$isStudent = ($role === 'student');

$studentSummary = [];
$studentRecords = [];
$teacherSummary = [];
$teacherRecords = [];

if ($isStudent) {
    $studentRecords = $attendanceController->getStudentAttendance($user['id']);
    $studentSummary = $attendanceController->getStudentAttendanceSummary($user['id']);
} elseif ($isTeacher) {
    $teacherRecords = $attendanceController->getTeacherAttendance($user['id']);
    $teacherSummary = $attendanceController->getTeacherAttendanceSummary($user['id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4"><i class="bi bi-person-check me-2"></i>My Attendance</h2>
            
            <?php if ($isStudent): ?>
            <!-- Student Attendance View -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-check-circle"></i> Present</h6>
                            <h3><?= $studentSummary['present'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-x-circle"></i> Absent</h6>
                            <h3><?= $studentSummary['absent'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-clock"></i> Late</h6>
                            <h3><?= $studentSummary['late'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-file-text"></i> Excused</h6>
                            <h3><?= $studentSummary['excused'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-light border mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Overall Attendance Rate:</strong>
                        <?php if ($studentSummary['total'] > 0): ?>
                            <span class="fs-4 text-success"><?= round(($studentSummary['present'] / $studentSummary['total']) * 100, 1) ?>%</span>
                            <small class="text-muted">(<?= $studentSummary['present'] ?> present out of <?= $studentSummary['total'] ?> total days)</small>
                        <?php else: ?>
                            <span class="text-muted">No attendance records yet.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0">Attendance History</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Remarks</th></tr></thead>
                            <tbody>
                                <?php foreach ($studentRecords as $rec): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($rec['date'])) ?></td>
                                    <td><?= htmlspecialchars(($rec['subject_code'] ?? '') . ' - ' . ($rec['subject_name'] ?? '')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($rec['status']) { 'present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'excused' => 'info', default => 'secondary' } ?>">
                                            <?= ucfirst($rec['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($rec['remarks'] ?? '-') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($studentRecords)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No attendance records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php elseif ($isTeacher): ?>
            <!-- Teacher Attendance View -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-check-circle"></i> Present</h6>
                            <h3><?= $teacherSummary['present'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-x-circle"></i> Absent</h6>
                            <h3><?= $teacherSummary['absent'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-clock"></i> Late</h6>
                            <h3><?= $teacherSummary['late'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white text-center">
                        <div class="card-body">
                            <h6><i class="bi bi-calendar-event"></i> Half Day</h6>
                            <h3><?= $teacherSummary['half_day'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-light border mb-4">
                <strong>Total Records:</strong> <?= $teacherSummary['total'] ?> days logged
            </div>
            
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Time Log History</h5>
                    <a href="teacher-time-log.php" class="btn btn-sm btn-primary"><i class="bi bi-clock"></i> Go to Time Log</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Hours</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($teacherRecords as $rec): 
                                    $hours = '';
                                    if ($rec['time_in'] && $rec['time_out']) {
                                        $in = new DateTime($rec['time_in']);
                                        $out = new DateTime($rec['time_out']);
                                        $diff = $in->diff($out);
                                        $hours = $diff->h + ($diff->i / 60);
                                        $hours = round($hours, 1) . ' hrs';
                                    }
                                ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($rec['date'])) ?></td>
                                    <td><?= $rec['time_in'] ? date('h:i A', strtotime($rec['time_in'])) : '-' ?></td>
                                    <td><?= $rec['time_out'] ? date('h:i A', strtotime($rec['time_out'])) : '-' ?></td>
                                    <td><?= $hours ?: '-' ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($rec['status']) { 'present' => 'success', 'absent' => 'danger', 'late' => 'warning', 'half_day' => 'info', default => 'secondary' } ?>">
                                            <?= ucfirst(str_replace('_', ' ', $rec['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($teacherRecords)): ?>
                                <tr><td colspan="5" class="text-center text-muted">No time log records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
