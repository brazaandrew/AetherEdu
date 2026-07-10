<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Info</h2>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

echo "<h3>Checking Files</h3>";

$files = [
    'src/Helpers/env.php',
    'src/Helpers/database.php',
    'src/Helpers/session.php',
    'src/Helpers/auth.php',
    'src/Middleware/AuthMiddleware.php',
    'src/Controllers/FinanceController.php',
    'src/Models/Finance.php',
    '.env',
];

echo "<ul>";
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    echo "<li>" . $file . " - " . ($exists ? "<span style='color:green'>EXISTS</span>" : "<span style='color:red'>MISSING</span>") . "</li>";
}
echo "</ul>";

echo "<h3>Testing Database</h3>";
try {
    require_once __DIR__ . '/src/Helpers/env.php';
    loadEnv(__DIR__ . '/.env');
    
    $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'not set');
    $dbname = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'not set');
    
    echo "<p>Host: $host</p>";
    echo "<p>Database: $dbname</p>";
    
    require_once __DIR__ . '/src/Helpers/database.php';
    $pdo = db();
    echo "<p style='color:green'>Database connection successful!</p>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables: " . implode(", ", $tables) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Testing Finance Controller</h3>";
try {
    require_once __DIR__ . '/src/Controllers/FinanceController.php';
    $fc = new FinanceController();
    echo "<p style='color:green'>FinanceController loaded successfully!</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
