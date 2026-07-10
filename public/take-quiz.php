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

$user = requireStudent();
$quizId = (int)($_GET['id'] ?? 0);

if (!$quizId) {
    header('Location: my-subjects.php');
    exit;
}

// Fetch quiz details
$stmt = db()->prepare('SELECT q.*, s.name as subject_name FROM quizzes q JOIN subjects s ON q.subject_id = s.id WHERE q.id = ?');
$stmt->execute([$quizId]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: my-subjects.php');
    exit;
}

// Check if tab_protection_enabled column exists in the result (for backward compatibility)
$checkColumnStmt = db()->prepare("SHOW COLUMNS FROM quizzes LIKE 'tab_protection_enabled'");
$checkColumnStmt->execute();
$columnExists = $checkColumnStmt->fetch();

if (!$columnExists || !array_key_exists('tab_protection_enabled', $quiz)) {
    $quiz['tab_protection_enabled'] = 0; // Default to disabled if column doesn't exist
}

// Check if already taken
$stmt = db()->prepare('SELECT id FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?');
$stmt->execute([$quizId, $user['id']]);
if ($stmt->fetch()) {
    header('Location: student-subject.php?id=' . $quiz['subject_id']);
    exit;
}

$message = '';
$error = '';

// Handle quiz submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    requireCsrf();
    
    try {
        db()->beginTransaction();
        
        // Create quiz attempt
        $stmt = db()->prepare('INSERT INTO quiz_attempts (quiz_id, student_id, submitted_at) VALUES (?, ?, NOW())');
        $stmt->execute([$quizId, $user['id']]);
        $attemptId = (int)db()->lastInsertId();
        
        // Fetch questions
        $stmt = db()->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ?');
        $stmt->execute([$quizId]);
        $questions = $stmt->fetchAll();
        
        // Save student answers
        foreach ($questions as $question) {
            $questionId = $question['id'];
            $studentAnswer = trim($_POST["answer_{$questionId}"] ?? '');
            
            // Insert answer (auto-grading will be done by GradeService)
            $stmt = db()->prepare('INSERT INTO quiz_answers (attempt_id, question_id, student_answer) VALUES (?, ?, ?)');
            $stmt->execute([$attemptId, $questionId, $studentAnswer]);
        }
        
        db()->commit();
        
        // Auto-grade the attempt using GradeService
        $gradeResult = GradeService::grade_quiz_attempt(db(), $attemptId);
        
        saveAudit($user['id'], 'submit', 'quiz_attempt', $attemptId, [
            'quiz_id' => $quizId, 
            'auto_score' => $gradeResult['auto_score'],
            'needs_manual' => $gradeResult['needs_manual']
        ]);
        
        // Recompute student grade for this subject
        GradeService::recomputeStudentSubjectGrade(db(), $user['id'], $quiz['subject_id']);
        
        $_SESSION['quiz_result'] = [
            'score' => $gradeResult['auto_score'],
            'max_score' => $quiz['max_score'],
            'subject_id' => $quiz['subject_id'],
            'needs_manual_grading' => $gradeResult['needs_manual'] > 0
        ];
        
        header('Location: quiz-result.php');
        exit;
        
    } catch (Exception $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        $error = 'Failed to submit quiz: ' . $e->getMessage();
    }
}

// Fetch questions
$stmt = db()->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id');
$stmt->execute([$quizId]);
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <link rel="stylesheet" href="assets/css/gamification.css?v=1">
    <style>
        .timer-box {
            position: fixed;
            top: 80px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
        }
        .timer-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4f46e5;
        }
        .timer-text.warning {
            color: #f59e0b;
        }
        .timer-text.danger {
            color: #ef4444;
        }
        .question-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .question-nav-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        .question-nav-btn.answered {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        .question-nav-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }
        
        @media (max-width: 768px) {
            .timer-box {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                width: 100%;
                text-align: center;
            }
            
            .question-nav {
                justify-content: center;
            }
            
            .question-nav-btn {
                width: 35px;
                height: 35px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Take Quiz'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <?php if ($quiz['time_limit_minutes'] > 0): ?>
        <div class="timer-box" id="timerBox">
            <div class="text-center">
                <small class="text-muted d-block">Time Remaining</small>
                <div class="timer-text" id="timerText"><?= $quiz['time_limit_minutes'] ?>:00</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quiz Progress Bar -->
        <div class="quiz-progress-header">
            <div class="container-fluid">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="quiz-q-counter">Question <span id="qCurrentNum">0</span> / <span><?= count($questions) ?></span> answered</div>
                    <div class="quiz-q-counter"><span id="qPctLabel">0</span>% complete</div>
                </div>
                <div class="quiz-progress-bar-track">
                    <div class="quiz-progress-bar-fill" id="quizProgressFill" style="width:0%"></div>
                </div>
            </div>
        </div>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="alert alert-warning">
                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Quiz Instructions</h5>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($quiz['instructions'])) ?></p>
                    <hr>
                    <p class="mb-0">
                        <strong>Total Questions:</strong> <?= count($questions) ?> •
                        <strong>Total Points:</strong> <?= $quiz['max_score'] ?>
                        <?php if ($quiz['time_limit_minutes'] > 0): ?>
                            • <strong>Time Limit:</strong> <?= $quiz['time_limit_minutes'] ?> minutes
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="quizForm">
                <?= csrfField() ?>
                
                <?php $qNum = 1; foreach ($questions as $question): ?>
                <div class="card mb-4 question-card" id="question_<?= $qNum ?>">
                    <div class="card-body">
                        <h5 class="mb-3">
                            Question <?= $qNum ?>
                            <span class="badge bg-info float-end"><?= $question['points'] ?> point<?= $question['points'] > 1 ? 's' : '' ?></span>
                        </h5>
                        <p class="lead"><?= nl2br(htmlspecialchars($question['question_text'])) ?></p>
                        
                        <?php if ($question['question_type'] === 'mcq'): ?>
                            <?php $choices = json_decode($question['choices_json'], true); ?>
                            <div class="ms-0 ms-md-2">
                                <?php foreach ($choices as $idx => $choice): ?>
                                    <label class="mcq-option">
                                        <input class="form-check-input answer-input flex-shrink-0" type="radio"
                                               name="answer_<?= $question['id'] ?>"
                                               id="q<?= $question['id'] ?>_<?= $idx ?>"
                                               value="<?= htmlspecialchars($choice) ?>"
                                               data-question="<?= $qNum ?>">
                                        <span><?= htmlspecialchars($choice) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            
                        <?php elseif ($question['question_type'] === 'truefalse'): ?>
                            <div class="ms-0 ms-md-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input answer-input" type="radio" 
                                           name="answer_<?= $question['id'] ?>" 
                                           id="q<?= $question['id'] ?>_true"
                                           value="True" data-question="<?= $qNum ?>" required>
                                    <label class="form-check-label" for="q<?= $question['id'] ?>_true">True</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input answer-input" type="radio" 
                                           name="answer_<?= $question['id'] ?>" 
                                           id="q<?= $question['id'] ?>_false"
                                           value="False" data-question="<?= $qNum ?>" required>
                                    <label class="form-check-label" for="q<?= $question['id'] ?>_false">False</label>
                                </div>
                            </div>
                            
                        <?php elseif ($question['question_type'] === 'identification'): ?>
                            <input type="text" class="form-control answer-input" 
                                   name="answer_<?= $question['id'] ?>" 
                                   placeholder="Enter your answer"
                                   data-question="<?= $qNum ?>" required>
                                   
                        <?php elseif ($question['question_type'] === 'essay'): ?>
                            <textarea class="form-control answer-input" 
                                      name="answer_<?= $question['id'] ?>" 
                                      rows="8" 
                                      placeholder="Write your answer here..."
                                      data-question="<?= $qNum ?>" required></textarea>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $qNum++; endforeach; ?>
                
                <div class="card">
                    <div class="card-body text-center">
                        <p class="mb-3">Make sure you have answered all questions before submitting.</p>
                        <button type="submit" name="submit_quiz" class="btn btn-success btn-lg" onclick="return confirm('Submit your quiz? You cannot change your answers after submission.')">
                            <i class="bi bi-check-circle me-2"></i>Submit Quiz
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($quiz['time_limit_minutes'] > 0): ?>
        // Timer functionality
        let timeRemaining = <?= $quiz['time_limit_minutes'] * 60 ?>;
        
        function updateTimer() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const timerText = document.getElementById('timerText');
            
            timerText.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeRemaining <= 60) {
                timerText.className = 'timer-text danger';
            } else if (timeRemaining <= 300) {
                timerText.className = 'timer-text warning';
            }
            
            if (timeRemaining <= 0) {
                alert('Time is up! Your quiz will be submitted automatically.');
                document.getElementById('quizForm').submit();
            }
            
            timeRemaining--;
        }
        
        setInterval(updateTimer, 1000);
        <?php endif; ?>
        
        // Track answered questions + progress bar
        const totalQ = <?= count($questions) ?>;
        let answeredSet = new Set();

        function updateQuizProgress() {
            const count = answeredSet.size;
            const pct   = totalQ > 0 ? Math.round((count / totalQ) * 100) : 0;
            const fillEl = document.getElementById('quizProgressFill');
            const numEl  = document.getElementById('qCurrentNum');
            const pctEl  = document.getElementById('qPctLabel');
            if (fillEl) fillEl.style.width = pct + '%';
            if (numEl)  numEl.textContent  = count;
            if (pctEl)  pctEl.textContent  = pct;
        }

        document.querySelectorAll('.answer-input').forEach(input => {
            input.addEventListener('change', function() {
                const qNum = this.dataset.question;
                if (qNum) answeredSet.add(qNum);
                this.closest('.question-card').classList.add('answered');
                updateQuizProgress();
            });
        });
        updateQuizProgress();
        
        // Only apply tab protection if it's enabled for this quiz
        <?php if ($quiz['tab_protection_enabled']): ?>
        let quizSubmitted = false;
        
        // Show warning when user tries to leave the page
        window.addEventListener('beforeunload', function(e) {
            if (!quizSubmitted) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your quiz progress will be lost.';
                return 'Are you sure you want to leave? Your quiz progress will be lost.';
            }
        });
        
        // Detect when tab loses focus
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden' && !quizSubmitted) {
                alert('Warning: You have switched away from the quiz tab. Please return to complete the quiz.');
            }
        });
        
        // Prevent right-click context menu
        document.addEventListener('contextmenu', function(e) {
            if (!quizSubmitted) {
                e.preventDefault();
                alert('Right-click is disabled during the quiz.');
            }
        });
        
        // Prevent common shortcuts that could be used to switch tabs or close browser
        document.addEventListener('keydown', function(e) {
            // Prevent F5 (refresh), Ctrl+R (refresh), Ctrl+Shift+T (reopen closed tab)
            if (!quizSubmitted && (
                e.key === 'F5' || 
                (e.ctrlKey && e.key === 'r') || 
                (e.ctrlKey && e.shiftKey && e.key === 'T') ||
                (e.ctrlKey && e.key === 'w') ||
                (e.key === 'Escape' && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA')
            )) {
                e.preventDefault();
                e.stopPropagation();
                alert('This action is disabled during the quiz.');
            }
        });
        
        // Mark quiz as submitted when form is submitted
        document.getElementById('quizForm').addEventListener('submit', function() {
            quizSubmitted = true;
        });
        <?php endif; ?>
    </script>
</body>
</html>
