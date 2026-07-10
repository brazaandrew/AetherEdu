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
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_fee_type'])) {
        $result = $financeController->addFeeType([
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'amount' => $_POST['amount'],
            'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0,
            'frequency' => $_POST['frequency'] ?? 'one_time',
            'grade_level' => $_POST['grade_level'] ?? null
        ]);
        if ($result['success']) {
            $message = 'Fee type added successfully!';
        } else {
            $error = $result['error'];
        }
    } elseif (isset($_POST['edit_fee_type'])) {
        $result = $financeController->updateFeeType((int) $_POST['fee_type_id'], [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'amount' => $_POST['amount'],
            'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0,
            'frequency' => $_POST['frequency'] ?? 'one_time',
            'grade_level' => $_POST['grade_level'] ?? null
        ]);
        if ($result['success']) {
            $message = 'Fee type updated successfully!';
        } else {
            $error = $result['error'];
        }
    } elseif (isset($_POST['seed_defaults'])) {
        // Seed standard TLCA fee types (from Statement of Account template)
        $defaults = [
            // Charges
            ['name' => 'Tuition Fee',         'description' => 'Annual tuition fee',          'amount' => 8000.00, 'frequency' => 'yearly',   'is_recurring' => 1],
            ['name' => 'Miscellaneous',      'description' => 'Miscellaneous school fees',  'amount' => 2000.00, 'frequency' => 'yearly',   'is_recurring' => 1],
            ['name' => 'Others',              'description' => 'Other fees / books',          'amount' => 550.00,  'frequency' => 'yearly',   'is_recurring' => 0],
            // Discounts / Grants (amount 0 — assigned per-student)
            ['name' => 'Early Bird Discount', 'description' => 'Discount for early enrollment','amount' => 0.00,    'frequency' => 'one_time', 'is_recurring' => 0],
            ['name' => 'Academic Scholar',    'description' => 'Academic scholarship discount','amount' => 0.00,    'frequency' => 'yearly',   'is_recurring' => 0],
            ['name' => 'Sports Discount',     'description' => 'Sports scholarship discount',  'amount' => 0.00,    'frequency' => 'yearly',   'is_recurring' => 0],
            ['name' => 'ESC Grant',           'description' => 'Education Service Contract grant','amount' => 9000.00,'frequency' => 'yearly','is_recurring' => 0],
            ['name' => 'SHS Voucher',         'description' => 'Senior High School voucher',  'amount' => 0.00,    'frequency' => 'yearly',   'is_recurring' => 0],
            ['name' => 'TLCA Scholar',        'description' => 'TLCA scholarship discount',   'amount' => 0.00,    'frequency' => 'yearly',   'is_recurring' => 0],
            ['name' => 'Full Scholar',        'description' => 'Full scholarship discount',   'amount' => 0.00,    'frequency' => 'yearly',   'is_recurring' => 0],
        ];
        $added = 0; $skipped = 0;
        $existing = array_map('strtolower', array_column($financeController->getFeeTypes(), 'name'));
        foreach ($defaults as $d) {
            if (in_array(strtolower($d['name']), $existing, true)) { $skipped++; continue; }
            $r = $financeController->addFeeType($d);
            if (!empty($r['success'])) $added++;
        }
        $message = "Seeded default fee types: $added added, $skipped already existed.";
    } elseif (isset($_POST['delete_fee_type'])) {
        $feeId = (int) ($_POST['fee_type_id'] ?? 0);
        if ($feeId <= 0) {
            $error = 'Invalid fee type.';
        } else {
            try {
                // Block delete if any student is already assigned this fee
                $chk = db()->prepare("SELECT COUNT(*) FROM student_fees WHERE fee_type_id = ?");
                $chk->execute([$feeId]);
                $inUse = (int) $chk->fetchColumn();
                if ($inUse > 0) {
                    $error = "Cannot delete: this fee type is assigned to $inUse student(s). Remove those assignments first.";
                } else {
                    $del = db()->prepare("DELETE FROM fee_types WHERE id = ?");
                    $del->execute([$feeId]);
                    $message = 'Fee type deleted successfully.';
                }
            } catch (Exception $e) {
                $error = 'Failed to delete fee type: ' . $e->getMessage();
            }
        }
    }
}

$feeTypes = $financeController->getFeeTypes();
$editFee = null;
if (isset($_GET['edit'])) {
    $editFee = $financeController->getFeeType((int) $_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Types - TLCA</title>
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
                <h2>Fee Types</h2>
                <div>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Add all default TLCA fee types (Tuition, Miscellaneous, Others, Discounts, Grants)?');">
                        <button type="submit" name="seed_defaults" value="1" class="btn btn-success">
                            <i class="bi bi-magic"></i> Seed Default Fees
                        </button>
                    </form>
                    <a href="finance.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Finance
                    </a>
                </div>
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
                            <h5 class="mb-0"><?= $editFee ? 'Edit' : 'Add' ?> Fee Type</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($editFee): ?>
                                    <input type="hidden" name="fee_type_id" value="<?= $editFee['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Fee Name</label>
                                    <input type="text" name="name" class="form-control" required 
                                           value="<?= $editFee['name'] ?? '' ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="2"><?= $editFee['description'] ?? '' ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Amount (₱)</label>
                                    <input type="number" name="amount" class="form-control" step="0.01" required 
                                           value="<?= $editFee['amount'] ?? '' ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Frequency</label>
                                    <select name="frequency" class="form-select">
                                        <option value="one_time" <?= ($editFee['frequency'] ?? '') === 'one_time' ? 'selected' : '' ?>>One Time</option>
                                        <option value="monthly" <?= ($editFee['frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                                        <option value="quarterly" <?= ($editFee['frequency'] ?? '') === 'quarterly' ? 'selected' : '' ?>>Quarterly</option>
                                        <option value="semester" <?= ($editFee['frequency'] ?? '') === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                                        <option value="yearly" <?= ($editFee['frequency'] ?? '') === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Grade Level (Optional)</label>
                                    <input type="text" name="grade_level" class="form-control" 
                                           value="<?= $editFee['grade_level'] ?? '' ?>" placeholder="e.g., Grade 1, Grade 7">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="is_recurring" class="form-check-input" 
                                           id="is_recurring" <?= ($editFee['is_recurring'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_recurring">Recurring Fee</label>
                                </div>
                                
                                <button type="submit" name="<?= $editFee ? 'edit_fee_type' : 'add_fee_type' ?>" class="btn btn-primary w-100">
                                    <?= $editFee ? 'Update' : 'Add' ?> Fee Type
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">All Fee Types</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Frequency</th>
                                            <th>Grade Level</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feeTypes as $fee): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($fee['name']) ?><br>
                                                <small class="text-muted"><?= htmlspecialchars($fee['description']) ?></small>
                                            </td>
                                            <td>₱<?= number_format((float) $fee['amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $fee['is_recurring'] ? 'info' : 'secondary' ?>">
                                                    <?= ucfirst($fee['frequency']) ?>
                                                </span>
                                            </td>
                                            <td><?= $fee['grade_level'] ?: 'All' ?></td>
                                            <td>
                                                <a href="?edit=<?= $fee['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete fee type &quot;<?= htmlspecialchars(addslashes($fee['name'])) ?>&quot;? This cannot be undone.');">
                                                    <input type="hidden" name="fee_type_id" value="<?= $fee['id'] ?>">
                                                    <button type="submit" name="delete_fee_type" value="1" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
