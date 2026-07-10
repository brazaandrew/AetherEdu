<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/database.php';

class TenantService {
    private PDO $masterDb;
    
    public function __construct() {
        $this->masterDb = db_master();
    }
    
    /**
     * Get all registered schools
     */
    public function listSchools(): array {
        $stmt = $this->masterDb->query("SELECT * FROM schools ORDER BY name ASC");
        return $stmt->fetchAll();
    }
    
    /**
     * Create a new school / tenant
     */
    public function createSchool(string $name, string $domain, string $adminEmail, string $adminPassword, string $schoolId, ?string $dbSuffix = null): array {
        // Clean and validate domain identifier
        $domain = preg_replace('/[^a-z0-9_-]/', '', strtolower($domain));
        if (empty($domain)) {
            return ['success' => false, 'error' => 'Invalid school domain identifier'];
        }
        
        $schoolId = trim($schoolId);
        if (empty($schoolId)) {
            return ['success' => false, 'error' => 'School ID is required'];
        }
        
        // Check if school ID already exists
        $stmt = $this->masterDb->prepare("SELECT COUNT(*) FROM schools WHERE school_id = ?");
        $stmt->execute([$schoolId]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'A school with this School ID is already registered'];
        }
        
        // Check if school domain already exists
        $stmt = $this->masterDb->prepare("SELECT COUNT(*) FROM schools WHERE domain = ?");
        $stmt->execute([$domain]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'A school with this subdomain/identifier already exists'];
        }
        
        $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
        $dbUser = $_ENV['DB_USER'] ?? 'root';
        $dbPass = $_ENV['DB_PASS'] ?? '';
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? ($connection === 'pgsql' ? '5432' : '3306');
        
        $userPrefix = '';
        if ($connection === 'mysql' && strpos($dbUser, 'if0_') === 0) {
            // InfinityFree style hosting (MySQL only)
            $userPrefix = $dbUser . "_";
        } else {
            $userPrefix = "elms_school_";
        }
        
        // Determine the database suffix
        $dbSuffixClean = '';
        if ($dbSuffix !== null) {
            $dbSuffixClean = str_replace(['-', ' '], '_', strtolower($dbSuffix));
            $dbSuffixClean = preg_replace('/[^a-z0-9_]/', '', $dbSuffixClean);
            $dbSuffixClean = trim($dbSuffixClean, '_');
        }
        
        if (empty($dbSuffixClean)) {
            $dbSuffixClean = str_replace(['-', ' '], '_', strtolower($schoolId));
            $dbSuffixClean = preg_replace('/[^a-z0-9_]/', '', $dbSuffixClean);
            $dbSuffixClean = trim($dbSuffixClean, '_');
        }
        
        if (empty($dbSuffixClean)) {
            $dbSuffixClean = str_replace(['-', ' '], '_', strtolower($domain));
            $dbSuffixClean = preg_replace('/[^a-z0-9_]/', '', $dbSuffixClean);
            $dbSuffixClean = trim($dbSuffixClean, '_');
        }
        
        // Truncate to avoid database name length limits (InfinityFree/MySQL limit is usually 64 chars total)
        $dbSuffixClean = substr($dbSuffixClean, 0, 30);
        
        $dbName = $userPrefix . $dbSuffixClean;
        $masterDbName = $_ENV['DB_NAME'] ?? 'elms';
        
        $singleDbMode = false;
        if (function_exists('env')) {
            $singleDbMode = filter_var(env('SINGLE_DATABASE_MODE', false), FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($singleDbMode) {
            $dbName = $masterDbName;
        } elseif ($dbSuffix !== null && (
            $dbSuffix === $masterDbName || 
            $dbSuffixClean === $masterDbName || 
            $userPrefix . $dbSuffixClean === $masterDbName
        )) {
            $dbName = $masterDbName;
        } else {
            if ($dbSuffix !== null && !empty($userPrefix) && strpos($dbSuffix, $userPrefix) === 0) {
                $dbName = $dbSuffix;
            }
        }
        
        try {
            // 1. Try to create the database (succeeds locally, fails on restricted hosts or Neon if database is already created or no privileges)
            try {
                if ($dbName !== $masterDbName) {
                    if ($connection === 'pgsql') {
                        $this->masterDb->exec("CREATE DATABASE \"$dbName\"");
                    } else {
                        $this->masterDb->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    }
                }
            } catch (PDOException $e) {
                // If CREATE DATABASE fails, check if the database already exists and is connectable
                if ($connection === 'pgsql') {
                    $dsnTest = "pgsql:host=$host;port=$port;dbname=$dbName;sslmode=require";
                    if (strpos($host, 'neon.tech') !== false) {
                        $parts = explode('.', $host);
                        $endpointId = $parts[0] ?? '';
                        if (!empty($endpointId)) {
                            $dsnTest .= ";options='endpoint=$endpointId'";
                        }
                    }
                } else {
                    $dsnTest = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
                }
                try {
                    $testPdo = new PDO($dsnTest, $dbUser, $dbPass);
                    unset($testPdo); // Database exists and is accessible!
                } catch (PDOException $connError) {
                    // If connection also fails, rethrow the original creation error
                    throw $e;
                }
            }
            
            // 2. Connect to the database
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
            $tenantPdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            // 3. Run migrations in alphabetical order
            $migrationsDir = __DIR__ . '/../../migrations';
            if (is_dir($migrationsDir)) {
                $files = scandir($migrationsDir);
                sort($files); // Ensure alphabetical order
                
                foreach ($files as $file) {
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' && strpos($file, 'infinityfree') === false) {
                        $sqlContent = file_get_contents($migrationsDir . '/' . $file);
                        
                        // Split SQL statements by ';' and run them sequentially
                        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
                        foreach ($statements as $statement) {
                            if (!empty($statement)) {
                                if ($connection === 'pgsql') {
                                    // Translate statement from MySQL to PostgreSQL
                                    $statement = str_replace('`', '', $statement);
                                    
                                    // Strip MySQL inline comments and triggers
                                    $statement = preg_replace('/COMMENT\s+\'([^\']*)\'/i', '', $statement);
                                    $statement = preg_replace('/ON UPDATE\s+current_timestamp\(\)/i', '', $statement);
                                    $statement = preg_replace('/ON UPDATE\s+CURRENT_TIMESTAMP/i', '', $statement);
                                    
                                    // Translate datatypes
                                    $statement = preg_replace('/\bint\(\d+\)/i', 'INT', $statement);
                                    $statement = preg_replace('/\btinyint\(\d+\)/i', 'SMALLINT', $statement);
                                    $statement = preg_replace('/\bdatetime\b/i', 'TIMESTAMP', $statement);
                                    $statement = preg_replace('/\btimestamp\b/i', 'TIMESTAMP', $statement);
                                    $statement = preg_replace('/\bdouble\b/i', 'DOUBLE PRECISION', $statement);
                                    $statement = preg_replace('/\benum\([^)]+\)/i', 'VARCHAR(255)', $statement);
                                    $statement = preg_replace('/current_timestamp\(\)/i', 'CURRENT_TIMESTAMP', $statement);
                                    
                                    // Auto-increment conversion
                                    $statement = preg_replace('/\bid\s+INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'id SERIAL PRIMARY KEY', $statement);
                                    $statement = preg_replace('/\bid\s+INT\s+NOT\s+NULL\s+AUTO_INCREMENT/i', 'id SERIAL PRIMARY KEY', $statement);
                                    $statement = preg_replace('/\bid\s+INT\s+AUTO_INCREMENT/i', 'id SERIAL PRIMARY KEY', $statement);
                                    $statement = preg_replace('/UNIQUE KEY\s+(\w+)\s*\(([^)]+)\)/i', 'CONSTRAINT $1 UNIQUE ($2)', $statement);
                                    $statement = preg_replace('/^\s*(?:INDEX|KEY)\s+\w+\s*\([^)]+\),?/im', '', $statement);
                                    $statement = preg_replace('/,\s*(?:INDEX|KEY)\s+\w+\s*\([^)]+\)/i', '', $statement);
                                    $statement = preg_replace('/\bAFTER\s+\w+/i', '', $statement);
                                    $statement = preg_replace('/\bCHANGE\s+(?:COLUMN\s+)?(\w+)\s+(\w+)\s+[^,;]+/i', 'RENAME COLUMN $1 TO $2', $statement);
                                    
                                    // Convert zero dates to NULL
                                    $statement = str_replace([
                                        "'0000-00-00 00:00:00'", 
                                        '"0000-00-00 00:00:00"', 
                                        "'0000-00-00'", 
                                        '"0000-00-00"'
                                    ], 'NULL', $statement);
                                    
                                    // Strip MySQL engine wrappers
                                    if (stripos($statement, 'ENGINE=') !== false) {
                                        $statement = preg_replace('/\) ENGINE=.*/is', ')', $statement);
                                    }
                                }
                                
                                try {
                                    $tenantPdo->exec($statement);
                                } catch (PDOException $migEx) {
                                    // Log the warning and continue. This guarantees onboarding succeeds whether the database is blank or pre-populated.
                                    error_log("Migration statement skipped: " . $migEx->getMessage());
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
            
            // 4. Register the default administrator in the new school database
            try {
                $check = $tenantPdo->query("SELECT COUNT(*) FROM users WHERE empidno = 'ADMIN001'")->fetchColumn();
                if (!$check) {
                    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
                    $stmt = $tenantPdo->prepare("
                        INSERT INTO users (empidno, name, email, password_hash, role) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute(['ADMIN001', $name . ' Admin', $adminEmail, $passwordHash, 'admin']);
                }
            } catch (PDOException $adminEx) {
                // Ignore key conflict warnings if it was inserted concurrently
                if (isset($adminEx->errorInfo[1]) && $adminEx->errorInfo[1] !== 1062) {
                    throw $adminEx;
                }
            }
            
            // 5. Register in the master database schools registry
            $stmt = $this->masterDb->prepare("
                INSERT INTO schools (name, domain, db_name, school_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $domain, $dbName, $schoolId]);
            
            return ['success' => true, 'db_name' => $dbName];
        } catch (Exception $e) {
            // Clean up created database if registration fails midway (locally only)
            // CRITICAL: NEVER drop the database if it is the master database!
            if ($dbName !== $masterDbName) {
                if ($connection === 'mysql' && strpos($dbUser, 'if0_') !== 0) {
                    try {
                        $this->masterDb->exec("DROP DATABASE IF EXISTS `$dbName`");
                    } catch (Exception $cleanupError) {
                        // Ignore cleanup error
                    }
                } elseif ($connection === 'pgsql') {
                    try {
                        $this->masterDb->exec("DROP DATABASE IF EXISTS \"$dbName\"");
                    } catch (Exception $cleanupError) {
                        // Ignore cleanup error
                    }
                }
            }
            
            return ['success' => false, 'error' => 'Failed to initialize school: ' . $e->getMessage()];
        }
    }
    
    /**
     * Delete a school and its associated database
     */
    public function deleteSchool(int $id): array {
        try {
            // Find school database name
            $stmt = $this->masterDb->prepare("SELECT db_name FROM schools WHERE id = ?");
            $stmt->execute([$id]);
            $dbName = $stmt->fetchColumn();
            
            if (!$dbName) {
                return ['success' => false, 'error' => 'School not found'];
            }
            
            // Drop database (safely sanitize database name)
            // CRITICAL: NEVER drop the database if it is the master database!
            $masterDbName = $_ENV['DB_NAME'] ?? 'elms';
            if ($dbName !== $masterDbName) {
                $dbNameSanitized = preg_replace('/[^a-z0-9_]/', '', $dbName);
                $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
                if ($connection === 'pgsql') {
                    $this->masterDb->exec("DROP DATABASE IF EXISTS \"$dbNameSanitized\"");
                } else {
                    $this->masterDb->exec("DROP DATABASE IF EXISTS `$dbNameSanitized`");
                }
            }
            
            // Remove school registry record
            $stmt = $this->masterDb->prepare("DELETE FROM schools WHERE id = ?");
            $stmt->execute([$id]);
            
            // Reset active school session if deleted school is currently loaded
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
            if (isset($_SESSION['active_school_db']) && $_SESSION['active_school_db'] === $dbName) {
                unset($_SESSION['active_school_id']);
                unset($_SESSION['active_school_db']);
                unset($_SESSION['active_school_name']);
                unset($_SESSION['user']);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to delete school: ' . $e->getMessage()];
        }
    }
}
