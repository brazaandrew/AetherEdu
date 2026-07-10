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

if (!in_array($role, ['teacher', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

$attendanceController = new AttendanceController();

// Handle clock in/out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clock_in'])) {
        $result = $attendanceController->clockIn($user['id']);
        if ($result['success']) {
            $_SESSION['time_log_message'] = $result['message'];
        } else {
            $_SESSION['time_log_error'] = $result['error'];
        }
    } elseif (isset($_POST['clock_out'])) {
        $result = $attendanceController->clockOut($user['id']);
        if ($result['success']) {
            $_SESSION['time_log_message'] = $result['message'];
        } else {
            $_SESSION['time_log_error'] = $result['error'];
        }
    }
    header('Location: teacher-time-log.php');
    exit;
}

$todayRecord = $attendanceController->getTeacherAttendanceToday($user['id']);
$teacherRecords = $attendanceController->getTeacherAttendance($user['id']);
$summary = $attendanceController->getTeacherAttendanceSummary($user['id']);

$message = $_SESSION['time_log_message'] ?? '';
$error = $_SESSION['time_log_error'] ?? '';
unset($_SESSION['time_log_message'], $_SESSION['time_log_error']);

// Calculate hours today
$hoursToday = '';
if ($todayRecord && $todayRecord['time_in']) {
    $timeIn = new DateTime($todayRecord['time_in']);
    $timeOut = $todayRecord['time_out'] ? new DateTime($todayRecord['time_out']) : new DateTime();
    $diff = $timeIn->diff($timeOut);
    $hoursToday = $diff->h + ($diff->i / 60);
    $hoursToday = round($hoursToday, 1);
}

$canClockIn = !$todayRecord || !$todayRecord['time_in'];
$canClockOut = $todayRecord && $todayRecord['time_in'] && !$todayRecord['time_out'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Time Log - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4"><i class="bi bi-clock-history me-2"></i>Teacher Time Log</h2>
            
            <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            
            <!-- Today's Status Card -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 <?= $todayRecord && $todayRecord['time_in'] ? 'border-success' : 'border-secondary' ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><i class="bi bi-calendar-check"></i> Today - <?= date('F d, Y') ?></h5>
                            
                            <?php if ($todayRecord && $todayRecord['time_in']): ?>
                                <div class="mb-3">
                                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Clocked In</span>
                                    <p class="mt-2 mb-0 text-muted">Time In: <strong><?= date('h:i A', strtotime($todayRecord['time_in'])) ?></strong></p>
                                    <?php if ($todayRecord['time_out']): ?>
                                        <p class="mb-0 text-muted">Time Out: <strong><?= date('h:i A', strtotime($todayRecord['time_out'])) ?></strong></p>
                                    <?php endif; ?>
                                    <?php if ($hoursToday): ?>
                                        <p class="mb-0 text-primary fw-bold">Hours: <?= $hoursToday ?> hrs</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <span class="badge bg-secondary fs-6"><i class="bi bi-circle"></i> Not Clocked In</span>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="d-flex gap-2 justify-content-center mb-3">
                                <?php if ($canClockIn): ?>
                                <button type="submit" name="clock_in" class="btn btn-success btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Time In
                                </button>
                                <?php endif; ?>
                                <?php if ($canClockOut): ?>
                                <button type="submit" name="clock_out" class="btn btn-danger btn-lg">
                                    <i class="bi bi-box-arrow-right"></i> Time Out
                                </button>
                                <?php endif; ?>
                                <?php if ($todayRecord && $todayRecord['time_out']): ?>
                                    <span class="text-muted"><i class="bi bi-check-all"></i> Done for today</span>
                                <?php endif; ?>
                            </form>
                            <a href="qr-attendance.php" class="btn btn-outline-primary">
                                <i class="bi bi-qr-code-scan"></i> Scan QR to Clock In/Out
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card bg-success text-white text-center h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-check-circle"></i> Present</h6>
                                    <h3><?= $summary['present'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white text-center h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-x-circle"></i> Absent</h6>
                                    <h3><?= $summary['absent'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark text-center h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-clock"></i> Late</h6>
                                    <h3><?= $summary['late'] ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white text-center h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-calendar-event"></i> Half Day</h6>
                                    <h3><?= $summary['half_day'] ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- History Table -->
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0">Time Log History</h5></div>
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
                                    <td><?= date('M d, Y (D)', strtotime($rec['date'])) ?></td>
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
                                <tr><td colspan="5" class="text-center text-muted">No time log records yet.</td></tr>
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
