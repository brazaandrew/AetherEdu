<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Services\GradeAggregationService;

class ActivityController {
    public function create_activity(): void {
        $u = requireRole(['teacher', 'admin']);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = (string) ($_POST['description'] ?? '');
        $maxScore = (int) ($_POST['max_score'] ?? 100);
        $deadline = (string) ($_POST['deadline'] ?? '');
        
        if (!$subjectId || $title === '') {
            $this->respond(false, [], 'Missing subject_id or title');
        }

        $activityId = Activity::create($subjectId, $title, $description, $maxScore, $deadline ?: null, (int)$u['id']);
        saveAudit((int)$u['id'], 'create', 'activity', $activityId, compact('subjectId', 'title'));
        $this->respond(true, ['activity_id' => $activityId]);
    }

    public function list_activities(): void {
        requireLogin();
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $activities = Activity::listBySubject($subjectId);
        $this->respond(true, ['activities' => $activities]);
    }

    public function submit_activity(): void {
        $u = requireRole(['student']);
        $activityId = (int) ($_POST['activity_id'] ?? 0);
        $answerText = (string) ($_POST['answer_text'] ?? '');
        $allowLate = (bool) (int) ($_POST['allow_late'] ?? 0);
        
        if (!$activityId) $this->respond(false, [], 'Missing activity_id');

        $activity = Activity::find($activityId);
        if (!$activity) $this->respond(false, [], 'Activity not found');
        
        if (!$allowLate && !empty($activity['deadline'])) {
            $now = new \DateTimeImmutable('now');
            $dl = new \DateTimeImmutable($activity['deadline']);
            if ($now > $dl) $this->respond(false, [], 'Past deadline');
        }

        $filePath = $this->handleFileUpload($activityId);
        $submissionId = ActivitySubmission::create($activityId, (int)$u['id'], $answerText, $filePath);
        saveAudit((int)$u['id'], 'submit', 'activity_submission', $submissionId, ['file_path' => $filePath]);
        $this->respond(true, ['submission_id' => $submissionId]);
    }

    public function grade_activity(): void {
        $u = requireRole(['teacher', 'admin']);
        $submissionId = (int) ($_POST['submission_id'] ?? 0);
        $score = (int) ($_POST['score'] ?? 0);
        
        if (!$submissionId) $this->respond(false, [], 'Missing submission_id');

        $submission = ActivitySubmission::find($submissionId);
        if (!$submission) $this->respond(false, [], 'Submission not found');
        if ($score < 0 || $score > (int)$submission['max_score']) {
            $this->respond(false, [], 'Invalid score range');
        }

        ActivitySubmission::grade($submissionId, $score, (int)$u['id']);
        GradeAggregationService::updateForSubject((int)$submission['student_id'], (int)$submission['subject_id']);
        saveAudit((int)$u['id'], 'grade', 'activity_submission', $submissionId, ['score' => $score]);
        $this->respond(true, ['graded' => true]);
    }

    private function handleFileUpload(int $activityId): ?string {
        if (empty($_FILES['file']['tmp_name'])) return null;

        $size = (int) $_FILES['file']['size'];
        $type = (string) $_FILES['file']['type'];
        $maxBytes = (int) env('MAX_UPLOAD_BYTES', 20971520);
        
        if ($size > $maxBytes) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'File too large']);
            exit;
        }

        $allowed = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($type, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid file type']);
            exit;
        }

        $ymDir = date('Y') . DIRECTORY_SEPARATOR . date('m');
        $baseDir = __DIR__ . '/../../public/uploads/activities';
        $destDir = $baseDir . DIRECTORY_SEPARATOR . $activityId . DIRECTORY_SEPARATOR . $ymDir;
        ensureDir($destDir);

        $orig = sanitizeFilename($_FILES['file']['name']);
        $hash = bin2hex(random_bytes(16));
        $stored = $hash . '_' . $orig;
        $destPath = $destDir . DIRECTORY_SEPARATOR . $stored;
        
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Failed to store file']);
            exit;
        }

        return 'uploads/activities/' . $activityId . '/' . date('Y') . '/' . date('m') . '/' . $stored;
    }

    private function respond(bool $ok, array $data = [], string $error = ''): void {
        http_response_code($ok ? 200 : 400);
        echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_SLASHES);
        exit;
    }
}
