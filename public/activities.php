<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';
require_once __DIR__ . '/../src/Services/NotificationService.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$role = $user['role'];

if ($role === 'student') {
    header('Location: my-subjects.php');
    exit;
}

$error = '';

// Handle Delete Activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_activity'])) {
    requireCsrf();
    
    $activityId = (int)($_POST['activity_id'] ?? 0);
    
    if ($activityId) {
        try {
            // Verify ownership for teachers
            if ($role === 'teacher') {
                $stmt = db()->prepare('SELECT a.id FROM activities a JOIN subjects s ON a.subject_id = s.id JOIN folder_teacher ft ON s.id = ft.subject_id WHERE a.id = ? AND ft.teacher_empidno = ?');
                $stmt->execute([$activityId, $user['empidno']]);
                if (!$stmt->fetch()) {
                    $error = 'You do not have permission to delete this activity';
                    goto skip_delete;
                }
            }
            
            // Delete activity (cascade will handle submissions)
            $stmt = db()->prepare('DELETE FROM activities WHERE id = ?');
            $stmt->execute([$activityId]);
            
            saveAudit($user['id'], 'delete', 'activity', $activityId, []);
            
            $_SESSION['success_message'] = 'Activity deleted successfully!';
            header('Location: activities.php');
            exit;
        } catch (Exception $e) {
            $error = 'Failed to delete activity';
        }
    }
    skip_delete:
}

// Handle Create Activity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_activity'])) {
    requireCsrf();
    
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $maxScore = (int)($_POST['max_score'] ?? 100);
    $deadline = trim($_POST['deadline'] ?? '');
    $activityType = $_POST['activity_type'] ?? 'file'; // file or essay
    
    if ($subjectId && $title) {
        try {
            $stmt = db()->prepare('INSERT INTO activities (subject_id, title, description, max_score, deadline, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$subjectId, $title, $description, $maxScore, $deadline ?: null, $user['id']]);
            
            $activityId = (int)db()->lastInsertId();
            saveAudit($user['id'], 'create', 'activity', $activityId, compact('subjectId', 'title'));

            // Notify enrolled students
            $subjectStmt = db()->prepare('SELECT name FROM subjects WHERE id = ?');
            $subjectStmt->execute([$subjectId]);
            $subjectName = $subjectStmt->fetchColumn() ?: 'Unknown Subject';
            NotificationService::notifyNewActivity($activityId, $title, $subjectId, $subjectName, $deadline ?: null);

            // Redirect to prevent duplicate submission on refresh
            $_SESSION['success_message'] = 'Activity created successfully!';
            header('Location: activities.php');
            exit;
        } catch (Exception $e) {
            $error = 'Failed to create activity';
        }
    } else {
        $error = 'Subject and title are required';
    }
}

// Get success message from session if exists
$message = '';
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Fetch activities
if ($role === 'teacher') {
    $stmt = db()->prepare('SELECT a.*, s.name as subject_name FROM activities a JOIN subjects s ON a.subject_id = s.id JOIN folder_teacher ft ON s.id = ft.subject_id WHERE ft.teacher_empidno = ? ORDER BY a.created_at DESC');
    $stmt->execute([$user['empidno']]);
} else {
    $stmt = db()->query('SELECT a.*, s.name as subject_name FROM activities a JOIN subjects s ON a.subject_id = s.id ORDER BY a.created_at DESC');
}
$activities = $stmt->fetchAll();

// Fetch teacher's subjects for dropdown
if ($role === 'teacher') {
    $stmt = db()->prepare('SELECT DISTINCT s.id, s.code, s.name FROM subjects s JOIN folder_teacher ft ON s.id = ft.subject_id WHERE ft.teacher_empidno = ? AND s.archived = 0 ORDER BY s.name');
    $stmt->execute([$user['empidno']]);
} else {
    $stmt = db()->query('SELECT id, code, name FROM subjects WHERE archived = 0 ORDER BY name');
}
$teacherSubjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activities - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Activities'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2>
                        <i class="bi bi-file-earmark-text"></i>
                        Activities
                    </h2>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createActivityModal">
                            <i class="bi bi-plus-circle me-2"></i> Create Activity
                        </button>
                    </div>
                </div>
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
            <?php foreach ($activities as $activity): ?>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h5>
                                <span class="badge bg-primary"><?= htmlspecialchars($activity['subject_name']) ?></span>
                            </div>
                            <span class="badge bg-success">Max: <?= $activity['max_score'] ?> pts</span>
                        </div>
                        <p class="text-muted mb-3"><?= htmlspecialchars($activity['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?php if ($activity['deadline']): ?>
                                    Due: <?= date('M d, Y', strtotime($activity['deadline'])) ?>
                                <?php else: ?>
                                    No deadline
                                <?php endif; ?>
                            </small>
                            <div class="btn-group">
                                <a href="activity-submissions.php?id=<?= $activity['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $activity['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($activities)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No activities created yet.
                </div>
            </div>
            <?php endif; ?>
        </div>
        </div>
    </div>

    <!-- Create Activity Modal -->
    <div class="modal fade" id="createActivityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="subject_id" class="form-label">Subject</label>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($teacherSubjects as $subj): ?>
                                    <option value="<?= $subj['id'] ?>"><?= htmlspecialchars($subj['name']) ?> (<?= htmlspecialchars($subj['code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_score" class="form-label">Maximum Score</label>
                                <input type="number" class="form-control" id="max_score" name="max_score" value="100" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Activity Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Instructions</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="activity_type" class="form-label">Submission Type</label>
                                <select class="form-select" id="activity_type" name="activity_type">
                                    <option value="file">File Upload</option>
                                    <option value="essay">Essay/Text</option>
                                    <option value="both">Both File and Text</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="deadline" class="form-label">Deadline (Optional)</label>
                                <input type="datetime-local" class="form-control" id="deadline" name="deadline">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_activity" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create Activity
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modals -->
    <?php foreach ($activities as $activity): ?>
    <div class="modal fade" id="deleteModal<?= $activity['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Delete Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this activity?</p>
                    <div class="alert alert-warning">
                        <strong><?= htmlspecialchars($activity['title']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($activity['subject_name']) ?></small>
                    </div>
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong>Warning:</strong> This will also delete all student submissions for this activity. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="activity_id" value="<?= $activity['id'] ?>">
                        <button type="submit" name="delete_activity" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Activity
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
