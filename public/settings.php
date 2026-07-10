<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

// Access Control: Only school administrators
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$user = $_SESSION['user'];
$db = db();
$successMsg = '';
$errorMsg = '';

// Helper to upsert a setting
function saveSetting(PDO $db, string $key, ?string $value): void {
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $schoolName = trim($_POST['school_name'] ?? '');
    $latitude = trim($_POST['school_latitude'] ?? '');
    $longitude = trim($_POST['school_longitude'] ?? '');
    $gpsRadius = trim($_POST['gps_radius_meters'] ?? '');
    
    try {
        $db->beginTransaction();
        
        // 1. Update School Name if provided
        if (!empty($schoolName)) {
            saveSetting($db, 'school_name', $schoolName);
            
            // Sync with master database registry
            $master = db_master();
            $stmt = $master->prepare("UPDATE schools SET name = ? WHERE db_name = ?");
            $stmt->execute([$schoolName, $_SESSION['active_school_db']]);
            $_SESSION['active_school_name'] = $schoolName;
        }
        
        // 2. Update GPS Settings
        saveSetting($db, 'school_latitude', $latitude !== '' ? $latitude : null);
        saveSetting($db, 'school_longitude', $longitude !== '' ? $longitude : null);
        saveSetting($db, 'gps_radius_meters', $gpsRadius !== '' ? $gpsRadius : '100');
        
        // 3. Update Landing Page Custom Fields
        saveSetting($db, 'school_hero_subtitle', isset($_POST['school_hero_subtitle']) ? trim($_POST['school_hero_subtitle']) : null);
        saveSetting($db, 'school_about_text', isset($_POST['school_about_text']) ? trim($_POST['school_about_text']) : null);
        saveSetting($db, 'school_contact_email', isset($_POST['school_contact_email']) ? trim($_POST['school_contact_email']) : null);
        saveSetting($db, 'school_contact_phone', isset($_POST['school_contact_phone']) ? trim($_POST['school_contact_phone']) : null);
        saveSetting($db, 'school_contact_address', isset($_POST['school_contact_address']) ? trim($_POST['school_contact_address']) : null);
        
        // 4. Handle School Logo Upload
        if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['school_logo']['tmp_name'];
            $fileName = $_FILES['school_logo']['name'];
            $fileSize = $_FILES['school_logo']['size'];
            $fileType = $_FILES['school_logo']['type'];
            
            if ($fileSize > 2 * 1024 * 1024) {
                throw new Exception("Logo file size must be less than 2MB.");
            }
            
            $allowedMimes = ['image/png', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/webp'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            
            if (!in_array($fileType, $allowedMimes, true) || !in_array($fileExtension, $allowedExtensions, true)) {
                throw new Exception("Invalid image format. Allowed formats: PNG, JPG, JPEG, GIF, WEBP.");
            }
            
            $uploadDir = __DIR__ . '/uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $schoolId = $_SESSION['active_school_id'] ?? 'default';
            $newFileName = $schoolId . '_logo_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $dbRelativePath = 'uploads/logos/' . $newFileName;
                saveSetting($db, 'school_logo', $dbRelativePath);
            } else {
                throw new Exception("Failed to save uploaded logo file.");
            }
        }
        
        // 5. Handle Landing Hero Image Upload
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['hero_image']['tmp_name'];
            $fileName = $_FILES['hero_image']['name'];
            $fileSize = $_FILES['hero_image']['size'];
            $fileType = $_FILES['hero_image']['type'];
            
            if ($fileSize > 2 * 1024 * 1024) {
                throw new Exception("Hero image file size must be less than 2MB.");
            }
            
            $allowedMimes = ['image/png', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/webp'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            
            if (!in_array($fileType, $allowedMimes, true) || !in_array($fileExtension, $allowedExtensions, true)) {
                throw new Exception("Invalid hero image format. Allowed formats: PNG, JPG, JPEG, GIF, WEBP.");
            }
            
            $uploadDir = __DIR__ . '/uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $schoolId = $_SESSION['active_school_id'] ?? 'default';
            $newFileName = $schoolId . '_banner_' . time() . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $dbRelativePath = 'uploads/banners/' . $newFileName;
                saveSetting($db, 'school_hero_image', $dbRelativePath);
            } else {
                throw new Exception("Failed to save uploaded hero image file.");
            }
        }
        
        $db->commit();
        $successMsg = 'Branding and school configuration settings updated successfully!';
    } catch (Exception $e) {
        $db->rollBack();
        $errorMsg = 'Error saving settings: ' . $e->getMessage();
    }
}

// Fetch Current Settings
$settings = [];
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Table might not exist
}

// Populate values or defaults
$currentSchoolName = $settings['school_name'] ?? ($_SESSION['active_school_name'] ?? 'The Light Christian Academy');
$currentLatitude = $settings['school_latitude'] ?? '14.1000';
$currentLongitude = $settings['school_longitude'] ?? '120.9500';
$currentRadius = $settings['gps_radius_meters'] ?? '100';
$currentLogo = get_school_logo();

// Landing page customizable fields
$currentHeroSubtitle = $settings['school_hero_subtitle'] ?? 'Providing quality education rooted in Christian values. Enroll students, manage grades, and enhance educational outcomes.';
$currentAboutText = $settings['school_about_text'] ?? 'We deliver an integrated digital workspace that connects students, teachers, and administrators. Our system simplifies course planning, progress evaluation, and secure data access.';
$currentContactEmail = $settings['school_contact_email'] ?? 'info@school.edu';
$currentContactPhone = $settings['school_contact_phone'] ?? '+63 912 345 6789';
$currentContactAddress = $settings['school_contact_address'] ?? '123 Academic St, Manila, Philippines';
$currentHeroImage = $settings['school_hero_image'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Settings - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        .logo-preview-box {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8fafc;
        }
        .logo-preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .banner-preview-box {
            width: 100%;
            max-width: 320px;
            height: 140px;
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8fafc;
            margin-bottom: 0.5rem;
        }
        .banner-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Settings'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2>
                        <i class="bi bi-gear-fill me-2 text-primary"></i>
                        School Administration Settings
                    </h2>
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

            <div class="row g-4">
                <div class="col-lg-3">
                    <div class="list-group shadow-sm border-0">
                        <a href="#general" class="list-group-item list-group-item-action active">
                            <i class="bi bi-sliders me-2"></i> General Branding
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action disabled opacity-50">
                            <i class="bi bi-bell me-2"></i> Notifications
                        </a>
                        <a href="#privacy" class="list-group-item list-group-item-action disabled opacity-50">
                            <i class="bi bi-shield-check me-2"></i> Privacy Settings
                        </a>
                        <a href="#appearance" class="list-group-item list-group-item-action disabled opacity-50">
                            <i class="bi bi-palette me-2"></i> Theme Customization
                        </a>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0"><i class="bi bi-sliders text-primary me-2"></i>Branding & GPS Configuration</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="save_settings">

                                <!-- School Customization -->
                                <h6 class="border-bottom pb-2 mb-3 text-primary"><i class="bi bi-building me-2"></i>Institution Details</h6>
                                
                                <div class="mb-3">
                                    <label for="school_name" class="form-label">School Name</label>
                                    <input type="text" class="form-control" id="school_name" name="school_name" value="<?= htmlspecialchars($currentSchoolName) ?>" required>
                                    <div class="form-text">This name will be displayed in the welcome index, logins, sidebar navigation, and reports.</div>
                                </div>

                                <!-- Logo Upload -->
                                <div class="row align-items-center mb-4 g-3">
                                    <div class="col-auto">
                                        <div class="logo-preview-box">
                                            <img src="<?= htmlspecialchars($currentLogo) ?>" alt="School Logo Preview">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label for="school_logo" class="form-label font-weight-bold">Upload Custom Logo</label>
                                        <input class="form-control" type="file" id="school_logo" name="school_logo" accept="image/png, image/jpeg, image/gif, image/webp">
                                        <div class="form-text">Recommended size: Square (e.g. 512x512px). Max file size: 2MB. Format: PNG, JPG, GIF, WEBP.</div>
                                    </div>
                                </div>

                                <!-- Landing Page Settings -->
                                <h6 class="border-bottom pb-2 mb-3 text-primary mt-4"><i class="bi bi-window me-2"></i>Welcome Landing Page Config</h6>
                                
                                <div class="mb-3">
                                    <label for="school_hero_subtitle" class="form-label">Homepage Hero Subtitle</label>
                                    <textarea class="form-control" id="school_hero_subtitle" name="school_hero_subtitle" rows="3"><?= htmlspecialchars($currentHeroSubtitle) ?></textarea>
                                    <div class="form-text">Introductory paragraph that shows up under the school name on the landing page hero block.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="hero_image" class="form-label">Hero Cover Image</label>
                                    <?php if ($currentHeroImage): ?>
                                        <div class="banner-preview-box">
                                            <img src="<?= htmlspecialchars($currentHeroImage) ?>" alt="Hero Banner Preview">
                                        </div>
                                    <?php endif; ?>
                                    <input class="form-control" type="file" id="hero_image" name="hero_image" accept="image/png, image/jpeg, image/gif, image/webp">
                                    <div class="form-text">Replaces the default illustration graphic. Max file size: 2MB. Format: PNG, JPG, GIF, WEBP.</div>
                                </div>

                                <div class="mb-4">
                                    <label for="school_about_text" class="form-label">About Us Description</label>
                                    <textarea class="form-control" id="school_about_text" name="school_about_text" rows="4"><?= htmlspecialchars($currentAboutText) ?></textarea>
                                    <div class="form-text">Custom text summarizing the school mission, curriculum details, or history.</div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="school_contact_email" class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" id="school_contact_email" name="school_contact_email" value="<?= htmlspecialchars($currentContactEmail) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="school_contact_phone" class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" id="school_contact_phone" name="school_contact_phone" value="<?= htmlspecialchars($currentContactPhone) ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="school_contact_address" class="form-label">Physical Address</label>
                                        <input type="text" class="form-control" id="school_contact_address" name="school_contact_address" value="<?= htmlspecialchars($currentContactAddress) ?>">
                                    </div>
                                </div>

                                <!-- GPS Attendance Settings -->
                                <h6 class="border-bottom pb-2 mb-3 text-primary mt-4"><i class="bi bi-geo-alt-fill me-2"></i>GPS Attendance Configuration</h6>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="school_latitude" class="form-label">School Latitude</label>
                                        <input type="number" step="any" class="form-control" id="school_latitude" name="school_latitude" value="<?= htmlspecialchars($currentLatitude) ?>" placeholder="e.g. 14.1000">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="school_longitude" class="form-label">School Longitude</label>
                                        <input type="number" step="any" class="form-control" id="school_longitude" name="school_longitude" value="<?= htmlspecialchars($currentLongitude) ?>" placeholder="e.g. 120.9500">
                                    </div>
                                    <div class="col-12">
                                        <label for="gps_radius_meters" class="form-label">Allowed Radius Range (in meters)</label>
                                        <input type="number" class="form-control" id="gps_radius_meters" name="gps_radius_meters" value="<?= htmlspecialchars($currentRadius) ?>" placeholder="e.g. 100" min="10">
                                        <div class="form-text">Specifies how close users must be to the coordinates to log attendance.</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-save-fill me-2"></i>Save Configuration Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
