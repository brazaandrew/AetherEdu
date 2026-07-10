<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class HR
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ========== EMPLOYEE 201 FILE ==========

    public function getAllEmployees(): array
    {
        $stmt = $this->db->query("SELECT id, name, empidno, role, email, grade_level, created_at FROM users WHERE role IN ('admin', 'teacher', 'registrar', 'librarian', 'cashier', 'nurse', 'hr', 'it_personnel') AND archived = 0 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployeeById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function get201File(int $employeeId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM employee_201_files WHERE employee_id = ?");
        $stmt->execute([$employeeId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        return $file ?: null;
    }

    public function save201File(array $data): int
    {
        $existing = $this->get201File($data['employee_id']);

        if ($existing) {
            $sql = "UPDATE employee_201_files SET
                    date_of_birth = ?, place_of_birth = ?, sex = ?, civil_status = ?,
                    citizenship = ?, height_cm = ?, weight_kg = ?, blood_type = ?,
                    gsis_no = ?, pagibig_no = ?, philhealth_no = ?, sss_no = ?, tin_no = ?,
                    agency_employee_no = ?, residential_address = ?, permanent_address = ?,
                    telephone_no = ?, mobile_no = ?, email = ?,
                    spouse_name = ?, spouse_occupation = ?, spouse_employer = ?, spouse_business_address = ?,
                    father_name = ?, father_occupation = ?, mother_name = ?, mother_occupation = ?,
                    date_hired = ?, employment_status = ?, position_title = ?, department = ?,
                    salary_grade = ?, monthly_salary = ?, skills = ?, recognitions = ?, organizations = ?
                    WHERE employee_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['date_of_birth'] ?? null, $data['place_of_birth'] ?? null,
                $data['sex'] ?? null, $data['civil_status'] ?? null,
                $data['citizenship'] ?? null, $data['height_cm'] ?? null,
                $data['weight_kg'] ?? null, $data['blood_type'] ?? null,
                $data['gsis_no'] ?? null, $data['pagibig_no'] ?? null,
                $data['philhealth_no'] ?? null, $data['sss_no'] ?? null,
                $data['tin_no'] ?? null, $data['agency_employee_no'] ?? null,
                $data['residential_address'] ?? null, $data['permanent_address'] ?? null,
                $data['telephone_no'] ?? null, $data['mobile_no'] ?? null,
                $data['email'] ?? null,
                $data['spouse_name'] ?? null, $data['spouse_occupation'] ?? null,
                $data['spouse_employer'] ?? null, $data['spouse_business_address'] ?? null,
                $data['father_name'] ?? null, $data['father_occupation'] ?? null,
                $data['mother_name'] ?? null, $data['mother_occupation'] ?? null,
                $data['date_hired'] ?? null, $data['employment_status'] ?? null,
                $data['position_title'] ?? null, $data['department'] ?? null,
                $data['salary_grade'] ?? null, $data['monthly_salary'] ?? null,
                $data['skills'] ?? null, $data['recognitions'] ?? null,
                $data['organizations'] ?? null,
                $data['employee_id']
            ]);
            return (int) $existing['id'];
        } else {
            $sql = "INSERT INTO employee_201_files
                    (employee_id, date_of_birth, place_of_birth, sex, civil_status, citizenship,
                     height_cm, weight_kg, blood_type, gsis_no, pagibig_no, philhealth_no, sss_no, tin_no,
                     agency_employee_no, residential_address, permanent_address, telephone_no, mobile_no, email,
                     spouse_name, spouse_occupation, spouse_employer, spouse_business_address,
                     father_name, father_occupation, mother_name, mother_occupation,
                     date_hired, employment_status, position_title, department,
                     salary_grade, monthly_salary, skills, recognitions, organizations)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['employee_id'], $data['date_of_birth'] ?? null, $data['place_of_birth'] ?? null,
                $data['sex'] ?? null, $data['civil_status'] ?? null, $data['citizenship'] ?? null,
                $data['height_cm'] ?? null, $data['weight_kg'] ?? null, $data['blood_type'] ?? null,
                $data['gsis_no'] ?? null, $data['pagibig_no'] ?? null, $data['philhealth_no'] ?? null,
                $data['sss_no'] ?? null, $data['tin_no'] ?? null, $data['agency_employee_no'] ?? null,
                $data['residential_address'] ?? null, $data['permanent_address'] ?? null,
                $data['telephone_no'] ?? null, $data['mobile_no'] ?? null, $data['email'] ?? null,
                $data['spouse_name'] ?? null, $data['spouse_occupation'] ?? null,
                $data['spouse_employer'] ?? null, $data['spouse_business_address'] ?? null,
                $data['father_name'] ?? null, $data['father_occupation'] ?? null,
                $data['mother_name'] ?? null, $data['mother_occupation'] ?? null,
                $data['date_hired'] ?? null, $data['employment_status'] ?? null,
                $data['position_title'] ?? null, $data['department'] ?? null,
                $data['salary_grade'] ?? null, $data['monthly_salary'] ?? null,
                $data['skills'] ?? null, $data['recognitions'] ?? null, $data['organizations'] ?? null
            ]);
            return (int) $this->db->lastInsertId();
        }
    }

    // ========== EDUCATION ==========

    public function addEducation(array $data): int
    {
        $sql = "INSERT INTO employee_education (employee_id, level, school_name, degree_course, year_graduated, highest_level, year_attended_from, year_attended_to, honors_received)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['employee_id'], $data['level'], $data['school_name'] ?? null,
            $data['degree_course'] ?? null, $data['year_graduated'] ?? null,
            $data['highest_level'] ?? null, $data['year_attended_from'] ?? null,
            $data['year_attended_to'] ?? null, $data['honors_received'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getEducationByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM employee_education WHERE employee_id = ? ORDER BY FIELD(level, 'elementary', 'secondary', 'vocational', 'college', 'graduate_studies')");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteEducation(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM employee_education WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ========== WORK EXPERIENCE ==========

    public function addWorkExperience(array $data): int
    {
        $sql = "INSERT INTO employee_work_experience (employee_id, date_from, date_to, position_title, department_office, monthly_salary, salary_grade, status_of_appointment, gov_service)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['employee_id'], $data['date_from'] ?? null, $data['date_to'] ?? null,
            $data['position_title'] ?? null, $data['department_office'] ?? null,
            $data['monthly_salary'] ?? null, $data['salary_grade'] ?? null,
            $data['status_of_appointment'] ?? null, $data['gov_service'] ?? 0
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getWorkExperienceByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM employee_work_experience WHERE employee_id = ? ORDER BY date_from DESC");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteWorkExperience(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM employee_work_experience WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ========== TRAININGS ==========

    public function addTraining(array $data): int
    {
        $sql = "INSERT INTO employee_trainings (employee_id, title, date_from, date_to, hours, type_of_ld, conducted_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['employee_id'], $data['title'], $data['date_from'] ?? null,
            $data['date_to'] ?? null, $data['hours'] ?? null,
            $data['type_of_ld'] ?? null, $data['conducted_by'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getTrainingsByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM employee_trainings WHERE employee_id = ? ORDER BY date_from DESC");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteTraining(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM employee_trainings WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ========== DOCUMENTS ==========

    public function addDocument(array $data): int
    {
        $sql = "INSERT INTO employee_documents (employee_id, document_name, document_type, file_path, uploaded_by)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['employee_id'], $data['document_name'], $data['document_type'],
            $data['file_path'], $data['uploaded_by']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getDocumentsByEmployee(int $employeeId): array
    {
        $stmt = $this->db->prepare("SELECT ed.*, u.name as uploaded_by_name FROM employee_documents ed LEFT JOIN users u ON ed.uploaded_by = u.id WHERE ed.employee_id = ? ORDER BY ed.uploaded_at DESC");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteDocument(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM employee_documents WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ========== STATS ==========

    public function getHRStats(): array
    {
        $stats = [];
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'teacher', 'registrar', 'librarian', 'cashier', 'nurse', 'hr', 'it_personnel') AND archived = 0");
        $stats['total_employees'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM employee_201_files");
        $stats['files_completed'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher' AND archived = 0");
        $stats['total_teachers'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT COUNT(*) FROM employee_documents");
        $stats['total_documents'] = $stmt->fetchColumn();

        return $stats;
    }
}
