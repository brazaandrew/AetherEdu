<?php
declare(strict_types=1);

namespace App\Models;

class QuizAnswer {
    public static function create(int $attemptId, int $questionId, string $studentAnswer, bool $isCorrect): void {
        $stmt = db()->prepare('INSERT INTO quiz_answers (attempt_id, question_id, student_answer, is_correct) VALUES (?, ?, ?, ?)');
        $stmt->execute([$attemptId, $questionId, $studentAnswer, $isCorrect ? 1 : 0]);
    }
}
