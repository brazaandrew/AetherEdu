<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/env.php';
require_once __DIR__ . '/../Helpers/database.php';
require_once __DIR__ . '/../Helpers/session.php';
require_once __DIR__ . '/../Helpers/auth.php';

class AuthMiddleware {
    /**
     * Require authentication for the entire system
     */
    public static function requireAuth(): array {
        return requireLogin();
    }
    
    /**
     * Require specific roles
     */
    public static function requireRoles(array $roles): array {
        return requireRole($roles);
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin(): array {
        return requireAdmin();
    }
    
    /**
     * Require teacher or admin role
     */
    public static function requireTeacher(): array {
        return requireTeacher();
    }
    
    /**
     * Require student role
     */
    public static function requireStudent(): array {
        return requireStudent();
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool {
        return isLoggedIn();
    }
    
    /**
     * Get current user
     */
    public static function getUser(): ?array {
        return getCurrentUser();
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool {
        return hasRole($role);
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole(array $roles): bool {
        return hasAnyRole($roles);
    }
    
    /**
     * Redirect to login if not authenticated
     */
    public static function redirectIfNotAuthenticated(string $redirectUrl = '/login.php'): void {
        if (!self::isAuthenticated()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    /**
     * Redirect to dashboard if already authenticated
     */
    public static function redirectIfAuthenticated(string $redirectUrl = '/dashboard.php'): void {
        if (self::isAuthenticated()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
}