<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';
require_once __DIR__ . '/../src/Services/GradeService.php';

use App\Services\GradeService;

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$role = $user['role'];

if ($role === 'student') {
    header('Location: my-subjects.php');
    exit;
}

$attemptId = (int)($_GET['id'] ?? 0);

if (!$attemptId) {
    header('Location: quizzes.php');
    exit;
}

// Check if tab_protection_enabled column exists in the database
$checkColumnStmt = db()->prepare("SHOW COLUMNS FROM quizzes LIKE 'tab_protection_enabled'");
$checkColumnStmt->execute();
$columnExists = $checkColumnStmt->fetch();

if ($columnExists) {
    $stmt = db()->prepare('
        SELECT qa.*, q.title as quiz_title, q.subject_id, q.max_score, q.tab_protection_enabled,
               s.name as subject_name, u.name as student_name, u.empidno as student_id
        FROM quiz_attempts qa
        JOIN quizzes q ON q.id = qa.quiz_id
        JOIN subjects s ON s.id = q.subject_id
        JOIN users u ON u.id = qa.student_id
        WHERE qa.id = ?
    ');
} else {
    $stmt = db()->prepare('
        SELECT qa.*, q.title as quiz_title, q.subject_id, q.max_score,
               s.name as subject_name, u.name as student_name, u.empidno as student_id
        FROM quiz_attempts qa
        JOIN quizzes q ON q.id = qa.quiz_id
        JOIN subjects s ON s.id = q.subject_id
        JOIN users u ON u.id = qa.student_id
        WHERE qa.id = ?
    ');
}
$stmt->execute([$attemptId]);
$attempt = $stmt->fetch();

if (!$attempt) {
    header('Location: quizzes.php');
    exit;
}

// Check permission (teacher must be assigned to subject or be admin)
if ($role === 'teacher') {
    $stmt = db()->prepare('SELECT id FROM folder_teacher WHERE subject_id = ? AND teacher_empidno = ?');
    $stmt->execute([$attempt['subject_id'], $user['empidno']]);
    if (!$stmt->fetch()) {
        header('Location: quizzes.php');
        exit;
    }
}

$message = '';
$error = '';

// Handle manual grading submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_grades'])) {
    requireCsrf();
    
    try {
        $essayScores = [];
        
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'essay_') === 0) {
                $questionId = (int)str_replace('essay_', '', $key);
                $points = (int)$value;
                if ($points >= 0) {
                    $essayScores[$questionId] = $points;
                }
            }
        }
        
        // Update manual scores and recompute grade
        GradeService::updateManualScores(db(), $attemptId, $essayScores);
        
        saveAudit($user['id'], 'grade_essay', 'quiz_attempt', $attemptId, ['essay_count' => count($essayScores)]);
        
        $message = 'Essay questions graded successfully! Student grade has been updated.';
        
        // Refresh attempt data
        $stmt->execute([$attemptId]);
        $attempt = $stmt->fetch();
        
    } catch (Exception $e) {
        $error = 'Failed to save grades: ' . $e->getMessage();
    }
}

// Fetch questions and answers
$stmt = db()->prepare('
    SELECT qq.*, qa.answer_text, qa.is_correct, qa.requires_manual_grading, qa.manual_points_awarded
    FROM quiz_questions qq
    LEFT JOIN quiz_answers qa ON qa.question_id = qq.id AND qa.attempt_id = ?
    WHERE qq.quiz_id = ?
    ORDER BY qq.id ASC
');
$stmt->execute([$attemptId, $attempt['quiz_id']]);
$questionsWithAnswers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Quiz Attempt - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Grade Quiz Attempt'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>
                            <i class="bi bi-pencil-square"></i>
                            Grade Quiz Attempt
                        </h2>
                        <p class="text-muted mb-0">
                            <strong><?= htmlspecialchars($attempt['quiz_title']) ?></strong> •
                            Student: <?= htmlspecialchars($attempt['student_name']) ?> (<?= htmlspecialchars($attempt['student_id']) ?>)
                        </p>
                    </div>
                    <a href="activity-submissions.php?quiz_id=<?= $attempt['quiz_id'] ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back
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

            <!-- Score Summary -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h6 class="text-muted">Auto-Graded Score</h6>
                            <h3 class="text-primary"><?= $attempt['auto_graded_score'] ?? 0 ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Current Total Score</h6>
                            <h3 class="text-success"><?= $attempt['score'] ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Max Score</h6>
                            <h3><?= $attempt['max_score'] ?></h3>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Status</h6>
                            <h3>
                                <?php if ($attempt['needs_manual_grading']): ?>
                                    <span class="badge bg-warning">Needs Grading</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Graded</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
                <?= csrfField() ?>
                
                <?php $qNum = 1; $hasEssay = false; ?>
                <?php foreach ($questionsWithAnswers as $qa): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5>
                                Question <?= $qNum++ ?>
                                <span class="badge bg-secondary"><?= ucfirst($qa['question_type']) ?></span>
                            </h5>
                            <span class="badge bg-info"><?= $qa['points'] ?> point<?= $qa['points'] > 1 ? 's' : '' ?></span>
                        </div>
                        
                        <p class="lead mb-3"><?= nl2br(htmlspecialchars($qa['question_text'])) ?></p>
                        
                        <?php if ($qa['question_type'] === 'mcq'): ?>
                            <?php $choices = json_decode($qa['choices_json'], true); ?>
                            <div class="mb-3">
                                <strong>Choices:</strong>
                                <ul class="mt-2">
                                    <?php foreach ($choices as $choice): ?>
                                        <li class="<?= $choice === $qa['correct_answer'] ? 'text-success fw-bold' : '' ?>">
                                            <?= htmlspecialchars($choice) ?>
                                            <?= $choice === $qa['correct_answer'] ? '✓ (Correct)' : '' ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php elseif ($qa['question_type'] !== 'essay'): ?>
                            <p><strong>Correct Answer:</strong> <span class="text-success"><?= htmlspecialchars($qa['correct_answer']) ?></span></p>
                        <?php endif; ?>
                        
                        <div class="alert <?= $qa['is_correct'] ? 'alert-success' : ($qa['question_type'] === 'essay' ? 'alert-info' : 'alert-danger') ?>">
                            <strong>Student's Answer:</strong>
                            <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($qa['answer_text'] ?: '(No answer provided)')) ?></p>
                        </div>
                        
                        <?php if ($qa['question_type'] === 'essay'): ?>
                            <?php $hasEssay = true; ?>
                            <div class="row align-items-center mt-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Points Awarded:</label>
                                    <input type="number" 
                                           class="form-control" 
                                           name="essay_<?= $qa['id'] ?>"
                                           value="<?= $qa['manual_points_awarded'] ?? 0 ?>"
                                           min="0" 
                                           max="<?= $qa['points'] ?>"
                                           required>
                                    <small class="text-muted">Maximum: <?= $qa['points'] ?> points</small>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($qa['manual_points_awarded'] !== null): ?>
                                        <div class="alert alert-success mb-0">
                                            <i class="bi bi-check-circle me-2"></i>
                                            Previously graded: <?= $qa['manual_points_awarded'] ?>/<?= $qa['points'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <?php if ($qa['is_correct']): ?>
                                    <span class="badge bg-success">✓ Correct (+<?= $qa['points'] ?> points)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">✗ Incorrect (0 points)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if ($hasEssay): ?>
                <div class="d-flex gap-2">
                    <button type="submit" name="submit_grades" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle me-2"></i>Save Grades
                    </button>
                    <a href="activity-submissions.php?quiz_id=<?= $attempt['quiz_id'] ?>" class="btn btn-outline-secondary btn-lg">
                        Cancel
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
