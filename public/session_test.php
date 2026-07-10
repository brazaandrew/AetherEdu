<?php
declare(strict_types=1);

// Enable error reporting to catch issues
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header("Content-Type: text/plain");

require_once __DIR__ . '/../src/Helpers/session.php';

echo "=== AetherEdu Session Diagnostic Tool ===\n\n";

$targetPath = __DIR__ . '/../sessions';
echo "Target sessions directory: " . realpath($targetPath) . "\n";
echo "Directory exists: " . (is_dir($targetPath) ? "Yes" : "No") . "\n";
echo "Directory is writable: " . (is_writable($targetPath) ? "Yes" : "No") . "\n";

startSecureSession();

if (!isset($_SESSION['test_counter'])) {
    $_SESSION['test_counter'] = 1;
    echo "Initial visit. Test counter set to 1.\n";
} else {
    $_SESSION['test_counter']++;
    echo "Subsequent visit. Test counter incremented to: " . $_SESSION['test_counter'] . "\n";
}

echo "Active Session Save Path: " . session_save_path() . "\n";
echo "Active Session ID: " . session_id() . "\n";
echo "Active Session Array: " . print_r($_SESSION, true) . "\n";
echo "\n=== End of Diagnostics ===\n";
