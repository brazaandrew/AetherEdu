<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireStudent();

// Fetch enrolled subjects with activity and quiz counts
$stmt = db()->prepare('
    SELECT s.*, e.enrolled_at,
        (SELECT COUNT(*) FROM activities a WHERE a.subject_id = s.id) as activity_count,
        (SELECT COUNT(*) FROM quizzes q WHERE q.subject_id = s.id) as quiz_count,
        (SELECT COUNT(*) 
         FROM activities a 
         LEFT JOIN activity_submissions asub ON a.id = asub.activity_id AND asub.student_id = ?
         WHERE a.subject_id = s.id AND asub.id IS NULL
        ) as pending_activities,
        (SELECT COUNT(*) 
         FROM quizzes q 
         LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.student_id = ?
         WHERE q.subject_id = s.id AND qa.id IS NULL
        ) as pending_quizzes
    FROM enrollments e
    JOIN subjects s ON e.subject_id = s.id
    WHERE e.student_id = ?
    ORDER BY s.name
');
$stmt->execute([$user['id'], $user['id'], $user['id']]);
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'My Subjects'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <h2>
                    <i class="bi bi-book"></i>
                    My Subjects
                </h2>
            </div>

            <div class="row g-4">
                <?php foreach ($subjects as $subject): ?>
                <div class="col-md-6 col-lg-4 col-sm-12">
                    <div class="card h-100 hover-shadow">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-2">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($subject['name']) ?></h5>
                                <?php if ($subject['pending_activities'] > 0 || $subject['pending_quizzes'] > 0): ?>
                                <span class="badge bg-danger rounded-pill">
                                    <?= $subject['pending_activities'] + $subject['pending_quizzes'] ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted mb-3">
                                <small><i class="bi bi-tag me-1"></i><?= htmlspecialchars($subject['code']) ?></small>
                            </p>
                            <p class="card-text small text-muted"><?= htmlspecialchars($subject['description']) ?></p>
                            <div class="d-flex gap-2 mb-3 flex-wrap justify-content-center">
                                <?php if ($subject['activity_count'] > 0): ?>
                                <span class="badge <?= $subject['pending_activities'] > 0 ? 'bg-danger' : 'bg-info' ?>">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    <?= $subject['activity_count'] ?> Activities
                                    <?php if ($subject['pending_activities'] > 0): ?>
                                        <span class="badge bg-light text-dark ms-1"><?= $subject['pending_activities'] ?> pending</span>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                                <?php if ($subject['quiz_count'] > 0): ?>
                                <span class="badge <?= $subject['pending_quizzes'] > 0 ? 'bg-danger' : 'bg-warning' ?>">
                                    <i class="bi bi-card-checklist me-1"></i>
                                    <?= $subject['quiz_count'] ?> Quizzes
                                    <?php if ($subject['pending_quizzes'] > 0): ?>
                                        <span class="badge bg-light text-dark ms-1"><?= $subject['pending_quizzes'] ?> pending</span>
                                    <?php endif; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <a href="student-subject.php?id=<?= $subject['id'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Open Subject
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($subjects)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You haven't enrolled in any subjects yet. <a href="enroll.php" class="alert-link">Click here to enroll</a>.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
