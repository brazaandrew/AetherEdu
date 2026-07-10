<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/Finance.php';

class FinanceController
{
    private Finance $financeModel;

    public function __construct()
    {
        $this->financeModel = new Finance();
    }

    // ========== FEE TYPES ==========

    public function getFeeTypes(): array
    {
        return $this->financeModel->getAllFeeTypes();
    }

    public function getFeeType(int $id): ?array
    {
        return $this->financeModel->getFeeTypeById($id);
    }

    public function addFeeType(array $data): array
    {
        try {
            $feeId = $this->financeModel->addFeeType($data);
            return ['success' => true, 'fee_id' => $feeId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateFeeType(int $id, array $data): array
    {
        try {
            $this->financeModel->updateFeeType($id, $data);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ========== STUDENT FEES ==========

    public function assignFee(array $data): array
    {
        try {
            $feeId = $this->financeModel->assignFeeToStudent($data);
            return ['success' => true, 'student_fee_id' => $feeId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getStudentFees(int $studentId): array
    {
        return $this->financeModel->getStudentFees($studentId);
    }

    public function getStudentBalance(int $studentId): float
    {
        return $this->financeModel->getStudentBalance($studentId);
    }

    // ========== PAYMENTS ==========

    public function recordPayment(array $data): array
    {
        try {
            // Get student fee details
            $studentFee = $this->financeModel->getStudentFeeById($data['student_fee_id']);
            if (!$studentFee) {
                return ['success' => false, 'error' => 'Student fee not found'];
            }

            if ($studentFee['balance'] <= 0) {
                return ['success' => false, 'error' => 'This fee is already fully paid'];
            }

            if ($data['amount'] > $studentFee['balance']) {
                return ['success' => false, 'error' => 'Payment amount exceeds remaining balance'];
            }

            // Record payment
            $paymentId = $this->financeModel->recordPayment($data);

            // Update balance
            $newBalance = $studentFee['balance'] - $data['amount'];
            $this->financeModel->updateStudentFeeBalance($data['student_fee_id'], $newBalance);

            return ['success' => true, 'payment_id' => $paymentId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getPayments(array $filters = []): array
    {
        return $this->financeModel->getAllPayments($filters);
    }

    public function getPayment(int $id): ?array
    {
        return $this->financeModel->getPaymentById($id);
    }

    public function getStudentPayments(int $studentId): array
    {
        return $this->financeModel->getPaymentsByStudent($studentId);
    }

    // ========== REPORTS ==========

    public function getDailyReport(string $date): array
    {
        return $this->financeModel->getDailyReport($date);
    }

    public function getMonthlyReport(string $year, string $month): array
    {
        return $this->financeModel->getMonthlyReport($year, $month);
    }

    public function getOverdueFees(): array
    {
        return $this->financeModel->getOverdueFees();
    }

    public function getStats(): array
    {
        return $this->financeModel->getFinanceStats();
    }
}
