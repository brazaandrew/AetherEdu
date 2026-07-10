<?php
declare(strict_types=1);

namespace App\Models;

class Activity {
    public static function create(int $subjectId, string $title, string $description, int $maxScore, ?string $deadline, int $createdBy): int {
        $stmt = db()->prepare('INSERT INTO activities (subject_id, title, description, max_score, deadline, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$subjectId, $title, $description, $maxScore, $deadline, $createdBy]);
        return (int) db()->lastInsertId();
    }

    public static function find(int $id): ?array {
        $stmt = db()->prepare('SELECT * FROM activities WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listBySubject(int $subjectId): array {
        $stmt = db()->prepare('SELECT * FROM activities WHERE subject_id=? ORDER BY created_at DESC');
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll();
    }
}
