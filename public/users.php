<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireRole(['admin', 'it_personnel']);

$message = '';
$error = '';
$success = isset($_GET['updated']) ? 'User updated successfully.' : null;

// Function to generate auto-incrementing IDs
function generateAutoId(string $role): string {
    // Get the last ID for this role
    $prefix = '';
    switch ($role) {
        case 'teacher':
            $prefix = 'T';
            break;
        case 'student':
            $prefix = 'S';
            break;
        case 'admin':
            $prefix = 'ADMIN';
            break;
        case 'it_personnel':
            $prefix = 'IT';
            break;
        case 'registrar':
            $prefix = 'REG';
            break;
        case 'librarian':
            $prefix = 'LIB';
            break;
        case 'cashier':
            $prefix = 'CASH';
            break;
        case 'nurse':
            $prefix = 'NUR';
            break;
        case 'hr':
            $prefix = 'HR';
            break;
        default:
            $prefix = 'U';
    }
    
    // Find the next available number
    $stmt = db()->prepare("SELECT empidno FROM users WHERE role = ? AND empidno REGEXP ? ORDER BY CAST(SUBSTRING(empidno, ?) AS UNSIGNED) DESC LIMIT 1");
    $stmt->execute([$role, "^{$prefix}[0-9]+$", strlen($prefix) + 1]);
    $lastUser = $stmt->fetch();
    
    if ($lastUser) {
        $lastNumber = (int)substr($lastUser['empidno'], strlen($prefix));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }
    
    // Format with leading zeros (e.g., 001, 002, etc.)
    return $prefix . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
}

// Handle user creation with image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    requireCsrf();
    
    $empidno = trim($_POST['empidno'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $gradeLevel = trim($_POST['grade_level'] ?? '');
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $fileType = $_FILES['image']['type'];
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        
        if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) { // 5MB max
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'user_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
            $uploadPath = __DIR__ . '/assets/images/' . $newFileName;
            
            // Create directory if it doesn't exist
            $uploadDir = dirname($uploadPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($fileTmpPath, $uploadPath)) {
                $imagePath = 'assets/images/' . $newFileName;
            } else {
                $error = 'Failed to upload image file';
            }
        } else {
            $error = 'Invalid image file. Only JPG, PNG, GIF allowed up to 5MB.';
        }
    }
    
    // Validate required fields (ID is now optional)
    if (!$name || !$email || !$password || !$role) {
        $error = 'All required fields must be filled';
    } 
    // For students, grade level is also required
    else if ($role === 'student' && !$gradeLevel) {
        $error = 'Grade level is required for student accounts';
    } else {
        // Always auto-generate ID regardless of input
        $empidno = generateAutoId($role);
        
        $result = registerUser($empidno, $name, $email, $password, $role, $gradeLevel, $imagePath);
        if ($result['success']) {
            $message = 'User created successfully with ID: ' . $empidno;
            saveAudit($user['id'], 'create', 'user', $result['user_id'], compact('empidno', 'name', 'email', 'role', 'gradeLevel', 'imagePath'));
        } else {
            $error = $result['error'];
        }
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    requireCsrf();
    
    $userId = (int)($_POST['user_id'] ?? 0);
    $newPassword = $_POST['new_password'] ?? '';
    
    if ($userId && $newPassword) {
        if (resetUserPassword($userId, $newPassword)) {
            $message = 'Password reset successfully!';
            saveAudit($user['id'], 'reset_password', 'user', $userId, []);
        } else {
            $error = 'Failed to reset password';
        }
    }
}

// Handle user archiving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_user'])) {
    requireCsrf();
    
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($userId && $userId !== $user['id']) {
        if (archiveUser($userId)) {
            $message = 'User archived successfully!';
            saveAudit($user['id'], 'archive', 'user', $userId, []);
        } else {
            $error = 'Failed to archive user';
        }
    }
}

// Handle user unarchiving
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unarchive_user'])) {
    requireCsrf();
    
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($userId) {
        if (unarchiveUser($userId)) {
            $message = 'User unarchived successfully!';
            saveAudit($user['id'], 'unarchive', 'user', $userId, []);
        } else {
            $error = 'Failed to unarchive user';
        }
    }
}

// Check if the archived column exists
try {
    $columnCheck = db()->query("SHOW COLUMNS FROM users LIKE 'archived'");
    $archivedColumnExists = $columnCheck->rowCount() > 0;
} catch (PDOException $e) {
    $archivedColumnExists = false;
}

// Pagination and filtering setup
$perPageOptions = [10, 20, 50];
$perPage = (int) ($_GET['per_page'] ?? 20);
if (!in_array($perPage, $perPageOptions, true)) {
    $perPage = 20;
}

$searchQuery = trim((string) ($_GET['q'] ?? ''));
$roleFilter = trim((string) ($_GET['role'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? 'active')); // active, archived, all

// Build WHERE conditions
$where = [];
$params = [];

// Status filter
if ($archivedColumnExists) {
    if ($statusFilter === 'active') {
        $where[] = 'archived = 0';
    } elseif ($statusFilter === 'archived') {
        $where[] = 'archived = 1';
    }
    // 'all' shows both active and archived
} else {
    // If archived column doesn't exist, treat all as active
    if ($statusFilter === 'archived') {
        $where[] = '1 = 0'; // No results for archived filter
    }
}

// Search filter
if ($searchQuery !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR empidno LIKE ?)';
    $like = '%' . $searchQuery . '%';
    $params = array_merge($params, [$like, $like, $like]);
}

// Role filter
if ($roleFilter !== '') {
    $where[] = 'role = ?';
    $params[] = $roleFilter;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total users for pagination
$countStmt = db()->prepare("SELECT COUNT(*) FROM users {$whereSql}");
$countStmt->execute($params);
$totalUsers = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalUsers / $perPage));
$page = max(1, (int) ($_GET['page'] ?? 1));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Fetch users with pagination
$listStmt = db()->prepare(
    "SELECT id, empidno, name, email, role, grade_level, image, created_at" . 
    ($archivedColumnExists ? ', archived' : ', 0 as archived') . 
    " FROM users {$whereSql} ORDER BY role, name LIMIT {$perPage} OFFSET {$offset}"
);
$listStmt->execute($params);
$users = $listStmt->fetchAll();

$rangeStart = $totalUsers > 0 ? $offset + 1 : 0;
$rangeEnd = min($offset + count($users), $totalUsers);

// URL helper function for pagination and filtering
$userListUrl = static function (array $overrides = []) use ($searchQuery, $roleFilter, $statusFilter, $page, $perPage): string {
    $params = array_filter([
        'page' => $page,
        'q' => $searchQuery,
        'role' => $roleFilter,
        'status' => $statusFilter !== 'active' ? $statusFilter : null,
        'per_page' => $perPage !== 20 ? $perPage : null,
    ], static fn ($v) => $v !== '' && $v !== null);
    foreach ($overrides as $key => $value) {
        if ($value === '' || $value === null || ($key === 'page' && (int) $value <= 1)) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    $qs = http_build_query($params);
    return 'users.php' . ($qs !== '' ? '?' . $qs : '');
};

// Pagination pages calculation
$paginationPages = [];
if ($totalPages > 1) {
    $windowStart = max(1, $page - 2);
    $windowEnd = min($totalPages, $page + 2);
    if ($windowStart > 1) {
        $paginationPages[] = 1;
        if ($windowStart > 2) {
            $paginationPages[] = '…';
        }
    }
    for ($p = $windowStart; $p <= $windowEnd; $p++) {
        $paginationPages[] = $p;
    }
    if ($windowEnd < $totalPages) {
        if ($windowEnd < $totalPages - 1) {
            $paginationPages[] = '…';
        }
        $paginationPages[] = $totalPages;
    }
}

// Count users by role for filter dropdown
$roleCounts = [];
$roleCountSql = "SELECT role, COUNT(*) as count FROM users";
if ($archivedColumnExists && $statusFilter !== 'all') {
    $archiveCondition = $statusFilter === 'active' ? 0 : 1;
    $roleCountSql .= " WHERE archived = {$archiveCondition}";
}
$roleCountSql .= " GROUP BY role ORDER BY role";
$roleStmt = db()->query($roleCountSql);
foreach ($roleStmt->fetchAll() as $roleRow) {
    $roleCounts[$roleRow['role']] = (int) $roleRow['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        .user-list-minimal { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .user-list-minimal .ul-action-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .user-list-minimal .ul-action-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
        }
        .user-list-minimal .ul-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            padding: 0 0.15rem;
        }
        .user-list-minimal .ul-filter-count {
            font-size: 0.875rem;
            color: #718096;
            flex-shrink: 0;
            white-space: nowrap;
            margin: 0;
        }
        .user-list-minimal .ul-filter-stack {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            flex: 1 1 auto;
            justify-content: flex-end;
            margin-left: auto;
            min-width: 0;
        }
        .user-list-minimal .ul-filter-stack .form-control,
        .user-list-minimal .ul-filter-stack .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #2d3748;
            box-shadow: none;
            height: 2.25rem;
        }
        .user-list-minimal .ul-filter-stack #searchUsers {
            flex: 1 1 10rem;
            min-width: 10rem;
            max-width: 16rem;
            width: auto;
        }
        .user-list-minimal .ul-filter-stack #filterRole,
        .user-list-minimal .ul-filter-stack #filterStatus {
            flex: 0 1 auto;
            min-width: 9rem;
            max-width: 11rem;
            width: auto;
        }
        .user-list-minimal .ul-filter-stack #perPage {
            flex: 0 0 auto;
            width: auto;
            min-width: 7.5rem;
        }
        .user-list-minimal #usersTable {
            margin: 0;
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9375rem;
            color: #2d3748;
        }
        .user-list-minimal #usersTable thead th {
            font-weight: 600;
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4a5568;
            background: #f1f3f5;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            text-align: left;
            vertical-align: middle;
        }
        .user-list-minimal #usersTable tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }
        .user-list-minimal #usersTable tbody tr:last-child td { border-bottom: none; }
        .user-list-minimal #usersTable tbody tr:hover { background: #fafbfc; }
        .user-list-minimal .ul-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1rem;
            padding: 1rem;
            background: #fff;
            border-top: 1px solid #edf2f7;
        }
        .user-list-minimal .ul-pagination-info {
            font-size: 0.875rem;
            color: #718096;
        }
        .user-list-minimal .ul-pagination-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
        }
        .user-list-minimal .ul-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            font-size: 0.875rem;
            color: #4a5568;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none;
            line-height: 1;
        }
        .user-list-minimal .ul-page-btn:hover:not(.disabled):not(.active) {
            background: #f7fafc;
            border-color: #cbd5e0;
            color: #2d3748;
        }
        .user-list-minimal .ul-page-btn.active {
            background: #3182ce;
            border-color: #3182ce;
            color: #fff;
            font-weight: 600;
        }
        .user-list-minimal .ul-page-btn.disabled {
            opacity: 0.45;
            pointer-events: none;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 500;
        }
        .archived-row {
            opacity: 0.7;
            background-color: #f8f9fa !important;
        }
</style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'User Management'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4 user-list-minimal">
            <?php if ($success): ?>
            <div class="alert alert-success py-2 px-3 small mb-3"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
            <div class="alert alert-success py-2 px-3 small mb-3"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="ul-action-card">
                <h2 class="ul-action-title">User Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="bi bi-person-plus me-2"></i>Create User
                </button>
            </div>

            <form method="get" class="ul-filter-bar" id="userFilterForm">
                <span class="ul-filter-count">
                    <?php if ($totalUsers > 0): ?>
                    Showing <?= $rangeStart ?>–<?= $rangeEnd ?> of <?= $totalUsers ?> user<?= $totalUsers === 1 ? '' : 's' ?>
                    <?php else: ?>
                    0 users
                    <?php endif; ?>
                </span>
                <div class="ul-filter-stack">
                    <input type="text" name="q" id="searchUsers" class="form-control" placeholder="Search users..." aria-label="Search users" value="<?= htmlspecialchars($searchQuery) ?>">
                    <select name="role" id="filterRole" class="form-select" aria-label="Filter by role">
                        <option value="">All roles</option>
                        <?php foreach (array_keys($roleCounts) as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"<?= $roleFilter === $role ? ' selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $role)) ?> (<?= $roleCounts[$role] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($archivedColumnExists): ?>
                    <select name="status" id="filterStatus" class="form-select" aria-label="Filter by status">
                        <option value="active"<?= $statusFilter === 'active' ? ' selected' : '' ?>>Active only</option>
                        <option value="archived"<?= $statusFilter === 'archived' ? ' selected' : '' ?>>Archived only</option>
                        <option value="all"<?= $statusFilter === 'all' ? ' selected' : '' ?>>All users</option>
                    </select>
                    <?php endif; ?>
                    <select name="per_page" id="perPage" class="form-select" aria-label="Rows per page">
                        <?php foreach ($perPageOptions as $option): ?>
                        <option value="<?= $option ?>"<?= $perPage === $option ? ' selected' : '' ?>><?= $option ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="mb-0" id="usersTable">
                        <thead>
                            <tr>
                                <th>Avatar</th>
                                <th>ID</th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Grade Level</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr<?= isset($u['archived']) && $u['archived'] ? ' class="archived-row"' : '' ?>>
                                <td>
                                    <img src="<?= htmlspecialchars($u['image'] ?: 'assets/images/default-avatar.php') ?>" 
                                         alt="Avatar" 
                                         class="user-avatar"
                                         onerror="this.onerror=null; this.src='assets/images/default-avatar.php';">
                                </td>
                                <td><?= $u['id'] ?></td>
                                <td><code><?= htmlspecialchars($u['empidno'] ?: 'N/A') ?></code></td>
                                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php 
                                    $badges = [
                                        'admin' => 'danger',
                                        'teacher' => 'primary',
                                        'student' => 'success',
                                        'it_personnel' => 'info',
                                        'registrar' => 'secondary',
                                        'librarian' => 'warning',
                                        'cashier' => 'info',
                                        'nurse' => 'danger',
                                        'hr' => 'dark'
                                    ];
                                    $badgeClass = $badges[$u['role']] ?? 'secondary';
                                    $roleName = match($u['role']) {
                                        'it_personnel' => 'IT Personnel',
                                        default => ucfirst(str_replace('_', ' ', $u['role']))
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?> role-badge"><?= $roleName ?></span>
                                </td>
                                <td><?= htmlspecialchars($u['grade_level'] ?: 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" 
                                                onclick="showResetPassword(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>')"
                                                title="Reset Password">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <?php if ($u['id'] !== $user['id']): ?>
                                            <?php if (isset($u['archived']) && $u['archived']): ?>
                                            <button class="btn btn-outline-success" 
                                                    onclick="confirmUnarchive(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>')"
                                                    title="Unarchive User">
                                                <i class="bi bi-box-arrow-in-down-left"></i>
                                            </button>
                                            <?php else: ?>
                                            <button class="btn btn-outline-warning" 
                                                    onclick="confirmArchive(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>')"
                                                    title="Archive User">
                                                <i class="bi bi-archive"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($users)): ?>
                            <tr class="empty-row">
                                <td colspan="9" class="text-center text-muted py-4">No users found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalUsers > 0): ?>
                <nav class="ul-pagination" aria-label="User list pagination">
                    <span class="ul-pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($totalPages > 1): ?>
                    <div class="ul-pagination-nav">
                        <a href="<?= htmlspecialchars($userListUrl(['page' => max(1, $page - 1)])) ?>" class="ul-page-btn<?= $page <= 1 ? ' disabled' : '' ?>" aria-label="Previous page">&lsaquo;</a>
                        <?php foreach ($paginationPages as $p): ?>
                            <?php if ($p === '…'): ?>
                            <span class="ul-page-ellipsis" aria-hidden="true">…</span>
                            <?php else: ?>
                            <a href="<?= htmlspecialchars($userListUrl(['page' => $p])) ?>" class="ul-page-btn<?= (int) $p === $page ? ' active' : '' ?>"><?= (int) $p ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="<?= htmlspecialchars($userListUrl(['page' => min($totalPages, $page + 1)])) ?>" class="ul-page-btn<?= $page >= $totalPages ? ' disabled' : '' ?>" aria-label="Next page">&rsaquo;</a>
                    </div>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Create New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" id="createUserForm" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required onchange="updateIdPlaceholder()">
                                <option value="">Select Role</option>
                                <?php if ($user['role'] === 'admin'): ?>
                                <option value="admin">Admin</option>
                                <option value="it_personnel">IT Personnel</option>
                                <option value="registrar">Registrar</option>
                                <option value="librarian">Librarian</option>
                                <option value="cashier">Cashier</option>
                                <option value="nurse">Nurse</option>
                                <option value="hr">HR</option>
                                <?php endif; ?>
                                <option value="teacher">Teacher</option>
                                <option value="student">Student</option>
                            </select>
                        </div>
                        <div class="mb-3" id="gradeLevelField" style="display: none;">
                            <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="grade_level" name="grade_level">
                                <option value="">Select Grade Level</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 9">Grade 9</option>
                                <option value="Grade 10">Grade 10</option>
                                <option value="Grade 11">Grade 11</option>
                                <option value="Grade 12">Grade 12</option>
                            </select>
                            <small class="text-muted">Required for student accounts</small>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">JPG, PNG, GIF up to 5MB</small>
                        </div>
                        <div class="mb-3" id="imagePreviewContainer" style="display: none;">
                            <label class="form-label">Image Preview</label>
                            <div class="d-flex justify-content-center">
                                <img id="imagePreview" src="" alt="Preview" class="rounded-circle" style="max-width: 100px; max-height: 100px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="empidno" class="form-label">
                                <span id="empidno_label">Employee/Student ID</span>
                            </label>
                            <input type="text" class="form-control" id="empidno" name="empidno">
                            <small class="text-muted" id="empidno_hint">
                                Unique identifier. Leave blank to auto-generate (e.g., T001 for teacher, S001 for student, ADMIN001 for admin)
                            </small>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Juan Dela Cruz">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="user@example.com">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="alert alert-info mb-0">
                            <strong><i class="bi bi-info-circle me-2"></i>Quick Tips:</strong>
                            <ul class="mb-0 mt-2">
                                <li>ID will be auto-generated if left blank</li>
                                <li>Grade level is required for students</li>
                                <li>Profile image is optional (JPG, PNG, GIF up to 5MB)</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="create_user" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" id="reset_user_id" name="user_id">
                    <div class="modal-body">
                        <p>Reset password for: <strong id="reset_user_name"></strong></p>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reset_password" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="archiveUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Archive</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" id="archive_user_id" name="user_id">
                    <div class="modal-body">
                        <p>Are you sure you want to archive user: <strong id="archive_user_name"></strong>?</p>
                        <p class="text-warning"><strong>The user will be deactivated but their data will be preserved.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="archive_user" class="btn btn-warning">
                            <i class="bi bi-archive me-2"></i>Archive User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Unarchive Confirmation Modal -->
    <div class="modal fade" id="unarchiveUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Unarchive</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" id="unarchive_user_id" name="user_id">
                    <div class="modal-body">
                        <p>Are you sure you want to unarchive user: <strong id="unarchive_user_name"></strong>?</p>
                        <p class="text-success"><strong>The user will be reactivated and able to log in again.</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="unarchive_user" class="btn btn-success">
                            <i class="bi bi-box-arrow-in-down-left me-2"></i>Unarchive User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateIdPlaceholder() {
            const role = document.getElementById('role').value;
            const empidnoInput = document.getElementById('empidno');
            const empidnoLabel = document.getElementById('empidno_label');
            const empidnoHint = document.getElementById('empidno_hint');
            const gradeLevelField = document.getElementById('gradeLevelField');
            
            // Show/hide grade level field for students
            if (role === 'student') {
                gradeLevelField.style.display = 'block';
                empidnoLabel.textContent = 'Student ID (Optional)';
                empidnoInput.placeholder = 'S001, S002, etc. (Leave blank to auto-generate)';
                empidnoHint.textContent = 'Leave blank to auto-generate student ID (e.g., S001, S002, etc.)';
            } else {
                gradeLevelField.style.display = 'none';
                if (role === 'teacher') {
                    empidnoLabel.textContent = 'Teacher ID (Optional)';
                    empidnoInput.placeholder = 'T001, T002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate teacher ID (e.g., T001, T002, etc.)';
                } else if (role === 'admin') {
                    empidnoLabel.textContent = 'Admin ID (Optional)';
                    empidnoInput.placeholder = 'ADMIN001, ADMIN002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate admin ID (e.g., ADMIN001, ADMIN002, etc.)';
                } else if (role === 'registrar') {
                    empidnoLabel.textContent = 'Registrar ID (Optional)';
                    empidnoInput.placeholder = 'REG001, REG002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate registrar ID (e.g., REG001, REG002, etc.)';
                } else if (role === 'librarian') {
                    empidnoLabel.textContent = 'Librarian ID (Optional)';
                    empidnoInput.placeholder = 'LIB001, LIB002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate librarian ID (e.g., LIB001, LIB002, etc.)';
                } else if (role === 'cashier') {
                    empidnoLabel.textContent = 'Cashier ID (Optional)';
                    empidnoInput.placeholder = 'CASH001, CASH002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate cashier ID (e.g., CASH001, CASH002, etc.)';
                } else if (role === 'nurse') {
                    empidnoLabel.textContent = 'Nurse ID (Optional)';
                    empidnoInput.placeholder = 'NUR001, NUR002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate nurse ID (e.g., NUR001, NUR002, etc.)';
                } else if (role === 'hr') {
                    empidnoLabel.textContent = 'HR ID (Optional)';
                    empidnoInput.placeholder = 'HR001, HR002, etc. (Leave blank to auto-generate)';
                    empidnoHint.textContent = 'Leave blank to auto-generate HR ID (e.g., HR001, HR002, etc.)';
                } else {
                    empidnoLabel.textContent = 'Employee/Student ID (Optional)';
                    empidnoInput.placeholder = 'Leave blank to auto-generate';
                    empidnoHint.textContent = 'Leave blank to auto-generate ID based on role';
                }
            }
        }
        
        function showResetPassword(userId, userName) {
            document.getElementById('reset_user_id').value = userId;
            document.getElementById('reset_user_name').textContent = userName;
            new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
        }
        
        function confirmArchive(userId, userName) {
            document.getElementById('archive_user_id').value = userId;
            document.getElementById('archive_user_name').textContent = userName;
            new bootstrap.Modal(document.getElementById('archiveUserModal')).show();
        }
        
        function confirmUnarchive(userId, userName) {
            document.getElementById('unarchive_user_id').value = userId;
            document.getElementById('unarchive_user_name').textContent = userName;
            new bootstrap.Modal(document.getElementById('unarchiveUserModal')).show();
        }
        
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    previewContainer.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });
        
        // Auto-submit filter form when filters change
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('userFilterForm');
            const searchInput = document.getElementById('searchUsers');
            const roleSelect = document.getElementById('filterRole');
            const statusSelect = document.getElementById('filterStatus');
            const perPageSelect = document.getElementById('perPage');
            
            // Debounce function for search input
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = function() {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            
            // Auto-submit form when search changes (with debounce)
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    filterForm.submit();
                }, 500));
            }
            
            // Auto-submit form when selects change
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
            
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
    </script>
</body>
</html>