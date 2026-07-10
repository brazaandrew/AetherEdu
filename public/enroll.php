<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireStudent();

$message = '';
$error = '';

// Create enrollments table if not exists
try {
    db()->exec('CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        subject_id INT NOT NULL,
        enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_enrollment (student_id, subject_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
} catch (PDOException $e) {
    // Table already exists
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    requireCsrf();
    
    $enrollmentKey = strtoupper(trim($_POST['enrollment_key'] ?? ''));
    
    if ($enrollmentKey) {
        // Find subject by enrollment key
        $stmt = db()->prepare('SELECT id, name FROM subjects WHERE enrollment_key = ?');
        $stmt->execute([$enrollmentKey]);
        $subject = $stmt->fetch();
        
        if ($subject) {
            // Check if already enrolled
            $stmt = db()->prepare('SELECT id FROM enrollments WHERE student_id = ? AND subject_id = ?');
            $stmt->execute([$user['id'], $subject['id']]);
            
            if ($stmt->fetch()) {
                $error = 'You are already enrolled in this subject.';
            } else {
                // Enroll student
                try {
                    $stmt = db()->prepare('INSERT INTO enrollments (student_id, subject_id, enrolled_at) VALUES (?, ?, NOW())');
                    $stmt->execute([$user['id'], $subject['id']]);
                    
                    $message = 'Successfully enrolled in: ' . htmlspecialchars($subject['name']);
                    saveAudit($user['id'], 'enroll', 'enrollment', (int)db()->lastInsertId(), ['subject_id' => $subject['id']]);
                } catch (PDOException $e) {
                    $error = 'Failed to enroll. Please try again.';
                }
            }
        } else {
            $error = 'Invalid enrollment key. Please check and try again.';
        }
    } else {
        $error = 'Please enter an enrollment key.';
    }
}

// Fetch enrolled subjects
$stmt = db()->prepare('
    SELECT s.*, e.enrolled_at,
        (SELECT COUNT(*) FROM activities a WHERE a.subject_id = s.id) as activity_count,
        (SELECT COUNT(*) FROM quizzes q WHERE q.subject_id = s.id) as quiz_count
    FROM enrollments e
    JOIN subjects s ON e.subject_id = s.id
    WHERE e.student_id = ?
    ORDER BY e.enrolled_at DESC
');
$stmt->execute([$user['id']]);
$enrolledSubjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Subjects - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Enroll in Subjects'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <h2>
                    <i class="bi bi-key"></i>
                    Enroll in Subjects
                </h2>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Enrollment Form -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body p-4">
                            <h4 class="mb-3">
                                <i class="bi bi-plus-circle me-2"></i>Enroll Using Key
                            </h4>
                            <p class="text-muted">Enter the enrollment key provided by your teacher to join a subject.</p>
                            <form method="POST" action="">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="enrollment_key" class="form-label">Enrollment Key</label>
                                    <input type="text" class="form-control form-control-lg text-uppercase text-center" 
                                           id="enrollment_key" name="enrollment_key" 
                                           placeholder="XXXXXXXX" 
                                           maxlength="8" 
                                           style="letter-spacing: 5px; font-size: 1.5rem; font-weight: bold;"
                                           required>
                                    <small class="text-muted">8-character key (e.g., 224DD06D)</small>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="enroll" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>Enroll Now
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Enrolled Subjects -->
            <h4 class="mb-3">
                <i class="bi bi-bookmark-check me-2"></i>My Enrolled Subjects
                <span class="badge bg-primary"><?= count($enrolledSubjects) ?></span>
            </h4>
            
            <div class="row g-4">
                <?php foreach ($enrolledSubjects as $subject): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 hover-shadow">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($subject['name']) ?></h5>
                            <p class="text-muted mb-3">
                                <small><i class="bi bi-tag me-1"></i><?= htmlspecialchars($subject['code']) ?></small>
                                <br>
                                <small><i class="bi bi-calendar me-1"></i>Enrolled: <?= date('M d, Y', strtotime($subject['enrolled_at'])) ?></small>
                            </p>
                            <p class="card-text"><?= htmlspecialchars($subject['description'] ?? 'No description') ?></p>
                            <div class="d-flex gap-3 mb-3">
                                <span class="badge bg-info">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    <?= $subject['activity_count'] ?> Activities
                                </span>
                                <span class="badge bg-warning">
                                    <i class="bi bi-card-checklist me-1"></i>
                                    <?= $subject['quiz_count'] ?> Quizzes
                                </span>
                            </div>
                            <a href="student-subject.php?id=<?= $subject['id'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Open Subject
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($enrolledSubjects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You haven't enrolled in any subjects yet. Use the enrollment key from your teacher to get started!
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-uppercase enrollment key input
        document.getElementById('enrollment_key').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
