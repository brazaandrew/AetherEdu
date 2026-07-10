<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_school_db'] = 'neondb'; // Simulating TLCA selection
$_SESSION['active_school_name'] = 'The Light Christian Academy';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header("Content-Type: text/plain");

$start = microtime(true);

require_once __DIR__ . '/../src/Helpers/env.php';
loadEnv(__DIR__ . '/../.env');

$envTime = microtime(true) - $start;
echo "1. Env loading time: " . number_format($envTime * 1000, 2) . " ms\n";

require_once __DIR__ . '/../src/Helpers/database.php';

$initDbStart = microtime(true);
$pdo = db();
$dbConnectTime = microtime(true) - $initDbStart;
echo "2. Database db() connection time: " . number_format($dbConnectTime * 1000, 2) . " ms\n";

$checkHealStart = microtime(true);
$checkHeal = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'database_healed'")->fetchColumn();
echo "3. Query 'database_healed' row time: " . number_format((microtime(true) - $checkHealStart) * 1000, 2) . " ms (Value: " . ($checkHeal ?: "null") . ")\n";

$queryStart = microtime(true);
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll();
$queryTime = microtime(true) - $queryStart;
echo "4. Query all settings time: " . number_format($queryTime * 1000, 2) . " ms (Found: " . count($settings) . " rows)\n";

$totalBackendTime = microtime(true) - $start;
echo "\nTotal backend processing time: " . number_format($totalBackendTime * 1000, 2) . " ms\n";
