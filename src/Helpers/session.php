<?php
declare(strict_types=1);

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    
    // Custom session save path to guarantee write permissions on shared hosts
    $sessionPath = __DIR__ . '/../../sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0777, true);
    }
    if (is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
    
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
    ini_set('session.use_strict_mode', '1');
    session_start();
    
    // Enforce session inactivity timeout
    if (isset($_SESSION['user'])) {
        $lifetime = 3600; // default
        if (function_exists('env')) {
            $lifetime = (int)env('SESSION_LIFETIME', 3600);
        }
        $currentTime = time();
        
        if (isset($_SESSION['last_activity']) && ($currentTime - $_SESSION['last_activity'] > $lifetime)) {
            // Log audit action if audit helper exists
            if (!function_exists('logAuditAction')) {
                @include_once __DIR__ . '/audit.php';
            }
            if (function_exists('logAuditAction')) {
                logAuditAction((int)$_SESSION['user']['id'], 'session_timeout_logout', 'user', (int)$_SESSION['user']['id']);
            }
            
            // Clean up session
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            
            // Check if request is AJAX
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
                      || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
            if ($isAjax) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'Session expired']);
                exit;
            } else {
                header('Location: login.php?expired=1');
                exit;
            }
        }
        
        $_SESSION['last_activity'] = $currentTime;
    } elseif (isset($_SESSION['user']) && !isset($_SESSION['last_activity'])) {
        // Brand new session on a new device — initialize last_activity immediately
        $_SESSION['last_activity'] = time();
    }
}

function currentUser(): array {
    return $_SESSION['user'] ?? [];
}

function requireLogin(): array {
    $u = currentUser();
    if (!isset($u['id'])) {
        // Check if this is an AJAX / API request
        $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                  || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);

        if ($isAjax) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
            exit;
        }

        // For normal page requests: redirect to login with school context
        if (isset($_SESSION['active_school_db'])) {
            // School already selected — go straight to login
            header('Location: login.php');
        } else {
            // No school selected — redirect to school landing page (default: tlca)
            $defaultSchool = 'tlca';
            try {
                $master = db_master();
                $firstSchool = $master->query("SELECT domain FROM schools ORDER BY id ASC LIMIT 1")->fetch();
                if ($firstSchool && !empty($firstSchool['domain'])) {
                    $defaultSchool = $firstSchool['domain'];
                }
            } catch (Exception $e) {
                // Use fallback
            }
            header('Location: ../index.php?school=' . urlencode($defaultSchool));
        }
        exit;
    }
    return $u;
}

function requireRole(array $allowed): array {
    $u = requireLogin();
    if (!in_array($u['role'] ?? '', $allowed, true)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }
    return $u;
}
