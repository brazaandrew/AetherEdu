<?php
declare(strict_types=1);

namespace App\Models;

class Grade {
    public static function upsert(int $studentId, int $subjectId, int $activityTotal, int $quizTotal, int $final): void {
        $stmt = db()->prepare('SELECT id FROM grades WHERE student_id=? AND subject_id=?');
        $stmt->execute([$studentId, $subjectId]);
        $row = $stmt->fetch();
        
        if ($row) {
            $upd = db()->prepare('UPDATE grades SET activity_grade_total=?, quiz_grade_total=?, final_grade=?, computed_at=NOW() WHERE id=?');
            $upd->execute([$activityTotal, $quizTotal, $final, (int)$row['id']]);
        } else {
            $ins = db()->prepare('INSERT INTO grades (student_id, subject_id, activity_grade_total, quiz_grade_total, final_grade, computed_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $ins->execute([$studentId, $subjectId, $activityTotal, $quizTotal, $final]);
        }
    }

    public static function findByStudentSubject(int $studentId, int $subjectId): ?array {
        $stmt = db()->prepare('SELECT * FROM grades WHERE student_id=? AND subject_id=?');
        $stmt->execute([$studentId, $subjectId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
