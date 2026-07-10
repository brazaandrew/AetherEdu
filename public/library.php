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

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $result = $libraryController->deleteBook((int)$_GET['delete']);
    if ($result['success']) {
        header('Location: library.php?success=deleted');
        exit;
    } else {
        $error = $result['error'];
    }
}

// Get filters
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (!empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

$books = $libraryController->getBooks($filters);
$categories = $libraryController->getCategories();
$stats = $libraryController->getStatistics();

$pageTitle = 'Library Management';
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
                        <h2><i class="bi bi-book me-2 text-primary"></i>Library Management</h2>
                        <p class="text-muted">Manage book inventory and track availability</p>
                    </div>
                    <a href="library-add.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Add New Book
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php if ($_GET['success'] === 'added'): ?>
                            Book added successfully!
                        <?php elseif ($_GET['success'] === 'updated'): ?>
                            Book updated successfully!
                        <?php elseif ($_GET['success'] === 'deleted'): ?>
                            Book deleted successfully!
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Books</h6>
                                        <h3><?= $stats['total_books'] ?></h3>
                                    </div>
                                    <i class="bi bi-book fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Available</h6>
                                        <h3><?= $stats['available_copies'] ?></h3>
                                    </div>
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Borrowed</h6>
                                        <h3><?= $stats['currently_borrowed'] ?></h3>
                                    </div>
                                    <i class="bi bi-arrow-left-right fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="card-title">Total Copies</h6>
                                        <h3><?= $stats['total_copies'] ?></h3>
                                    </div>
                                    <i class="bi bi-collection fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" name="search" placeholder="Search by title, author, ISBN..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="available" <?= ($_GET['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="unavailable" <?= ($_GET['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                                    <option value="damaged" <?= ($_GET['status'] ?? '') === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                                    <option value="lost" <?= ($_GET['status'] ?? '') === 'lost' ? 'selected' : '' ?>>Lost</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Books Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Book Inventory</h5>
                        <a href="library-borrowings.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-left-right me-1"></i>View Borrowings
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Copies</th>
                                        <th>Available</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($books)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <i class="bi bi-book fs-1 text-muted"></i>
                                                <p class="mt-2 text-muted">No books found</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($books as $book): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($book['isbn'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($book['title']) ?></td>
                                                <td><?= htmlspecialchars($book['author']) ?></td>
                                                <td><?= htmlspecialchars($book['category'] ?? 'Uncategorized') ?></td>
                                                <td><?= $book['total_copies'] ?></td>
                                                <td>
                                                    <span class="badge <?= $book['available_copies'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $book['status'] === 'available' ? 'success' : ($book['status'] === 'damaged' ? 'warning' : 'secondary') ?>">
                                                        <?= ucfirst($book['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($book['shelf_location'] ?? 'N/A') ?></td>
                                                <td>
                                                    <a href="library-edit.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="?delete=<?= $book['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this book?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
