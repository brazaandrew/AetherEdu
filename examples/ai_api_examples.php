<?php
/**
 * Example: Using AI API Programmatically
 * 
 * This file demonstrates how to use the AI integration from PHP code
 * for batch operations or custom integrations.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Helpers/env.php';

use App\Services\AIService;

// Load environment
loadEnv(__DIR__ . '/.env');

// Initialize AI Service
$aiService = new AIService();

echo "========================================\n";
echo "  eLMS AI API Examples\n";
echo "========================================\n\n";

// Example 1: Generate Activity
echo "Example 1: Generate Activity\n";
echo "----------------------------\n";
try {
    $activityData = $aiService->generateActivity(
        "Climate Change and Global Warming",
        "Environmental Science",
        [
            'difficulty' => 'medium',
            'activity_type' => 'research',
            'max_score' => 100
        ]
    );
    
    echo "Title: " . $activityData['title'] . "\n";
    echo "Description: " . substr($activityData['description'], 0, 100) . "...\n";
    echo "Max Score: " . $activityData['max_score'] . "\n";
    echo "Deadline: " . $activityData['deadline_days'] . " days\n";
    echo "✓ Success!\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Generate Quiz
echo "Example 2: Generate Quiz\n";
echo "------------------------\n";
try {
    $quizData = $aiService->generateQuiz(
        "Basic Algebra",
        "Mathematics",
        [
            'difficulty' => 'easy',
            'question_count' => 5,
            'question_types' => ['mcq', 'truefalse'],
            'time_limit' => 15
        ]
    );
    
    echo "Title: " . $quizData['title'] . "\n";
    echo "Questions: " . count($quizData['questions']) . "\n";
    echo "Max Score: " . $quizData['max_score'] . "\n";
    echo "Time Limit: " . $quizData['time_limit_minutes'] . " minutes\n";
    
    echo "\nSample Questions:\n";
    foreach (array_slice($quizData['questions'], 0, 2) as $i => $q) {
        echo "  Q" . ($i + 1) . ": " . $q['question_text'] . "\n";
        echo "      Type: " . $q['question_type'] . "\n";
        echo "      Answer: " . $q['correct_answer'] . "\n";
    }
    echo "✓ Success!\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Generate MCQ Questions
echo "Example 3: Generate MCQ Questions Only\n";
echo "--------------------------------------\n";
try {
    $questions = $aiService->generateMCQQuestions(
        "World War II",
        3,
        'medium'
    );
    
    echo "Generated " . count($questions) . " questions:\n\n";
    
    foreach ($questions as $i => $q) {
        echo ($i + 1) . ". " . $q['question_text'] . "\n";
        if (isset($q['choices'])) {
            foreach ($q['choices'] as $choice) {
                echo "   " . $choice . "\n";
            }
        }
        echo "   Correct Answer: " . $q['correct_answer'] . "\n";
        echo "   Points: " . ($q['points'] ?? 1) . "\n\n";
    }
    echo "✓ Success!\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Batch Generation
echo "Example 4: Batch Generate Multiple Quizzes\n";
echo "------------------------------------------\n";

$topics = [
    ['topic' => 'Photosynthesis', 'subject' => 'Biology'],
    ['topic' => 'Newton\'s Laws of Motion', 'subject' => 'Physics'],
    ['topic' => 'American Revolution', 'subject' => 'History'],
];

echo "Generating quizzes for " . count($topics) . " topics...\n\n";

foreach ($topics as $i => $item) {
    try {
        $quiz = $aiService->generateQuiz(
            $item['topic'],
            $item['subject'],
            [
                'difficulty' => 'medium',
                'question_count' => 5,
                'question_types' => ['mcq'],
                'time_limit' => 10
            ]
        );
        
        echo ($i + 1) . ". " . $quiz['title'] . "\n";
        echo "   " . count($quiz['questions']) . " questions, {$quiz['max_score']} points\n";
        echo "   ✓ Generated\n";
        
    } catch (Exception $e) {
        echo ($i + 1) . ". Failed: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Batch generation complete!\n\n";

echo "========================================\n";
echo "  Examples Complete\n";
echo "========================================\n";
echo "\nNotes:\n";
echo "- Each API call uses tokens and may incur costs\n";
echo "- Review generated content before using in production\n";
echo "- Adjust difficulty and question types as needed\n";
echo "- See AIService.php for more customization options\n";
