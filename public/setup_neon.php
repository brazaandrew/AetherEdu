<?php
declare(strict_types=1);

// Enable error reporting to catch issues
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header("Content-Type: text/plain");

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';

loadEnv(__DIR__ . '/../.env');

echo "=== Neon School Registry Setup Tool ===\n\n";

try {
    $master = db_master();
    
    // Clear existing schools to start fresh
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    if ($connection === 'pgsql') {
        $master->exec("TRUNCATE TABLE schools RESTART IDENTITY CASCADE");
    } else {
        $master->exec("TRUNCATE TABLE schools");
    }
    echo "SUCCESS: Cleared master schools table.\n";
    
    // Register TLCA to point to 'elms_school_tlca' (local database)
    $stmt = $master->prepare("INSERT INTO schools (name, domain, db_name, school_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'The Light Christian Academy',
        'tlca',
        'elms_school_tlca',
        'SCH-TLCA'
    ]);
    
    echo "SUCCESS: Registered 'The Light Christian Academy' pointing to 'elms_school_tlca' database.\n\n";
    echo "Step-by-step next actions:\n";
    echo "1. Go to your welcome landing page: http://localhost/NewElms/\n";
    echo "2. Select 'The Light Christian Academy' from the directory list.\n";
    echo "3. Log in with your admin credentials. All your data will now load from your local EnvKit MySQL!\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
