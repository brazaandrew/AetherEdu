<?php
declare(strict_types=1);

namespace App\Models;

class ActivitySubmission {
    public static function create(int $activityId, int $studentId, string $answerText, ?string $filePath): int {
        $stmt = db()->prepare('INSERT INTO activity_submissions (activity_id, student_id, answer_text, file_path, submitted_at, score, graded_by, graded_at) VALUES (?, ?, ?, ?, NOW(), NULL, NULL, NULL)');
        $stmt->execute([$activityId, $studentId, $answerText, $filePath]);
        return (int) db()->lastInsertId();
    }

    public static function find(int $id): ?array {
        $stmt = db()->prepare('SELECT s.*, a.max_score, a.subject_id FROM activity_submissions s JOIN activities a ON a.id=s.activity_id WHERE s.id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function grade(int $id, int $score, int $gradedBy): void {
        $stmt = db()->prepare('UPDATE activity_submissions SET score=?, graded_by=?, graded_at=NOW() WHERE id=?');
        $stmt->execute([$score, $gradedBy, $id]);
    }
}
