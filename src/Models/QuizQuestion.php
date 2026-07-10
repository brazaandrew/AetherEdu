<?php
declare(strict_types=1);

namespace App\Models;

class QuizQuestion {
    public static function create(int $quizId, string $questionText, string $questionType, string $choicesJson, string $correctAnswer, int $points): int {
        $stmt = db()->prepare('INSERT INTO quiz_questions (quiz_id, question_text, question_type, choices_json, correct_answer, points) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$quizId, $questionText, $questionType, $choicesJson, $correctAnswer, $points]);
        return (int) db()->lastInsertId();
    }

    public static function listByQuiz(int $quizId): array {
        $stmt = db()->prepare('SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY id ASC');
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }
}
