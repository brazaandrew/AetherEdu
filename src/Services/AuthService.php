<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/env.php';
require_once __DIR__ . '/../Helpers/database.php';
require_once __DIR__ . '/../Helpers/session.php';
require_once __DIR__ . '/../Helpers/auth.php';
require_once __DIR__ . '/../Helpers/audit.php';

class AuthService {
    private PDO $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Authenticate user with email and password
     */
    public function authenticate(string $email, string $password): array {
        try {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                // Log failed login attempt
                $this->logFailedLoginAttempt($email);
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
                'role' => $user['role']
            ];
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['last_activity'] = time();
            
            // Log successful login
            logAuditAction((int)$user['id'], 'login', 'user', (int)$user['id']);
            
            return ['success' => true, 'user' => $_SESSION['user']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Authentication failed'];
        }
    }
    
    /**
     * Register a new user
     */
    public function register(string $empidno, string $name, string $email, string $password, string $role): array {
        // Validate role
        if (!in_array($role, ['admin', 'teacher', 'student', 'it_personnel', 'registrar', 'librarian', 'cashier', 'nurse', 'hr'], true)) {
            return ['success' => false, 'error' => 'Invalid role'];
        }
        
        // Validate password strength
        if (!$this->isPasswordStrong($password)) {
            return ['success' => false, 'error' => 'Password does not meet requirements'];
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare('INSERT INTO users (empidno, name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$empidno, $name, $email, $passwordHash, $role]);
            
            $userId = (int)$this->db->lastInsertId();
            
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
    public function logout(): void {
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
     * Change user password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        // Validate password strength
        if (!$this->isPasswordStrong($newPassword)) {
            return ['success' => false, 'error' => 'New password does not meet requirements'];
        }
        
        try {
            // Verify current password
            $stmt = $this->db->prepare('SELECT password_hash FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();
            
            if (!$userData || !password_verify($currentPassword, $userData['password_hash'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }
            
            // Hash new password
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([$passwordHash, $userId]);
            
            // Log the password change action
            logAuditAction($userId, 'change_password', 'user', $userId);
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to change password'];
        }
    }
    
    /**
     * Reset user password (admin function)
     */
    public function resetPassword(int $userId, string $newPassword): bool {
        // Validate password strength
        if (!$this->isPasswordStrong($newPassword)) {
            return false;
        }
        
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        try {
            $stmt = $this->db->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
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
     * Check if password meets strength requirements
     */
    private function isPasswordStrong(string $password): bool {
        // Load password policy from config
        $config = require __DIR__ . '/../Config/auth.php';
        $policy = $config['password'];
        
        // Check minimum length
        if (strlen($password) < $policy['min_length']) {
            return false;
        }
        
        // Check for uppercase letters
        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            return false;
        }
        
        // Check for lowercase letters
        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            return false;
        }
        
        // Check for numbers
        if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            return false;
        }
        
        // Check for special characters
        if ($policy['require_special_chars'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log failed login attempt
     */
    private function logFailedLoginAttempt(string $email): void {
        try {
            // In a real implementation, you might want to store failed attempts
            // to implement rate limiting or lockout mechanisms
            error_log("Failed login attempt for email: $email");
        } catch (Exception $e) {
            // Silently fail to avoid disrupting the login flow
        }
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser(): ?array {
        return getCurrentUser();
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool {
        return isLoggedIn();
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], $roles, true);
    }
}