<?php
declare(strict_types=1);

// Global authentication check
// Include this file at the top of all pages that require authentication

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';

loadEnv(__DIR__ . '/../../.env');
startSecureSession();

// This will automatically redirect to login if not authenticated
// and can be included at the top of any page that requires login
function requireAuth(): array {
    return requireLogin();
}

// This will redirect authenticated users away from pages they shouldn't access
// (like login page when already logged in)
function redirectIfAuthenticated(string $redirectUrl = '/dashboard.php'): void {
    if (isLoggedIn()) {
        header("Location: $redirectUrl");
        exit;
    }
}

// Get current user information
function getCurrentUserInfo(): ?array {
    return getCurrentUser();
}

// Check if user has specific role
function userHasRole(string $role): bool {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

// Check if user has any of the specified roles
function userHasAnyRole(array $roles): bool {
    $user = getCurrentUser();
    return $user && in_array($user['role'], $roles, true);
}