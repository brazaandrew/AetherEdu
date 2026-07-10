<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

// Only allow admin and teacher roles to access reports
if ($role !== 'admin' && $role !== 'teacher') {
    header('Location: dashboard.php');
    exit;
}

// Get analytics data
$userAnalytics = getUserAnalyticsByRole();
$subjectAnalytics = getSubjectAnalytics();
$activityAnalytics = getActivityAnalytics();
$quizAnalytics = getQuizAnalytics();
$enrollmentAnalytics = getEnrollmentAnalytics();
$gradeAnalytics = getGradeAnalytics();
$recentActivityLogs = getRecentActivityLogs(20);

// Get chart data
$userDistributionData = getUserDistributionChartData();
$contentCreationData = getContentCreationChartData();
$gradeDistributionData = getGradeDistributionChartData();
$enrollmentTrendsData = getEnrollmentTrendsChartData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark: #1e293b;
            --light-bg: #f8fafc;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--light-bg);
            color: var(--dark);
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }
        .sidebar-header h4 {
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-header small {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 0.9rem 1.5rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            margin: 0.25rem 0.75rem;
            border-radius: 8px;
        }
        .sidebar .nav-link i {
            font-size: 1.2rem;
            width: 24px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: var(--sidebar-hover);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        .top-bar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 1.25rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .top-bar h3 {
            font-weight: 700;
            color: var(--dark);
            font-size: 1.75rem;
        }
        .top-bar small {
            color: #64748b;
            font-size: 0.9rem;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .card-header {
            border-bottom: 2px solid #f1f5f9;
            padding: 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
            height: 100%;
        }
        .stat-card h6 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #64748b;
        }
        .stat-card h2 {
            font-weight: 700;
            font-size: 2.5rem;
            color: var(--dark);
        }
        .dropdown-toggle {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            background: white;
            transition: all 0.3s;
            font-weight: 500;
        }
        .dropdown-toggle:hover {
            border-color: var(--primary-color);
            background: #f8fafc;
        }
        .mobile-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.4);
            z-index: 999;
            cursor: pointer;
            transition: all 0.3s;
        }
        .mobile-toggle:hover {
            transform: scale(1.1);
        }
        .activity-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.2s;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-item:hover {
            background-color: #f8fafc;
        }
        .activity-user {
            font-weight: 600;
            color: var(--dark);
        }
        .activity-action {
            font-weight: 500;
        }
        .activity-timestamp {
            font-size: 0.85rem;
            color: #64748b;
        }
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .analytics-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .analytics-card h6 {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        .analytics-card h4 {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--dark);
            margin: 0;
        }
        .analytics-card .trend {
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }
        .positive-trend {
            color: #10b981;
        }
        .negative-trend {
            color: #ef4444;
        }
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h3 class="mb-0">Reports & Analytics</h3>
                <small class="text-muted">View system analytics and activity reports</small>
            </div>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-2"></i>
                    <?= htmlspecialchars($user['name']) ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>

        <!-- Reports Content -->
        <div class="container-fluid p-4">
            <div class="row g-4">
                <!-- User Analytics -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">User Analytics</h5>
                        </div>
                        <div class="card-body">
                            <div class="analytics-grid">
                                <?php foreach ($userAnalytics as $userRole): ?>
                                    <div class="analytics-card">
                                        <h6><?= ucfirst($userRole['role']) ?> Users</h6>
                                        <h4><?= $userRole['count'] ?></h4>
                                        <div class="trend positive-trend">+<?= $userRole['recent_count'] ?> in last 30 days</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content Analytics -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Content Analytics</h5>
                        </div>
                        <div class="card-body">
                            <div class="analytics-grid">
                                <div class="analytics-card">
                                    <h6>Total Subjects</h6>
                                    <h4><?= $subjectAnalytics['total_subjects'] ?></h4>
                                    <div class="trend positive-trend">+<?= $subjectAnalytics['recent_subjects'] ?> in last 30 days</div>
                                </div>
                                <div class="analytics-card">
                                    <h6>Total Activities</h6>
                                    <h4><?= $activityAnalytics['total_activities'] ?></h4>
                                    <div class="trend positive-trend">+<?= $activityAnalytics['recent_activities'] ?> in last 30 days</div>
                                </div>
                                <div class="analytics-card">
                                    <h6>Total Quizzes</h6>
                                    <h4><?= $quizAnalytics['total_quizzes'] ?></h4>
                                    <div class="trend positive-trend">+<?= $quizAnalytics['recent_quizzes'] ?> in last 30 days</div>
                                </div>
                                <div class="analytics-card">
                                    <h6>Enrollments</h6>
                                    <h4><?= $enrollmentAnalytics['total_enrollments'] ?></h4>
                                    <div class="trend positive-trend">+<?= $enrollmentAnalytics['recent_enrollments'] ?> in last 30 days</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grade Analytics -->
                <?php if ($role === 'admin'): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Grade Analytics</h5>
                        </div>
                        <div class="card-body">
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
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentActivityLogs)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recentActivityLogs as $log): ?>
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="activity-user"><?= htmlspecialchars($log['user_name'] ?? 'Unknown User') ?></div>
                                                    <div class="activity-action text-muted"><?= htmlspecialchars($log['action']) ?> on <?= htmlspecialchars($log['target_type']) ?> #<?= $log['target_id'] ?></div>
                                                    <?php if (!empty($log['details'])): ?>
                                                        <small class="text-muted">Details: <?= htmlspecialchars(substr($log['details'], 0, 100)) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="activity-timestamp text-end">
                                                    <?= date('M j, g:i A', strtotime($log['timestamp'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-activity" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-0">No recent activity to display.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Data Analytics Charts</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="userDistributionChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="contentCreationChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="gradeDistributionChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="enrollmentTrendsChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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