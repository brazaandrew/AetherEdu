<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/settings.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/AttendanceController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

header('Content-Type: application/json');

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['teacher', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$teacherLat = isset($_POST['latitude']) ? (float)$_POST['latitude'] : 0;
$teacherLng = isset($_POST['longitude']) ? (float)$_POST['longitude'] : 0;

// Verify GPS coordinates
$schoolLat = (float) getSetting('school_latitude', '14.1000');
$schoolLng = (float) getSetting('school_longitude', '120.9500');
$radius = (float) getSetting('gps_radius_meters', '100');

$distance = haversineDistance($schoolLat, $schoolLng, $teacherLat, $teacherLng);

if ($distance > $radius) {
    echo json_encode(['success' => false, 'error' => 'You are too far from the school. Distance: ' . round($distance) . 'm']);
    exit;
}

$attendanceController = new AttendanceController();

if ($action === 'clock_in') {
    $result = $attendanceController->clockIn($user['id']);
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => $result['message'], 'time' => date('h:i A')]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
} elseif ($action === 'clock_out') {
    $result = $attendanceController->clockOut($user['id']);
    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => $result['message'], 'time' => date('h:i A')]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
