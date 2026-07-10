<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Services/AuthService.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

resolve_tenant();

// Redirect if already logged in
AuthMiddleware::redirectIfAuthenticated();

// Redirect to school selection if no school is selected
if (!isset($_SESSION['active_school_db'])) {
    // Detect the default school from the schools table, fallback to 'tlca'
    $defaultSchool = 'tlca';
    try {
        $master = db_master();
        $firstSchool = $master->query("SELECT domain FROM schools ORDER BY id ASC LIMIT 1")->fetch();
        if ($firstSchool && !empty($firstSchool['domain'])) {
            $defaultSchool = $firstSchool['domain'];
        }
    } catch (Exception $e) {
        // Use fallback
    }
    header('Location: ../index.php?school=' . urlencode($defaultSchool));
    exit;
}

$error = '';
$authService = new AuthService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $result = $authService->authenticate($email, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['error'];
        }
    } else {
        $error = 'Please enter email and password';
    }
}

$schoolName = $_SESSION['active_school_name'] ?? 'The Light Christian Academy';
$schoolAcronym = '';
foreach (explode(' ', $schoolName) as $w) {
    $schoolAcronym .= strtoupper($w[0] ?? '');
}
if (empty($schoolAcronym)) {
    $schoolAcronym = 'LMS';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($schoolAcronym) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        body {
            margin: 0;
            background-color: var(--bg-primary);
        }
        .login-split-container {
            min-height: 100vh;
            display: flex;
            width: 100%;
        }
        .login-split-left {
            flex: 1;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
            color: #FFFFFF;
        }
        .login-split-left::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
        }
        .login-split-left::after {
            content: '';
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.1) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
        }
        .login-left-content {
            position: relative;
            z-index: 5;
            max-width: 500px;
        }
        .login-left-content h1 {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1.2;
            margin-top: 1.5rem;
            background: linear-gradient(to right, #FFFFFF, #94A3B8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .login-split-right {
            width: 550px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            border-left: 1px solid var(--border-color);
            background-color: var(--bg-primary);
            position: relative;
        }
        .login-form-card {
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary);
        }
        @media (max-width: 992px) {
            .login-split-left { display: none !important; }
            .login-split-right { width: 100%; border-left: none; }
        }
    </style>
</head>
<body>
    <div class="login-split-container">
        <!-- Left Banner -->
        <div class="login-split-left">
            <div class="login-left-content">
                <div class="brand-logo-wrap d-flex align-items-center gap-3">
                    <div class="brand-icon bg-primary shadow-lg" style="width: 54px; height: 54px;">
                        <img src="<?= htmlspecialchars(get_school_logo()) ?>" alt="School Logo">
                    </div>
                    <div>
                        <h4 class="text-white mb-0 font-heading fw-bold" style="letter-spacing: 0.5px;"><?= htmlspecialchars($schoolAcronym) ?></h4>
                        <small class="text-white-50"><?= htmlspecialchars($schoolName) ?></small>
                    </div>
                </div>
                
                <h1 class="font-heading">AetherEdu Learning Console.</h1>
                <p class="text-secondary mt-3 fs-5" style="color: #94a3b8 !important;">Access your dashboard, view schedules, upload assignments, and engage with AI powered curriculum builders.</p>
                
                <div class="d-flex align-items-center gap-4 mt-5 pt-3 border-top border-secondary border-opacity-25">
                    <div>
                        <h5 class="text-white mb-0">100%</h5>
                        <small class="text-white-50">SaaS Isolation</small>
                    </div>
                    <div class="vr text-white-50 opacity-25"></div>
                    <div>
                        <h5 class="text-white mb-0">99.9%</h5>
                        <small class="text-white-50">Uptime SLA</small>
                    </div>
                    <div class="vr text-white-50 opacity-25"></div>
                    <div>
                        <h5 class="text-white mb-0">AI</h5>
                        <small class="text-white-50">Quiz Helpers</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Login Form -->
        <div class="login-split-right">
            <div class="login-form-card">
                <div class="text-center mb-4 d-lg-none">
                    <img src="<?= htmlspecialchars(get_school_logo()) ?>" alt="Logo" style="height: 70px; width: auto; border-radius: var(--border-radius-sm); margin-bottom: 10px;">
                    <h3 class="mb-1"><?= htmlspecialchars($schoolAcronym) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($schoolName) ?></p>
                </div>
                
                <div class="mb-4">
                    <h2 class="fw-bold mb-1">Welcome Back</h2>
                    <p class="text-muted">Sign in to your learning account.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 px-3 small border-0 bg-danger bg-opacity-10 text-danger rounded-sm mb-4 animate-fade-in" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php elseif (isset($_GET['expired']) && $_GET['expired'] === '1'): ?>
                    <div class="alert alert-warning py-2 px-3 small border-0 bg-warning bg-opacity-10 text-warning rounded-sm mb-4 animate-fade-in" role="alert">
                        <i class="bi bi-clock-history me-2"></i>Your session has expired due to inactivity. Please sign in again.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <!-- Email field -->
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autofocus>
                        <label for="email">Email Address</label>
                    </div>
                    
                    <!-- Password field -->
                    <div class="mb-3">
                        <div class="input-group">
                            <div class="form-floating flex-grow-1">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <label for="password">Password</label>
                            </div>
                            <button class="btn btn-outline-light border-start-0 px-3" type="button" id="togglePassword" style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-color: var(--border-color);">
                                <i class="bi bi-eye text-muted" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Remember and Forgot options -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label text-muted small" for="rememberMe">Remember me</label>
                        </div>
                        <a href="#" class="text-primary text-decoration-none small fw-semibold">Forgot password?</a>
                    </div>
                    
                    <!-- Login Buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg py-2 fs-6">Sign In</button>
                        <a href="../index.php?school=clear" class="btn btn-outline-light py-2 fs-6 mt-1">
                            <i class="bi bi-arrow-left"></i> Change School
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (togglePasswordButton && passwordInput && eyeIcon) {
                togglePasswordButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'text') {
                        eyeIcon.className = 'bi bi-eye-slash text-muted';
                    } else {
                        eyeIcon.className = 'bi bi-eye text-muted';
                    }
                });
            }
        });
    </script>
</body>
</html>
