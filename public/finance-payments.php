<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/FinanceController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'cashier'])) {
    header('Location: dashboard.php');
    exit;
}

$financeController = new FinanceController();

// Ensure or_number column exists in payments table (self-healing)
try {
    db()->exec("ALTER TABLE payments ADD COLUMN or_number VARCHAR(100) DEFAULT NULL AFTER reference_number;");
} catch (Exception $e) {
    // Fail silently if column already exists
}

$message = '';
$error = '';
$showReceiptModalId = null;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_payment'])) {
    $autoRef = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
    $result = $financeController->recordPayment([
        'student_fee_id' => (int) $_POST['student_fee_id'],
        'student_id' => (int) $_POST['student_id'],
        'amount' => (float) $_POST['amount'],
        'payment_method' => $_POST['payment_method'],
        'reference_number' => $autoRef,
        'or_number' => !empty($_POST['or_number']) ? trim($_POST['or_number']) : null,
        'notes' => $_POST['notes'] ?? null,
        'received_by' => $_SESSION['user']['id']
    ]);
    
    if ($result['success']) {
        $message = 'Payment recorded successfully! Sales Invoice: ' . $autoRef . ' <a href="print-receipt.php?id=' . $result['payment_id'] . '" target="_blank" class="btn btn-sm btn-success ms-2" id="printReceiptBtn"><i class="bi bi-printer"></i> Print Receipt</a>';
        $showReceiptModalId = $result['payment_id'];
    } else {
        $error = $result['error'];
    }
}

// Handle monthly payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_monthly_payment'])) {
    $studentId = (int)$_POST['student_id'];
    $payAmount = (float)$_POST['amount'];
    $method = $_POST['payment_method'];
    $orNumber = !empty($_POST['or_number']) ? trim($_POST['or_number']) : null;
    $month = !empty($_POST['month']) ? trim($_POST['month']) : '';
    $notes = $_POST['notes'] ?? '';
    
    if ($payAmount <= 0) {
        $error = 'Payment amount must be greater than zero.';
    } elseif (empty($month)) {
        $error = 'Please select a billing month.';
    } else {
        try {
            $db = db();
            $db->beginTransaction();
            
            $autoRef = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            
            // Fetch all student fees with positive balances
            $stmt = $db->prepare("SELECT sf.id, sf.balance, ft.name AS fee_name 
                                  FROM student_fees sf 
                                  JOIN fee_types ft ON sf.fee_type_id = ft.id 
                                  WHERE sf.student_id = ? AND sf.balance > 0 AND sf.status <> 'paid' 
                                  ORDER BY sf.id ASC");
            $stmt->execute([$studentId]);
            $outstanding = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $remainingPayment = $payAmount;
            $paymentCount = 0;
            
            foreach ($outstanding as $fee) {
                if ($remainingPayment <= 0) break;
                
                $applyAmount = min($remainingPayment, (float)$fee['balance']);
                
                // Record payment in payments table
                $stmtPay = $db->prepare("INSERT INTO payments (student_fee_id, student_id, amount, payment_method, reference_number, or_number, notes, received_by) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtPay->execute([
                    $fee['id'],
                    $studentId,
                    $applyAmount,
                    $method,
                    $autoRef,
                    $orNumber,
                    trim("Monthly installment payment. Month: " . $month . ". " . $notes),
                    $_SESSION['user']['id']
                ]);
                
                // Update balance on student_fees row
                $newBalance = $fee['balance'] - $applyAmount;
                $status = $newBalance <= 0 ? 'paid' : 'partial';
                
                $stmtUp = $db->prepare("UPDATE student_fees SET balance = ?, status = ? WHERE id = ?");
                $stmtUp->execute([$newBalance, $status, $fee['id']]);
                
                $remainingPayment -= $applyAmount;
                $paymentCount++;
            }
            
            if ($paymentCount > 0) {
                $db->commit();
                $message = 'Monthly installment payment of ₱' . number_format($payAmount, 2) . ' recorded successfully across outstanding fees! Sales Invoice: ' . $autoRef;
                if ($remainingPayment > 0) {
                    $message .= ' Note: Student is fully paid. Excess of ₱' . number_format($remainingPayment, 2) . ' was not charged.';
                }
            } else {
                $db->rollBack();
                $error = 'The student has no outstanding positive balance fees to pay.';
            }
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error processing monthly payment: ' . $e->getMessage();
        }
    }
}

// Get students with outstanding fees
$db = db();
$stmt = $db->query("SELECT id, name, empidno FROM users WHERE role = 'student' AND archived = 0 ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selectedStudent = null;
$studentFees = [];
$monthlyInstallment = 0.0;
$paidMonths = [];
if (isset($_GET['student_id'])) {
    $selectedStudentId = (int) $_GET['student_id'];
    $stmt = $db->prepare("SELECT id, name, empidno FROM users WHERE id = ?");
    $stmt->execute([$selectedStudentId]);
    $selectedStudent = $stmt->fetch(PDO::FETCH_ASSOC);
    $studentFees = $financeController->getStudentFees($selectedStudentId);
    
    // Calculate Monthly Installment
    $tuitionFee = 0.0;
    $miscFee = 0.0;
    $otherFee = 0.0;
    $discountTotal = 0.0;
    
    foreach ($studentFees as $f) {
        $name = strtolower($f['fee_name']);
        $amt = (float)$f['balance'];
        
        if (strpos($name, 'tuition') !== false) {
            $tuitionFee += $amt;
        } elseif (strpos($name, 'miscellaneous') !== false || strpos($name, 'misc') !== false) {
            $miscFee += $amt;
        } elseif (strpos($name, 'others') !== false || strpos($name, 'book') !== false) {
            $otherFee += $amt;
        }
        
        if (strpos($name, 'discount') !== false || 
            strpos($name, 'grant') !== false || 
            strpos($name, 'scholar') !== false || 
            strpos($name, 'voucher') !== false) {
            $discountTotal += abs($amt);
        }
    }
    
    $subtotal = $tuitionFee + $miscFee + $otherFee;
    $payable = max(0.0, $subtotal - $discountTotal);
    
    $previousAccount = 0.0;
    $currentMonth = (int) date('n');
    $currentYear  = (int) date('Y');
    $sy = $currentMonth >= 6 ? ($currentYear . '-' . ($currentYear + 1)) : (($currentYear - 1) . '-' . $currentYear);
    
    $pb = $db->prepare("SELECT COALESCE(SUM(balance),0) FROM student_fees WHERE student_id = ? AND (school_year IS NOT NULL AND school_year <> '' AND school_year <> ?) AND status <> 'paid'");
    $pb->execute([$selectedStudentId, $sy]);
    $previousAccount = max(0.0, (float)$pb->fetchColumn());
    
    $totalPayable = $payable + $previousAccount;
    $monthlyInstallment = $totalPayable > 0 ? round($totalPayable / 9, 2) : 0.0;
    
    // Fetch already paid months from payments log
    $stmtPm = $db->prepare("SELECT notes FROM payments WHERE student_id = ?");
    $stmtPm->execute([$selectedStudentId]);
    $paymentNotes = $stmtPm->fetchAll(PDO::FETCH_COLUMN);
    
    $billingMonths = ['JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER', 'JANUARY', 'FEBRUARY', 'MARCH'];
    foreach ($billingMonths as $m) {
        foreach ($paymentNotes as $note) {
            if ($note && strpos(strtoupper($note), 'MONTH: ' . $m) !== false) {
                $paidMonths[] = $m;
                break;
            }
        }
    }
}
$selectedStudent = $selectedStudent;
$studentFees = $studentFees;
$monthlyInstallment = $monthlyInstallment;
$paidMonths = $paidMonths;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Record Payment</h2>
                <a href="finance.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Finance
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Select Student</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Search Student</label>
                                <input type="text" class="form-control mb-3" id="studentSearchInput" placeholder="Type ID, Lastname, or Firstname..." autocomplete="off" value="<?= $selectedStudent ? htmlspecialchars($selectedStudent['name'] . ' (' . $selectedStudent['empidno'] . ')') : '' ?>">
                                
                                <div id="studentListGroup" class="list-group overflow-y-auto border rounded" style="max-height: 250px;">
                                    <?php foreach ($students as $student): ?>
                                        <a href="?student_id=<?= $student['id'] ?>" 
                                           class="list-group-item list-group-item-action student-item py-2 px-3 d-flex justify-content-between align-items-center <?= ($selectedStudent['id'] ?? '') == $student['id'] ? 'active' : '' ?>"
                                           data-name="<?= htmlspecialchars(strtolower($student['name'])) ?>"
                                           data-empidno="<?= htmlspecialchars(strtolower($student['empidno'])) ?>">
                                            <div>
                                                <div class="fw-semibold" style="font-size: 0.88rem;"><?= htmlspecialchars($student['name']) ?></div>
                                                <small class="<?= ($selectedStudent['id'] ?? '') == $student['id'] ? 'text-white-50' : 'text-muted' ?>"><?= htmlspecialchars($student['empidno']) ?></small>
                                            </div>
                                            <?php if (($selectedStudent['id'] ?? '') == $student['id']): ?>
                                                <i class="bi bi-check-circle-fill"></i>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($selectedStudent): ?>
                    <div class="card mt-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Student Info</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?= htmlspecialchars($selectedStudent['name']) ?></p>
                            <p><strong>ID:</strong> <?= $selectedStudent['empidno'] ?></p>
                            <p><strong>Total Balance:</strong> 
                                <span class="badge bg-danger fs-6">
                                    ₱<?= number_format($financeController->getStudentBalance($selectedStudent['id']), 2) ?>
                                </span>
                            </p>
                            <a href="print-registration.php?id=<?= $selectedStudent['id'] ?>" class="btn btn-sm btn-outline-dark w-100" target="_blank">
                                <i class="bi bi-receipt me-1"></i> Print Certificate of Registration
                            </a>
                            <a href="statement-of-account.php?id=<?= $selectedStudent['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                <i class="bi bi-file-text me-1"></i> View Statement of Account
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-8">
                    <?php if ($selectedStudent): ?>
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                            <h5 class="mb-0">Outstanding Fees</h5>
                            <?php if ($monthlyInstallment > 0): ?>
                                <button type="button" class="btn btn-success btn-sm px-3 rounded-pill" 
                                        data-bs-toggle="modal" data-bs-target="#monthlyPaymentModal">
                                    <i class="bi bi-calendar-check me-1"></i> Pay Monthly Installment (₱<?= number_format($monthlyInstallment, 2) ?>)
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($studentFees)): ?>
                                <p class="text-muted">No fees assigned to this student.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Fee Name</th>
                                                <th>Amount</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($studentFees as $fee): ?>
                                            <?php if ($fee['status'] === 'paid') continue; ?>
                                            <tr>
                                                <td><?= htmlspecialchars($fee['fee_name']) ?></td>
                                                <td>₱<?= number_format((float) $fee['amount'], 2) ?></td>
                                                <td>₱<?= number_format((float) $fee['balance'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= match($fee['status']) {
                                                        'pending' => 'warning',
                                                        'partial' => 'info',
                                                        'overdue' => 'danger',
                                                        default => 'secondary'
                                                    } ?>">
                                                        <?= ucfirst($fee['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            data-bs-toggle="modal" data-bs-target="#paymentModal"
                                                            onclick="setPaymentFee(<?= $fee['id'] ?>, <?= $fee['balance'] ?>, '<?= htmlspecialchars($fee['fee_name']) ?>')">
                                                        Pay
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Select a student to view their fees and record payments.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?? '' ?>">
                        <input type="hidden" name="student_fee_id" id="modalFeeId">
                        
                        <div class="mb-3">
                            <label class="form-label">Fee</label>
                            <input type="text" class="form-control" id="modalFeeName" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remaining Balance</label>
                            <input type="text" class="form-control" id="modalBalance" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Amount (₱)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required id="modalAmount">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sales Invoice (Auto-Generated)</label>
                            <input type="text" class="form-control bg-light" value="[Auto-Generated on Save]" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="record_payment" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Monthly Payment Modal -->
    <?php if ($selectedStudent): ?>
    <div class="modal fade" id="monthlyPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-check text-success me-2"></i>Pay Monthly Installment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($selectedStudent['name'] ?? '') ?>" readonly disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Monthly Installment (₱)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" value="<?= $monthlyInstallment ?>" required id="monthlyAmount">
                            <div class="form-text">Calculated as 1/9th of the total net payable for the school year. Can be adjusted.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Select Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-select" required>
                                <option value="">-- Select Month --</option>
                                <?php 
                                $billingMonths = ['JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER', 'JANUARY', 'FEBRUARY', 'MARCH'];
                                foreach ($billingMonths as $m): 
                                    $isPaid = in_array($m, $paidMonths);
                                ?>
                                    <option value="<?= $m ?>" <?= $isPaid ? 'disabled' : '' ?>>
                                        <?= ucfirst(strtolower($m)) ?> <?= $isPaid ? '(Already Paid)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sales Invoice (Auto-Generated)</label>
                            <input type="text" class="form-control bg-light" value="[Auto-Generated on Save]" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. July Installment"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="record_monthly_payment" class="btn btn-success">Record Monthly Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setPaymentFee(feeId, balance, feeName) {
            document.getElementById('modalFeeId').value = feeId;
            document.getElementById('modalBalance').value = '₱' + parseFloat(balance).toFixed(2);
            document.getElementById('modalFeeName').value = feeName;
            document.getElementById('modalAmount').max = balance;
        }

        // Live Student Search Filter (ID, Lastname, Firstname)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearchInput');
            const listGroup = document.getElementById('studentListGroup');
            
            if (searchInput && listGroup) {
                const items = Array.from(listGroup.getElementsByClassName('student-item'));
                
                searchInput.addEventListener('input', function() {
                    const query = searchInput.value.toLowerCase().trim();
                    
                    items.forEach(item => {
                        const name = item.getAttribute('data-name') || '';
                        const empidno = item.getAttribute('data-empidno') || '';
                        
                        if (query === '' || name.includes(query) || empidno.includes(query)) {
                            item.style.setProperty('display', 'flex', 'important');
                        } else {
                            item.style.setProperty('display', 'none', 'important');
                        }
                    });
                });
                
                // Highlight text on input focus for easy clear & re-search
                searchInput.addEventListener('focus', function() {
                    if (searchInput.value !== '') {
                        searchInput.select();
                    }
                });
            }
        });
    </script>
    
    <?php if ($showReceiptModalId): ?>
    <!-- Auto Receipt Modal -->
    <div class="modal fade" id="autoReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sales Invoice / Official Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="print-receipt.php?id=<?= $showReceiptModalId ?>" style="width: 100%; height: 75vh; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var receiptModal = new bootstrap.Modal(document.getElementById('autoReceiptModal'));
            receiptModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
