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

// Only students can view their balance
if ($role !== 'student') {
    header('Location: dashboard.php');
    exit;
}

$financeController = new FinanceController();
$studentId = $_SESSION['user']['id'];

$studentFees = $financeController->getStudentFees($studentId);
$payments = $financeController->getStudentPayments($studentId);
$totalBalance = $financeController->getStudentBalance($studentId);

// Calculate totals
$totalFees = array_sum(array_column($studentFees, 'amount'));
$totalPaid = array_sum(array_column($payments, 'amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Balance - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <h2 class="mb-4">My Balance</h2>
            
            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <h6>Total Balance Due</h6>
                            <h3>₱<?= number_format($totalBalance, 2) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>Total Paid</h6>
                            <h3>₱<?= number_format($totalPaid, 2) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Total Fees</h6>
                            <h3>₱<?= number_format($totalFees, 2) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Outstanding Fees -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">My Fees</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($studentFees)): ?>
                                <p class="text-muted">No fees assigned yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Fee</th>
                                                <th>Amount</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($studentFees as $fee): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($fee['fee_name']) ?></td>
                                                <td>₱<?= number_format((float) $fee['amount'], 2) ?></td>
                                                <td>₱<?= number_format((float) ($fee['amount'] - $fee['balance']), 2) ?></td>
                                                <td>₱<?= number_format((float) $fee['balance'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= match($fee['status']) {
                                                        'pending' => 'warning',
                                                        'partial' => 'info',
                                                        'paid' => 'success',
                                                        'overdue' => 'danger',
                                                        default => 'secondary'
                                                    } ?>">
                                                        <?= ucfirst($fee['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Payment History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($payments)): ?>
                                <p class="text-muted">No payments recorded yet.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Fee</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($payment['received_at'])) ?></td>
                                                <td><?= htmlspecialchars($payment['fee_name']) ?></td>
                                                <td>₱<?= number_format((float) $payment['amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= match($payment['payment_method']) {
                                                        'cash' => 'success',
                                                        'check' => 'warning',
                                                        'bank_transfer' => 'info',
                                                        'online' => 'primary',
                                                        default => 'secondary'
                                                    } ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
