<?php
require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Services/AuthService.php';
loadEnv(__DIR__ . '/../.env');
startSecureSession();

$schoolLookup = $_SESSION['active_school_lookup'] ?? '';

$authService = new AuthService();
$authService->logout();

$redirectUrl = 'login.php';
$params = [];
if (isset($_GET['expired']) && $_GET['expired'] == '1') {
    $params[] = 'expired=1';
}
if (!empty($schoolLookup)) {
    $params[] = 'school=' . urlencode($schoolLookup);
}
if (!empty($params)) {
    $redirectUrl .= '?' . implode('&', $params);
}

header('Location: ' . $redirectUrl);
exit;
