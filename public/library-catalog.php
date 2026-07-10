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

// All users can access the catalog
$libraryController = new LibraryController();

// Get filters
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (!empty($_GET['category'])) {
    $filters['category'] = $_GET['category'];
}

$books = $libraryController->getBooks($filters);
$categories = $libraryController->getCategories();

$pageTitle = 'Library Catalog';
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
                        <h2><i class="bi bi-collection me-2 text-primary"></i>Library Catalog</h2>
                        <p class="text-muted">Browse available books in the library</p>
                    </div>
                    <?php if ($role === 'student'): ?>
                        <a href="my-borrowings.php" class="btn btn-outline-primary">
                            <i class="bi bi-book me-2"></i>My Borrowings
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
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
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Books Grid -->
                <div class="row g-4">
                    <?php if (empty($books)): ?>
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-book fs-1 text-muted"></i>
                            <p class="mt-3 text-muted">No books found matching your search.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="card h-100 book-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-<?= $book['available_copies'] > 0 ? 'success' : 'danger' ?>">
                                                <?= $book['available_copies'] > 0 ? 'Available' : 'Unavailable' ?>
                                            </span>
                                            <?php if ($book['category']): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($book['category']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                                        <p class="card-text text-muted mb-1">
                                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($book['author']) ?>
                                        </p>
                                        
                                        <?php if ($book['publisher']): ?>
                                            <p class="card-text text-muted mb-1">
                                                <small><i class="bi bi-building me-1"></i><?= htmlspecialchars($book['publisher']) ?></small>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <p class="card-text text-muted mb-2">
                                            <small><i class="bi bi-geo-alt me-1"></i>Shelf: <?= htmlspecialchars($book['shelf_location'] ?? 'N/A') ?></small>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <span class="text-muted">
                                                <i class="bi bi-stack me-1"></i>
                                                <?= $book['available_copies'] ?> / <?= $book['total_copies'] ?> copies
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($book['description'])): ?>
                                            <hr>
                                            <p class="card-text small text-muted">
                                                <?= htmlspecialchars(substr($book['description'], 0, 100)) ?><?= strlen($book['description']) > 100 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <?php if ($role === 'student' && $book['available_copies'] > 0): ?>
                                            <button class="btn btn-outline-primary btn-sm w-100" disabled>
                                                <i class="bi bi-bookmark me-1"></i>Ask Librarian to Borrow
                                            </button>
                                        <?php elseif ($role === 'student'): ?>
                                            <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                                                <i class="bi bi-x-circle me-1"></i>Not Available
                                            </button>
                                        <?php else: ?>
                                            <a href="library-borrowings.php" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-arrow-left-right me-1"></i>Manage Borrowing
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
