<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$role = $user['role'];

// Redirect teachers to grade management page
if ($role === 'teacher') {
    header('Location: teacher-grades.php');
    exit;
}

// Fetch grades
if ($role === 'student') {
    $stmt = db()->prepare('SELECT g.*, s.name as subject_name, s.code as subject_code FROM grades g JOIN subjects s ON g.subject_id = s.id WHERE g.student_id = ? ORDER BY s.name');
    $stmt->execute([$user['id']]);
} else {
    // Admin can see all grades
    $stmt = db()->query('SELECT g.*, u.name as student_name, u.empidno, s.name as subject_name, s.code as subject_code FROM grades g JOIN users u ON g.student_id = u.id JOIN subjects s ON g.subject_id = s.id ORDER BY s.name, u.name');
}
$grades = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Grades'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2>
                        <i class="bi bi-graph-up"></i>
                        Grades
                    </h2>
                </div>
            </div>

            <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <?php if ($role !== 'student'): ?>
                                <th data-label="Student">Student</th>
                                <?php endif; ?>
                                <th data-label="Subject">Subject</th>
                                <th class="text-center" data-label="Q1">Q1</th>
                                <th class="text-center" data-label="Q2">Q2</th>
                                <th class="text-center" data-label="Q3">Q3</th>
                                <th class="text-center" data-label="Q4">Q4</th>
                                <th class="text-center" data-label="Average">Average</th>
                                <th data-label="Updated">Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grades as $grade): ?>
                            <tr>
                                <?php if ($role !== 'student'): ?>
                                <td data-label="Student">
                                    <strong><?= htmlspecialchars($grade['student_name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($grade['empidno']) ?></small>
                                </td>
                                <?php endif; ?>
                                <td data-label="Subject">
                                    <strong><?= htmlspecialchars($grade['subject_name']) ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($grade['subject_code']) ?></small>
                                </td>
                                <td class="text-center" data-label="Q1">
                                    <?php if ($grade['q1_grade'] !== null): ?>
                                        <span class="badge bg-info"><?= number_format((float)$grade['q1_grade'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" data-label="Q2">
                                    <?php if ($grade['q2_grade'] !== null): ?>
                                        <span class="badge bg-info"><?= number_format((float)$grade['q2_grade'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" data-label="Q3">
                                    <?php if ($grade['q3_grade'] !== null): ?>
                                        <span class="badge bg-info"><?= number_format((float)$grade['q3_grade'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" data-label="Q4">
                                    <?php if ($grade['q4_grade'] !== null): ?>
                                        <span class="badge bg-info"><?= number_format((float)$grade['q4_grade'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center" data-label="Average">
                                    <?php if ($grade['average_grade'] !== null): ?>
                                        <strong class="badge <?= (float)$grade['average_grade'] >= 75 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                            <?= number_format((float)$grade['average_grade'], 2) ?>
                                        </strong>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted" data-label="Updated">
                                    <?= $grade['updated_at'] ? date('M d, Y', strtotime($grade['updated_at'])) : '-' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($grades)): ?>
                            <tr>
                                <td colspan="<?= $role === 'student' ? '7' : '8' ?>" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        No grades available yet.
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
