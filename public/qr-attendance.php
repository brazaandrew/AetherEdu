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

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['teacher', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

$schoolLat = (float) getSetting('school_latitude', '14.1000');
$schoolLng = (float) getSetting('school_longitude', '120.9500');
$radius = (float) getSetting('gps_radius_meters', '100');

$attendanceController = new AttendanceController();
$todayRecord = $attendanceController->getTeacherAttendanceToday($user['id']);

$alreadyClockedIn = $todayRecord && $todayRecord['time_in'];
$alreadyClockedOut = $todayRecord && $todayRecord['time_out'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR Attendance - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #40A2E3 0%, #2E8BC0 100%); min-height: 100vh; }
        .attendance-card { max-width: 500px; margin: 0 auto; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .status-icon { font-size: 4rem; }
        .gps-pulse { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.1); opacity: 0.7; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="attendance-card card w-100">
        <div class="card-body text-center p-4">
            <div class="mb-3">
                <img src="image/new.png" alt="TLCA Logo" style="width: 60px; height: 60px; object-fit: contain;">
            </div>
            <h4 class="mb-1">The Light Christian Academy</h4>
            <p class="text-muted mb-4">Teacher QR Attendance</p>
            
            <div id="loadingState">
                <div class="status-icon text-primary gps-pulse mb-3">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h5>Getting your location...</h5>
                <p class="text-muted">Please allow location access</p>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            
            <div id="resultState" class="d-none">
                <div id="resultIcon" class="status-icon mb-3"></div>
                <h5 id="resultTitle"></h5>
                <p id="resultMessage" class="text-muted"></p>
                <div id="resultDetails" class="alert alert-light border mt-3"></div>
                <a href="teacher-time-log.php" class="btn btn-primary w-100">
                    <i class="bi bi-clock-history"></i> View Time Log
                </a>
            </div>
            
            <div id="manualState" class="d-none">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> GPS access denied or unavailable.
                </div>
                <p class="text-muted">Please enable location services and refresh, or use the manual time log.</p>
                <a href="teacher-time-log.php" class="btn btn-outline-primary w-100">
                    <i class="bi bi-clock"></i> Manual Time Log
                </a>
            </div>
            
            <hr class="my-4">
            <p class="small text-muted mb-0">
                <i class="bi bi-person"></i> <?= htmlspecialchars($user['name']) ?> | 
                <i class="bi bi-calendar"></i> <?= date('F d, Y') ?>
            </p>
            
            <?php if ($alreadyClockedOut): ?>
            <div class="alert alert-success mt-3 mb-0">
                <i class="bi bi-check-all"></i> You have already clocked out today.
            </div>
            <?php elseif ($alreadyClockedIn): ?>
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle"></i> You are already clocked in. Use Time Log to clock out.
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const schoolLat = <?= json_encode($schoolLat) ?>;
        const schoolLng = <?= json_encode($schoolLng) ?>;
        const radius = <?= json_encode($radius) ?>;
        const alreadyClockedIn = <?= json_encode($alreadyClockedIn) ?>;
        const alreadyClockedOut = <?= json_encode($alreadyClockedOut) ?>;
        
        function haversine(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }
        
        async function verifyAndClock(lat, lng) {
            const distance = haversine(schoolLat, schoolLng, lat, lng);
            
            if (distance > radius) {
                showResult('error', 'Too Far From School', 
                    `You are ${Math.round(distance)} meters away from the school.`,
                    `Allowed radius: ${radius}m<br>Your distance: ${Math.round(distance)}m`);
                return;
            }
            
            // Determine action
            let action = 'clock_in';
            if (alreadyClockedIn && !alreadyClockedOut) {
                action = 'clock_out';
            } else if (alreadyClockedOut) {
                showResult('info', 'Already Complete', 
                    'You have already clocked in and out for today.',
                    'See you tomorrow!');
                return;
            }
            
            // Send to server
            try {
                const formData = new FormData();
                formData.append('action', action);
                formData.append('latitude', lat);
                formData.append('longitude', lng);
                formData.append('distance', Math.round(distance));
                
                const response = await fetch('qr-attendance-api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    const actionText = action === 'clock_in' ? 'Clocked In' : 'Clocked Out';
                    const timeText = result.time ? ` at ${result.time}` : '';
                    showResult('success', actionText + timeText, result.message,
                        `Distance: ${Math.round(distance)}m from school`);
                } else {
                    showResult('error', 'Error', result.error || 'Something went wrong.', '');
                }
            } catch (err) {
                showResult('error', 'Network Error', 'Could not connect to server. Please try again.', '');
            }
        }
        
        function showResult(type, title, message, details) {
            document.getElementById('loadingState').classList.add('d-none');
            document.getElementById('resultState').classList.remove('d-none');
            
            const iconDiv = document.getElementById('resultIcon');
            const titleEl = document.getElementById('resultTitle');
            const msgEl = document.getElementById('resultMessage');
            const detailsEl = document.getElementById('resultDetails');
            
            if (type === 'success') {
                iconDiv.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
            } else if (type === 'error') {
                iconDiv.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
            } else {
                iconDiv.innerHTML = '<i class="bi bi-info-circle-fill text-info"></i>';
            }
            
            titleEl.textContent = title;
            msgEl.textContent = message;
            detailsEl.innerHTML = details;
        }
        
        function showManual() {
            document.getElementById('loadingState').classList.add('d-none');
            document.getElementById('manualState').classList.remove('d-none');
        }
        
        // Get GPS location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    verifyAndClock(lat, lng);
                },
                (error) => {
                    console.error('GPS Error:', error);
                    showManual();
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        } else {
            showManual();
        }
    </script>
</body>
</html>
