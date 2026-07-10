<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Services\QuizGradingService;
use App\Services\GradeAggregationService;

class QuizController {
    public function create_quiz(): void {
        $u = requireRole(['teacher', 'admin']);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $instructions = (string) ($_POST['instructions'] ?? '');
        $timeLimit = (int) ($_POST['time_limit_minutes'] ?? 0);
        $maxScore = (int) ($_POST['max_score'] ?? 0);
        
        if (!$subjectId || $title === '') {
            $this->respond(false, [], 'Missing subject_id or title');
        }

        $quizId = Quiz::create($subjectId, $title, $instructions, $timeLimit, $maxScore, (int)$u['id']);
        saveAudit((int)$u['id'], 'create', 'quiz', $quizId, compact('subjectId', 'title'));
        $this->respond(true, ['quiz_id' => $quizId]);
    }

    public function add_question(): void {
        $u = requireRole(['teacher', 'admin']);
        $quizId = (int) ($_POST['quiz_id'] ?? 0);
        $questionText = trim((string) ($_POST['question_text'] ?? ''));
        $questionType = trim((string) ($_POST['question_type'] ?? ''));
        $choicesJson = (string) ($_POST['choices_json'] ?? '');
        $correctAnswer = (string) ($_POST['correct_answer'] ?? '');
        $points = (int) ($_POST['points'] ?? 1);
        
        if (!$quizId || $questionText === '' || $questionType === '') {
            $this->respond(false, [], 'Missing fields');
        }
        if (!in_array($questionType, ['mcq', 'truefalse', 'id'], true)) {
            $this->respond(false, [], 'Invalid question_type');
        }

        $questionId = QuizQuestion::create($quizId, $questionText, $questionType, $choicesJson, $correctAnswer, $points);
        saveAudit((int)$u['id'], 'create', 'quiz_question', $questionId, compact('quizId'));
        $this->respond(true, ['question_id' => $questionId]);
    }

    public function list_quizzes(): void {
        requireLogin();
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $quizzes = Quiz::listBySubject($subjectId);
        $this->respond(true, ['quizzes' => $quizzes]);
    }

    public function start_quiz(): void {
        $u = requireRole(['student']);
        $quizId = (int) ($_POST['quiz_id'] ?? 0);
        if (!$quizId) $this->respond(false, [], 'Missing quiz_id');

        $attemptId = QuizAttempt::create($quizId, (int)$u['id']);
        saveAudit((int)$u['id'], 'create', 'quiz_attempt', $attemptId, compact('quizId'));
        $this->respond(true, ['attempt_id' => $attemptId]);
    }

    public function submit_quiz(): void {
        $u = requireRole(['student']);
        $attemptId = (int) ($_POST['attempt_id'] ?? 0);
        $answers = json_decode((string)($_POST['answers'] ?? '[]'), true) ?: [];
        
        if (!$attemptId || !$answers) {
            $this->respond(false, [], 'Missing attempt_id or answers');
        }

        $attempt = QuizAttempt::find($attemptId);
        if (!$attempt || (int)$attempt['student_id'] !== (int)$u['id']) {
            $this->respond(false, [], 'Attempt not found');
        }
        if ($attempt['submitted_at']) {
            $this->respond(false, [], 'Attempt already submitted');
        }

        $score = QuizGradingService::gradeAttempt($attemptId, $answers);
        GradeAggregationService::updateForSubject((int)$u['id'], (int)$attempt['subject_id']);
        saveAudit((int)$u['id'], 'submit', 'quiz_attempt', $attemptId, ['score' => $score]);
        $this->respond(true, ['score' => $score]);
    }

    private function respond(bool $ok, array $data = [], string $error = ''): void {
        http_response_code($ok ? 200 : 400);
        echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_SLASHES);
        exit;
    }
}
