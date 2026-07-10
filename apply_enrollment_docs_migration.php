<?php
require_once __DIR__ . '/src/Helpers/env.php';
require_once __DIR__ . '/src/Helpers/database.php';

loadEnv(__DIR__ . '/.env');

try {
    $sql = "CREATE TABLE IF NOT EXISTS student_enrollment_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        document_type ENUM('birth_certificate', 'psa_nso', 'sf10', 'peac') NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_size INT,
        mime_type VARCHAR(100),
        uploaded_by INT,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (uploaded_by) REFERENCES users(id)
    )";
    
    db()->exec($sql);
    echo "SUCCESS: student_enrollment_documents table created!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
