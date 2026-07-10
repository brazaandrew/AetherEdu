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

$user = requireTeacher();
$quizId = (int)($_GET['id'] ?? 0);

$message = '';
$error = '';

// Handle Delete Quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_quiz'])) {
    requireCsrf();
    
    $quizId = (int)($_POST['quiz_id'] ?? 0);
    
    if ($quizId) {
        try {
            // Verify ownership for teachers
            if ($user['role'] === 'teacher') {
                $stmt = db()->prepare('SELECT q.id FROM quizzes q JOIN subjects s ON q.subject_id = s.id JOIN folder_teacher ft ON s.id = ft.subject_id WHERE q.id = ? AND ft.teacher_empidno = ?');
                $stmt->execute([$quizId, $user['empidno']]);
                if (!$stmt->fetch()) {
                    $error = 'You do not have permission to delete this quiz';
                    goto skip_delete;
                }
            }
            
            db()->beginTransaction();
            
            // Get all attempt IDs for this quiz
            $stmt = db()->prepare('SELECT id FROM quiz_attempts WHERE quiz_id = ?');
            $stmt->execute([$quizId]);
            $attemptIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Delete quiz answers for these attempts
            if (!empty($attemptIds)) {
                $placeholders = implode(',', array_fill(0, count($attemptIds), '?'));
                $stmt = db()->prepare("DELETE FROM quiz_answers WHERE attempt_id IN ($placeholders)");
                $stmt->execute($attemptIds);
            }
            
            // Delete quiz attempts
            $stmt = db()->prepare('DELETE FROM quiz_attempts WHERE quiz_id = ?');
            $stmt->execute([$quizId]);
            
            // Delete quiz questions
            $stmt = db()->prepare('DELETE FROM quiz_questions WHERE quiz_id = ?');
            $stmt->execute([$quizId]);
            
            // Delete the quiz itself
            $stmt = db()->prepare('DELETE FROM quizzes WHERE id = ?');
            $stmt->execute([$quizId]);
            
            db()->commit();
            
            saveAudit($user['id'], 'delete', 'quiz', $quizId, []);
            
            $_SESSION['success_message'] = 'Quiz deleted successfully!';
            header('Location: quizzes.php');
            exit;
        } catch (Exception $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $error = 'Failed to delete quiz';
        }
    }
    skip_delete:
}

// Get success message from session if exists
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Check if redirected after deletion
if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $message = 'Quiz deleted successfully!';
}

// Handle Create Quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quiz'])) {
    requireCsrf();
    
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $timeLimit = (int)($_POST['time_limit_minutes'] ?? 0);
    $maxScore = (int)($_POST['max_score'] ?? 100);
    
    if ($subjectId && $title) {
        try {
            $tabProtection = (int)($_POST['tab_protection'] ?? 0);
            
            // Check if tab_protection_enabled column exists in the database
            $checkColumnStmt = db()->prepare("SHOW COLUMNS FROM quizzes LIKE 'tab_protection_enabled'");
            $checkColumnStmt->execute();
            $columnExists = $checkColumnStmt->fetch();
            
            if ($columnExists) {
                // Column exists, use the full query with tab_protection_enabled
                $stmt = db()->prepare('INSERT INTO quizzes (subject_id, title, instructions, time_limit_minutes, max_score, tab_protection_enabled, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$subjectId, $title, $instructions, $timeLimit, $maxScore, $tabProtection, $user['id']]);
            } else {
                // Column doesn't exist, use query without tab_protection_enabled
                $stmt = db()->prepare('INSERT INTO quizzes (subject_id, title, instructions, time_limit_minutes, max_score, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$subjectId, $title, $instructions, $timeLimit, $maxScore, $user['id']]);
            }
            
            $newQuizId = (int)db()->lastInsertId();
            $message = 'Quiz created successfully!';
            saveAudit($user['id'], 'create', 'quiz', $newQuizId, compact('subjectId', 'title'));

            // Notify enrolled students
            $subjectStmt = db()->prepare('SELECT name FROM subjects WHERE id = ?');
            $subjectStmt->execute([$subjectId]);
            $subjectName = $subjectStmt->fetchColumn() ?: 'Unknown Subject';
            NotificationService::notifyNewQuiz($newQuizId, $title, $subjectId, $subjectName, $timeLimit);

            header('Location: quiz-builder.php?id=' . $newQuizId);
            exit;
        } catch (Exception $e) {
            $error = 'Failed to create quiz: ' . $e->getMessage();
        }
    } else {
        $error = 'Subject and title are required';
    }
}

// Fetch subjects for dropdown
if ($user['role'] === 'teacher') {
    $stmt = db()->prepare('SELECT DISTINCT s.id, s.code, s.name FROM subjects s JOIN folder_teacher ft ON s.id = ft.subject_id WHERE ft.teacher_empidno = ? AND s.archived = 0 ORDER BY s.name');
    $stmt->execute([$user['empidno']]);
    $teacherSubjects = $stmt->fetchAll();
} else { // admin
    $stmt = db()->query('SELECT id, code, name FROM subjects WHERE archived = 0 ORDER BY name');
    $teacherSubjects = $stmt->fetchAll();
}
// Fetch quizzes
if ($user['role'] === 'teacher') {
    $stmt = db()->prepare('SELECT q.*, s.name as subject_name, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as question_count FROM quizzes q JOIN subjects s ON q.subject_id = s.id JOIN folder_teacher ft ON s.id = ft.subject_id WHERE ft.teacher_empidno = ? ORDER BY q.created_at DESC');
    $stmt->execute([$user['empidno']]);
} else {
    $stmt = db()->query('SELECT q.*, s.name as subject_name, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as question_count FROM quizzes q JOIN subjects s ON q.subject_id = s.id ORDER BY q.created_at DESC');
}
$quizzes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Quizzes'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h2>
                        <i class="bi bi-card-checklist"></i>
                        Quizzes
                    </h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                        <i class="bi bi-plus-circle me-2"></i> Create Quiz
                    </button>
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
                <?php foreach ($quizzes as $quiz): ?>
                <div class="col-lg-6 col-md-12 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($quiz['title']) ?></h5>
                                    <span class="badge bg-primary"><?= htmlspecialchars($quiz['subject_name']) ?></span>
                                </div>
                                <div class="text-start text-md-end">
                                    <span class="badge bg-info"><?= $quiz['question_count'] ?> Questions</span>
                                    <br>
                                    <span class="badge bg-success mt-1"><?= $quiz['max_score'] ?> pts</span>
                                </div>
                            </div>
                            <p class="text-muted mb-3"><?= htmlspecialchars($quiz['instructions']) ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= $quiz['time_limit_minutes'] > 0 ? $quiz['time_limit_minutes'] . ' mins' : 'No time limit' ?>
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <a href="quiz-builder.php?id=<?= $quiz['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="quiz-submissions.php?id=<?= $quiz['id'] ?>" class="btn btn-outline-success">
                                        <i class="bi bi-people"></i> Submissions
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $quiz['id'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($quizzes)): ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No quizzes created yet.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create Quiz Modal -->
    <div class="modal fade" id="createQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Create New Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                            <?php if (empty($teacherSubjects)): ?>
                                <?php if ($user['role'] === 'teacher'): ?>
                                    <div class="alert alert-warning mb-2">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No subjects assigned to you. Please contact the administrator to assign subjects.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-2">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        No subjects available. Please create a subject first.
                                    </div>
                                <?php endif; ?>
                                <select class="form-select" id="subject_id" name="subject_id" required disabled>
                                    <option value="">No subjects available</option>
                                </select>
                            <?php else: ?>
                                <select class="form-select" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($teacherSubjects as $subj): ?>
                                    <option value="<?= $subj['id'] ?>">
                                        <?= htmlspecialchars($subj['name']) ?> (<?= htmlspecialchars($subj['code']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="title" class="form-label">Quiz Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="time_limit_minutes" class="form-label">Time Limit (minutes)</label>
                                <input type="number" class="form-control" id="time_limit_minutes" name="time_limit_minutes" min="0" value="0">
                                <small class="text-muted">0 = No time limit</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_score" class="form-label">Maximum Score</label>
                                <input type="number" class="form-control" id="max_score" name="max_score" value="100" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tab_protection" class="form-label">Tab Protection</label>
                            <select class="form-select" id="tab_protection" name="tab_protection">
                                <option value="0" selected>Disabled</option>
                                <option value="1">Enabled</option>
                            </select>
                            <small class="text-muted">Prevents students from switching tabs or closing the browser during the quiz</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_quiz" class="btn btn-primary" <?= empty($teacherSubjects) ? 'disabled' : '' ?>>
                            <i class="bi bi-arrow-right me-2"></i>Create & Add Questions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modals -->
    <?php foreach ($quizzes as $quiz): ?>
    <div class="modal fade" id="deleteModal<?= $quiz['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Delete Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this quiz?</p>
                    <div class="alert alert-warning">
                        <strong><?= htmlspecialchars($quiz['title']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($quiz['subject_name']) ?></small>
                    </div>
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <strong>Warning:</strong> This will delete all questions, student attempts, and submissions. This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                        <button type="submit" name="delete_quiz" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete Quiz
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
