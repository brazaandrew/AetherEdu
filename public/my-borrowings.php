<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/LibraryController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];
$userId = $user['id'];

// Only students can access
if ($role !== 'student') {
    header('Location: library-catalog.php');
    exit;
}

$libraryController = new LibraryController();

// Get student's borrowings
$currentBorrowings = $libraryController->getStudentBorrowings($userId, 'borrowed');
$borrowingHistory = $libraryController->getStudentBorrowingHistory($userId);

$pageTitle = 'My Borrowings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/topbar.php'; ?>
            
            <div class="container-fluid py-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="bi bi-book me-2 text-primary"></i>My Borrowings</h2>
                        <p class="text-muted">View your borrowed books and history</p>
                    </div>
                    <a href="library-catalog.php" class="btn btn-outline-primary">
                        <i class="bi bi-collection me-2"></i>Browse Catalog
                    </a>
                </div>

                <!-- Current Borrowings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Currently Borrowed</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($currentBorrowings)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="mt-3 text-muted">You don't have any borrowed books.</p>
                                <a href="library-catalog.php" class="btn btn-primary">Browse Library</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Book</th>
                                            <th>Borrowed Date</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($currentBorrowings as $borrowing): 
                                            $isOverdue = strtotime($borrowing['due_date']) < time();
                                        ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($borrowing['book_title']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($borrowing['book_author']) ?></small>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($borrowing['borrowed_at'])) ?></td>
                                                <td>
                                                    <?php if ($isOverdue): ?>
                                                        <span class="text-danger fw-bold">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            <?= date('M j, Y', strtotime($borrowing['due_date'])) ?>
                                                        </span><br>
                                                        <small class="text-danger">Overdue!</small>
                                                    <?php else: ?>
                                                        <?= date('M j, Y', strtotime($borrowing['due_date'])) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $isOverdue ? 'danger' : 'warning' ?>">
                                                        <?= $isOverdue ? 'Overdue' : 'Borrowed' ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($borrowing['shelf_location'] ?? 'N/A') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Borrowing History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Borrowing History</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($borrowingHistory)): ?>
                            <div class="text-center py-4">
                                <p class="text-muted mb-0">No borrowing history yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Book</th>
                                            <th>Borrowed Date</th>
                                            <th>Due Date</th>
                                            <th>Returned Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($borrowingHistory as $history): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($history['book_title']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($history['book_author']) ?></small>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($history['borrowed_at'])) ?></td>
                                                <td><?= date('M j, Y', strtotime($history['due_date'])) ?></td>
                                                <td>
                                                    <?php if ($history['returned_at']): ?>
                                                        <?= date('M j, Y', strtotime($history['returned_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $history['status'] === 'returned' ? 'success' : ($history['status'] === 'overdue' ? 'danger' : 'warning') ?>">
                                                        <?= ucfirst($history['status']) ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
