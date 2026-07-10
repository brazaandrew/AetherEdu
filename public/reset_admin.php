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

echo "=== Dynamic Admin Password Reset Tool ===\n\n";

try {
    $master = db_master();
    
    // Fetch all registered schools dynamically
    $stmt = $master->query("SELECT * FROM schools");
    $schools = $stmt->fetchAll();
    
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? ($connection === 'pgsql' ? '5432' : '3306');
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    
    // Build list of target databases to reset
    $dbs = [];
    $mainDb = $_ENV['DB_NAME'] ?? 'neondb';
    $dbs[$mainDb] = 'admin@tlca.edu';
    
    foreach ($schools as $school) {
        $dbName = $school['db_name'];
        $domain = $school['domain'];
        $dbs[$dbName] = "admin@{$domain}.edu";
    }
    
    foreach ($dbs as $dbName => $email) {
        echo "Connecting to database: $dbName...\n";
        try {
            if ($connection === 'pgsql') {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbName;sslmode=require";
                if (strpos($host, 'neon.tech') !== false) {
                    $parts = explode('.', $host);
                    $endpointId = $parts[0] ?? '';
                    if (!empty($endpointId)) {
                        $dsn .= ";options='endpoint=$endpointId'";
                    }
                }
            } else {
                $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
            }
            
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $newPassword = 'adminpassword123';
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Check if ADMIN001 exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE empidno = 'ADMIN001'");
            $stmt->execute();
            $exists = $stmt->fetchColumn();
            
            if ($exists) {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, password_hash = ?, archived = 0, role = 'admin' WHERE empidno = 'ADMIN001'");
                $stmt->execute([$email, $hash]);
                echo "SUCCESS: Updated existing ADMIN001 in $dbName.\n";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (empidno, name, email, password_hash, role) VALUES ('ADMIN001', 'Admin User', ?, ?, 'admin')");
                $stmt->execute([$email, $hash]);
                echo "SUCCESS: Created new ADMIN001 in $dbName.\n";
            }
            
            echo "Credentials for $dbName:\n";
            echo "  Email: $email\n";
            echo "  Password: $newPassword\n\n";
        } catch (Exception $e) {
            echo "FAILED: " . $e->getMessage() . "\n\n";
        }
    }
} catch (Exception $masterEx) {
    echo "MASTER DATABASE ERROR: " . $masterEx->getMessage() . "\n";
}
