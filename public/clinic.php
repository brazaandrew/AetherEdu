<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/ClinicController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'teacher', 'nurse'])) {
    header('Location: dashboard.php');
    exit;
}

$clinicController = new ClinicController();
$stats = $clinicController->getStats();
$recentVisits = $clinicController->getAllVisits(10);

// Get students list for search
$db = db();
$stmt = $db->query("SELECT id, name, empidno, grade_level FROM users WHERE role = 'student' AND archived = 0 ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['clinic_message'] ?? '';
$error = $_SESSION['clinic_error'] ?? '';
unset($_SESSION['clinic_message'], $_SESSION['clinic_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <h2 class="mb-4">School Clinic</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Medical Profiles</h6>
                            <h3><?= $stats['total_profiles'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Today's Visits</h6>
                            <h3><?= $stats['today_visits'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Monthly Visits</h6>
                            <h3><?= $stats['month_visits'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Students (30d)</h6>
                            <h3><?= $stats['students_visited_30d'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search Student -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-search"></i> Find Student Medical Record</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <select class="form-select" id="studentSelect" onchange="viewStudentProfile(this.value)">
                                <option value="">Select a student...</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= htmlspecialchars($student['name']) ?> (<?= $student['empidno'] ?>)
                                    <?= $student['grade_level'] ? '- ' . $student['grade_level'] : '' ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="searchInput" placeholder="Type to search students..." onkeyup="filterStudents()">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Recent Visits -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Clinic Visits</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentVisits)): ?>
                                <p class="text-muted">No recent visits.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student</th>
                                                <th>Complaint</th>
                                                <th>Action</th>
                                                <th>Attended By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentVisits as $visit): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($visit['visit_date'])) ?></td>
                                                <td><?= htmlspecialchars($visit['student_name']) ?></td>
                                                <td><?= htmlspecialchars(substr($visit['complaint'] ?? '', 0, 50)) ?>...</td>
                                                <td>
                                                    <span class="badge bg-<?= match($visit['action_taken']) {
                                                        'treated' => 'success',
                                                        'referred' => 'warning',
                                                        'sent_home' => 'danger',
                                                        'rested' => 'info',
                                                        default => 'secondary'
                                                    } ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $visit['action_taken'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($visit['attended_by_name'] ?? 'N/A') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="clinic-student-profile.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> New Medical Profile
                                </a>
                                <a href="clinic-student-profile.php?action=visit" class="btn btn-success">
                                    <i class="bi bi-heart-pulse"></i> Record Clinic Visit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewStudentProfile(studentId) {
            if (studentId) {
                window.location.href = 'clinic-student-profile.php?student_id=' + studentId;
            }
        }
        
        function filterStudents() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const select = document.getElementById('studentSelect');
            const options = select.getElementsByTagName('option');
            
            for (let i = 1; i < options.length; i++) {
                const text = options[i].text.toLowerCase();
                options[i].style.display = text.includes(input) ? '' : 'none';
            }
        }
    </script>
</body>
</html>
