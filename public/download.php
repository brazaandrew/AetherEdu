<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$fileId = (int)($_GET['id'] ?? 0);

if (!$fileId) {
    http_response_code(404);
    die('File not found');
}

// Fetch file details
$stmt = db()->prepare('SELECT * FROM files WHERE id = ?');
$stmt->execute([$fileId]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    die('File not found');
}

// Check authorization (basic check - can be enhanced with enrollment checks)
$canAccess = true;

// If teacher, check if assigned to subject
if ($user['role'] === 'teacher') {
    $stmt = db()->prepare('SELECT COUNT(*) FROM folder_teacher WHERE subject_id = ? AND teacher_empidno = ?');
    $stmt->execute([$file['subject_id'], $user['empidno']]);
    $canAccess = (int)$stmt->fetchColumn() > 0;
}

if (!$canAccess && $user['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}

// Construct file path
$filePath = __DIR__ . '/uploads/' . $file['stored_filename'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found on server');
}

// Serve file
header('Content-Type: ' . $file['mime_type']);
header('Content-Disposition: inline; filename="' . $file['original_filename'] . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');
header('Pragma: public');

readfile($filePath);
exit;
