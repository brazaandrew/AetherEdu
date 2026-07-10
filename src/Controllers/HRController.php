<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/HR.php';

class HRController
{
    private HR $hrModel;

    public function __construct()
    {
        $this->hrModel = new HR();
    }

    public function getAllEmployees(): array
    {
        return $this->hrModel->getAllEmployees();
    }

    public function getEmployee(int $id): ?array
    {
        return $this->hrModel->getEmployeeById($id);
    }

    public function get201File(int $employeeId): ?array
    {
        return $this->hrModel->get201File($employeeId);
    }

    public function save201File(array $data): array
    {
        try {
            $fileId = $this->hrModel->save201File($data);
            return ['success' => true, 'file_id' => $fileId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addEducation(array $data): array
    {
        try {
            $id = $this->hrModel->addEducation($data);
            return ['success' => true, 'education_id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getEducation(int $employeeId): array
    {
        return $this->hrModel->getEducationByEmployee($employeeId);
    }

    public function deleteEducation(int $id): array
    {
        try {
            $this->hrModel->deleteEducation($id);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addWorkExperience(array $data): array
    {
        try {
            $id = $this->hrModel->addWorkExperience($data);
            return ['success' => true, 'experience_id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getWorkExperience(int $employeeId): array
    {
        return $this->hrModel->getWorkExperienceByEmployee($employeeId);
    }

    public function deleteWorkExperience(int $id): array
    {
        try {
            $this->hrModel->deleteWorkExperience($id);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addTraining(array $data): array
    {
        try {
            $id = $this->hrModel->addTraining($data);
            return ['success' => true, 'training_id' => $id];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getTrainings(int $employeeId): array
    {
        return $this->hrModel->getTrainingsByEmployee($employeeId);
    }

    public function deleteTraining(int $id): array
    {
        try {
            $this->hrModel->deleteTraining($id);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDocuments(int $employeeId): array
    {
        return $this->hrModel->getDocumentsByEmployee($employeeId);
    }

    public function deleteDocument(int $id): array
    {
        try {
            $this->hrModel->deleteDocument($id);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getStats(): array
    {
        return $this->hrModel->getHRStats();
    }
}
