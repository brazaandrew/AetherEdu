<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Clinic.php';

class ClinicController
{
    private Clinic $clinicModel;

    public function __construct()
    {
        $this->clinicModel = new Clinic();
    }

    public function getAllMedicalProfiles(): array
    {
        return $this->clinicModel->getAllMedicalProfiles();
    }

    public function getMedicalProfile(int $studentId): ?array
    {
        return $this->clinicModel->getMedicalProfileByStudentId($studentId);
    }

    public function saveMedicalProfile(array $data): array
    {
        try {
            // Calculate BMI if height and weight provided
            if (!empty($data['height_cm']) && !empty($data['weight_kg'])) {
                $heightM = (float) $data['height_cm'] / 100;
                $data['bmi'] = (float) $data['weight_kg'] / ($heightM * $heightM);
            }

            $profileId = $this->clinicModel->createOrUpdateMedicalProfile($data);
            return ['success' => true, 'profile_id' => $profileId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addClinicVisit(array $data): array
    {
        try {
            $visitId = $this->clinicModel->addClinicVisit($data);
            return ['success' => true, 'visit_id' => $visitId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getStudentVisits(int $studentId): array
    {
        return $this->clinicModel->getClinicVisitsByStudent($studentId);
    }

    public function getAllVisits(int $limit = 50): array
    {
        return $this->clinicModel->getAllClinicVisits($limit);
    }

    public function addImmunization(array $data): array
    {
        try {
            $recordId = $this->clinicModel->addImmunizationRecord($data);
            return ['success' => true, 'record_id' => $recordId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getStudentImmunizations(int $studentId): array
    {
        return $this->clinicModel->getImmunizationRecordsByStudent($studentId);
    }

    public function deleteImmunization(int $id): array
    {
        try {
            $this->clinicModel->deleteImmunizationRecord($id);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getStats(): array
    {
        return $this->clinicModel->getClinicStats();
    }
}
