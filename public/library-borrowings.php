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

// Only admin, teachers, and librarians can access
if (!in_array($role, ['admin', 'teacher', 'librarian'])) {
    header('Location: dashboard.php');
    exit;
}

$libraryController = new LibraryController();

// Handle return action
if (isset($_POST['return_book']) && is_numeric($_POST['borrowing_id'])) {
    $result = $libraryController->returnBook((int)$_POST['borrowing_id'], $_POST['return_notes'] ?? null);
    if ($result['success']) {
        $success = 'Book returned successfully!';
    } else {
        $error = $result['error'];
    }
}

// Handle borrow action
if (isset($_POST['borrow_book'])) {
    $result = $libraryController->borrowBook(
        (int)$_POST['book_id'],
        (int)$_POST['student_id'],
        $_POST['due_date'],
        $_POST['borrow_notes'] ?? null
    );
    if ($result['success']) {
        $success = 'Book borrowed successfully!';
    } else {
        $error = $result['error'];
    }
}

// Get filters
$status = $_GET['status'] ?? 'borrowed';
$borrowings = $libraryController->getAllBorrowings(['status' => $status]);
$overdueBooks = $libraryController->getOverdueBooks();

$pageTitle = 'Library Borrowings';
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
                        <h2><i class="bi bi-arrow-left-right me-2 text-primary"></i>Library Borrowings</h2>
                        <p class="text-muted">Manage book borrowings and returns</p>
                    </div>
                    <a href="library.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Library
                    </a>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Overdue Alert -->
                <?php if (!empty($overdueBooks)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong><?= count($overdueBooks) ?></strong> book(s) are currently overdue!
                    </div>
                <?php endif; ?>

                <!-- Status Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="?status=borrowed" class="btn btn-<?= $status === 'borrowed' ? 'primary' : 'outline-primary' ?>">Currently Borrowed</a>
                            <a href="?status=returned" class="btn btn-<?= $status === 'returned' ? 'primary' : 'outline-primary' ?>">Returned</a>
                            <a href="?status=overdue" class="btn btn-<?= $status === 'overdue' ? 'primary' : 'outline-primary' ?>">Overdue</a>
                        </div>
                    </div>
                </div>

                <!-- Borrowings Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><?= ucfirst($status) ?> Books</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#borrowModal">
                            <i class="bi bi-plus-lg me-1"></i>Borrow Book
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Student</th>
                                        <th>Borrowed Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($borrowings)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                                <p class="mt-2 text-muted">No <?= $status ?> books found</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($borrowings as $borrowing): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($borrowing['book_title']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($borrowing['book_author']) ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($borrowing['student_name']) ?><br>
                                                    <small class="text-muted"><?= htmlspecialchars($borrowing['student_id_number']) ?></small>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($borrowing['borrowed_at'])) ?></td>
                                                <td>
                                                    <?php if (strtotime($borrowing['due_date']) < time() && $borrowing['status'] === 'borrowed'): ?>
                                                        <span class="text-danger fw-bold"><?= date('M j, Y', strtotime($borrowing['due_date'])) ?></span>
                                                    <?php else: ?>
                                                        <?= date('M j, Y', strtotime($borrowing['due_date'])) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $borrowing['status'] === 'borrowed' ? 'warning' : ($borrowing['status'] === 'returned' ? 'success' : 'danger') ?>">
                                                        <?= ucfirst($borrowing['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($borrowing['status'] === 'borrowed'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="borrowing_id" value="<?= $borrowing['id'] ?>">
                                                            <button type="submit" name="return_book" class="btn btn-sm btn-success" onclick="return confirm('Confirm book return?')">
                                                                <i class="bi bi-check-lg"></i> Return
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrow Modal -->
    <div class="modal fade" id="borrowModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Borrow Book</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Book ID</label>
                            <input type="number" class="form-control" name="book_id" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <input type="number" class="form-control" name="student_id" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="borrow_notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="borrow_book" class="btn btn-primary">Borrow Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
