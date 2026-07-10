<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class Finance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    // ========== FEE TYPES ==========

    public function getAllFeeTypes(): array
    {
        $stmt = $this->db->query("SELECT * FROM fee_types WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeeTypeById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM fee_types WHERE id = ?");
        $stmt->execute([$id]);
        $fee = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fee ?: null;
    }

    public function addFeeType(array $data): int
    {
        $sql = "INSERT INTO fee_types (name, description, amount, is_recurring, frequency, grade_level) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['amount'],
            $data['is_recurring'] ?? 0,
            $data['frequency'] ?? 'one_time',
            $data['grade_level'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateFeeType(int $id, array $data): bool
    {
        $sql = "UPDATE fee_types SET name = ?, description = ?, amount = ?, is_recurring = ?, 
                frequency = ?, grade_level = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['amount'],
            $data['is_recurring'] ?? 0,
            $data['frequency'] ?? 'one_time',
            $data['grade_level'] ?? null,
            $id
        ]);
    }

    // ========== STUDENT FEES ==========

    public function assignFeeToStudent(array $data): int
    {
        $sql = "INSERT INTO student_fees (student_id, fee_type_id, amount, discount, balance, due_date, school_year) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmtFt = $this->db->prepare("SELECT name FROM fee_types WHERE id = ?");
        $stmtFt->execute([$data['fee_type_id']]);
        $name = strtolower($stmtFt->fetchColumn() ?: '');
        
        $isDiscount = (
            strpos($name, 'discount') !== false ||
            strpos($name, 'grant') !== false ||
            strpos($name, 'scholar') !== false ||
            strpos($name, 'voucher') !== false
        );
        
        $amount = (float) $data['amount'];
        $discount = (float) ($data['discount'] ?? 0);
        
        if ($isDiscount) {
            $amount = -abs($amount);
            if ($discount > 0) {
                $amount = -abs($discount);
            }
            $balance = $amount;
        } else {
            $balance = $amount - $discount;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_id'],
            $data['fee_type_id'],
            $amount,
            $discount,
            $balance,
            $data['due_date'] ?? null,
            $data['school_year'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getStudentFees(int $studentId): array
    {
        $sql = "SELECT sf.*, ft.name as fee_name, ft.frequency 
                FROM student_fees sf 
                JOIN fee_types ft ON sf.fee_type_id = ft.id 
                WHERE sf.student_id = ? 
                ORDER BY sf.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentBalance(int $studentId): float
    {
        $sql = "SELECT COALESCE(SUM(balance), 0) FROM student_fees WHERE student_id = ? AND status != 'paid'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return (float) $stmt->fetchColumn();
    }

    public function getStudentFeeById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_fees WHERE id = ?");
        $stmt->execute([$id]);
        $fee = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fee ?: null;
    }

    public function updateStudentFeeBalance(int $feeId, float $newBalance): bool
    {
        $status = $newBalance <= 0 ? 'paid' : ($newBalance < $this->getStudentFeeById($feeId)['amount'] ? 'partial' : 'pending');
        $sql = "UPDATE student_fees SET balance = ?, status = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newBalance, $status, $feeId]);
    }

    // ========== PAYMENTS ==========

    public function recordPayment(array $data): int
    {
        $sql = "INSERT INTO payments (student_fee_id, student_id, amount, payment_method, reference_number, or_number, notes, received_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['student_fee_id'],
            $data['student_id'],
            $data['amount'],
            $data['payment_method'] ?? 'cash',
            $data['reference_number'] ?? null,
            $data['or_number'] ?? null,
            $data['notes'] ?? null,
            $data['received_by']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function getPaymentsByStudent(int $studentId): array
    {
        $sql = "SELECT p.*, ft.name as fee_name 
                FROM payments p 
                JOIN student_fees sf ON p.student_fee_id = sf.id 
                JOIN fee_types ft ON sf.fee_type_id = ft.id 
                WHERE p.student_id = ? 
                ORDER BY p.received_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaymentById(int $id): ?array
    {
        $sql = "SELECT p.*, u.name as received_by_name, ft.name as fee_name, s.name as student_name, s.empidno 
                FROM payments p 
                JOIN users u ON p.received_by = u.id 
                JOIN student_fees sf ON p.student_fee_id = sf.id 
                JOIN fee_types ft ON sf.fee_type_id = ft.id 
                JOIN users s ON p.student_id = s.id 
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        return $payment ?: null;
    }

    public function getAllPayments(array $filters = []): array
    {
        $sql = "SELECT p.*, u.name as received_by_name, ft.name as fee_name, s.name as student_name, s.empidno 
                FROM payments p 
                JOIN users u ON p.received_by = u.id 
                JOIN student_fees sf ON p.student_fee_id = sf.id 
                JOIN fee_types ft ON sf.fee_type_id = ft.id 
                JOIN users s ON p.student_id = s.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['student_id'])) {
            $sql .= " AND p.student_id = ?";
            $params[] = $filters['student_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(p.received_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(p.received_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY p.received_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========== REPORTS ==========

    public function getDailyReport(string $date): array
    {
        $sql = "SELECT COUNT(*) as total_transactions, COALESCE(SUM(amount), 0) as total_amount 
                FROM payments WHERE DATE(received_at) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMonthlyReport(string $year, string $month): array
    {
        $sql = "SELECT COUNT(*) as total_transactions, COALESCE(SUM(amount), 0) as total_amount 
                FROM payments WHERE YEAR(received_at) = ? AND MONTH(received_at) = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$year, $month]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOverdueFees(): array
    {
        $sql = "SELECT sf.*, ft.name as fee_name, u.name as student_name, u.empidno 
                FROM student_fees sf 
                JOIN fee_types ft ON sf.fee_type_id = ft.id 
                JOIN users u ON sf.student_id = u.id 
                WHERE sf.status IN ('pending', 'partial', 'overdue') 
                AND sf.due_date < CURDATE() 
                ORDER BY sf.due_date ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFinanceStats(): array
    {
        $stats = [];
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM fee_types WHERE status = 'active'");
        $stats['active_fee_types'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM student_fees WHERE status != 'paid'");
        $stats['outstanding_fees'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COALESCE(SUM(balance), 0) FROM student_fees WHERE status != 'paid'");
        $stats['total_outstanding'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE DATE(received_at) = CURDATE()");
        $stats['today_collections'] = $stmt->fetchColumn();
        
        $stmt = $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE MONTH(received_at) = MONTH(CURDATE()) AND YEAR(received_at) = YEAR(CURDATE())");
        $stats['month_collections'] = $stmt->fetchColumn();
        
        return $stats;
    }
}
