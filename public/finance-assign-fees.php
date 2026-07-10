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

// Auto school year
$currentMonth = (int) date('n');
$currentYear  = (int) date('Y');
$defaultSY = $currentMonth >= 6 ? ($currentYear . '-' . ($currentYear + 1)) : (($currentYear - 1) . '-' . $currentYear);

// ===== Handle POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Delete a student fee assignment
    if (isset($_POST['delete_fee'])) {
        $feeId = (int) $_POST['fee_id'];
        $sid   = (int) $_POST['student_id'];
        try {
            // Only allow delete if no payment has been recorded against it
            $chk = db()->prepare("SELECT COUNT(*) FROM payments WHERE student_fee_id = ?");
            $chk->execute([$feeId]);
            if ((int)$chk->fetchColumn() > 0) {
                $error = 'Cannot remove a fee that already has recorded payments.';
            } else {
                $del = db()->prepare("DELETE FROM student_fees WHERE id = ? AND student_id = ?");
                $del->execute([$feeId, $sid]);
                $message = 'Fee assignment removed.';
            }
        } catch (Exception $e) {
            $error = 'Failed to remove fee: ' . $e->getMessage();
        }
    }

    // Bulk assign fees
    if (isset($_POST['assign_fees'])) {
        $studentId  = (int) ($_POST['student_id'] ?? 0);
        $schoolYear = trim($_POST['school_year'] ?? $defaultSY);
        $dueDate    = $_POST['due_date'] ?: null;
        $selected   = $_POST['fees'] ?? []; // array of fee_type_id => ['enabled','amount','discount']

        if ($studentId <= 0) {
            $error = 'Please select a student.';
        } elseif (empty($selected)) {
            $error = 'Please select at least one fee to assign.';
        } else {
            $assigned = 0; $skipped = 0;
            foreach ($selected as $feeTypeId => $row) {
                if (empty($row['enabled'])) continue;
                $amount   = (float) ($row['amount'] ?? 0);
                $discount = (float) ($row['discount'] ?? 0);
                if ($amount < 0) continue;

                // Skip duplicates for same student / fee / school_year
                try {
                    $dup = db()->prepare("SELECT COUNT(*) FROM student_fees WHERE student_id = ? AND fee_type_id = ? AND school_year = ?");
                    $dup->execute([$studentId, (int)$feeTypeId, $schoolYear]);
                    if ((int)$dup->fetchColumn() > 0) { $skipped++; continue; }
                } catch (Exception $e) { /* ignore */ }

                $r = $financeController->assignFee([
                    'student_id'   => $studentId,
                    'fee_type_id'  => (int) $feeTypeId,
                    'amount'       => $amount,
                    'discount'     => $discount,
                    'due_date'     => $dueDate,
                    'school_year'  => $schoolYear,
                ]);
                if (!empty($r['success'])) $assigned++;
            }
            $message = "Assigned $assigned fee(s) to student. Skipped $skipped duplicate(s).";
        }
    }

    // ===== BULK ASSIGN by grade level =====
    if (isset($_POST['assign_by_grade'])) {
        $gradeLevel = trim($_POST['grade_level'] ?? '');
        $schoolYear = trim($_POST['bulk_school_year'] ?? $defaultSY);
        $dueDate    = $_POST['bulk_due_date'] ?: null;
        $selected   = $_POST['bulk_fees'] ?? [];

        if ($gradeLevel === '') {
            $error = 'Please select a grade level.';
        } elseif (empty($selected)) {
            $error = 'Please select at least one fee to assign.';
        } else {
            $stmt = db()->prepare("SELECT id FROM users WHERE role = 'student' AND archived = 0 AND grade_level = ?");
            $stmt->execute([$gradeLevel]);
            $studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($studentIds)) {
                $error = "No active students found in Grade $gradeLevel.";
            } else {
                $totalAssigned = 0; $totalSkipped = 0; $studentsTouched = 0;
                foreach ($studentIds as $sid) {
                    $sid = (int) $sid;
                    $anyAssigned = false;
                    foreach ($selected as $feeTypeId => $row) {
                        if (empty($row['enabled'])) continue;
                        $amount   = (float) ($row['amount'] ?? 0);
                        $discount = (float) ($row['discount'] ?? 0);
                        if ($amount < 0) continue;

                        try {
                            $dup = db()->prepare("SELECT COUNT(*) FROM student_fees WHERE student_id = ? AND fee_type_id = ? AND school_year = ?");
                            $dup->execute([$sid, (int)$feeTypeId, $schoolYear]);
                            if ((int)$dup->fetchColumn() > 0) { $totalSkipped++; continue; }
                        } catch (Exception $e) { /* ignore */ }

                        $r = $financeController->assignFee([
                            'student_id'   => $sid,
                            'fee_type_id'  => (int) $feeTypeId,
                            'amount'       => $amount,
                            'discount'     => $discount,
                            'due_date'     => $dueDate,
                            'school_year'  => $schoolYear,
                        ]);
                        if (!empty($r['success'])) { $totalAssigned++; $anyAssigned = true; }
                    }
                    if ($anyAssigned) $studentsTouched++;
                }
                $studentCount = count($studentIds);
                $message = "Bulk assigned: $totalAssigned fee(s) across $studentsTouched of $studentCount student(s) in Grade $gradeLevel. Skipped $totalSkipped duplicate(s).";
            }
        }
    }
}

// ===== Load data =====
$selectedId = (int) ($_GET['id'] ?? ($_POST['student_id'] ?? 0));

$allStudents = db()->query("SELECT id, name, grade_level, empidno FROM users WHERE role = 'student' AND archived = 0 ORDER BY name")->fetchAll();

// Distinct grade levels with student counts
$gradeLevels = [];
try {
    $gradeLevels = db()->query("SELECT grade_level, COUNT(*) AS student_count FROM users WHERE role = 'student' AND archived = 0 AND grade_level IS NOT NULL AND grade_level <> '' GROUP BY grade_level ORDER BY CAST(grade_level AS UNSIGNED), grade_level")->fetchAll();
} catch (Exception $e) { /* ignore */ }

$selectedStudent = null;
$existingFees    = [];
$totalBalance    = 0.0;
if ($selectedId > 0) {
    $s = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'student' LIMIT 1");
    $s->execute([$selectedId]);
    $selectedStudent = $s->fetch();

    if ($selectedStudent) {
        try {
            $existingFees = $financeController->getStudentFees($selectedId);
            $totalBalance = $financeController->getStudentBalance($selectedId);
        } catch (Exception $e) {
            $error = $error ?: 'Finance tables missing. Please run finance migration.';
        }
    }
}

$feeTypes = [];
try {
    $feeTypes = $financeController->getFeeTypes();
} catch (Exception $e) { /* tables missing */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Student Payment - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        .fee-row input[type=number]:disabled { background: #f0f0f0; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/includes/sidebar.php'; ?>
<div class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Assign Student Payment</h2>
                <small class="text-muted">Assign fees and discounts to a student</small>
            </div>
            <a href="finance.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Finance
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-individual" type="button" role="tab">
                    <i class="bi bi-person"></i> Individual Student
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bulk" type="button" role="tab">
                    <i class="bi bi-people-fill"></i> By Year / Grade Level
                </button>
            </li>
        </ul>

        <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-individual" role="tabpanel">

        <!-- Student selector -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-9">
                        <label class="form-label fw-bold">Select Student</label>
                        <select name="id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Choose a student --</option>
                            <?php foreach ($allStudents as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $selectedId === (int)$s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?><?= !empty($s['grade_level']) ? ' — Grade '.htmlspecialchars((string)$s['grade_level']) : '' ?><?= !empty($s['empidno']) ? ' ('.htmlspecialchars($s['empidno']).')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Load Student
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($selectedStudent): ?>
        <div class="row">
            <!-- Student Summary -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Student Info</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($selectedStudent['name']) ?></p>
                        <p><strong>Grade:</strong> <?= htmlspecialchars((string)($selectedStudent['grade_level'] ?? 'N/A')) ?></p>
                        <p><strong>ID:</strong> <?= htmlspecialchars((string)($selectedStudent['empidno'] ?? 'N/A')) ?></p>
                        <hr>
                        <p class="mb-1"><strong>Outstanding Balance:</strong></p>
                        <h3 class="text-danger">₱<?= number_format($totalBalance, 2) ?></h3>
                        <div class="d-grid gap-2 mt-3">
                            <a href="statement-of-account.php?id=<?= $selectedStudent['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-receipt me-1"></i> View SOA
                            </a>
                            <a href="finance-payments.php?student_id=<?= $selectedStudent['id'] ?>" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-cash me-1"></i> Record Payment
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Fees Form -->
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Assign New Fees</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($feeTypes)): ?>
                            <div class="alert alert-warning mb-0">
                                No fee types found. Go to <a href="finance-fee-types.php">Fee Types</a> and click "Seed Default Fees" first.
                            </div>
                        <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">School Year</label>
                                    <input type="text" name="school_year" class="form-control" value="<?= htmlspecialchars($defaultSY) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Due Date (optional)</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px">Select</th>
                                            <th>Fee Type</th>
                                            <th style="width:140px">Amount (₱)</th>
                                            <th style="width:140px">Discount (₱)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($feeTypes as $ft): ?>
                                        <tr class="fee-row">
                                            <td>
                                                <input type="checkbox"
                                                       class="form-check-input toggle-fee"
                                                       name="fees[<?= $ft['id'] ?>][enabled]"
                                                       value="1"
                                                       data-target="fee-<?= $ft['id'] ?>">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($ft['name']) ?></strong>
                                                <?php if (!empty($ft['description'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($ft['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0"
                                                       class="form-control form-control-sm fee-<?= $ft['id'] ?>"
                                                       name="fees[<?= $ft['id'] ?>][amount]"
                                                       value="<?= (float)$ft['amount'] ?>"
                                                       disabled>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0"
                                                       class="form-control form-control-sm fee-<?= $ft['id'] ?>"
                                                       name="fees[<?= $ft['id'] ?>][discount]"
                                                       value="0"
                                                       disabled>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="selectAll">
                                    <i class="bi bi-check-all"></i> Select All
                                </button>
                                <button type="submit" name="assign_fees" value="1" class="btn btn-success">
                                    <i class="bi bi-save me-1"></i> Assign Selected Fees
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Existing fees -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Currently Assigned Fees</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($existingFees)): ?>
                            <p class="text-muted mb-0">No fees assigned to this student yet.</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fee</th>
                                        <th>SY</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Due</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($existingFees as $f): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($f['fee_name']) ?></td>
                                        <td><?= htmlspecialchars((string)($f['school_year'] ?? '')) ?></td>
                                        <td>₱<?= number_format((float)$f['amount'], 2) ?></td>
                                        <td>₱<?= number_format((float)($f['discount'] ?? 0), 2) ?></td>
                                        <td class="fw-bold">₱<?= number_format((float)$f['balance'], 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= match($f['status']) {
                                                'paid' => 'success',
                                                'partial' => 'warning',
                                                'overdue' => 'danger',
                                                default => 'secondary'
                                            } ?>"><?= ucfirst($f['status']) ?></span>
                                        </td>
                                        <td><?= $f['due_date'] ? date('M d, Y', strtotime($f['due_date'])) : '—' ?></td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Remove this fee assignment?');">
                                                <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">
                                                <input type="hidden" name="fee_id" value="<?= $f['id'] ?>">
                                                <button type="submit" name="delete_fee" value="1" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
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
        <?php else: ?>
            <div class="alert alert-info">Select a student above to start assigning fees.</div>
        <?php endif; ?>

        </div><!-- /#tab-individual -->

        <!-- BULK by grade level -->
        <div class="tab-pane fade" id="tab-bulk" role="tabpanel">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Assign Fees to All Students in a Grade Level</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($gradeLevels)): ?>
                        <div class="alert alert-warning mb-0">No grade levels found. Students must have a grade_level set.</div>
                    <?php elseif (empty($feeTypes)): ?>
                        <div class="alert alert-warning mb-0">No fee types defined. Please <a href="finance-fee-types.php">seed default fees</a> first.</div>
                    <?php else: ?>
                    <form method="POST" onsubmit="return confirm('This will assign the selected fees to ALL students in the chosen grade level. Continue?');">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Grade Level</label>
                                <select name="grade_level" class="form-select" required>
                                    <option value="">-- Choose grade level --</option>
                                    <?php foreach ($gradeLevels as $gl): ?>
                                        <option value="<?= htmlspecialchars((string)$gl['grade_level']) ?>">
                                            Grade <?= htmlspecialchars((string)$gl['grade_level']) ?> (<?= (int)$gl['student_count'] ?> student<?= (int)$gl['student_count']==1?'':'s' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School Year</label>
                                <input type="text" name="bulk_school_year" class="form-control" value="<?= htmlspecialchars($defaultSY) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Due Date (optional)</label>
                                <input type="date" name="bulk_due_date" class="form-control">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">Select</th>
                                        <th>Fee Type</th>
                                        <th style="width:160px">Amount (₱)</th>
                                        <th style="width:160px">Discount (₱)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feeTypes as $ft): ?>
                                    <tr class="fee-row">
                                        <td>
                                            <input type="checkbox"
                                                   class="form-check-input toggle-bulk-fee"
                                                   name="bulk_fees[<?= $ft['id'] ?>][enabled]"
                                                   value="1"
                                                   data-target="bulk-fee-<?= $ft['id'] ?>">
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($ft['name']) ?></strong>
                                            <?php if (!empty($ft['description'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($ft['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm bulk-fee-<?= $ft['id'] ?>"
                                                   name="bulk_fees[<?= $ft['id'] ?>][amount]"
                                                   value="<?= (float)$ft['amount'] ?>"
                                                   disabled>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control form-control-sm bulk-fee-<?= $ft['id'] ?>"
                                                   name="bulk_fees[<?= $ft['id'] ?>][discount]"
                                                   value="0"
                                                   disabled>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllBulk">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                            <button type="submit" name="assign_by_grade" value="1" class="btn btn-warning">
                                <i class="bi bi-people-fill me-1"></i> Assign to All Students in Grade
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /#tab-bulk -->

        </div><!-- /.tab-content -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Enable/disable amount/discount inputs based on checkbox
document.querySelectorAll('.toggle-fee').forEach(cb => {
    cb.addEventListener('change', function() {
        const target = this.dataset.target;
        document.querySelectorAll('.' + target).forEach(inp => {
            inp.disabled = !this.checked;
        });
    });
});
// Select all shortcut
const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('click', () => {
        document.querySelectorAll('.toggle-fee').forEach(cb => {
            cb.checked = true;
            cb.dispatchEvent(new Event('change'));
        });
    });
}
// Bulk tab toggle + select-all
document.querySelectorAll('.toggle-bulk-fee').forEach(cb => {
    cb.addEventListener('change', function() {
        const target = this.dataset.target;
        document.querySelectorAll('.' + target).forEach(inp => {
            inp.disabled = !this.checked;
        });
    });
});
const selectAllBulk = document.getElementById('selectAllBulk');
if (selectAllBulk) {
    selectAllBulk.addEventListener('click', () => {
        document.querySelectorAll('.toggle-bulk-fee').forEach(cb => {
            cb.checked = true;
            cb.dispatchEvent(new Event('change'));
        });
    });
}
</script>
</body>
</html>