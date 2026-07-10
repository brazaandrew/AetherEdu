<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';

loadEnv(__DIR__ . '/.env');

try {
    $pdo = db();
    
    $sql = file_get_contents(__DIR__ . '/migrations/019_add_hr_role_and_201_file_tables.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\nHR migration applied successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
