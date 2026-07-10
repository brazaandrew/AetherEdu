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
$activityId = (int)($_GET['id'] ?? 0);

if (!$activityId) {
    header('Location: my-subjects.php');
    exit;
}

// Fetch activity details
$stmt = db()->prepare('SELECT a.*, s.name as subject_name FROM activities a JOIN subjects s ON a.subject_id = s.id WHERE a.id = ?');
$stmt->execute([$activityId]);
$activity = $stmt->fetch();

if (!$activity) {
    header('Location: my-subjects.php');
    exit;
}

// Check existing submission
$stmt = db()->prepare('SELECT * FROM activity_submissions WHERE activity_id = ? AND student_id = ?');
$stmt->execute([$activityId, $user['id']]);
$existingSubmission = $stmt->fetch();

$message = '';
$error = '';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_activity']) && !$existingSubmission) {
    requireCsrf();
    
    $answerText = trim($_POST['answer_text'] ?? '');
    $filePath = null;
    
    // Handle file upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['submission_file'];
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Only PDF, DOC, DOCX, JPG, PNG allowed.';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $error = 'File too large. Maximum 10MB.';
        } else {
            // Create upload directory
            $uploadDir = __DIR__ . "/uploads/activities/{$activityId}/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = $user['id'] . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $filePath = "uploads/activities/{$activityId}/{$fileName}";
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
    
    if (!$error && ($answerText || $filePath)) {
        try {
            $stmt = db()->prepare('INSERT INTO activity_submissions (activity_id, student_id, answer_text, file_path, submitted_at) VALUES (?, ?, ?, ?, NOW())');
            $stmt->execute([$activityId, $user['id'], $answerText, $filePath]);
            
            $submissionId = (int)db()->lastInsertId();
            saveAudit($user['id'], 'submit', 'activity_submission', $submissionId, ['activity_id' => $activityId]);
            
            $_SESSION['submission_success'] = true;
            header('Location: student-subject.php?id=' . $activity['subject_id']);
            exit;
        } catch (Exception $e) {
            $error = 'Failed to submit activity.';
        }
    } elseif (!$error) {
        $error = 'Please provide text answer or upload a file.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Activity - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Submit Activity'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>
                            <i class="bi bi-file-earmark-text"></i>
                            <?= htmlspecialchars($activity['title']) ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <span class="badge bg-primary"><?= htmlspecialchars($activity['subject_name']) ?></span>
                            • <?= $activity['max_score'] ?> points
                            <?php if ($activity['deadline']): ?>
                                • Due: <?= date('M d, Y h:i A', strtotime($activity['deadline'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="student-subject.php?id=<?= $activity['subject_id'] ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(htmlspecialchars($activity['description'])) ?></p>
                </div>
            </div>

            <?php if ($existingSubmission): ?>
                <!-- View Existing Submission -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Your Submission</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Submitted on: <?= date('M d, Y h:i A', strtotime($existingSubmission['submitted_at'])) ?>
                        </p>
                        
                        <?php if ($existingSubmission['answer_text']): ?>
                            <div class="mb-3">
                                <h6>Text Answer:</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($existingSubmission['answer_text']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($existingSubmission['file_path']): ?>
                            <div class="mb-3">
                                <h6>Attached File:</h6>
                                <a href="<?= htmlspecialchars($existingSubmission['file_path']) ?>" class="btn btn-outline-primary" target="_blank">
                                    <i class="bi bi-download me-2"></i>Download Submission
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($existingSubmission['score'] !== null): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-star me-2"></i>Graded</h5>
                                <h3>Score: <?= $existingSubmission['score'] ?> / <?= $activity['max_score'] ?></h3>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-clock me-2"></i>
                                Your submission is pending grading by your teacher.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Submission Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Submit Your Work</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <?= csrfField() ?>
                            
                            <div class="mb-4">
                                <label for="answer_text" class="form-label">Text Answer</label>
                                <textarea class="form-control" id="answer_text" name="answer_text" rows="8" 
                                          placeholder="Type your answer here..."></textarea>
                                <small class="text-muted">You can write your answer directly here.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="submission_file" class="form-label">Upload File (Optional)</label>
                                <input type="file" class="form-control" id="submission_file" name="submission_file" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</small>
                            </div>
                            
                            <?php if ($activity['deadline'] && strtotime($activity['deadline']) < time()): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Warning:</strong> This activity is past the deadline. Late submissions may be penalized.
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="submit_activity" class="btn btn-primary btn-lg" 
                                        onclick="return confirm('Submit your activity? You cannot edit after submission.')">
                                    <i class="bi bi-check-circle me-2"></i>Submit Activity
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
