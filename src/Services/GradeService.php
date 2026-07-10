<?php
declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * GradeService - Handles quiz auto-grading and grade aggregation
 */
class GradeService {
    
    /**
     * Auto-grade a quiz attempt
     * Grades MCQ, True/False, and Identification questions automatically
     * Leaves essay questions for manual grading
     * 
     * @param PDO $pdo Database connection
     * @param int $attempt_id Quiz attempt ID
     * @return array [
     *   'auto_score' => int,
     *   'total_auto_points' => int, 
     *   'manual_points' => int,
     *   'total_questions' => int,
     *   'auto_graded' => int,
     *   'needs_manual' => int
     * ]
     */
    public static function grade_quiz_attempt(PDO $pdo, int $attempt_id): array {
        // Fetch attempt details
        $stmt = db()->prepare('SELECT * FROM quiz_attempts WHERE id = ?');
        $stmt->execute([$attempt_id]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attempt) {
            throw new \RuntimeException("Quiz attempt not found: {$attempt_id}");
        }
        
        $quiz_id = (int)$attempt['quiz_id'];
        $student_id = (int)$attempt['student_id'];
        
        // Fetch all questions for this quiz
        $stmt = $pdo->prepare('
            SELECT id, question_type, correct_answer, choices_json, points 
            FROM quiz_questions 
            WHERE quiz_id = ? 
            ORDER BY id ASC
        ');
        $stmt->execute([$quiz_id]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch student's answers
        $stmt = $pdo->prepare('
            SELECT question_id, student_answer 
            FROM quiz_answers 
            WHERE attempt_id = ?
        ');
        $stmt->execute([$attempt_id]);
        $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Map answers by question_id
        $answerMap = [];
        foreach ($answers as $ans) {
            $answerMap[(int)$ans['question_id']] = $ans['student_answer'];
        }
        
        $auto_score = 0;
        $total_auto_points = 0;
        $manual_points = 0;
        $auto_graded_count = 0;
        $needs_manual_count = 0;
        
        // Grade each question
        foreach ($questions as $question) {
            $question_id = (int)$question['id'];
            $question_type = $question['question_type'];
            $correct_answer = $question['correct_answer'];
            $points = (int)$question['points'];
            $student_answer = $answerMap[$question_id] ?? '';
            
            $is_correct = false;
            $requires_manual_grading = false;
            
            // Auto-grade non-essay questions
            if ($question_type === 'mcq') {
                $is_correct = self::gradeMCQ($question, $student_answer);
                $total_auto_points += $points;
                $auto_graded_count++;
            } elseif ($question_type === 'truefalse') {
                $is_correct = self::gradeTrueFalse($correct_answer, $student_answer);
                $total_auto_points += $points;
                $auto_graded_count++;
            } elseif ($question_type === 'identification') {
                $is_correct = self::gradeIdentification($correct_answer, $student_answer);
                $total_auto_points += $points;
                $auto_graded_count++;
            } elseif ($question_type === 'essay') {
                // Essay requires manual grading
                $requires_manual_grading = true;
                $manual_points += $points;
                $needs_manual_count++;
            }
            
            // Update quiz_answers table with is_correct flag
            $update_stmt = $pdo->prepare('
                UPDATE quiz_answers 
                SET is_correct = ?, requires_manual_grading = ? 
                WHERE attempt_id = ? AND question_id = ?
            ');
            $update_stmt->execute([
                $is_correct ? 1 : 0,
                $requires_manual_grading ? 1 : 0,
                $attempt_id,
                $question_id
            ]);
            
            // Add to auto score if correct
            if ($is_correct) {
                $auto_score += $points;
            }
        }
        
        // Update quiz_attempts with auto-graded score
        // Note: Final score will be updated after manual grading is complete
        $update_attempt = $pdo->prepare('
            UPDATE quiz_attempts 
            SET score = ?, 
                auto_graded_score = ?,
                needs_manual_grading = ?
            WHERE id = ?
        ');
        $update_attempt->execute([
            $auto_score, // Initially set score to auto_score
            $auto_score,
            $needs_manual_count > 0 ? 1 : 0,
            $attempt_id
        ]);
        
        return [
            'auto_score' => $auto_score,
            'total_auto_points' => $total_auto_points,
            'manual_points' => $manual_points,
            'total_questions' => count($questions),
            'auto_graded' => $auto_graded_count,
            'needs_manual' => $needs_manual_count
        ];
    }
    
    /**
     * Grade a multiple choice question
     */
    private static function gradeMCQ(array $question, string $student_answer): bool {
        $correct_answer = $question['correct_answer'];
        $choices = json_decode($question['choices_json'], true);
        
        // Validate that correct answer exists in choices
        if (!is_array($choices) || !in_array($correct_answer, $choices, true)) {
            return false;
        }
        
        // Exact match comparison
        return trim($student_answer) === trim($correct_answer);
    }
    
    /**
     * Grade a True/False question
     */
    private static function gradeTrueFalse(string $correct_answer, string $student_answer): bool {
        // Case-insensitive comparison
        return strcasecmp(trim($student_answer), trim($correct_answer)) === 0;
    }
    
    /**
     * Grade an identification question
     */
    private static function gradeIdentification(string $correct_answer, string $student_answer): bool {
        // Case-insensitive comparison
        return strcasecmp(trim($student_answer), trim($correct_answer)) === 0;
    }
    
    /**
     * Recompute student's final grade for a subject
     * Called after teacher publishes manual scores
     * 
     * @param PDO $pdo Database connection
     * @param int $student_id Student ID
     * @param int $subject_id Subject ID
     * @return array Grade breakdown
     */
    public static function recomputeStudentSubjectGrade(PDO $pdo, int $student_id, int $subject_id): array {
        // Calculate total quiz score (including manually graded essays)
        $quiz_stmt = $pdo->prepare('
            SELECT COALESCE(SUM(qa.score), 0) as total_quiz_score,
                   COUNT(qa.id) as quiz_count
            FROM quiz_attempts qa
            JOIN quizzes q ON q.id = qa.quiz_id
            WHERE qa.student_id = ? 
              AND q.subject_id = ?
              AND qa.status = "completed"
        ');
        $quiz_stmt->execute([$student_id, $subject_id]);
        $quiz_data = $quiz_stmt->fetch(PDO::FETCH_ASSOC);
        $quiz_total = (int)($quiz_data['total_quiz_score'] ?? 0);
        $quiz_count = (int)($quiz_data['quiz_count'] ?? 0);
        
        // Calculate total activity score
        $activity_stmt = $pdo->prepare('
            SELECT COALESCE(SUM(asub.score), 0) as total_activity_score,
                   COUNT(asub.id) as activity_count
            FROM activity_submissions asub
            JOIN activities a ON a.id = asub.activity_id
            WHERE asub.student_id = ?
              AND a.subject_id = ?
              AND asub.score IS NOT NULL
        ');
        $activity_stmt->execute([$student_id, $subject_id]);
        $activity_data = $activity_stmt->fetch(PDO::FETCH_ASSOC);
        $activity_total = (int)($activity_data['total_activity_score'] ?? 0);
        $activity_count = (int)($activity_data['activity_count'] ?? 0);
        
        // Calculate final grade
        $final_grade = $quiz_total + $activity_total;
        
        // Upsert into grades table
        $check_stmt = $pdo->prepare('
            SELECT id FROM grades 
            WHERE student_id = ? AND subject_id = ?
        ');
        $check_stmt->execute([$student_id, $subject_id]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record
            $update_stmt = $pdo->prepare('
                UPDATE grades 
                SET activity_grade_total = ?,
                    quiz_grade_total = ?,
                    final_grade = ?,
                    computed_at = NOW()
                WHERE id = ?
            ');
            $update_stmt->execute([
                $activity_total,
                $quiz_total,
                $final_grade,
                (int)$existing['id']
            ]);
        } else {
            // Insert new record
            $insert_stmt = $pdo->prepare('
                INSERT INTO grades (
                    student_id, 
                    subject_id, 
                    activity_grade_total, 
                    quiz_grade_total, 
                    final_grade,
                    computed_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ');
            $insert_stmt->execute([
                $student_id,
                $subject_id,
                $activity_total,
                $quiz_total,
                $final_grade
            ]);
        }
        
        return [
            'student_id' => $student_id,
            'subject_id' => $subject_id,
            'activity_total' => $activity_total,
            'activity_count' => $activity_count,
            'quiz_total' => $quiz_total,
            'quiz_count' => $quiz_count,
            'final_grade' => $final_grade
        ];
    }
    
    /**
     * Update manual essay scores and recompute final grade
     * 
     * @param PDO $pdo Database connection
     * @param int $attempt_id Quiz attempt ID
     * @param array $essay_scores ['question_id' => points_awarded]
     */
    public static function updateManualScores(PDO $pdo, int $attempt_id, array $essay_scores): void {
        // Fetch attempt details
        $stmt = $pdo->prepare('
            SELECT qa.student_id, q.subject_id, qa.auto_graded_score
            FROM quiz_attempts qa
            JOIN quizzes q ON q.id = qa.quiz_id
            WHERE qa.id = ?
        ');
        $stmt->execute([$attempt_id]);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$attempt) {
            throw new \RuntimeException("Quiz attempt not found: {$attempt_id}");
        }
        
        $auto_score = (int)($attempt['auto_graded_score'] ?? 0);
        $manual_score = 0;
        
        // Update each essay question score
        foreach ($essay_scores as $question_id => $awarded_points) {
            $update_stmt = $pdo->prepare('
                UPDATE quiz_answers 
                SET manual_points_awarded = ?,
                    is_correct = (manual_points_awarded > 0)
                WHERE attempt_id = ? AND question_id = ?
            ');
            $update_stmt->execute([
                (int)$awarded_points,
                $attempt_id,
                (int)$question_id
            ]);
            
            $manual_score += (int)$awarded_points;
        }
        
        // Update quiz attempt with final score
        $final_score = $auto_score + $manual_score;
        $update_attempt = $pdo->prepare('
            UPDATE quiz_attempts 
            SET score = ?,
                needs_manual_grading = 0,
                graded_at = NOW()
            WHERE id = ?
        ');
        $update_attempt->execute([$final_score, $attempt_id]);
        
        // Recompute student's subject grade
        self::recomputeStudentSubjectGrade(
            $pdo,
            (int)$attempt['student_id'],
            (int)$attempt['subject_id']
        );
    }
}
