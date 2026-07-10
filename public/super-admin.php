<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Services/TenantService.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

// Passkey gate check
$definedPasskey = $_ENV['SUPER_ADMIN_PASSKEY'] ?? 'AetherEduAdmin2026';
$errorGate = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['super_admin_logged_in']);
    header('Location: super-admin.php');
    exit;
}

// Handle login submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'super_admin_login') {
    $enteredPasskey = $_POST['passkey'] ?? '';
    if ($enteredPasskey === $definedPasskey) {
        $_SESSION['super_admin_logged_in'] = true;
        header('Location: super-admin.php');
        exit;
    } else {
        $errorGate = 'Invalid Super Admin Passkey.';
    }
}

$isSuperAdmin = isset($_SESSION['super_admin_logged_in']) && $_SESSION['super_admin_logged_in'] === true;

if (!$isSuperAdmin):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AetherEdu - Super Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap');
        body {
            background: linear-gradient(135deg, #0d1b2a 0%, #1e293b 100%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF !important;
        }
        .gate-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            color: white !important;
            animation: slideUp 0.5s ease-out;
        }
        .gate-card h4, 
        .gate-card p, 
        .gate-card label, 
        .gate-card small {
            color: #FFFFFF !important;
        }
        .gate-card .text-white-50 {
            color: rgba(255, 255, 255, 0.55) !important;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="gate-card text-center">
        <div class="mb-4">
            <img src="image/aetheredu_logo.png" alt="AetherEdu Logo" style="height: 70px; width: auto; margin-bottom: 15px;">
            <h4 style="font-family: 'Poppins', sans-serif;">AetherEdu Console</h4>
            <p class="text-white-50 small">Super Admin Verification Gate</p>
        </div>

        <?php if ($errorGate): ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorGate) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="action" value="super_admin_login">
            <div class="mb-3 text-start">
                <label for="passkey" class="form-label text-white-50 small">Security Passkey</label>
                <div class="input-group">
                    <input type="password" class="form-control bg-dark border-secondary text-white" id="passkey" name="passkey" required autofocus>
                    <button class="btn btn-outline-secondary" type="button" id="togglePasskey">
                        <i class="bi bi-eye text-white-50"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                <i class="bi bi-shield-lock-fill me-2"></i>Authenticate
            </button>
        </form>
        
        <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-start">
            <a href="../index.php" class="text-info text-decoration-none small">
                <i class="bi bi-arrow-left"></i> Back to Main Site
            </a>
        </div>
    </div>

    <script>
        document.getElementById('togglePasskey').addEventListener('click', function() {
            const passkeyInput = document.getElementById('passkey');
            const icon = this.querySelector('i');
            const type = passkeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passkeyInput.setAttribute('type', type);
            if (type === 'text') {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
<?php
exit;
endif;

$tenantService = new TenantService();
$successMsg = '';
$errorMsg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_school') {
        $name = trim($_POST['school_name'] ?? '');
        $domain = trim($_POST['school_domain'] ?? '');
        $adminEmail = trim($_POST['admin_email'] ?? '');
        $adminPassword = $_POST['admin_password'] ?? '';
        $schoolId = trim($_POST['school_id'] ?? '');
        $dbSuffix = trim($_POST['db_suffix'] ?? '');
        
        if ($name && $domain && $adminEmail && $adminPassword && $schoolId) {
            $res = $tenantService->createSchool($name, $domain, $adminEmail, $adminPassword, $schoolId, $dbSuffix);
            if ($res['success']) {
                $successMsg = "School '$name' registered successfully! Database initialized.";
            } else {
                $errorMsg = $res['error'] ?? 'Failed to register school.';
            }
        } else {
            $errorMsg = 'Please fill out all fields.';
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete_school') {
        $schoolId = (int)($_POST['school_id'] ?? 0);
        if ($schoolId > 0) {
            $res = $tenantService->deleteSchool($schoolId);
            if ($res['success']) {
                $successMsg = 'School and associated database deleted successfully.';
            } else {
                $errorMsg = $res['error'] ?? 'Failed to delete school.';
            }
        }
    }
}

$schools = $tenantService->listSchools();
$totalSchools = count($schools);

// Fetch dynamic analytics and recent activity
$countUsers = 0;
$countSubjects = 0;
$countQuizzes = 0;
$countGrades = 0;
$countAuditLogs = 0;
$recentActivities = [];

try {
    $db = db();
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('users', $tables)) {
        $countUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
    if (in_array('subjects', $tables)) {
        $countSubjects = (int)$db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    }
    if (in_array('quizzes', $tables)) {
        $countQuizzes = (int)$db->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
    }
    if (in_array('enrollments', $tables)) {
        $countGrades = (int)$db->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();
    }
    if (in_array('audit_logs', $tables)) {
        $countAuditLogs = (int)$db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
        $recentActivities = $db->query("SELECT user_id, action, target_type, target_id, timestamp FROM audit_logs ORDER BY timestamp DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Fail silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Super Admin Dashboard - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        body {
            background-color: var(--bg-primary) !important;
            min-height: 100vh;
            color: var(--text-primary);
        }
        .main-saas-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .welcome-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: var(--border-radius-lg);
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
        }
        .welcome-section h2 {
            font-weight: 800;
            color: #ffffff !important;
        }
        .welcome-section p {
            color: #cbd5e1 !important;
        }
        .table th, .table td {
            padding: 1rem 0.85rem !important;
            vertical-align: middle;
        }
        .table td.school-name {
            font-weight: 600;
            max-width: 250px;
            white-space: normal !important;
            word-wrap: break-word;
        }
        
        /* Stats Cards */
        .dashboard-stat-card {
            background: var(--bg-card) !important;
            border-radius: var(--border-radius-md) !important;
            padding: 1.5rem !important;
            box-shadow: var(--shadow-sm) !important;
            border: 1px solid var(--border-color) !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            height: 100%;
        }
        .dashboard-stat-card:hover {
            transform: translateY(-3px) !important;
            box-shadow: var(--shadow-md) !important;
        }
        .dashboard-stat-card.users { border-top: 3px solid var(--primary) !important; }
        .dashboard-stat-card.subjects { border-top: 3px solid var(--accent) !important; }
        .dashboard-stat-card.IT { border-top: 3px solid var(--warning) !important; }
        
        .dashboard-stat-info {
            display: flex;
            flex-direction: column;
        }
        .dashboard-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }
    </style>
</head>
<body>
    <div class="main-saas-content">
        <div class="welcome-section d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2><i class="bi bi-clouds-fill me-3 text-info"></i>SaaS Super Admin Panel</h2>
                <p class="mb-0 text-slate-300">Register new educational institutions, manage isolated school tenants, and monitor schema updates.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-light btn-lg rounded-pill px-4">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Go to Portal
                </a>
                <a href="super-admin.php?action=logout" class="btn btn-danger btn-lg rounded-pill px-4">
                    <i class="bi bi-lock-fill me-2"></i>Lock Panel
                </a>
            </div>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($successMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="dashboard-stat-card users">
                    <div class="dashboard-stat-info">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Active School Tenants</h6>
                        <h2 class="mb-0 text-primary" style="font-weight: 800; font-size: 1.8rem;"><?= $totalSchools ?></h2>
                    </div>
                    <div class="dashboard-stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--primary);">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-stat-card subjects">
                    <div class="dashboard-stat-info">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">Master Database</h6>
                        <h2 class="mb-0 text-success" style="font-weight: 800; font-size: 1.3rem; word-break: break-all;"><?= htmlspecialchars($_ENV['DB_NAME'] ?? 'elms') ?></h2>
                    </div>
                    <div class="dashboard-stat-icon" style="background: rgba(20, 184, 166, 0.1); color: var(--accent);">
                        <i class="bi bi-database"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-stat-card IT">
                    <div class="dashboard-stat-info">
                        <h6 class="text-muted text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px; font-weight: 600;">System Environment</h6>
                        <h2 class="mb-0 text-warning" style="font-weight: 800; font-size: 1.8rem;">Active</h2>
                    </div>
                    <div class="dashboard-stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                        <i class="bi bi-cpu"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics & Charts Grid -->
        <div class="row g-4 mb-4">
            <!-- Global SaaS Metrics Chart -->
            <div class="col-lg-7">
                <div class="card h-100 shadow-sm border-0" style="border-radius: var(--border-radius-md); background: var(--bg-card); border: 1px solid var(--border-color) !important;">
                    <div class="card-header border-0 bg-transparent py-3" style="border-bottom: 1px solid var(--border-color) !important;">
                        <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-graph-up-arrow me-2"></i>Global SaaS Platform Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="saasAnalyticsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent System Activity / Audit Trail -->
            <div class="col-lg-5">
                <div class="card h-100 shadow-sm border-0" style="border-radius: var(--border-radius-md); background: var(--bg-card); border: 1px solid var(--border-color) !important;">
                    <div class="card-header border-0 bg-transparent py-3" style="border-bottom: 1px solid var(--border-color) !important;">
                        <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-clock-history me-2"></i>Recent System Activity</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($recentActivities)): ?>
                                <div class="p-4 text-center text-muted small">
                                    <i class="bi bi-info-circle d-block fs-3 mb-2"></i>
                                    No recent activity logged.
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $act): ?>
                                    <div class="list-group-item bg-transparent border-0 py-3 px-4 border-bottom" style="border-color: var(--border-color) !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-light text-primary border" style="font-size: 0.72rem;"><?= htmlspecialchars($act['action']) ?></span>
                                            <small class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($act['timestamp']) ?></small>
                                        </div>
                                        <div class="small text-secondary" style="font-size: 0.8rem;">
                                            User ID: <strong><?= $act['user_id'] ?></strong> performed action on <strong><?= htmlspecialchars($act['target_type']) ?></strong>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Add School Form -->
            <div class="col-lg-5">
                <div class="card h-100 shadow-sm border-0" style="border-radius: var(--border-radius-md); overflow: hidden; background: var(--bg-card); border: 1px solid var(--border-color) !important;">
                    <div class="card-header border-0 bg-transparent py-3" style="border-bottom: 1px solid var(--border-color) !important;">
                        <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-plus-circle-fill me-2"></i>Onboard New School</h5>
                    </div>
                    <div class="card-body pt-3">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="create_school">
                            
                            <div class="mb-3">
                                <label for="school_id" class="form-label fw-medium">School ID / Registration ID</label>
                                <input type="text" class="form-control" id="school_id" name="school_id" placeholder="e.g. SCH-12345" required>
                                <div class="form-text">A unique identifier. Cannot create another database for the same School ID.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="db_suffix" class="form-label fw-medium">Database Name Suffix (Optional)</label>
                                <input type="text" class="form-control" id="db_suffix" name="db_suffix" placeholder="e.g. sch_test" pattern="^[a-zA-Z0-9_]*$">
                                <div class="form-text">For hosts like InfinityFree where you manually create the database. If left blank, it will automatically use a sanitized version of the School ID. Only letters, numbers, and underscores are allowed.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="school_name" class="form-label fw-medium">School Name</label>
                                <input type="text" class="form-control" id="school_name" name="school_name" placeholder="e.g. Oakwood Christian Academy" required>
                                <div class="form-text">Display name of the institution.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="school_domain" class="form-label fw-medium">Subdomain Identifier</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="school_domain" name="school_domain" placeholder="e.g. oakwood" required pattern="^[a-zA-Z0-9_-]+$">
                                    <span class="input-group-text bg-light text-muted" style="border-color: var(--border-color);">
                                        <?php 
                                        $currentHost = $_SERVER['HTTP_HOST'] ?? '';
                                        if (strpos($currentHost, '.') !== false && !in_array($currentHost, ['127.0.0.1', '::1'])) {
                                            $parts = explode('.', $currentHost, 2);
                                            echo '.' . ($parts[1] ?? 'test');
                                        } else {
                                            echo '.lms.local';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="form-text">Used for routing (letters, numbers, hyphen).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_email" class="form-label fw-medium">Default Administrator Email</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" placeholder="e.g. admin@oakwood.edu" required>
                                <div class="form-text">Used to log in as the default School Admin.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="admin_password" class="form-label fw-medium">Admin Temporary Password</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Min. 8 characters" required minlength="8">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-rocket-takeoff me-2"></i>Launch School Database
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Schools Directory Table -->
            <div class="col-lg-7">
                <div class="card h-100 shadow-sm border-0" style="border-radius: var(--border-radius-md); overflow: hidden; background: var(--bg-card); border: 1px solid var(--border-color) !important;">
                    <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center" style="border-bottom: 1px solid var(--border-color) !important;">
                        <h5 class="mb-0 fw-semibold text-primary"><i class="bi bi-list-stars me-2"></i>School Tenants Registry</h5>
                    </div>
                    <div class="card-body pt-3">
                        <div class="table-responsive" style="overflow-x: hidden;">
                            <table class="table align-middle table-hover" style="width: 100%;">
                                <thead>
                                    <tr style="border-bottom: 2px solid var(--border-color);">
                                        <th style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; padding: 0.5rem 0.25rem;">School ID</th>
                                        <th style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; padding: 0.5rem 0.25rem;">School Name</th>
                                        <th style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; padding: 0.5rem 0.25rem; white-space: nowrap;">Routing Target</th>
                                        <th style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; padding: 0.5rem 0.25rem; white-space: nowrap;">Database</th>
                                        <th style="font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; padding: 0.5rem 0.25rem; white-space: nowrap;" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($schools)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-building-exclamation fs-1 d-block mb-3"></i>
                                                No schools onboarded yet.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($schools as $school): ?>
                                            <?php
                                            $host = $_SERVER['HTTP_HOST'] ?? 'lms.local';
                                            $suffix = 'lms.local';
                                            if (strpos($host, '.') !== false && !in_array($host, ['127.0.0.1', '::1'])) {
                                                $parts = explode('.', $host, 2);
                                                $suffix = $parts[1] ?? 'lms.local';
                                                $displayRoute = $school['domain'] . '.' . $suffix;
                                            } else {
                                                $displayRoute = $school['domain'] . '.lms.local';
                                            }
                                            ?>
                                            <tr style="border-bottom: 1px solid var(--border-color);">
                                                <td style="padding: 0.75rem 0.25rem; font-size: 0.88rem; font-weight: normal; color: var(--text-secondary);">
                                                    <?= htmlspecialchars($school['school_id'] ?? 'N/A') ?>
                                                </td>
                                                <td class="school-name" style="font-size: 0.88rem; font-weight: 600;">
                                                    <?= htmlspecialchars($school['name']) ?>
                                                </td>
                                                <td style="padding: 0.75rem 0.25rem; white-space: nowrap;">
                                                    <code style="font-size: 0.8rem;"><?= htmlspecialchars($displayRoute) ?></code>
                                                </td>
                                                <td style="padding: 0.75rem 0.25rem; white-space: nowrap;">
                                                    <span class="badge bg-light text-dark border" style="font-size: 0.8rem; font-weight: normal;"><?= htmlspecialchars($school['db_name']) ?></span>
                                                </td>
                                                <td class="text-end" style="padding: 0.75rem 0.25rem; white-space: nowrap;">
                                                    <div class="d-flex gap-1 justify-content-end">
                                                        <a href="../index.php?school=<?= urlencode($school['domain']) ?>" class="btn btn-sm btn-outline-success px-2 py-1" style="font-size: 0.78rem;">
                                                            <i class="bi bi-box-arrow-in-right"></i> Launch
                                                        </a>
                                                        <form method="POST" action="" onsubmit="return confirm('WARNING: Permanent deletion of tenant registry. Drop database (<?= htmlspecialchars($school['db_name']) ?>)?');" style="display:inline;">
                                                            <input type="hidden" name="action" value="delete_school">
                                                            <input type="hidden" name="school_id" value="<?= $school['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" style="font-size: 0.78rem;">
                                                                <i class="bi bi-trash"></i> Drop
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('saasAnalyticsChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Users', 'Subjects', 'Quizzes', 'Enrollments', 'Audit Logs'],
                    datasets: [{
                        label: 'Record Count',
                        data: [
                            <?= $countUsers ?>,
                            <?= $countSubjects ?>,
                            <?= $countQuizzes ?>,
                            <?= $countGrades ?>,
                            <?= $countAuditLogs ?>
                        ],
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.75)', // primary blue
                            'rgba(20, 184, 166, 0.75)', // accent teal
                            'rgba(245, 158, 11, 0.75)', // warning yellow
                            'rgba(34, 197, 94, 0.75)',  // success green
                            'rgba(139, 92, 246, 0.75)'  // purple
                        ],
                        borderColor: [
                            '#2563EB',
                            '#14B8A6',
                            '#F59E0B',
                            '#22C55E',
                            '#8B5CF6'
                        ],
                        borderWidth: 1.5,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(226, 232, 240, 0.08)'
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
