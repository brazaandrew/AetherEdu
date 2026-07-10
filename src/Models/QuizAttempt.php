<?php
declare(strict_types=1);

namespace App\Models;

class QuizAttempt {
    public static function create(int $quizId, int $studentId): int {
        $stmt = db()->prepare('INSERT INTO quiz_attempts (quiz_id, student_id, score, submitted_at) VALUES (?, ?, 0, NULL)');
        $stmt->execute([$quizId, $studentId]);
        return (int) db()->lastInsertId();
    }

    public static function find(int $id): ?array {
        $stmt = db()->prepare('SELECT qa.*, q.subject_id FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateScore(int $id, int $score): void {
        $stmt = db()->prepare('UPDATE quiz_attempts SET score=?, submitted_at=NOW() WHERE id=?');
        $stmt->execute([$score, $id]);
    }
}
