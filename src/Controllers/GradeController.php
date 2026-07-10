<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Grade;
use App\Services\GradeAggregationService;

class GradeController {
    public function list_grades(): void {
        $u = requireLogin();
        $subjectId = (int) ($_GET['subject_id'] ?? 0);
        $studentId = (int) ($_GET['student_id'] ?? 0);
        
        if ($u['role'] === 'student') {
            $studentId = (int)$u['id'];
        }
        
        if (!$studentId) $this->respond(false, [], 'Missing student_id');

        $grade = Grade::findByStudentSubject($studentId, $subjectId);
        $this->respond(true, ['grade' => $grade ?: []]);
    }

    public function publish_grades(): void {
        $u = requireRole(['teacher', 'admin']);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        
        if (!$subjectId || !$studentId) {
            $this->respond(false, [], 'Missing subject_id or student_id');
        }

        GradeAggregationService::updateForSubject($studentId, $subjectId);
        saveAudit((int)$u['id'], 'publish', 'grades', $subjectId, ['student_id' => $studentId]);
        $this->respond(true, ['published' => true]);
    }

    private function respond(bool $ok, array $data = [], string $error = ''): void {
        http_response_code($ok ? 200 : 400);
        echo json_encode(['ok' => $ok, 'data' => $data, 'error' => $error], JSON_UNESCAPED_SLASHES);
        exit;
    }
}
