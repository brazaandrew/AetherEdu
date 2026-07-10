<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$submissionId = (int)($_GET['id'] ?? 0);

if (!$submissionId) {
    header('Location: dashboard.php');
    exit;
}

// Fetch submission details
$stmt = db()->prepare('
    SELECT asub.*, a.subject_id, a.id as activity_id
    FROM activity_submissions asub
    JOIN activities a ON asub.activity_id = a.id
    WHERE asub.id = ?
');
$stmt->execute([$submissionId]);
$submission = $stmt->fetch();

if (!$submission || !$submission['stored_filename']) {
    header('Location: dashboard.php');
    exit;
}

// Authorization check
if ($user['role'] === 'student') {
    // Students can only download their own submissions
    if ((int)$submission['student_id'] !== (int)$user['id']) {
        http_response_code(403);
        die('Access denied');
    }
} elseif ($user['role'] === 'teacher') {
    // Teachers can only download submissions from their subjects
    $stmt = db()->prepare('
        SELECT COUNT(*) FROM folder_teacher 
        WHERE subject_id = ? AND teacher_empidno = ?
    ');
    $stmt->execute([$submission['subject_id'], $user['empidno']]);
    if (!$stmt->fetchColumn()) {
        http_response_code(403);
        die('Access denied');
    }
}
// Admins can download any submission

// Build file path
$filePath = __DIR__ . '/../uploads/activities/' . $submission['activity_id'] . '/' . $submission['stored_filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Serve the file
$originalFilename = $submission['original_filename'] ?? 'download';
$mimeType = mime_content_type($filePath);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $originalFilename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;
