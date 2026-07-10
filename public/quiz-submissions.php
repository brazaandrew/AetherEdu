<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireTeacher();
$quizId = (int)($_GET['id'] ?? 0);

if (!$quizId) {
    header('Location: quizzes.php');
    exit;
}

// Fetch quiz details
$stmt = db()->prepare('SELECT q.*, s.name as subject_name FROM quizzes q JOIN subjects s ON q.subject_id = s.id WHERE q.id = ?');
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: quizzes.php');
    exit;
}

// Fetch attempts with student info
$stmt = db()->prepare('
    SELECT qa.*, u.name as student_name, u.empidno as student_id
    FROM quiz_attempts qa
    JOIN users u ON qa.student_id = u.id
    WHERE qa.quiz_id = ?
    ORDER BY qa.submitted_at DESC
');
$stmt->execute([$quizId]);
$attempts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Submissions - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Quiz Submissions'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>
                            <i class="bi bi-card-checklist"></i>
                            <?= htmlspecialchars($quiz['title']) ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <span class="badge bg-primary"><?= htmlspecialchars($quiz['subject_name']) ?></span>
                            • Max Score: <?= $quiz['max_score'] ?> points
                        </p>
                    </div>
                    <a href="quizzes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back to Quizzes
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Student Attempts
                        <span class="badge bg-info ms-2"><?= count($attempts) ?> Total</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($attempts)): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            No submissions yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Student ID</th>
                                        <th>Submitted</th>
                                        <th class="text-center">Score</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attempts as $attempt): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($attempt['student_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($attempt['student_id'] ?? 'N/A') ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= date('M d, Y', strtotime($attempt['submitted_at'])) ?>
                                                <br>
                                                <i class="bi bi-clock me-1"></i>
                                                <?= date('h:i A', strtotime($attempt['submitted_at'])) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($attempt['score'] !== null): ?>
                                                <span class="badge bg-success fs-6"><?= $attempt['score'] ?> / <?= $quiz['max_score'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($attempt['score'] !== null): ?>
                                                <span class="badge bg-success">Graded</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Needs Grading</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="quiz-attempt-review.php?attempt_id=<?= $attempt['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Review
                                            </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
