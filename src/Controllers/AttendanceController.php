<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Attendance.php';

class AttendanceController
{
    private Attendance $attendanceModel;

    public function __construct()
    {
        $this->attendanceModel = new Attendance();
    }

    // ========== STUDENT ATTENDANCE ==========

    public function getSubjectsForTeacher(string $teacherEmpidno): array
    {
        return $this->attendanceModel->getSubjectsForTeacher($teacherEmpidno);
    }

    public function getAllSubjects(): array
    {
        return $this->attendanceModel->getAllSubjects();
    }

    public function getStudentsForSubject(int $subjectId): array
    {
        return $this->attendanceModel->getStudentsForSubject($subjectId);
    }

    public function saveAttendance(int $subjectId, string $date, array $attendanceData, int $markedBy): array
    {
        try {
            foreach ($attendanceData as $studentId => $data) {
                $status = $data['status'] ?? 'absent';
                $remarks = $data['remarks'] ?? null;
                $this->attendanceModel->saveStudentAttendance($subjectId, $date, (int)$studentId, $status, $remarks, $markedBy);
            }
            return ['success' => true, 'message' => 'Attendance saved successfully!'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAttendanceForSubject(int $subjectId, string $date): array
    {
        return $this->attendanceModel->getAttendanceForSubject($subjectId, $date);
    }

    public function getStudentAttendance(int $studentId, ?int $subjectId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->attendanceModel->getStudentAttendance($studentId, $subjectId, $dateFrom, $dateTo);
    }

    public function getStudentAttendanceSummary(int $studentId, ?int $subjectId = null): array
    {
        return $this->attendanceModel->getStudentAttendanceSummary($studentId, $subjectId);
    }

    public function getAttendanceDatesForSubject(int $subjectId): array
    {
        return $this->attendanceModel->getAttendanceDatesForSubject($subjectId);
    }

    // ========== TEACHER ATTENDANCE ==========

    public function getTeacherAttendanceToday(int $teacherId): ?array
    {
        return $this->attendanceModel->getTeacherAttendanceToday($teacherId);
    }

    public function clockIn(int $teacherId): array
    {
        try {
            $this->attendanceModel->clockIn($teacherId);
            return ['success' => true, 'message' => 'Clocked in successfully!'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function clockOut(int $teacherId): array
    {
        try {
            $this->attendanceModel->clockOut($teacherId);
            return ['success' => true, 'message' => 'Clocked out successfully!'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTeacherAttendance(int $teacherId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->attendanceModel->getTeacherAttendance($teacherId, $dateFrom, $dateTo);
    }

    public function getAllTeacherAttendance(?string $dateFrom = null, ?string $dateTo = null): array
    {
        return $this->attendanceModel->getAllTeacherAttendance($dateFrom, $dateTo);
    }

    public function getTeacherAttendanceSummary(int $teacherId): array
    {
        return $this->attendanceModel->getTeacherAttendanceSummary($teacherId);
    }

    public function getMonthlyStudentAttendance(int $subjectId, int $year, int $month): array
    {
        return $this->attendanceModel->getMonthlyStudentAttendance($subjectId, $year, $month);
    }
}
