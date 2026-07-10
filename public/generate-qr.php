<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/settings.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setSetting('school_latitude', $_POST['latitude'] ?? '14.1000');
    setSetting('school_longitude', $_POST['longitude'] ?? '120.9500');
    setSetting('gps_radius_meters', $_POST['radius'] ?? '100');
    $_SESSION['qr_message'] = 'School GPS coordinates updated successfully!';
    header('Location: generate-qr.php');
    exit;
}

$latitude = getSetting('school_latitude', '14.1000');
$longitude = getSetting('school_longitude', '120.9500');
$radius = getSetting('gps_radius_meters', '100');

// Build QR code URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$qrUrl = $protocol . '://' . $host . '/qr-attendance.php';
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($qrUrl);

$message = $_SESSION['qr_message'] ?? '';
unset($_SESSION['qr_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate QR Attendance - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid p-4">
            <h2 class="mb-4"><i class="bi bi-qr-code me-2"></i>QR Code Attendance</h2>
            
            <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>School GPS Configuration</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">School Latitude</label>
                                    <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($latitude) ?>" placeholder="e.g. 14.1000" required>
                                    <small class="text-muted">Get from Google Maps (right-click > coordinates)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">School Longitude</label>
                                    <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($longitude) ?>" placeholder="e.g. 120.9500" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allowed Radius (meters)</label>
                                    <input type="number" name="radius" class="form-control" value="<?= htmlspecialchars($radius) ?>" min="10" max="1000" required>
                                    <small class="text-muted">Max distance teachers can be from school to clock in</small>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save & Generate QR</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ol class="mb-0">
                                <li>Open <a href="https://maps.google.com" target="_blank">Google Maps</a></li>
                                <li>Right-click on your school location</li>
                                <li>Copy the latitude, longitude numbers</li>
                                <li>Paste them above and save</li>
                                <li>Print or display the QR code</li>
                                <li>Teachers scan with their phone camera</li>
                                <li>GPS location is verified automatically</li>
                            </ol>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-qr-code-scan me-2"></i>Attendance QR Code</h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="printQR()"><i class="bi bi-printer"></i> Print</button>
                        </div>
                        <div class="card-body text-center" id="qrPrintArea">
                            <img src="<?= $qrApiUrl ?>" alt="Attendance QR Code" class="img-fluid mb-3" style="max-width: 400px;">
                            <h5 class="mb-1">The Light Christian Academy</h5>
                            <p class="text-muted mb-2">Teacher QR Attendance</p>
                            <p class="small text-muted">Scan to clock in/out with GPS verification</p>
                            <div class="alert alert-light border">
                                <small><strong>URL:</strong> <?= htmlspecialchars($qrUrl) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printQR() {
            const printWindow = window.open('', '_blank');
            const qrContent = document.getElementById('qrPrintArea').innerHTML;
            printWindow.document.write(`
                <html><head><title>Print QR Code</title>
                <style>body{text-align:center;padding:40px;font-family:Arial;}img{max-width:400px;}</style>
                </head><body>${qrContent}</body></html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>
