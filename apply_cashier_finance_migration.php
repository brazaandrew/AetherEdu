<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';

loadEnv(__DIR__ . '/.env');

echo "Applying cashier role and finance tables migration...\n\n";

try {
    $pdo = db();
    
    // Read and execute migration file
    $sql = file_get_contents(__DIR__ . '/migrations/016_add_cashier_role_and_finance_tables.sql');
    
    // Execute each statement separately
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\nMigration applied successfully!\n";
    echo "Added: cashier role, fee_types, student_fees, payments tables\n";
    
} catch (PDOException $e) {
    echo "Error applying migration: " . $e->getMessage() . "\n";
    exit(1);
}
