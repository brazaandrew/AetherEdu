<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class Clinic
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ========== MEDICAL PROFILES ==========

    public function getAllMedicalProfiles(): array
    {
        $sql = "SELECT smp.*, u.name as student_name, u.empidno as student_idno, u.grade_level 
                FROM student_medical_profiles smp 
                JOIN users u ON smp.student_id = u.id 
                ORDER BY u.name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMedicalProfileByStudentId(int $studentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_medical_profiles WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        return $profile ?: null;
    }

    public function createOrUpdateMedicalProfile(array $data): int
    {
        $existing = $this->getMedicalProfileByStudentId($data['student_id']);
        
        if ($existing) {
            $sql = "UPDATE student_medical_profiles SET 
                    blood_type = ?, height_cm = ?, weight_kg = ?, bmi = ?,
                    medical_conditions = ?, allergies = ?, medications = ?,
                    emergency_contact_name = ?, emergency_contact_phone = ?, emergency_contact_relationship = ?,
                    physician_name = ?, physician_phone = ?,
                    insurance_provider = ?, insurance_policy_number = ?, notes = ?
                    WHERE student_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['blood_type'] ?? null,
                $data['height_cm'] ?? null,
                $data['weight_kg'] ?? null,
                $data['bmi'] ?? null,
                $data['medical_conditions'] ?? null,
                $data['allergies'] ?? null,
                $data['medications'] ?? null,
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                $data['emergency_contact_relationship'] ?? null,
                $data['physician_name'] ?? null,
                $data['physician_phone'] ?? null,
                $data['insurance_provider'] ?? null,
                $data['insurance_policy_number'] ?? null,
                $data['notes'] ?? null,
                $data['student_id']
            ]);
            return (int) $existing['id'];
        } else {
            $sql = "INSERT INTO student_medical_profiles 
                    (student_id, blood_type, height_cm, weight_kg, bmi, medical_conditions, allergies, medications,
                     emergency_contact_name, emergency_contact_phone, emergency_contact_relationship,
                     physician_name, physician_phone, insurance_provider, insurance_policy_number, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['student_id'],
                $data['blood_type'] ?? null,
                $data['height_cm'] ?? null,
                $data['weight_kg'] ?? null,
                $data['bmi'] ?? null,
                $data['medical_conditions'] ?? null,
                $data['allergies'] ?? null,
                $data['medications'] ?? null,
                $data['emergency_contact_name'] ?? null,
                $data['emergency_contact_phone'] ?? null,
                $data['emergency_contact_relationship'] ?? null,
                $data['physician_name'] ?? null,
                $data['physician_phone'] ?? null,
                $data['insurance_provider'] ?? null,
                $data['insurance_policy_number'] ?? null,
                $data['notes'] ?? null
            ]);
            return (int) $this->db->lastInsertId();
        }
    }

    // ========== CLINIC VISITS ==========

    public function addClinicVisit(array $data): int
    {
        $sql = "INSERT INTO clinic_visits 
                (student_id, visit_date, visit_time, complaint, diagnosis, treatment, medication_given, action_taken, attended_by, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'],
            $data['visit_date'],
            $data['visit_time'] ?? null,
            $data['complaint'] ?? null,
            $data['diagnosis'] ?? null,
            $data['treatment'] ?? null,
            $data['medication_given'] ?? null,
            $data['action_taken'] ?? 'treated',
            $data['attended_by'] ?? null,
            $data['notes'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getClinicVisitsByStudent(int $studentId): array
    {
        $sql = "SELECT cv.*, u.name as attended_by_name 
                FROM clinic_visits cv 
                LEFT JOIN users u ON cv.attended_by = u.id 
                WHERE cv.student_id = ? 
                ORDER BY cv.visit_date DESC, cv.visit_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllClinicVisits(int $limit = 50): array
    {
        $sql = "SELECT cv.*, u.name as student_name, u.empidno as student_idno, 
                       us.name as attended_by_name 
                FROM clinic_visits cv 
                JOIN users u ON cv.student_id = u.id 
                LEFT JOIN users us ON cv.attended_by = us.id 
                ORDER BY cv.visit_date DESC, cv.visit_time DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== IMMUNIZATION RECORDS ==========

    public function addImmunizationRecord(array $data): int
    {
        $sql = "INSERT INTO immunization_records 
                (student_id, vaccine_name, date_administered, dose_number, administering_facility, notes)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'],
            $data['vaccine_name'],
            $data['date_administered'] ?? null,
            $data['dose_number'] ?? 1,
            $data['administering_facility'] ?? null,
            $data['notes'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getImmunizationRecordsByStudent(int $studentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM immunization_records WHERE student_id = ? ORDER BY date_administered DESC");
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteImmunizationRecord(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM immunization_records WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ========== STATS ==========

    public function getClinicStats(): array
    {
        $stats = [];
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM student_medical_profiles");
        $stats['total_profiles'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM clinic_visits WHERE visit_date = CURDATE()");
        $stats['today_visits'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM clinic_visits WHERE MONTH(visit_date) = MONTH(CURDATE()) AND YEAR(visit_date) = YEAR(CURDATE())");
        $stats['month_visits'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(DISTINCT student_id) FROM clinic_visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
        $stats['students_visited_30d'] = $stmt->fetchColumn();
        
        return $stats;
    }
}
