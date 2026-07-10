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

// Check if tab_protection_enabled column exists in the database
$checkColumnStmt = db()->prepare("SHOW COLUMNS FROM quizzes LIKE 'tab_protection_enabled'");
$checkColumnStmt->execute();
$columnExists = $checkColumnStmt->fetch();

if ($columnExists) {
    // Column exists, fetch with tab_protection_enabled
    $stmt = db()->prepare('SELECT q.*, s.name as subject_name FROM quizzes q JOIN subjects s ON q.subject_id = s.id WHERE q.id = ?');
    $stmt->execute([$quizId]);
    $quiz = $stmt->fetch();
} else {
    // Column doesn't exist, fetch without it and set default value
    $stmt = db()->prepare('SELECT q.*, s.name as subject_name FROM quizzes q JOIN subjects s ON q.subject_id = s.id WHERE q.id = ?');
    $stmt->execute([$quizId]);
    $quiz = $stmt->fetch();
    $quiz['tab_protection_enabled'] = 0; // Default to disabled if column doesn't exist
}

if (!$quiz) {
    header('Location: quizzes.php');
    exit;
}

$message = '';
$error = '';

// Handle Add Multiple MCQ Questions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mcq_batch'])) {
    requireCsrf();

    $batch = $_POST['mcq'] ?? [];
    $points = (int)($_POST['batch_points'] ?? 1);

    if (!empty($batch) && $points > 0) {
        try {
            db()->beginTransaction();
            $insertStmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');

            foreach ($batch as $row) {
                $question = trim($row['question'] ?? '');
                $choices = array_values(array_filter(array_map('trim', $row['choices'] ?? [])));
                $correct = trim($row['correct'] ?? '');

                if ($question && !empty($choices) && $correct) {
                    $choicesJson = json_encode($choices);
                    $insertStmt->execute([$quizId, $question, 'mcq', $choicesJson, $correct, $points]);
                }
            }

            db()->commit();
            $message = 'MCQ questions added successfully!';
            saveAudit($user['id'], 'create_batch', 'quiz_question', 0, ['quiz_id' => $quizId, 'count' => count($batch)]);
        } catch (Exception $e) {
            db()->rollBack();
            $error = 'Failed to add MCQ batch';
        }
    } else {
        $error = 'Please add at least one question with choices and a correct answer';
    }
}

// Handle Add Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    requireCsrf();
    
    $questionText = trim($_POST['question_text'] ?? '');
    $questionType = $_POST['question_type'] ?? '';
    $points = (int)($_POST['points'] ?? 1);
    $correctAnswer = trim($_POST['correct_answer'] ?? '');
    
    if ($questionText && $questionType && in_array($questionType, ['mcq', 'truefalse', 'identification', 'essay'], true)) {
        $choicesJson = '';
        
        if ($questionType === 'mcq') {
            $choices = array_filter(array_map('trim', [
                $_POST['choice_a'] ?? '',
                $_POST['choice_b'] ?? '',
                $_POST['choice_c'] ?? '',
                $_POST['choice_d'] ?? ''
            ]));
            $choicesJson = json_encode(array_values($choices));
        } elseif ($questionType === 'truefalse') {
            $choicesJson = json_encode(['True', 'False']);
        }
        
        try {
            $stmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$quizId, $questionText, $questionType, $choicesJson, $correctAnswer, $points]);
            
            $message = 'Question added successfully!';
            saveAudit($user['id'], 'create', 'quiz_question', (int)db()->lastInsertId(), ['quiz_id' => $quizId]);
        } catch (Exception $e) {
            $error = 'Failed to add question';
        }
    } else {
        $error = 'All fields are required';
    }
}

// Handle Add Multiple MCQ Questions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mcq_batch'])) {
    requireCsrf();

    $batch = $_POST['mcq'] ?? [];
    $points = (int)($_POST['batch_points'] ?? 1);

    if (!empty($batch) && $points > 0) {
        try {
            db()->beginTransaction();
            $insertStmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');

            foreach ($batch as $row) {
                $question = trim($row['question'] ?? '');
                $choices = array_values(array_filter(array_map('trim', $row['choices'] ?? [])));
                $correct = trim($row['correct'] ?? '');

                if ($question && !empty($choices) && $correct) {
                    $choicesJson = json_encode($choices);
                    $insertStmt->execute([$quizId, $question, 'mcq', $choicesJson, $correct, $points]);
                }
            }

            db()->commit();
            $message = 'MCQ questions added successfully!';
            saveAudit($user['id'], 'create_batch', 'quiz_question', 0, ['quiz_id' => $quizId, 'count' => count($batch)]);
        } catch (Exception $e) {
            db()->rollBack();
            $error = 'Failed to add MCQ batch';
        }
    } else {
        $error = 'Please add at least one question with choices and a correct answer';
    }
}

// Handle Add Multiple True/False Questions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tf_batch'])) {
    requireCsrf();

    $batch = $_POST['tf'] ?? [];
    $points = (int)($_POST['batch_points_tf'] ?? 1);

    if (!empty($batch) && $points > 0) {
        try {
            db()->beginTransaction();
            $insertStmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');

            foreach ($batch as $row) {
                $question = trim($row['question'] ?? '');
                $correct = trim($row['correct'] ?? '');
                if ($question && ($correct === 'True' || $correct === 'False')) {
                    $choicesJson = json_encode(['True', 'False']);
                    $insertStmt->execute([$quizId, $question, 'truefalse', $choicesJson, $correct, $points]);
                }
            }

            db()->commit();
            $message = 'True/False questions added successfully!';
            saveAudit($user['id'], 'create_batch_tf', 'quiz_question', 0, ['quiz_id' => $quizId, 'count' => count($batch)]);
        } catch (Exception $e) {
            db()->rollBack();
            $error = 'Failed to add True/False batch';
        }
    } else {
        $error = 'Please add at least one True/False question';
    }
}

// Handle Add Multiple Identification Questions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_id_batch'])) {
    requireCsrf();

    $batch = $_POST['idq'] ?? [];
    $points = (int)($_POST['batch_points_id'] ?? 1);

    if (!empty($batch) && $points > 0) {
        try {
            db()->beginTransaction();
            $insertStmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');

            foreach ($batch as $row) {
                $question = trim($row['question'] ?? '');
                $correct = trim($row['correct'] ?? '');
                if ($question && $correct) {
                    $insertStmt->execute([$quizId, $question, 'identification', '', $correct, $points]);
                }
            }

            db()->commit();
            $message = 'Identification questions added successfully!';
            saveAudit($user['id'], 'create_batch_id', 'quiz_question', 0, ['quiz_id' => $quizId, 'count' => count($batch)]);
        } catch (Exception $e) {
            db()->rollBack();
            $error = 'Failed to add Identification batch';
        }
    } else {
        $error = 'Please add at least one Identification question with answer';
    }
}

// Handle Add Multiple Essay Questions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_essay_batch'])) {
    requireCsrf();

    $batch = $_POST['essay'] ?? [];
    $points = (int)($_POST['batch_points_essay'] ?? 1);

    if (!empty($batch) && $points > 0) {
        try {
            db()->beginTransaction();
            $insertStmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');

            foreach ($batch as $row) {
                $question = trim($row['question'] ?? '');
                if ($question) {
                    $insertStmt->execute([$quizId, $question, 'essay', '', '', $points]);
                }
            }

            db()->commit();
            $message = 'Essay questions added successfully!';
            saveAudit($user['id'], 'create_batch_essay', 'quiz_question', 0, ['quiz_id' => $quizId, 'count' => count($batch)]);
        } catch (Exception $e) {
            db()->rollBack();
            $error = 'Failed to add Essay batch';
        }
    } else {
        $error = 'Please add at least one Essay question';
    }
}

// Handle Edit Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_question'])) {
    requireCsrf();
    
    $questionId = (int)($_POST['question_id'] ?? 0);
    $questionText = trim($_POST['question_text'] ?? '');
    $questionType = $_POST['question_type'] ?? '';
    $points = (int)($_POST['points'] ?? 1);
    $correctAnswer = trim($_POST['correct_answer'] ?? '');
    
    if ($questionId && $questionText && $questionType && in_array($questionType, ['mcq', 'truefalse', 'identification', 'essay'], true)) {
        $choicesJson = '';
        
        if ($questionType === 'mcq') {
            $choices = array_filter(array_map('trim', [
                $_POST['edit_choice_a'] ?? '',
                $_POST['edit_choice_b'] ?? '',
                $_POST['edit_choice_c'] ?? '',
                $_POST['edit_choice_d'] ?? ''
            ]));
            $choicesJson = json_encode(array_values($choices));
        } elseif ($questionType === 'truefalse') {
            $choicesJson = json_encode(['True', 'False']);
        }
        
        try {
            $stmt = db()->prepare('UPDATE quiz_questions SET question_text = ?, question_type = ?, choices_json = ?, correct_answer = ?, points = ? WHERE id = ? AND quiz_id = ?');
            $stmt->execute([$questionText, $questionType, $choicesJson, $correctAnswer, $points, $questionId, $quizId]);
            
            $message = 'Question updated successfully!';
            saveAudit($user['id'], 'update', 'quiz_question', $questionId, ['quiz_id' => $quizId]);
        } catch (Exception $e) {
            $error = 'Failed to update question';
        }
    } else {
        $error = 'All fields are required';
    }
}

// Handle Delete Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_question'])) {
    requireCsrf();
    
    $questionId = (int)($_POST['question_id'] ?? 0);
    
    if ($questionId) {
        $stmt = db()->prepare('DELETE FROM quiz_questions WHERE id = ? AND quiz_id = ?');
        if ($stmt->execute([$questionId, $quizId])) {
            $message = 'Question deleted successfully!';
            saveAudit($user['id'], 'delete', 'quiz_question', $questionId, ['quiz_id' => $quizId]);
        }
    }
}

// Handle Update Quiz Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quiz_settings'])) {
    requireCsrf();
    
    $title = trim($_POST['title'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $timeLimit = (int)($_POST['time_limit_minutes'] ?? 0);
    $maxScore = (int)($_POST['max_score'] ?? 100);
    $tabProtection = (int)($_POST['tab_protection'] ?? 0);
    
    if ($title) {
        try {
            // Check if tab_protection_enabled column exists in the database
            $checkColumnStmt = db()->prepare("SHOW COLUMNS FROM quizzes LIKE 'tab_protection_enabled'");
            $checkColumnStmt->execute();
            $columnExists = $checkColumnStmt->fetch();
            
            if ($columnExists) {
                // Column exists, use the full query with tab_protection_enabled
                $stmt = db()->prepare('UPDATE quizzes SET title = ?, instructions = ?, time_limit_minutes = ?, max_score = ?, tab_protection_enabled = ? WHERE id = ?');
                $stmt->execute([$title, $instructions, $timeLimit, $maxScore, $tabProtection, $quizId]);
            } else {
                // Column doesn't exist, use query without tab_protection_enabled
                $stmt = db()->prepare('UPDATE quizzes SET title = ?, instructions = ?, time_limit_minutes = ?, max_score = ? WHERE id = ?');
                $stmt->execute([$title, $instructions, $timeLimit, $maxScore, $quizId]);
            }
            
            // Refresh quiz data
            $stmt = db()->prepare('SELECT q.*, s.name as subject_name FROM quizzes q JOIN subjects s ON q.subject_id = s.id WHERE q.id = ?');
            $stmt->execute([$quizId]);
            $quiz = $stmt->fetch();
            
            $message = 'Quiz settings updated successfully!';
            saveAudit($user['id'], 'update', 'quiz', $quizId, compact('title', 'instructions', 'timeLimit', 'maxScore', 'tabProtection'));
        } catch (Exception $e) {
            $error = 'Failed to update quiz settings: ' . $e->getMessage();
        }
    } else {
        $error = 'Title is required';
    }
}

// Fetch questions
$stmt = db()->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC');
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();

$totalPoints = array_sum(array_column($questions, 'points'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Builder - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Quiz Builder'; include __DIR__ . '/includes/topbar.php'; ?>
        
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
                            • <?= count($questions) ?> Questions
                            • Total Points: <?= $totalPoints ?> / <?= $quiz['max_score'] ?>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addQuestionModal">
                            <i class="bi bi-plus-circle me-2"></i> Add Question
                        </button>
                        <a href="quizzes.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quiz Settings Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Quiz Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label">Quiz Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="max_score" class="form-label">Maximum Score</label>
                                <input type="number" class="form-control" id="max_score" name="max_score" value="<?= $quiz['max_score'] ?>" min="1" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="time_limit_minutes" class="form-label">Time Limit (minutes)</label>
                                <input type="number" class="form-control" id="time_limit_minutes" name="time_limit_minutes" value="<?= $quiz['time_limit_minutes'] ?>" min="0">
                                <small class="text-muted">0 = No time limit</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tab_protection" class="form-label">Tab Protection</label>
                                <select class="form-select" id="tab_protection" name="tab_protection">
                                    <option value="0" <?= (!isset($quiz['tab_protection_enabled']) || $quiz['tab_protection_enabled'] == 0) ? 'selected' : '' ?>>Disabled</option>
                                    <option value="1" <?= (isset($quiz['tab_protection_enabled']) && $quiz['tab_protection_enabled'] == 1) ? 'selected' : '' ?>>Enabled</option>
                                </select>
                                <small class="text-muted">Prevents students from switching tabs or closing the browser during the quiz</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3"><?= htmlspecialchars($quiz['instructions']) ?></textarea>
                        </div>
                        <button type="submit" name="update_quiz_settings" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update Settings
                        </button>
                    </form>
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

            <?php if (empty($questions)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    No questions added yet. Click "Add Question" to get started.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php $qNum = 1; foreach ($questions as $q): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-2">
                                            Question <?= $qNum++ ?>
                                            <span class="badge bg-secondary"><?= ucfirst($q['question_type']) ?></span>
                                            <span class="badge bg-info"><?= $q['points'] ?> pt<?= $q['points'] > 1 ? 's' : '' ?></span>
                                        </h5>
                                        <p class="mb-3"><?= nl2br(htmlspecialchars($q['question_text'])) ?></p>
                                        
                                        <?php if ($q['question_type'] === 'mcq'): ?>
                                            <?php $choices = json_decode($q['choices_json'], true); ?>
                                            <div class="ms-4">
                                                <?php foreach ($choices as $idx => $choice): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" disabled <?= $choice === $q['correct_answer'] ? 'checked' : '' ?>>
                                                        <label class="form-check-label <?= $choice === $q['correct_answer'] ? 'text-success fw-bold' : '' ?>">
                                                            <?= htmlspecialchars($choice) ?>
                                                            <?= $choice === $q['correct_answer'] ? '✓' : '' ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif ($q['question_type'] === 'truefalse'): ?>
                                            <p class="text-success"><strong>Answer: <?= htmlspecialchars($q['correct_answer']) ?></strong></p>
                                        <?php elseif ($q['question_type'] === 'identification'): ?>
                                            <p class="text-success"><strong>Answer: <?= htmlspecialchars($q['correct_answer']) ?></strong></p>
                                        <?php elseif ($q['question_type'] === 'essay'): ?>
                                            <p class="text-muted"><em>Essay question (manual grading required)</em></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="fillEditModal(<?= $q['id'] ?>, '<?= addslashes(htmlspecialchars($q['question_text'], ENT_QUOTES)) ?>', '<?= $q['question_type'] ?>', <?= $q['points'] ?>, '<?= addslashes(htmlspecialchars($q['correct_answer'], ENT_QUOTES)) ?>', <?= $q['choices_json'] ? json_encode(json_decode($q['choices_json'], true)) : 'null' ?>)" 
                                                data-bs-toggle="modal" data-bs-target="#editQuestionModal">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                                            <button type="submit" name="delete_question" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Delete this question?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Question Modal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="addQuestionForm">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="question_type" class="form-label">Question Type</label>
                                <select class="form-select" id="question_type" name="question_type" required onchange="toggleQuestionFields()">
                                    <option value="">Select Type</option>
                                    <option value="mcq">Multiple Choice</option>
                                    <option value="truefalse">True/False</option>
                                    <option value="identification">Identification</option>
                                    <option value="essay">Essay</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="points" class="form-label">Points</label>
                                <input type="number" class="form-control" id="points" name="points" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="question_text" class="form-label">Question</label>
                            <textarea class="form-control" id="question_text" name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <!-- MCQ Options -->
                        <div id="mcq_fields" style="display:none;">
                            <label class="form-label">Answer Choices</label>
                            <div class="mb-2">
                                <input type="text" class="form-control" name="choice_a" placeholder="Choice A">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" name="choice_b" placeholder="Choice B">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" name="choice_c" placeholder="Choice C">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="choice_d" placeholder="Choice D">
                            </div>
                        </div>
                        
                        <!-- True/False Options -->
                        <div id="tf_fields" style="display:none;">
                            <div class="mb-3">
                                <label for="correct_answer_tf" class="form-label">Correct Answer</label>
                                <select class="form-select" id="correct_answer_tf" name="correct_answer">
                                    <option value="">Select Answer</option>
                                    <option value="True">True</option>
                                    <option value="False">False</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Identification/MCQ Answer -->
                        <div id="id_fields" style="display:none;">
                            <div class="mb-3">
                                <label for="correct_answer_id" class="form-label">Correct Answer</label>
                                <input type="text" class="form-control" id="correct_answer_id" name="correct_answer">
                            </div>
                        </div>
                        
                        <!-- Essay (no answer needed) -->
                        <div id="essay_fields" style="display:none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Essay questions require manual grading.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_question" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Add Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Question Modal -->
    <div class="modal fade" id="editQuestionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="editQuestionForm">
                    <?= csrfField() ?>
                    <input type="hidden" name="question_id" id="edit_question_id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="edit_question_type" class="form-label">Question Type</label>
                                <select class="form-select" id="edit_question_type" name="question_type" required onchange="toggleEditQuestionFields()">
                                    <option value="">Select Type</option>
                                    <option value="mcq">Multiple Choice</option>
                                    <option value="truefalse">True/False</option>
                                    <option value="identification">Identification</option>
                                    <option value="essay">Essay</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_points" class="form-label">Points</label>
                                <input type="number" class="form-control" id="edit_points" name="points" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_question_text" class="form-label">Question</label>
                            <textarea class="form-control" id="edit_question_text" name="question_text" rows="3" required></textarea>
                        </div>
                        
                        <!-- Edit MCQ Options -->
                        <div id="edit_mcq_fields" style="display:none;">
                            <label class="form-label">Answer Choices</label>
                            <div class="mb-2">
                                <input type="text" class="form-control" name="edit_choice_a" id="edit_choice_a" placeholder="Choice A">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" name="edit_choice_b" id="edit_choice_b" placeholder="Choice B">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control" name="edit_choice_c" id="edit_choice_c" placeholder="Choice C">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="edit_choice_d" id="edit_choice_d" placeholder="Choice D">
                            </div>
                        </div>
                        
                        <!-- Edit True/False Options -->
                        <div id="edit_tf_fields" style="display:none;">
                            <div class="mb-3">
                                <label for="edit_correct_answer_tf" class="form-label">Correct Answer</label>
                                <select class="form-select" id="edit_correct_answer_tf" name="correct_answer">
                                    <option value="">Select Answer</option>
                                    <option value="True">True</option>
                                    <option value="False">False</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Edit Identification/MCQ Answer -->
                        <div id="edit_id_fields" style="display:none;">
                            <div class="mb-3">
                                <label for="edit_correct_answer_id" class="form-label">Correct Answer</label>
                                <input type="text" class="form-control" id="edit_correct_answer_id" name="correct_answer">
                            </div>
                        </div>
                        
                        <!-- Edit Essay (no answer needed) -->
                        <div id="edit_essay_fields" style="display:none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Essay questions require manual grading.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_question" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Update Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Multiple MCQ Modal -->
    <div class="modal fade" id="addMcqBatchModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Multiple MCQ Questions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="addMcqBatchForm">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Default Points per Question</label>
                            <input type="number" class="form-control" name="batch_points" value="1" min="1" required>
                        </div>
                        <div id="mcqBatchContainer">
                            <!-- Question rows will be inserted here -->
                        </div>
                        <button type="button" class="btn btn-outline-primary" onclick="addMcqRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Question Row
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_mcq_batch" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Multiple True/False Modal -->
    <div class="modal fade" id="addTfBatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Multiple True/False</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="addTfBatchForm">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Default Points per Question</label>
                            <input type="number" class="form-control" name="batch_points_tf" value="1" min="1" required>
                        </div>
                        <div id="tfBatchContainer"></div>
                        <button type="button" class="btn btn-outline-primary" onclick="addTfRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Question Row
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_tf_batch" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Multiple Identification Modal -->
    <div class="modal fade" id="addIdBatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Multiple Identification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="addIdBatchForm">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Default Points per Question</label>
                            <input type="number" class="form-control" name="batch_points_id" value="1" min="1" required>
                        </div>
                        <div id="idBatchContainer"></div>
                        <button type="button" class="btn btn-outline-primary" onclick="addIdRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Question Row
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_id_batch" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Multiple Essay Modal -->
    <div class="modal fade" id="addEssayBatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add Multiple Essay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="addEssayBatchForm">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Default Points per Question</label>
                            <input type="number" class="form-control" name="batch_points_essay" value="1" min="1" required>
                        </div>
                        <div id="essayBatchContainer"></div>
                        <button type="button" class="btn btn-outline-primary" onclick="addEssayRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Question Row
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_essay_batch" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Save All
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function mcqRowTemplate(index) {
            return `
            <div class="card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Question #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMcqRow(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="mcq[${index}][question]" rows="2" required></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" class="form-control mb-2" name="mcq[${index}][choices][]" placeholder="Choice A" required>
                            <input type="text" class="form-control mb-2" name="mcq[${index}][choices][]" placeholder="Choice B" required>
                            <input type="text" class="form-control mb-2" name="mcq[${index}][choices][]" placeholder="Choice C">
                            <input type="text" class="form-control" name="mcq[${index}][choices][]" placeholder="Choice D">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correct Answer</label>
                            <input type="text" class="form-control" name="mcq[${index}][correct]" placeholder="Enter exact matching choice" required>
                            <small class="text-muted">Must match one of the choices exactly.</small>
                        </div>
                    </div>
                </div>
            </div>`;
        }

        let mcqIndex = 0;
        function addMcqRow() {
            const container = document.getElementById('mcqBatchContainer');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = mcqRowTemplate(mcqIndex);
            container.appendChild(wrapper.firstElementChild);
            mcqIndex++;
        }
        function removeMcqRow(index) {
            const container = document.getElementById('mcqBatchContainer');
            const cards = container.querySelectorAll('.card');
            cards.forEach(card => {
                if (parseInt(card.getAttribute('data-index')) === index) {
                    card.remove();
                }
            });
        }
        // Initialize with one row when modal opens
        document.getElementById('addMcqBatchModal').addEventListener('shown.bs.modal', () => {
            const container = document.getElementById('mcqBatchContainer');
            if (container.children.length === 0) addMcqRow();
        });

        function tfRowTemplate(index) {
            return `
            <div class="card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Question #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTfRow(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="tf[${index}][question]" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <select class="form-select" name="tf[${index}][correct]" required>
                            <option value="True">True</option>
                            <option value="False">False</option>
                        </select>
                    </div>
                </div>
            </div>`;
        }
        let tfIndex = 0;
        function addTfRow() {
            const container = document.getElementById('tfBatchContainer');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = tfRowTemplate(tfIndex);
            container.appendChild(wrapper.firstElementChild);
            tfIndex++;
        }
        function removeTfRow(index) {
            const container = document.getElementById('tfBatchContainer');
            const cards = container.querySelectorAll('.card');
            cards.forEach(card => { if (parseInt(card.getAttribute('data-index')) === index) card.remove(); });
        }
        document.getElementById('addTfBatchModal').addEventListener('shown.bs.modal', () => {
            const container = document.getElementById('tfBatchContainer');
            if (container.children.length === 0) addTfRow();
        });

        function idRowTemplate(index) {
            return `
            <div class="card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Question #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIdRow(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="idq[${index}][question]" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <input type="text" class="form-control" name="idq[${index}][correct]" required>
                    </div>
                </div>
            </div>`;
        }
        let idIndex = 0;
        function addIdRow() {
            const container = document.getElementById('idBatchContainer');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = idRowTemplate(idIndex);
            container.appendChild(wrapper.firstElementChild);
            idIndex++;
        }
        function removeIdRow(index) {
            const container = document.getElementById('idBatchContainer');
            const cards = container.querySelectorAll('.card');
            cards.forEach(card => { if (parseInt(card.getAttribute('data-index')) === index) card.remove(); });
        }
        document.getElementById('addIdBatchModal').addEventListener('shown.bs.modal', () => {
            const container = document.getElementById('idBatchContainer');
            if (container.children.length === 0) addIdRow();
        });

        function essayRowTemplate(index) {
            return `
            <div class="card mb-3" data-index="${index}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Question #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEssayRow(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" name="essay[${index}][question]" rows="3" required></textarea>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i> No correct answer; essays require manual grading.
                    </div>
                </div>
            </div>`;
        }
        let essayIndex = 0;
        function addEssayRow() {
            const container = document.getElementById('essayBatchContainer');
            const wrapper = document.createElement('div');
            wrapper.innerHTML = essayRowTemplate(essayIndex);
            container.appendChild(wrapper.firstElementChild);
            essayIndex++;
        }
        function removeEssayRow(index) {
            const container = document.getElementById('essayBatchContainer');
            const cards = container.querySelectorAll('.card');
            cards.forEach(card => { if (parseInt(card.getAttribute('data-index')) === index) card.remove(); });
        }
        document.getElementById('addEssayBatchModal').addEventListener('shown.bs.modal', () => {
            const container = document.getElementById('essayBatchContainer');
            if (container.children.length === 0) addEssayRow();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleQuestionFields() {
            const type = document.getElementById('question_type').value;
            const mcq = document.getElementById('mcq_fields');
            const tf = document.getElementById('tf_fields');
            const idf = document.getElementById('id_fields');
            const essay = document.getElementById('essay_fields');
            
            mcq.style.display = 'none';
            tf.style.display = 'none';
            idf.style.display = 'none';
            essay.style.display = 'none';
            
            // Clear all correct answer fields
            document.getElementById('correct_answer_tf').value = '';
            document.getElementById('correct_answer_id').value = '';
            
            if (type === 'mcq') {
                mcq.style.display = 'block';
                idf.style.display = 'block';
                const idAns = document.getElementById('correct_answer_id');
                if (idAns) idAns.setAttribute('placeholder', 'Enter the correct choice exactly as written above');
            } else if (type === 'truefalse') {
                tf.style.display = 'block';
            } else if (type === 'identification') {
                idf.style.display = 'block';
                const idAns = document.getElementById('correct_answer_id');
                if (idAns) idAns.setAttribute('placeholder', 'Enter the correct answer');
            } else if (type === 'essay') {
                essay.style.display = 'block';
            }
        }
        
        function toggleEditQuestionFields() {
            const type = document.getElementById('edit_question_type').value;
            const mcq = document.getElementById('edit_mcq_fields');
            const tf = document.getElementById('edit_tf_fields');
            const idf = document.getElementById('edit_id_fields');
            const essay = document.getElementById('edit_essay_fields');
            
            mcq.style.display = 'none';
            tf.style.display = 'none';
            idf.style.display = 'none';
            essay.style.display = 'none';
            
            // Clear all correct answer fields
            document.getElementById('edit_correct_answer_tf').value = '';
            document.getElementById('edit_correct_answer_id').value = '';
            
            if (type === 'mcq') {
                mcq.style.display = 'block';
                idf.style.display = 'block';
                const idAns = document.getElementById('edit_correct_answer_id');
                if (idAns) idAns.setAttribute('placeholder', 'Enter the correct choice exactly as written above');
            } else if (type === 'truefalse') {
                tf.style.display = 'block';
            } else if (type === 'identification') {
                idf.style.display = 'block';
                const idAns = document.getElementById('edit_correct_answer_id');
                if (idAns) idAns.setAttribute('placeholder', 'Enter the correct answer');
            } else if (type === 'essay') {
                essay.style.display = 'block';
            }
        }
        
        function fillEditModal(id, questionText, questionType, points, correctAnswer, choices) {
            document.getElementById('edit_question_id').value = id;
            document.getElementById('edit_question_text').value = questionText;
            document.getElementById('edit_question_type').value = questionType;
            document.getElementById('edit_points').value = points;
            
            // Reset all fields first
            document.getElementById('edit_choice_a').value = '';
            document.getElementById('edit_choice_b').value = '';
            document.getElementById('edit_choice_c').value = '';
            document.getElementById('edit_choice_d').value = '';
            document.getElementById('edit_correct_answer_id').value = correctAnswer;
            document.getElementById('edit_correct_answer_tf').value = correctAnswer;
            
            // Handle choices for MCQ
            if (questionType === 'mcq' && choices && Array.isArray(choices)) {
                if (choices[0] !== undefined) document.getElementById('edit_choice_a').value = choices[0];
                if (choices[1] !== undefined) document.getElementById('edit_choice_b').value = choices[1];
                if (choices[2] !== undefined) document.getElementById('edit_choice_c').value = choices[2];
                if (choices[3] !== undefined) document.getElementById('edit_choice_d').value = choices[3];
            }
            
            // Set correct answer based on question type
            if (questionType === 'truefalse') {
                document.getElementById('edit_correct_answer_tf').value = correctAnswer;
            } else if (questionType === 'identification' || questionType === 'mcq') {
                document.getElementById('edit_correct_answer_id').value = correctAnswer;
            }
            
            // Trigger the field toggle to show appropriate fields
            toggleEditQuestionFields();
        }
    </script>
</body>
</html>
