<?php
declare(strict_types=1);

require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';

loadEnv(__DIR__ . '/.env');

try {
    $db = db();
    
    echo "Starting enrollment retention status migration...\n";
    
    // Check if enrollments table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'enrollments'")->rowCount();
    
    if ($tableCheck === 0) {
        echo "Creating enrollments table...\n";
        $db->exec("
            CREATE TABLE IF NOT EXISTS `enrollments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `student_id` INT NOT NULL,
                `subject_id` INT NOT NULL,
                `enrolled_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_student_id` (`student_id`),
                INDEX `idx_subject_id` (`subject_id`),
                UNIQUE KEY `unique_student_subject_enrollment` (`student_id`, `subject_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ Enrollments table created\n";
    }
    
    // Check if retention_status column exists
    $columnCheck = $db->query("SHOW COLUMNS FROM enrollments LIKE 'retention_status'")->rowCount();
    
    if ($columnCheck === 0) {
        echo "Adding retention status columns...\n";
        
        // Add columns
        $db->exec("
            ALTER TABLE `enrollments` 
            ADD COLUMN `retention_status` ENUM('Promoted', 'Retained', 'Irregular') DEFAULT 'Promoted' AFTER `enrolled_at`,
            ADD COLUMN `retention_reason` TEXT DEFAULT NULL AFTER `retention_status`,
            ADD COLUMN `retention_school_year` VARCHAR(20) DEFAULT NULL AFTER `retention_reason`,
            ADD COLUMN `retention_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `retention_school_year`,
            ADD COLUMN `retention_updated_by` INT DEFAULT NULL AFTER `retention_updated_at`
        ");
        echo "✓ Retention status columns added\n";
        
        // Add indexes
        $db->exec("
            ALTER TABLE `enrollments` 
            ADD INDEX `idx_retention_status` (`retention_status`),
            ADD INDEX `idx_retention_school_year` (`retention_school_year`),
            ADD INDEX `idx_retention_updated_by` (`retention_updated_by`)
        ");
        echo "✓ Indexes created\n";
        
        // Add foreign key constraint
        try {
            $db->exec("
                ALTER TABLE `enrollments` 
                ADD CONSTRAINT `fk_enrollments_retention_updated_by` 
                FOREIGN KEY (`retention_updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ");
            echo "✓ Foreign key constraint added\n";
        } catch (Exception $e) {
            echo "⚠ Warning: Could not add foreign key constraint: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "✓ Retention status columns already exist\n";
    }
    
    // Show final table structure
    echo "\nCurrent enrollments table structure:\n";
    $columns = $db->query("DESCRIBE enrollments")->fetchAll();
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']} {$column['Default']}\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>