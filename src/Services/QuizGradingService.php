<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;

class QuizGradingService {
    public static function gradeAttempt(int $attemptId, array $answers): int {
        $questions = QuizQuestion::listByQuiz(0); // Will be replaced with actual quiz_id from attempt
        $attempt = QuizAttempt::find($attemptId);
        if (!$attempt) throw new \RuntimeException('Attempt not found');
        
        $questions = QuizQuestion::listByQuiz((int)$attempt['quiz_id']);
        $byId = [];
        foreach ($questions as $q) { $byId[(int)$q['id']] = $q; }

        $totalScore = 0;
        foreach ($answers as $ans) {
            $qid = (int) ($ans['question_id'] ?? 0);
            $val = trim((string) ($ans['answer'] ?? ''));
            if (!$qid || !isset($byId[$qid])) continue;
            
            $q = $byId[$qid];
            $correct = self::checkAnswer($q, $val);
            QuizAnswer::create($attemptId, $qid, $val, $correct);
            if ($correct) $totalScore += (int) $q['points'];
        }

        QuizAttempt::updateScore($attemptId, $totalScore);
        return $totalScore;
    }

    private static function checkAnswer(array $question, string $studentAnswer): bool {
        switch ($question['question_type']) {
            case 'mcq': {
                $choices = json_decode($question['choices_json'], true) ?: [];
                return in_array($question['correct_answer'], $choices, true) && ($studentAnswer === $question['correct_answer']);
            }
            case 'truefalse': {
                return strcasecmp(trim($studentAnswer), trim($question['correct_answer'])) === 0 
                    && in_array(strtolower($studentAnswer), ['true', 'false'], true);
            }
            case 'id': {
                return strcasecmp(trim($studentAnswer), trim($question['correct_answer'])) === 0;
            }
        }
        return false;
    }
}
