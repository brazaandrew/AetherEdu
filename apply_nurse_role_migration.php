<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';

loadEnv(__DIR__ . '/.env');

try {
    $pdo = db();
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'it_personnel', 'registrar', 'librarian', 'cashier', 'nurse') NOT NULL DEFAULT 'student'");
    echo "Nurse role added successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
