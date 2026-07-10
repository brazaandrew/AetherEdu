<?php
declare(strict_types=1);

try {
    require_once __DIR__ . '/../src/Helpers/env.php';
    require_once __DIR__ . '/../src/Helpers/database.php';
    require_once __DIR__ . '/../src/Helpers/session.php';
    require_once __DIR__ . '/../src/Helpers/auth.php';
    require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
    
    loadEnv(__DIR__ . '/../.env');
    startSecureSession();
    
    $user = AuthMiddleware::requireAuth();
    $role = $user['role'];
    
    // Get analytics data
    $analytics = getAdminDashboardAnalytics();
    $recentActivityLogs = getRecentActivityLogs(10);
    $gradeAnalytics = getGradeAnalytics();
    
    // Get chart data
    $userDistributionData = getUserDistributionChartData();
    $contentCreationData = getContentCreationChartData();
    $gradeDistributionData = getGradeDistributionChartData();
    $enrollmentTrendsData = getEnrollmentTrendsChartData();
} catch (Throwable $e) {
    header("Content-Type: text/plain");
    echo "DASHBOARD ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Premium Dashboard Theme Customizations */
        .dashboard-stat-card {
            background: var(--bg-card);
            border-radius: var(--border-radius-md);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
            border: 1px solid var(--border-color);
            height: 100%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .dashboard-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .dashboard-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
        }
        
        .dashboard-stat-card.users::before { background: var(--primary); }
        .dashboard-stat-card.subjects::before { background: var(--accent); }
        .dashboard-stat-card.activities::before { background: var(--warning); }
        .dashboard-stat-card.quizzes::before { background: var(--success); }
        .dashboard-stat-card.teachers::before { background: var(--primary); }
        .dashboard-stat-card.students::before { background: var(--accent); }
        .dashboard-stat-card.admins::before { background: var(--danger); }
        .dashboard-stat-card.it::before { background: var(--secondary-color); }
        .dashboard-stat-card.pending::before { background: var(--danger); }
        
        .dashboard-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            flex-shrink: 0;
        }
        
        .dashboard-stat-card.users .dashboard-stat-icon { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .dashboard-stat-card.subjects .dashboard-stat-icon { background: rgba(20, 184, 166, 0.1); color: var(--accent); }
        .dashboard-stat-card.activities .dashboard-stat-icon { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .dashboard-stat-card.quizzes .dashboard-stat-icon { background: rgba(34, 197, 94, 0.1); color: var(--success); }
        .dashboard-stat-card.teachers .dashboard-stat-icon { background: rgba(37, 99, 235, 0.1); color: var(--primary); }
        .dashboard-stat-card.students .dashboard-stat-icon { background: rgba(20, 184, 166, 0.1); color: var(--accent); }
        .dashboard-stat-card.admins .dashboard-stat-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .dashboard-stat-card.it .dashboard-stat-icon { background: rgba(59, 130, 246, 0.1); color: var(--info-color); }
        .dashboard-stat-card.pending .dashboard-stat-icon { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        
        .dashboard-stat-card h6 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .dashboard-stat-card h2 {
            font-weight: 800;
            font-size: 2rem;
            color: var(--text-primary);
            margin: 0;
        }
        
        .analytics-section {
            margin-top: 2rem;
        }
        
        .analytics-section h4 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-top: 1rem;
        }
        
        .analytics-card {
            background: var(--bg-card);
            border-radius: var(--border-radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .analytics-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .analytics-card h6 {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .analytics-card h4 {
            font-weight: 800;
            font-size: 1.75rem;
            color: var(--primary);
            margin: 0;
        }
        
        .analytics-card .trend {
            font-size: 0.85rem;
            margin-top: 0.4rem;
            font-weight: 600;
        }
        
        .positive-trend { color: var(--success); }
        .negative-trend { color: var(--danger); }
        
        .activity-item {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--bg-card);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item:hover {
            background-color: rgba(37, 99, 235, 0.02);
        }
        
        .activity-user {
            font-weight: 700;
            color: var(--primary);
        }
        
        .activity-action {
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .activity-timestamp {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius-md);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }
        
        .welcome-banner h2 {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
            position: relative;
            color: #FFFFFF;
        }
        
        .welcome-banner p {
            opacity: 0.9;
            margin: 0;
            position: relative;
            color: rgba(255, 255, 255, 0.85);
        }
        
        @media (max-width: 992px) {
            .dashboard-stat-card h2 {
                font-size: 1.75rem;
            }
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <?php 
        $pageTitle = 'Dashboard';
        include 'includes/topbar.php'; 
        ?>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">
            <?php if ($role === 'admin'): ?>
                <!-- Admin Dashboard -->
                <?php
                $totalUsers = $analytics['total']['users'];
                $totalStudents = $analytics['by_role']['students'] ?? 0;
                $totalSubjects = $analytics['total']['subjects'];
                $totalActivities = $analytics['total']['activities'];
                $totalQuizzes = $analytics['total']['quizzes'];
                ?>
                
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h2>
                    <p>Monitor system performance, user activity, and academic progress</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-xl col-md-4 col-sm-6 col-12">
                        <div class="dashboard-stat-card users">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Total Users</h6>
                                    <h2><?= $totalUsers ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-4 col-sm-6 col-12">
                        <div class="dashboard-stat-card students">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Total Students</h6>
                                    <h2><?= $totalStudents ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-4 col-sm-6 col-12">
                        <div class="dashboard-stat-card subjects">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Subjects</h6>
                                    <h2><?= $totalSubjects ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-book"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6 col-sm-6 col-12">
                        <div class="dashboard-stat-card activities">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Activities</h6>
                                    <h2><?= $totalActivities ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl col-md-6 col-sm-6 col-12">
                        <div class="dashboard-stat-card quizzes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Quizzes</h6>
                                    <h2><?= $totalQuizzes ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-card-checklist"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div class="analytics-section">
                    <h4><i class="bi bi-graph-up-arrow me-2"></i>Analytics Overview</h4>
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h6>Recent Activity (7 days)</h6>
                            <h4><?= $analytics['recent']['activities'] ?></h4>
                            <div class="trend positive-trend">+<?= $analytics['recent']['activities'] ?> activities created</div>
                        </div>
                        <div class="analytics-card">
                            <h6>Recent Quizzes (7 days)</h6>
                            <h4><?= $analytics['recent']['quizzes'] ?></h4>
                            <div class="trend positive-trend">+<?= $analytics['recent']['quizzes'] ?> quizzes created</div>
                        </div>
                        <div class="analytics-card">
                            <h6>Recent Submissions (7 days)</h6>
                            <h4><?= $analytics['recent']['submissions'] ?></h4>
                            <div class="trend positive-trend">+<?= $analytics['recent']['submissions'] ?> submissions</div>
                        </div>
                        <div class="analytics-card">
                            <h6>Recent Quiz Attempts (7 days)</h6>
                            <h4><?= $analytics['recent']['quiz_attempts'] ?></h4>
                            <div class="trend positive-trend">+<?= $analytics['recent']['quiz_attempts'] ?> attempts</div>
                        </div>
                    </div>
                </div>

                <!-- Grade Analytics Section -->
                <div class="analytics-section">
                    <h4><i class="bi bi-award me-2"></i>Grade Analytics</h4>
                    <div class="analytics-grid">
                        <div class="analytics-card">
                            <h6>Average Grade</h6>
                            <h4><?= $gradeAnalytics['average_grade'] !== null ? number_format((float)$gradeAnalytics['average_grade'], 2) : 'N/A' ?></h4>
                            <div class="trend">Overall average grade</div>
                        </div>
                        <div class="analytics-card">
                            <h6>Excellent Grades (90+)</h6>
                            <h4><?= $gradeAnalytics['excellent_count'] ?></h4>
                            <div class="trend positive-trend">Students with excellent grades</div>
                        </div>
                        <div class="analytics-card">
                            <h6>Good Grades (80-89)</h6>
                            <h4><?= $gradeAnalytics['good_count'] ?></h4>
                            <div class="trend positive-trend">Students with good grades</div>
                        </div>
                        <div class="analytics-card">
                            <h6>Improvement Needed (<70)</h6>
                            <h4><?= $gradeAnalytics['poor_count'] ?></h4>
                            <div class="trend negative-trend">Students needing support</div>
                        </div>
                    </div>
                </div>

                <!-- Student Distribution by Year Level -->
                <div class="analytics-section">
                    <h4><i class="bi bi-grid-3x3-gap-fill me-2"></i>Students by Grade Level</h4>
                    <div class="row g-3">
                        <?php 
                        $db = db();
                        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
                        if ($driver === 'pgsql') {
                            $stmtLevels = $db->query("SELECT grade_level, COUNT(*) AS student_count FROM users WHERE role = 'student' AND archived = 0 AND grade_level IS NOT NULL AND grade_level <> '' GROUP BY grade_level ORDER BY CAST(NULLIF(regexp_replace(grade_level, '\\D', '', 'g'), '') AS INTEGER) ASC, grade_level ASC");
                        } else {
                            $stmtLevels = $db->query("SELECT grade_level, COUNT(*) AS student_count FROM users WHERE role = 'student' AND archived = 0 AND grade_level IS NOT NULL AND grade_level <> '' GROUP BY grade_level ORDER BY CAST(grade_level AS UNSIGNED) ASC, grade_level ASC");
                        }
                        $gradeLevels = $stmtLevels->fetchAll();
                        if (empty($gradeLevels)):
                        ?>
                            <div class="col-12">
                                <div class="alert alert-light border text-center text-muted py-3 mb-0">
                                    No students are enrolled in any grade levels.
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($gradeLevels as $gl): ?>
                                <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                                    <div class="analytics-card text-center py-3">
                                        <h6 class="text-uppercase text-muted" style="font-size: 0.8rem; letter-spacing: 0.5px; font-weight: 600;">Grade <?= htmlspecialchars((string)$gl['grade_level']) ?></h6>
                                        <h4 class="mb-0 text-primary mt-1" style="font-weight: 750; font-size: 1.6rem;"><?= (int)$gl['student_count'] ?></h4>
                                        <div class="trend positive-trend mt-2" style="font-size: 0.75rem;">
                                            <?= (int)$gl['student_count'] === 1 ? 'Student' : 'Students' ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="analytics-section">
                    <h4><i class="bi bi-bar-chart-line me-2"></i>Data Analytics Charts</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">User Distribution by Role</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="userDistributionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Content Creation Trends</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="contentCreationChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Grade Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="gradeDistributionChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Enrollment Trends</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="enrollmentTrendsChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($role === 'it_personnel'): ?>
                <!-- IT Personnel Dashboard -->
                <?php
                $totalTeachers = $analytics['by_role']['teachers'];
                $totalStudents = $analytics['by_role']['students'];
                $totalAdmins = $analytics['by_role']['admins'];
                $totalIT = $analytics['by_role']['it_personnel'];
                ?>
                
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2><i class="bi bi-laptop me-2"></i>IT Personnel Dashboard</h2>
                    <p>Manage users and monitor system status</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card teachers">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Teachers</h6>
                                    <h2><?= $totalTeachers ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card students">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Students</h6>
                                    <h2><?= $totalStudents ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card admins">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Admins</h6>
                                    <h2><?= $totalAdmins ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-shield-fill-exclamation"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card it">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>IT Personnel</h6>
                                    <h2><?= $totalIT ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-laptop"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($role === 'nurse'): ?>
                <!-- Nurse Dashboard -->
                <?php
                require_once __DIR__ . '/../src/Controllers/ClinicController.php';
                $clinicController = new ClinicController();
                $clinicStats = $clinicController->getStats();
                $recentVisits = $clinicController->getAllVisits(5);
                ?>
                            
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2><i class="bi bi-heart-pulse me-2"></i>Nurse Dashboard</h2>
                    <p>Manage student health records and clinic visits</p>
                </div>
                            
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card users">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Medical Profiles</h6>
                                    <h2><?= $clinicStats['total_profiles'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-file-medical"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card activities">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Today's Visits</h6>
                                    <h2><?= $clinicStats['today_visits'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card quizzes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Monthly Visits</h6>
                                    <h2><?= $clinicStats['month_visits'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card pending">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Students (30d)</h6>
                                    <h2><?= $clinicStats['students_visited_30d'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                            
                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <a href="clinic.php" class="btn btn-primary">
                                <i class="bi bi-heart-pulse"></i> School Clinic
                            </a>
                            <a href="clinic-student-profile.php" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> Record Visit
                            </a>
                        </div>
                    </div>
                </div>
                            
                <!-- Recent Visits -->
                <?php if (!empty($recentVisits)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Clinic Visits</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student</th>
                                                <th>Complaint</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentVisits as $visit): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($visit['visit_date'])) ?></td>
                                                <td><?= htmlspecialchars($visit['student_name']) ?></td>
                                                <td><?= htmlspecialchars(substr($visit['complaint'] ?? '', 0, 40)) ?>...</td>
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
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            
            <?php elseif ($role === 'hr'): ?>
                <!-- HR Dashboard -->
                <?php
                require_once __DIR__ . '/../src/Controllers/HRController.php';
                $hrController = new HRController();
                $hrStats = $hrController->getStats();
                $employees = $hrController->getAllEmployees();
                ?>
                
                <div class="welcome-banner">
                    <h2><i class="bi bi-person-vcard me-2"></i>HR Dashboard</h2>
                    <p>Manage employee 201 files and personnel records</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card users">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Total Employees</h6>
                                    <h2><?= $hrStats['total_employees'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card subjects">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>201 Files</h6>
                                    <h2><?= $hrStats['files_completed'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-folder-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card activities">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Teachers</h6>
                                    <h2><?= $hrStats['total_teachers'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card quizzes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Documents</h6>
                                    <h2><?= $hrStats['total_documents'] ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-files"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row g-4 mt-1">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Employee Overview</h5>
                                <a href="hr-dashboard.php" class="btn btn-sm btn-primary">View All Employees</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Role</th>
                                                <th>201 File Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $recentEmployees = array_slice($employees, 0, 5);
                                            foreach ($recentEmployees as $emp): 
                                                $has201File = $hrController->get201File($emp['id']) !== null;
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($emp['empidno']) ?></td>
                                                <td><?= htmlspecialchars($emp['name']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= match($emp['role']) {
                                                        'admin' => 'danger',
                                                        'teacher' => 'primary',
                                                        'registrar' => 'secondary',
                                                        'librarian' => 'warning',
                                                        'cashier' => 'info',
                                                        'nurse' => 'danger',
                                                        'hr' => 'dark',
                                                        'it_personnel' => 'info',
                                                        default => 'secondary'
                                                    } ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $emp['role'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($has201File): ?>
                                                        <span class="badge bg-success">Complete</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="employee-201-file.php?employee_id=<?= $emp['id'] ?>" class="btn btn-sm btn-primary">View 201 File</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            <?php elseif ($role === 'teacher'): ?>
                <!-- Teacher Dashboard -->
                <?php
                $stmt = db()->prepare('SELECT COUNT(DISTINCT ft.subject_id) FROM folder_teacher ft WHERE ft.teacher_empidno = ?');
                $stmt->execute([$user['empidno']]);
                $mySubjects = $stmt->fetchColumn();
                
                $stmt = db()->prepare('SELECT COUNT(*) FROM activities a JOIN folder_teacher ft ON a.subject_id = ft.subject_id WHERE ft.teacher_empidno = ?');
                $stmt->execute([$user['empidno']]);
                $myActivities = $stmt->fetchColumn();
                
                $stmt = db()->prepare('SELECT COUNT(*) FROM quizzes q JOIN folder_teacher ft ON q.subject_id = ft.subject_id WHERE ft.teacher_empidno = ?');
                $stmt->execute([$user['empidno']]);
                $myQuizzes = $stmt->fetchColumn();
                
                $stmt = db()->prepare('SELECT COUNT(*) FROM activity_submissions asub JOIN activities a ON asub.activity_id = a.id JOIN folder_teacher ft ON a.subject_id = ft.subject_id WHERE ft.teacher_empidno = ? AND asub.score IS NULL');
                $stmt->execute([$user['empidno']]);
                $pendingGrading = $stmt->fetchColumn();
                ?>
                
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2><i class="bi bi-person-badge me-2"></i>Teacher Dashboard</h2>
                    <p>Manage your subjects, activities, and student progress</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card subjects">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>My Subjects</h6>
                                    <h2><?= $mySubjects ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-book"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card activities">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Activities</h6>
                                    <h2><?= $myActivities ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card quizzes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Quizzes</h6>
                                    <h2><?= $myQuizzes ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-card-checklist"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card pending">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Pending Grading</h6>
                                    <h2><?= $pendingGrading ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($role === 'registrar'): ?>
                <!-- Registrar Dashboard -->
                <?php
                $totalStudents = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
                $newThisYear = (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'student' AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn();
                try {
                    $totalDocs = (int) db()->query("SELECT COUNT(*) FROM student_enrollment_documents")->fetchColumn();
                } catch (Exception $e) { $totalDocs = 0; }
                try {
                    $gradeBreakdown = db()->query("SELECT grade_level, COUNT(*) AS c FROM users WHERE role = 'student' AND grade_level IS NOT NULL GROUP BY grade_level ORDER BY grade_level")->fetchAll();
                } catch (Exception $e) { $gradeBreakdown = []; }
                ?>

                <div class="welcome-banner">
                    <h2><i class="bi bi-person-plus me-2"></i>Registrar Dashboard</h2>
                    <p>Manage student enrollment, records, and documents</p>
                </div>

                <div class="row g-4">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card users">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Total Students</h6>
                                    <h2><?= $totalStudents ?></h2>
                                </div>
                                <div class="dashboard-stat-icon"><i class="bi bi-people"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card subjects">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Enrolled This Year</h6>
                                    <h2><?= $newThisYear ?></h2>
                                </div>
                                <div class="dashboard-stat-icon"><i class="bi bi-calendar-check"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card activities">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Grade Levels</h6>
                                    <h2><?= count($gradeBreakdown) ?></h2>
                                </div>
                                <div class="dashboard-stat-icon"><i class="bi bi-mortarboard"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="dashboard-stat-card quizzes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Documents</h6>
                                    <h2><?= $totalDocs ?></h2>
                                </div>
                                <div class="dashboard-stat-icon"><i class="bi bi-files"></i></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Students per Grade Level</h5>
                                <a href="student-list.php" class="btn btn-sm btn-primary">View Student List</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($gradeBreakdown)): ?>
                                    <p class="text-muted mb-0">No student data available yet.</p>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead><tr><th>Grade Level</th><th>Number of Students</th></tr></thead>
                                        <tbody>
                                        <?php foreach ($gradeBreakdown as $g): ?>
                                            <tr>
                                                <td>Grade <?= htmlspecialchars((string)$g['grade_level']) ?></td>
                                                <td><?= (int)$g['c'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Student Dashboard -->
                <?php
                $stmt = db()->prepare('SELECT COUNT(DISTINCT a.subject_id) FROM activities a');
                $stmt->execute();
                $availableSubjects = $stmt->fetchColumn();
                
                $stmt = db()->prepare('SELECT COUNT(*) FROM activity_submissions WHERE student_id = ?');
                $stmt->execute([$user['id']]);
                $submittedActivities = $stmt->fetchColumn();
                
                $stmt = db()->prepare('SELECT COUNT(*) FROM quiz_attempts WHERE student_id = ?');
                $stmt->execute([$user['id']]);
                $completedQuizzes = $stmt->fetchColumn();
                ?>
                
                <!-- Welcome Banner -->
                <div class="welcome-banner">
                    <h2><i class="bi bi-mortarboard-fill me-2"></i>Student Dashboard</h2>
                    <p>Access your subjects, submit activities, and track your progress</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="dashboard-stat-card subjects">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Available Subjects</h6>
                                    <h2><?= $availableSubjects ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-book"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="dashboard-stat-card activities">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Submitted Activities</h6>
                                    <h2><?= $submittedActivities ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-file-earmark-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="dashboard-stat-card quizzes">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Completed Quizzes</h6>
                                    <h2><?= $completedQuizzes ?></h2>
                                </div>
                                <div class="dashboard-stat-icon">
                                    <i class="bi bi-card-checklist"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Activity Section - Admin only -->
            <?php if ($role === 'admin'): ?>
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-activity me-2 text-primary"></i>Recent Activity</h5>
                            <span class="badge bg-primary"><?= count($recentActivityLogs) ?> items</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentActivityLogs)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentActivityLogs as $log): ?>
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <i class="bi bi-person-circle text-primary"></i>
                                                        <span class="activity-user"><?= htmlspecialchars($log['user_name'] ?? 'Unknown User') ?></span>
                                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($log['action']) ?></span>
                                                    </div>
                                                    <div class="activity-action">
                                                        <i class="bi bi-arrow-right-short text-muted"></i>
                                                        <?= htmlspecialchars($log['target_type']) ?> #<?= $log['target_id'] ?>
                                                    </div>
                                                    <?php if (!empty($log['details'])): ?>
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            <?= htmlspecialchars(substr($log['details'], 0, 100)) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="activity-timestamp text-end ms-3">
                                                    <i class="bi bi-clock text-muted me-1"></i>
                                                    <?= date('M j, g:i A', strtotime($log['timestamp'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-activity"></i>
                                    <h4>No Recent Activity</h4>
                                    <p>System activity will appear here once users start interacting with the platform.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User Distribution Chart
        const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(userDistributionCtx, {
            type: 'doughnut',
            data: <?php echo json_encode($userDistributionData); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });

        // Content Creation Chart
        const contentCreationCtx = document.getElementById('contentCreationChart').getContext('2d');
        new Chart(contentCreationCtx, {
            type: 'line',
            data: <?php echo json_encode($contentCreationData); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Grade Distribution Chart
        const gradeDistributionCtx = document.getElementById('gradeDistributionChart').getContext('2d');
        new Chart(gradeDistributionCtx, {
            type: 'bar',
            data: <?php echo json_encode($gradeDistributionData); ?>,
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Enrollment Trends Chart
        const enrollmentTrendsCtx = document.getElementById('enrollmentTrendsChart').getContext('2d');
        new Chart(enrollmentTrendsCtx, {
            type: 'line',
            data: <?php echo json_encode($enrollmentTrendsData); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>