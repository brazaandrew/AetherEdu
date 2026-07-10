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

// Restrict access to admin and cashier
if (!in_array($role, ['admin', 'cashier'])) {
    header('Location: dashboard.php');
    exit;
}

$financeController = new FinanceController();

// Defensive defaults so cards always render even if finance tables are missing / empty
$defaultStats = [
    'today_collections' => 0,
    'month_collections' => 0,
    'total_outstanding' => 0,
    'outstanding_fees'  => 0,
    'active_fee_types'  => 0,
];
$stats = $defaultStats;
$recentPayments = [];
$overdueFees = [];
$financeError = '';

try {
    $raw = $financeController->getStats();
    // Merge and cast numeric values so cards never show blank/null
    foreach ($defaultStats as $k => $v) {
        $stats[$k] = isset($raw[$k]) ? $raw[$k] : 0;
    }
} catch (Exception $e) {
    $financeError = 'Finance tables missing. Please run the finance migration.';
}

try {
    $recentPayments = $financeController->getPayments(['date_from' => date('Y-m-d', strtotime('-7 days'))]);
} catch (Exception $e) { /* ignore */ }

try {
    $overdueFees = $financeController->getOverdueFees();
} catch (Exception $e) { /* ignore */ }

$role = $_SESSION['user']['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        /* Scoped finance stat cards — avoid .stat-card white-gradient override */
        .finance-stat {
            border: none !important;
            border-radius: 12px;
            min-height: 115px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            transition: transform .2s ease, box-shadow .2s ease;
            overflow: hidden;
        }
        .finance-stat:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .finance-stat .card-body { padding: 1.25rem 1.5rem; }
        .finance-stat h3 { font-size: 1.75rem; line-height: 1.1; }
        .finance-stat h6 { font-size: .85rem; letter-spacing: .5px; opacity: .95; }
    </style>
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <h2 class="mb-4">Finance Management</h2>

            <?php if ($financeError): ?>
                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($financeError) ?></div>
            <?php endif; ?>
            
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card finance-stat bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Today's Collections</h6>
                                    <h3 class="mt-2 mb-0 text-white fw-bold">₱<?= number_format((float) ($stats['today_collections'] ?? 0), 2) ?></h3>
                                </div>
                                <i class="bi bi-cash-coin fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card finance-stat bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Monthly Collections</h6>
                                    <h3 class="mt-2 mb-0 text-white fw-bold">₱<?= number_format((float) ($stats['month_collections'] ?? 0), 2) ?></h3>
                                </div>
                                <i class="bi bi-graph-up fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card finance-stat bg-warning text-dark h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 text-dark">Outstanding Balance</h6>
                                    <h3 class="mt-2 mb-0 text-dark fw-bold">₱<?= number_format((float) ($stats['total_outstanding'] ?? 0), 2) ?></h3>
                                </div>
                                <i class="bi bi-exclamation-triangle fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card finance-stat bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0 text-white">Outstanding Fees</h6>
                                    <h3 class="mt-2 mb-0 text-white fw-bold"><?= (int) ($stats['outstanding_fees'] ?? 0) ?></h3>
                                </div>
                                <i class="bi bi-receipt fs-1 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mb-4">
                <a href="finance-payments.php" class="btn btn-primary me-2">
                    <i class="bi bi-plus-circle"></i> Record Payment
                </a>
                <a href="finance-assign-fees.php" class="btn btn-warning me-2">
                    <i class="bi bi-cash-coin"></i> Assign Student Payment
                </a>
                <a href="finance-fee-types.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-gear"></i> Fee Types
                </a>
                <a href="statement-of-account.php" class="btn btn-outline-dark me-2">
                    <i class="bi bi-receipt"></i> Statement of Account
                </a>
                <a href="finance-reports.php" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-bar-graph"></i> Reports
                </a>
            </div>
            
            <div class="row">
                <!-- Recent Payments -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Recent Payments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentPayments)): ?>
                                <p class="text-muted">No recent payments found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student</th>
                                                <th>Fee</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Received By</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($recentPayments, 0, 10) as $payment): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($payment['received_at'])) ?></td>
                                                <td><?= htmlspecialchars($payment['student_name']) ?></td>
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
                                                <td><?= htmlspecialchars($payment['received_by_name']) ?></td>
                                                <td>
                                                    <a href="print-receipt.php?id=<?= $payment['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Print Receipt">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
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
                
                <!-- Overdue Fees -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Overdue Fees</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($overdueFees)): ?>
                                <p class="text-muted">No overdue fees.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach (array_slice($overdueFees, 0, 8) as $fee): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($fee['student_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($fee['fee_name']) ?></small>
                                        </div>
                                        <span class="badge bg-danger">₱<?= number_format((float) $fee['balance'], 2) ?></span>
                                    </div>
                                    <?php endforeach; ?>
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
