<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$_SESSION['last_activity'] = time();
$lifetime = 3600; // default
if (function_exists('env')) {
    $lifetime = (int)env('SESSION_LIFETIME', 3600);
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'remaining' => $lifetime,
    'lifetime' => $lifetime
]);
