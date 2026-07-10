<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';
require_once __DIR__ . '/../src/Services/GradeAggregationService.php';
require_once __DIR__ . '/../src/Services/NotificationService.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireTeacher();
$activityId = (int)($_GET['id'] ?? 0);

if (!$activityId) {
    header('Location: activities.php');
    exit;
}

// Fetch activity details
$stmt = db()->prepare('SELECT a.*, s.name as subject_name FROM activities a JOIN subjects s ON a.subject_id = s.id WHERE a.id = ?');
$stmt->execute([$activityId]);
$activity = $stmt->fetch();

if (!$activity) {
    header('Location: activities.php');
    exit;
}

$message = '';
$error = '';

// Get success message from session if exists
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    requireCsrf();
    
    $submissionId = (int)($_POST['submission_id'] ?? 0);
    $score = (int)($_POST['score'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');
    
    if ($submissionId && $score >= 0 && $score <= $activity['max_score']) {
        $stmt = db()->prepare('UPDATE activity_submissions SET score = ?, graded_by = ?, graded_at = NOW() WHERE id = ? AND activity_id = ?');
        if ($stmt->execute([$score, $user['id'], $submissionId, $activityId])) {
            // Update student's grade
            $stmt = db()->prepare('SELECT student_id FROM activity_submissions WHERE id = ?');
            $stmt->execute([$submissionId]);
            $studentId = $stmt->fetchColumn();
            
            // Recalculate grades
            require_once __DIR__ . '/../src/Services/GradeAggregationService.php';
            \App\Services\GradeAggregationService::updateForSubject((int)$studentId, (int)$activity['subject_id']);
            
            saveAudit($user['id'], 'grade', 'activity_submission', $submissionId, ['score' => $score]);
            
            // Notify the student
            NotificationService::notifyActivityGraded((int)$studentId, $activity['title'], $score, (int)$activity['max_score'], (int)$activity['subject_id']);
            
            // Redirect to prevent duplicate submission on refresh
            $_SESSION['success_message'] = 'Grade saved successfully!';
            header('Location: activity-submissions.php?id=' . $activityId);
            exit;
        } else {
            $error = 'Failed to save grade';
        }
    } else {
        $error = 'Invalid score';
    }
}

// Fetch submissions
$stmt = db()->prepare('
    SELECT asub.*, u.name as student_name, u.empidno as student_id
    FROM activity_submissions asub
    JOIN users u ON asub.student_id = u.id
    WHERE asub.activity_id = ?
    ORDER BY asub.submitted_at DESC
');
$stmt->execute([$activityId]);
$submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Submissions - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Activity Submissions'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>
                            <i class="bi bi-file-earmark-check"></i>
                            <?= htmlspecialchars($activity['title']) ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <span class="badge bg-primary"><?= htmlspecialchars($activity['subject_name']) ?></span>
                            • Max Score: <?= $activity['max_score'] ?> points
                            <?php if ($activity['deadline']): ?>
                                • Deadline: <?= date('M d, Y h:i A', strtotime($activity['deadline'])) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="activities.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back to Activities
                    </a>
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

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Student Submissions
                        <span class="badge bg-info ms-2"><?= count($submissions) ?> Total</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($submissions)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No submissions yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th data-label="Student">Student</th>
                                        <th data-label="Student ID">Student ID</th>
                                        <th data-label="Submitted">Submitted</th>
                                        <th data-label="Answer/File">Answer/File</th>
                                        <th class="text-center" data-label="Score">Score</th>
                                        <th class="text-center" data-label="Actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $sub): ?>
                                    <tr>
                                        <td data-label="Student"><strong><?= htmlspecialchars($sub['student_name']) ?></strong></td>
                                        <td data-label="Student ID"><?= htmlspecialchars($sub['student_id'] ?? 'N/A') ?></td>
                                        <td data-label="Submitted">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= date('M d, Y', strtotime($sub['submitted_at'])) ?>
                                                <br>
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('h:i A', strtotime($sub['submitted_at'])) ?>
                                            </small>
                                        </td>
                                        <td data-label="Answer/File">
                                            <?php if ($sub['answer_text']): ?>
                                                <button class="btn btn-sm btn-outline-info w-100 mb-2" onclick="showAnswer(<?= htmlspecialchars(json_encode($sub['answer_text']), ENT_QUOTES) ?>)">
                                                    <i class="bi bi-file-text"></i> View Text
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($sub['stored_filename']): ?>
                                                <a href="download-submission.php?id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline-primary w-100" target="_blank">
                                                    <i class="bi bi-download"></i> <?= htmlspecialchars($sub['original_filename'] ?? 'Download') ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!$sub['answer_text'] && !$sub['stored_filename']): ?>
                                                <span class="text-muted">No submission</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center" data-label="Score">
                                            <?php if ($sub['score'] !== null): ?>
                                                <span class="badge bg-success fs-6"><?= $sub['score'] ?> / <?= $activity['max_score'] ?></span>
                                                <br>
                                                <small class="text-muted">Graded on <?= date('M d', strtotime($sub['graded_at'])) ?></small>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Not Graded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center" data-label="Actions">
                                            <button class="btn btn-sm btn-primary w-100" onclick="gradeSubmission(<?= $sub['id'] ?>, '<?= htmlspecialchars($sub['student_name']) ?>', <?= $sub['score'] ?? 0 ?>)">
                                                <i class="bi bi-pencil-square"></i> Grade
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Grade Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" id="submission_id" name="submission_id">
                    <div class="modal-body">
                        <p>Grading: <strong id="student_name_display"></strong></p>
                        <div class="mb-3">
                            <label for="score" class="form-label">Score</label>
                            <input type="number" class="form-control" id="score" name="score" min="0" max="<?= $activity['max_score'] ?>" required>
                            <small class="text-muted">Maximum: <?= $activity['max_score'] ?> points</small>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Feedback (Optional)</label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="grade_submission" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Save Grade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Answer View Modal -->
    <div class="modal fade" id="answerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Student Answer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card bg-light">
                        <div class="card-body">
                            <p id="answer_text" class="mb-0" style="white-space: pre-wrap;"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function gradeSubmission(submissionId, studentName, currentScore) {
            document.getElementById('submission_id').value = submissionId;
            document.getElementById('student_name_display').textContent = studentName;
            document.getElementById('score').value = currentScore || '';
            new bootstrap.Modal(document.getElementById('gradeModal')).show();
        }
        
        function showAnswer(answerText) {
            document.getElementById('answer_text').textContent = answerText;
            new bootstrap.Modal(document.getElementById('answerModal')).show();
        }
    </script>
</body>
</html>
