<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';

loadEnv(__DIR__ . '/.env');

echo "Applying clinic tables migration...\n\n";

try {
    $pdo = db();
    
    $sql = file_get_contents(__DIR__ . '/migrations/017_add_clinic_tables.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "\nClinic tables migration applied successfully!\n";
    echo "Added: student_medical_profiles, clinic_visits, immunization_records\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
