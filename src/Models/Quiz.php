<?php
declare(strict_types=1);

namespace App\Models;

class Quiz {
    public static function create(int $subjectId, string $title, string $instructions, int $timeLimit, int $maxScore, int $createdBy): int {
        $stmt = db()->prepare('INSERT INTO quizzes (subject_id, title, instructions, time_limit_minutes, max_score, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$subjectId, $title, $instructions, $timeLimit, $maxScore, $createdBy]);
        return (int) db()->lastInsertId();
    }

    public static function find(int $id): ?array {
        $stmt = db()->prepare('SELECT * FROM quizzes WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listBySubject(int $subjectId): array {
        $stmt = db()->prepare('SELECT q.*, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id=q.id) AS question_count FROM quizzes q WHERE q.subject_id=? ORDER BY q.created_at DESC');
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll();
    }
}
