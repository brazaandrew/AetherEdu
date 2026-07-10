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
$categories = $libraryController->getCategories();

// Get book ID
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$bookId) {
    header('Location: library.php');
    exit;
}

$book = $libraryController->getBook($bookId);
if (!$book) {
    header('Location: library.php?error=notfound');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'isbn' => $_POST['isbn'] ?? null,
        'title' => $_POST['title'] ?? '',
        'author' => $_POST['author'] ?? '',
        'publisher' => $_POST['publisher'] ?? null,
        'publication_year' => !empty($_POST['publication_year']) ? (int)$_POST['publication_year'] : null,
        'category' => $_POST['category'] ?? null,
        'description' => $_POST['description'] ?? null,
        'total_copies' => (int)($_POST['total_copies'] ?? 1),
        'available_copies' => (int)($_POST['available_copies'] ?? 0),
        'shelf_location' => $_POST['shelf_location'] ?? null,
        'status' => $_POST['status'] ?? 'available'
    ];

    if (empty($data['title']) || empty($data['author'])) {
        $error = 'Title and Author are required fields.';
    } else {
        $result = $libraryController->updateBook($bookId, $data);
        if ($result['success']) {
            header('Location: library.php?success=updated');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Edit Book';
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
                        <h2><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Book</h2>
                        <p class="text-muted">Update book information</p>
                    </div>
                    <a href="library.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Library
                    </a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Book Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Book Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <!-- ISBN -->
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">ISBN</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>" placeholder="978-3-16-148410-0">
                            </div>

                            <!-- Title -->
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
                            </div>

                            <!-- Author -->
                            <div class="col-md-6">
                                <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
                            </div>

                            <!-- Publisher -->
                            <div class="col-md-6">
                                <label for="publisher" class="form-label">Publisher</label>
                                <input type="text" class="form-control" id="publisher" name="publisher" value="<?= htmlspecialchars($book['publisher'] ?? '') ?>">
                            </div>

                            <!-- Publication Year -->
                            <div class="col-md-3">
                                <label for="publication_year" class="form-label">Publication Year</label>
                                <input type="number" class="form-control" id="publication_year" name="publication_year" min="1800" max="2099" value="<?= htmlspecialchars($book['publication_year'] ?? '') ?>">
                            </div>

                            <!-- Category -->
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" class="form-control" id="category" name="category" list="categoryList" value="<?= htmlspecialchars($book['category'] ?? '') ?>" placeholder="Select or type new">
                                <datalist id="categoryList">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat) ?>">
                                    <?php endforeach; ?>
                                    <option value="Fiction">
                                    <option value="Non-Fiction">
                                    <option value="Science">
                                    <option value="Mathematics">
                                    <option value="History">
                                    <option value="Literature">
                                    <option value="Reference">
                                    <option value="Textbook">
                                </datalist>
                            </div>

                            <!-- Total Copies -->
                            <div class="col-md-3">
                                <label for="total_copies" class="form-label">Total Copies</label>
                                <input type="number" class="form-control" id="total_copies" name="total_copies" min="1" value="<?= $book['total_copies'] ?>" required>
                            </div>

                            <!-- Available Copies -->
                            <div class="col-md-3">
                                <label for="available_copies" class="form-label">Available Copies</label>
                                <input type="number" class="form-control" id="available_copies" name="available_copies" min="0" value="<?= $book['available_copies'] ?>" required>
                            </div>

                            <!-- Shelf Location -->
                            <div class="col-md-6">
                                <label for="shelf_location" class="form-label">Shelf Location</label>
                                <input type="text" class="form-control" id="shelf_location" name="shelf_location" value="<?= htmlspecialchars($book['shelf_location'] ?? '') ?>" placeholder="e.g., A-12-3">
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="available" <?= $book['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="unavailable" <?= $book['status'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                                    <option value="damaged" <?= $book['status'] === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                                    <option value="lost" <?= $book['status'] === 'lost' ? 'selected' : '' ?>>Lost</option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of the book..."><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-2"></i>Update Book
                                    </button>
                                    <a href="library.php" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
