<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/HRController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'hr'])) {
    header('Location: dashboard.php');
    exit;
}

$hrController = new HRController();
$stats = $hrController->getStats();
$employees = $hrController->getAllEmployees();

$message = $_SESSION['hr_message'] ?? '';
unset($_SESSION['hr_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR - Employee 201 Files - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <h2 class="mb-4">Human Resources - Employee 201 Files</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6>Total Employees</h6>
                            <h3><?= $stats['total_employees'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6>201 Files Completed</h6>
                            <h3><?= $stats['files_completed'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6>Teachers</h6>
                            <h3><?= $stats['total_teachers'] ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h6>Documents</h6>
                            <h3><?= $stats['total_documents'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employee List -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Employee List</h5>
                    <input type="text" class="form-control w-25" id="searchEmployee" placeholder="Search employee..." onkeyup="filterEmployees()">
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="employeeTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>201 File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): 
                                    $has201File = $hrController->get201File($emp['id']) !== null;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($emp['empidno']) ?></td>
                                    <td><?= htmlspecialchars($emp['name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($emp['role']) {
                                            'admin' => 'danger',
                                            'teacher' => 'primary',
                                            'registrar' => 'secondary',
                                            'librarian' => 'warning',
                                            'cashier' => 'info',
                                            'nurse' => 'danger',
                                            'hr' => 'dark',
                                            'it_personnel' => 'info',
                                            default => 'secondary'
                                        } ?>">
                                            <?= ucfirst(str_replace('_', ' ', $emp['role'])) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($emp['email']) ?></td>
                                    <td>
                                        <?php if ($has201File): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Complete</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle"></i> Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="employee-201-file.php?employee_id=<?= $emp['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-folder2-open"></i> View 201 File
                                        </a>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterEmployees() {
            const input = document.getElementById('searchEmployee').value.toLowerCase();
            const table = document.getElementById('employeeTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(input)) {
                        found = true;
                        break;
                    }
                }
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>
