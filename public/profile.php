<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$message = '';
$error = '';

// Handle profile update with image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    requireCsrf();
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Handle image upload
    $imagePath = $user['image'] ?? null; // Keep existing image if no new upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $fileType = $_FILES['image']['type'];
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        
        if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) { // 5MB max
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'user_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $uploadPath = __DIR__ . '/assets/images/' . $newFileName;
            
            // Create directory if it doesn't exist
            $uploadDir = dirname($uploadPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                // Delete old image if it exists and it's not the default
                if ($user['image'] && $user['image'] !== 'assets/images/default-avatar.php') {
                    $oldImagePath = __DIR__ . '/' . $user['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imagePath = 'assets/images/' . $newFileName;
            } else {
                $error = 'Failed to upload image file';
            }
        } else {
            $error = 'Invalid image file. Only JPG, PNG, GIF allowed up to 5MB.';
        }
    }
    
    if (!$name || !$email) {
        $error = 'Name and email are required';
    } else {
        $updateData = [
            'name' => $name,
            'email' => $email,
            'image' => $imagePath
        ];
        
        if (updateUser($user['id'], $updateData)) {
            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['image'] = $imagePath;
            
            $message = 'Profile updated successfully!';
            saveAudit($user['id'], 'update', 'profile', $user['id'], ['name' => $name, 'email' => $email, 'image' => $imagePath]);
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    requireCsrf();
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $result = changePassword($currentPassword, $newPassword);
        if ($result['success']) {
            $message = 'Password changed successfully!';
        } else {
            $error = $result['error'];
        }
    }
}

// Refresh user data
$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Profile'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <h2><i class="bi bi-person-circle"></i> My Profile</h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-person me-2"></i>Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?= htmlspecialchars($currentUser['name']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF up to 5MB</small>
                                </div>
                                <?php if ($currentUser['image'] ?? null): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Current Image</label>
                                        <div>
                                            <img src="<?= htmlspecialchars($currentUser['image']) ?>" 
                                                 alt="Current Profile Image" 
                                                 class="rounded-circle" 
                                                 style="width: 100px; height: 100px; object-fit: cover;"
                                                 onerror="this.onerror=null; this.src='assets/images/default-avatar.php';">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>Security</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-warning w-100">
                                    <i class="bi bi-key me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-body text-center">
                            <img src="<?= htmlspecialchars($currentUser['image'] ?? 'assets/images/default-avatar.php') ?>" 
                                 alt="Profile Image" 
                                 class="rounded-circle mb-3" 
                                 style="width: 120px; height: 120px; object-fit: cover;"
                                 onerror="this.onerror=null; this.src='assets/images/default-avatar.php';">
                            <h5 class="mb-1"><?= htmlspecialchars($currentUser['name']) ?></h5>
                            <p class="text-muted mb-1"><?= htmlspecialchars($currentUser['email']) ?></p>
                            <small class="text-muted">ID: <?= htmlspecialchars($currentUser['empidno']) ?></small><br>
                            <small class="text-muted">Role: <?= ucfirst($currentUser['role'] ?? '') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>