<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class Attendance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ========== STUDENT ATTENDANCE ==========

    public function getStudentsForSubject(int $subjectId): array
    {
        $stmt = $this->db->prepare("SELECT u.id, u.name, u.empidno FROM users u JOIN enrollments e ON u.id = e.student_id WHERE e.subject_id = ? AND u.archived = 0 ORDER BY u.name");
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubjectsForTeacher(string $teacherEmpidno): array
    {
        $stmt = $this->db->prepare("SELECT s.id, s.code, s.name FROM subjects s JOIN folder_teacher ft ON s.id = ft.subject_id WHERE ft.teacher_empidno = ? AND s.archived = 0 ORDER BY s.name");
        $stmt->execute([$teacherEmpidno]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSubjects(): array
    {
        $stmt = $this->db->query("SELECT id, code, name FROM subjects WHERE archived = 0 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveStudentAttendance(int $subjectId, string $date, int $studentId, string $status, ?string $remarks, int $markedBy): bool
    {
        $stmt = $this->db->prepare("INSERT INTO student_attendance (date, subject_id, student_id, status, remarks, marked_by)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks), marked_by = VALUES(marked_by)");
        return $stmt->execute([$date, $subjectId, $studentId, $status, $remarks, $markedBy]);
    }

    public function getAttendanceForSubject(int $subjectId, string $date): array
    {
        $stmt = $this->db->prepare("SELECT sa.*, u.name as student_name FROM student_attendance sa JOIN users u ON sa.student_id = u.id WHERE sa.subject_id = ? AND sa.date = ?");
        $stmt->execute([$subjectId, $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentAttendance(int $studentId, ?int $subjectId = null, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT sa.*, s.name as subject_name, s.code as subject_code FROM student_attendance sa JOIN subjects s ON sa.subject_id = s.id WHERE sa.student_id = ?";
        $params = [$studentId];
        if ($subjectId) {
            $sql .= " AND sa.subject_id = ?";
            $params[] = $subjectId;
        }
        if ($dateFrom) {
            $sql .= " AND sa.date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND sa.date <= ?";
            $params[] = $dateTo;
        }
        $sql .= " ORDER BY sa.date DESC, s.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentAttendanceSummary(int $studentId, ?int $subjectId = null): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM student_attendance WHERE student_id = ?";
        $params = [$studentId];
        if ($subjectId) {
            $sql .= " AND subject_id = ?";
            $params[] = $subjectId;
        }
        $sql .= " GROUP BY status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']] = (int)$row['count'];
            $summary['total'] += (int)$row['count'];
        }
        return $summary;
    }

    public function getAttendanceDatesForSubject(int $subjectId): array
    {
        $stmt = $this->db->prepare("SELECT DISTINCT date FROM student_attendance WHERE subject_id = ? ORDER BY date DESC");
        $stmt->execute([$subjectId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ========== TEACHER ATTENDANCE (SELF-LOG) ==========

    public function getTeacherAttendanceToday(int $teacherId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM teacher_attendance WHERE teacher_id = ? AND date = CURDATE()");
        $stmt->execute([$teacherId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function clockIn(int $teacherId): bool
    {
        $stmt = $this->db->prepare("INSERT INTO teacher_attendance (teacher_id, date, time_in, status)
            VALUES (?, CURDATE(), CURTIME(), 'present')
            ON DUPLICATE KEY UPDATE time_in = COALESCE(time_in, CURTIME()), status = 'present'");
        return $stmt->execute([$teacherId]);
    }

    public function clockOut(int $teacherId): bool
    {
        $stmt = $this->db->prepare("UPDATE teacher_attendance SET time_out = CURTIME() WHERE teacher_id = ? AND date = CURDATE()");
        return $stmt->execute([$teacherId]);
    }

    public function getTeacherAttendance(int $teacherId, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT * FROM teacher_attendance WHERE teacher_id = ?";
        $params = [$teacherId];
        if ($dateFrom) {
            $sql .= " AND date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND date <= ?";
            $params[] = $dateTo;
        }
        $sql .= " ORDER BY date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllTeacherAttendance(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $sql = "SELECT ta.*, u.name as teacher_name, u.empidno FROM teacher_attendance ta JOIN users u ON ta.teacher_id = u.id WHERE 1=1";
        $params = [];
        if ($dateFrom) {
            $sql .= " AND ta.date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND ta.date <= ?";
            $params[] = $dateTo;
        }
        $sql .= " ORDER BY ta.date DESC, u.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherAttendanceSummary(int $teacherId): array
    {
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM teacher_attendance WHERE teacher_id = ? GROUP BY status");
        $stmt->execute([$teacherId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'half_day' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $summary[$row['status']] = (int)$row['count'];
            $summary['total'] += (int)$row['count'];
        }
        return $summary;
    }

    public function getMonthlyStudentAttendance(int $subjectId, int $year, int $month): array
    {
        $stmt = $this->db->prepare("SELECT sa.*, u.name as student_name FROM student_attendance sa JOIN users u ON sa.student_id = u.id WHERE sa.subject_id = ? AND YEAR(sa.date) = ? AND MONTH(sa.date) = ? ORDER BY sa.date, u.name");
        $stmt->execute([$subjectId, $year, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
