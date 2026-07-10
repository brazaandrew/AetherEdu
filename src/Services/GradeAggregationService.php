<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Grade;

class GradeAggregationService {
    public static function updateForSubject(int $studentId, int $subjectId): void {
        // Aggregate quiz scores
        $q = db()->prepare('SELECT COALESCE(SUM(qa.score),0) AS total_quiz FROM quiz_attempts qa JOIN quizzes q ON q.id=qa.quiz_id WHERE qa.student_id=? AND q.subject_id=? AND qa.submitted_at IS NOT NULL');
        $q->execute([$studentId, $subjectId]);
        $quizTotal = (int) ($q->fetch()['total_quiz'] ?? 0);

        // Aggregate activity scores
        $a = db()->prepare('SELECT COALESCE(SUM(s.score),0) AS total_act FROM activity_submissions s JOIN activities a ON a.id=s.activity_id WHERE s.student_id=? AND a.subject_id=? AND s.score IS NOT NULL');
        $a->execute([$studentId, $subjectId]);
        $actTotal = (int) ($a->fetch()['total_act'] ?? 0);

        $final = $quizTotal + $actTotal;
        Grade::upsert($studentId, $subjectId, $actTotal, $quizTotal, $final);
    }
}
