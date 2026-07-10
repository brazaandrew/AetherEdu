<?php
declare(strict_types=1);

require_once __DIR__ . '/audit.php';

// Authentication Helper Functions
// Note: startSecureSession(), requireLogin(), and requireRole() are in session.php

/**
 * Login user with email and password
 */
function loginUser(string $email, string $password): array {
    require_once __DIR__ . '/database.php';
    
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid email or password'];
    }
    
    if (isset($user['archived']) && (int)$user['archived'] === 1) {
        return ['success' => false, 'error' => 'Your account has been archived. Login is denied.'];
    }
    
    // Set session data
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'empidno' => $user['empidno'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'image' => $user['image'] ?? null
    ];
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['last_activity'] = time();
    
    // Log the login action
    logAuditAction((int)$user['id'], 'login', 'user', (int)$user['id']);
    
    return ['success' => true, 'user' => $_SESSION['user']];
}

/**
 * Register new user (Admin only)
 */
function registerUser(string $empidno, string $name, string $email, string $password, string $role, string $gradeLevel = '', string $imagePath = null): array {
    require_once __DIR__ . '/database.php';
    
    // Validate role
    if (!in_array($role, ['admin', 'teacher', 'student', 'it_personnel', 'registrar', 'librarian', 'cashier', 'nurse', 'hr'], true)) {
        return ['success' => false, 'error' => 'Invalid role'];
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // For students, include grade_level in the insert
        if ($role === 'student' && !empty($gradeLevel)) {
            if ($imagePath !== null) {
                $stmt = db()->prepare('INSERT INTO users (empidno, name, email, password_hash, role, grade_level, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$empidno, $name, $email, $passwordHash, $role, $gradeLevel, $imagePath]);
            } else {
                $stmt = db()->prepare('INSERT INTO users (empidno, name, email, password_hash, role, grade_level, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$empidno, $name, $email, $passwordHash, $role, $gradeLevel]);
            }
        } else {
            if ($imagePath !== null) {
                $stmt = db()->prepare('INSERT INTO users (empidno, name, email, password_hash, role, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$empidno, $name, $email, $passwordHash, $role, $imagePath]);
            } else {
                $stmt = db()->prepare('INSERT INTO users (empidno, name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$empidno, $name, $email, $passwordHash, $role]);
            }
        }
        
        $userId = (int)db()->lastInsertId();
        
        // Log the registration action
        $currentUser = getCurrentUser();
        if ($currentUser) {
            logAuditAction($currentUser['id'], 'register_user', 'user', $userId);
        }
        
        return ['success' => true, 'user_id' => $userId];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            return ['success' => false, 'error' => 'Email or EMPIDNO already exists'];
        }
        return ['success' => false, 'error' => 'Failed to create user'];
    }
}

/**
 * Logout user
 */
function logoutUser(): void {
    // Log the logout action
    $user = getCurrentUser();
    if ($user) {
        logAuditAction($user['id'], 'logout', 'user', $user['id']);
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Get current logged-in user
 */
function getCurrentUser(): ?array {
    $sessionUser = $_SESSION['user'] ?? null;
    
    // If user exists in session but doesn't have image field, fetch complete user data from database
    if ($sessionUser && !array_key_exists('image', $sessionUser)) {
        require_once __DIR__ . '/database.php';
        
        $stmt = db()->prepare('SELECT id, empidno, name, email, role, image FROM users WHERE id = ?');
        $stmt->execute([$sessionUser['id']]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Update session with complete user data
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'empidno' => $user['empidno'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'image' => $user['image'] ?? null
            ];
            return $_SESSION['user'];
        }
    }
    
    return $sessionUser;
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

/**
 * Check if user has specific role
 */
function hasRole(string $role): bool {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

/**
 * Check if user has any of the specified roles
 */
function hasAnyRole(array $roles): bool {
    $user = getCurrentUser();
    return $user && in_array($user['role'], $roles, true);
}

/**
 * Require specific role(s) - wrapper for requireRole() in session.php
 */
function requireAdmin(): array {
    return requireRole(['admin']);
}

/**
 * Require teacher or admin role
 */
function requireTeacher(): array {
    return requireRole(['admin', 'teacher']);
}

/**
 * Require student role
 */
function requireStudent(): array {
    return requireRole(['student']);
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token HTML input
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Require CSRF token in POST requests
 */
function requireCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCsrfToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}

/**
 * Reset user password (Admin only)
 */
function resetUserPassword(int $userId, string $newPassword): bool {
    require_once __DIR__ . '/database.php';
    
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    try {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $result = $stmt->execute([$passwordHash, $userId]);
        
        // Log the password reset action
        $currentUser = getCurrentUser();
        if ($currentUser && $result) {
            logAuditAction($currentUser['id'], 'reset_password', 'user', $userId);
        }
        
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update user information
 */
function updateUser(int $userId, array $data): bool {
    require_once __DIR__ . '/database.php';
    
    $allowed = ['empidno', 'name', 'email', 'role', 'grade_level', 'image', 'archived'];
    $updates = [];
    $params = [];
    
    foreach ($data as $key => $value) {
        if (in_array($key, $allowed, true)) {
            $updates[] = "$key = ?";
            $params[] = $value;
        }
    }
    
    if (empty($updates)) return false;
    
    $params[] = $userId;
    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
    
    try {
        $stmt = db()->prepare($sql);
        $result = $stmt->execute($params);
        
        // Log the update action
        $currentUser = getCurrentUser();
        if ($currentUser && $result) {
            logAuditAction($currentUser['id'], 'update_user', 'user', $userId);
        }
        
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete user
 */
function deleteUser(int $userId): bool {
    require_once __DIR__ . '/database.php';
    
    try {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $result = $stmt->execute([$userId]);
        
        // Log the delete action
        $currentUser = getCurrentUser();
        if ($currentUser && $result) {
            logAuditAction($currentUser['id'], 'delete_user', 'user', $userId);
        }
        
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Archive user (set archived = 1 instead of deleting)
 */
function archiveUser(int $userId): bool {
    require_once __DIR__ . '/database.php';
    
    try {
        $stmt = db()->prepare('UPDATE users SET archived = 1 WHERE id = ?');
        $result = $stmt->execute([$userId]);
        
        // Log the archive action
        $currentUser = getCurrentUser();
        if ($currentUser && $result) {
            logAuditAction($currentUser['id'], 'archive_user', 'user', $userId);
        }
        
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Unarchive user (set archived = 0)
 */
function unarchiveUser(int $userId): bool {
    require_once __DIR__ . '/database.php';
    
    try {
        $stmt = db()->prepare('UPDATE users SET archived = 0 WHERE id = ?');
        $result = $stmt->execute([$userId]);
        
        // Log the unarchive action
        $currentUser = getCurrentUser();
        if ($currentUser && $result) {
            logAuditAction($currentUser['id'], 'unarchive_user', 'user', $userId);
        }
        
        return $result;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Change user's own password
 */
function changePassword(string $currentPassword, string $newPassword): array {
    require_once __DIR__ . '/database.php';
    
    $user = getCurrentUser();
    if (!$user) {
        return ['success' => false, 'error' => 'Not logged in'];
    }
    
    // Verify current password
    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch();
    
    if (!$userData || !password_verify($currentPassword, $userData['password_hash'])) {
        return ['success' => false, 'error' => 'Current password is incorrect'];
    }
    
    // Hash new password
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    try {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $user['id']]);
        
        // Log the password change action
        logAuditAction($user['id'], 'change_password', 'user', $user['id']);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Failed to change password'];
    }
}
