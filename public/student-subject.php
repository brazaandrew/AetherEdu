<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireStudent();
$subjectId = (int)($_GET['id'] ?? 0);

if (!$subjectId) {
    header('Location: my-subjects.php');
    exit;
}

$message = '';
$error = '';

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_now'])) {
    requireCsrf();
    
    // Check if student is already enrolled
    $stmt = db()->prepare('SELECT id FROM enrollments WHERE student_id = ? AND subject_id = ?');
    $stmt->execute([$user['id'], $subjectId]);
    
    if (!$stmt->fetch()) {
        // Enroll student
        try {
            $stmt = db()->prepare('INSERT INTO enrollments (student_id, subject_id, enrolled_at) VALUES (?, ?, NOW())');
            $stmt->execute([$user['id'], $subjectId]);
            
            $message = 'Successfully enrolled in this subject!';
            saveAudit($user['id'], 'enroll', 'enrollment', (int)db()->lastInsertId(), ['subject_id' => $subjectId]);
        } catch (PDOException $e) {
            $error = 'Failed to enroll. Please try again.';
        }
    }
}

// Fetch subject details
$stmt = db()->prepare('SELECT * FROM subjects WHERE id = ?');
$stmt->execute([$subjectId]);
$subject = $stmt->fetch();

if (!$subject) {
    header('Location: my-subjects.php');
    exit;
}

// Check if student is enrolled
$stmt = db()->prepare('SELECT id, enrolled_at FROM enrollments WHERE student_id = ? AND subject_id = ?');
$stmt->execute([$user['id'], $subjectId]);
$enrollment = $stmt->fetch();
$isEnrolled = (bool)$enrollment;

// Fetch activities with submission status
$stmt = db()->prepare('
    SELECT a.*, 
        asub.id as submission_id,
        asub.score,
        asub.submitted_at
    FROM activities a
    LEFT JOIN activity_submissions asub ON a.id = asub.activity_id AND asub.student_id = ?
    WHERE a.subject_id = ?
    ORDER BY a.created_at DESC
');
$stmt->execute([$user['id'], $subjectId]);
$activities = $stmt->fetchAll();

// Fetch quizzes with attempt status
$stmt = db()->prepare('
    SELECT q.*,
        qa.id as attempt_id,
        qa.score,
        qa.submitted_at
    FROM quizzes q
    LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = ?
    WHERE q.subject_id = ?
    ORDER BY q.created_at DESC
');
$stmt->execute([$user['id'], $subjectId]);
$quizzes = $stmt->fetchAll();

// Fetch files
$stmt = db()->prepare('SELECT * FROM files WHERE subject_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$subjectId]);
$files = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject['name']) ?> - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <link rel="stylesheet" href="assets/css/gamification.css?v=1">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = htmlspecialchars($subject['name']); include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>
                            <i class="bi bi-book"></i>
                            <?= htmlspecialchars($subject['name']) ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="bi bi-tag me-1"></i><?= htmlspecialchars($subject['code']) ?>
                            <?php if ($isEnrolled): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="bi bi-check-circle me-1"></i>Enrolled
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="my-subjects.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back to Subjects
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

            <?php if (!$isEnrolled): ?>
            <!-- Enrollment Required Notice -->
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="bi bi-exclamation-triangle me-2"></i>Enrollment Required
                    </h5>
                    <p class="mb-0">You need to enroll in this subject to access activities and quizzes.</p>
                </div>
                <form method="POST" style="display:inline;">
                    <?= csrfField() ?>
                    <button type="submit" name="enroll_now" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Enroll Now
                    </button>
                </form>
            </div>
            <?php else: ?>
            <!-- Enrollment Info + Subject Progress Ring -->
            <?php
                $totalItems = count($activities) + count($quizzes);
                $completedItems = 0;
                foreach ($activities as $a) { if ($a['submission_id']) $completedItems++; }
                foreach ($quizzes as $q) { if ($q['attempt_id']) $completedItems++; }
                $completionPct = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                $circumference = 157; // 2*pi*25
                $dashOffset = $circumference - ($circumference * $completionPct / 100);
            ?>
            <div class="subject-progress-ring">
                <div class="ring-wrap">
                    <svg class="progress-ring-svg" width="60" height="60" viewBox="0 0 60 60">
                        <circle class="progress-ring-circle-bg" cx="30" cy="30" r="25" stroke-width="5"/>
                        <circle class="progress-ring-circle-fill" id="ringFill" cx="30" cy="30" r="25" stroke-width="5"/>
                    </svg>
                    <span class="ring-center-text" id="ringText">0%</span>
                </div>
                <div>
                    <div class="fw-700" style="font-size:0.95rem;">Subject Progress</div>
                    <div class="text-muted" style="font-size:0.82rem;"><?= $completedItems ?> of <?= $totalItems ?> task<?= $totalItems !== 1 ? 's' : '' ?> completed</div>
                    <div class="mt-1">
                        <span class="xp-badge"><i class="bi bi-lightning-fill"></i> <?= $completedItems * 50 + ($completionPct >= 100 ? 50 : 0) ?> XP earned</span>
                    </div>
                </div>
            </div>
            <div class="alert alert-success mb-3" style="font-size:0.85rem;">
                <i class="bi bi-info-circle me-2"></i>
                You enrolled in this subject on <?= date('M d, Y', strtotime($enrollment['enrolled_at'])) ?>
            </div>
            <?php endif; ?>

            <!-- Materials Section -->
            <?php if (!empty($files)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Course Materials</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($files as $file): ?>
                        <a href="download.php?id=<?= $file['id'] ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                                <div>
                                    <i class="bi bi-file-pdf text-danger me-2"></i>
                                    <?= htmlspecialchars($file['original_filename']) ?>
                                </div>
                                <small class="text-muted"><?= date('M d, Y', strtotime($file['uploaded_at'])) ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($isEnrolled): ?>

            <!-- Activities Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($activities)): ?>
                        <p class="text-muted mb-0">No activities posted yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($activities as $activity):
                                $isCompleted = (bool)$activity['submission_id'];
                                $actXp = 50;
                            ?>
                            <div class="list-group-item game-list-item <?= $isCompleted ? 'completed' : '' ?>">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3" style="padding-left:4px;">
                                    <div class="flex-grow-1 w-100">
                                        <h6 class="mb-1"><?= htmlspecialchars($activity['title']) ?></h6>
                                        <p class="text-muted mb-2"><small><?= nl2br(htmlspecialchars($activity['description'])) ?></small></p>
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                            <span class="xp-badge"><i class="bi bi-lightning-fill"></i> +<?= $actXp ?> XP</span>
                                            <span class="badge bg-info"><?= $activity['max_score'] ?> pts</span>
                                            <?php if ($activity['deadline']): ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i>
                                                    Due: <?= date('M d, Y', strtotime($activity['deadline'])) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($isCompleted): ?>
                                                <span class="completion-badge"><i class="bi bi-check-circle-fill"></i> Submitted</span>
                                                <?php if ($activity['score'] !== null): ?>
                                                    <span class="badge bg-primary">Score: <?= $activity['score'] ?>/<?= $activity['max_score'] ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Submitted</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="w-100 w-md-auto">
                                        <?php if (!$isCompleted): ?>
                                            <a href="submit-activity.php?id=<?= $activity['id'] ?>" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-upload me-1"></i> Submit
                                            </a>
                                        <?php else: ?>
                                            <a href="submit-activity.php?id=<?= $activity['id'] ?>" class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quizzes Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Quizzes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($quizzes)): ?>
                        <p class="text-muted mb-0">No quizzes available yet.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($quizzes as $quiz):
                                $isAttempted = (bool)$quiz['attempt_id'];
                                $qPct = ($isAttempted && $quiz['score'] !== null && $quiz['max_score'] > 0)
                                    ? ($quiz['score'] / $quiz['max_score']) * 100 : 0;
                                $qXp = $isAttempted ? ($qPct >= 90 ? 100 : ($qPct >= 75 ? 75 : ($qPct >= 50 ? 50 : 20))) : 0;
                            ?>
                            <div class="list-group-item game-list-item <?= $isAttempted ? 'completed' : '' ?>">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3" style="padding-left:4px;">
                                    <div class="flex-grow-1 w-100">
                                        <h6 class="mb-1"><?= htmlspecialchars($quiz['title']) ?></h6>
                                        <p class="text-muted mb-2"><small><?= htmlspecialchars($quiz['instructions']) ?></small></p>
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                            <?php if ($isAttempted && $qXp > 0): ?>
                                                <span class="xp-badge"><i class="bi bi-lightning-fill"></i> +<?= $qXp ?> XP</span>
                                            <?php else: ?>
                                                <span class="xp-badge"><i class="bi bi-lightning-fill"></i> up to +100 XP</span>
                                            <?php endif; ?>
                                            <span class="badge bg-info"><?= $quiz['max_score'] ?> pts</span>
                                            <?php if ($quiz['time_limit_minutes'] > 0): ?>
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock me-1"></i><?= $quiz['time_limit_minutes'] ?> mins
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($isAttempted): ?>
                                                <span class="completion-badge"><i class="bi bi-check-circle-fill"></i> Completed</span>
                                                <?php if ($quiz['score'] !== null): ?>
                                                    <span class="badge bg-primary">Score: <?= $quiz['score'] ?>/<?= $quiz['max_score'] ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Taken</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="w-100 w-md-auto">
                                        <?php if (!$isAttempted): ?>
                                            <a href="take-quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary btn-sm w-100">
                                                <i class="bi bi-play-circle me-1"></i> Start Quiz
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                <i class="bi bi-check-circle me-1"></i> Completed
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Animate progress ring
    document.addEventListener('DOMContentLoaded', () => {
        const pct = <?= $completionPct ?? 0 ?>;
        const circumference = 157;
        const fill = document.getElementById('ringFill');
        const text = document.getElementById('ringText');
        if (fill) {
            setTimeout(() => {
                fill.style.strokeDashoffset = circumference - (circumference * pct / 100);
                if (text) text.textContent = pct + '%';
            }, 400);
        }
    });
    </script>
</body>
</html>
