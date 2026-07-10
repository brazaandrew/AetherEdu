<?php
declare(strict_types=1);

function db_master() {
    static $master_pdo = null;
    
    if ($master_pdo === null) {
        $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? ($connection === 'pgsql' ? '5432' : '3306');
        $dbname = $_ENV['DB_NAME'] ?? 'elms';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        
        if ($connection === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            if (strpos($host, 'neon.tech') !== false) {
                $parts = explode('.', $host);
                $endpointId = $parts[0] ?? '';
                if (!empty($endpointId)) {
                    $dsn .= ";options='endpoint=$endpointId'";
                }
            }
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        }
        
        try {
            $master_pdo = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true,
                ]
            );
            
            // Self-healing bootstrap: Ensure schools table exists
            if ($connection === 'pgsql') {
                $master_pdo->exec("
                    CREATE TABLE IF NOT EXISTS schools (
                        id SERIAL PRIMARY KEY,
                        school_id VARCHAR(255) UNIQUE NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        domain VARCHAR(255) UNIQUE NOT NULL,
                        db_name VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
                ");
            } else {
                $master_pdo->exec("
                    CREATE TABLE IF NOT EXISTS schools (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        school_id VARCHAR(255) UNIQUE NOT NULL,
                        name VARCHAR(255) NOT NULL,
                        domain VARCHAR(255) UNIQUE NOT NULL,
                        db_name VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }

            // Dynamically drop unique constraint on db_name column if present to support shared database multi-tenancy
            try {
                if ($connection === 'pgsql') {
                    $master_pdo->exec("ALTER TABLE schools DROP CONSTRAINT IF EXISTS schools_db_name_key");
                } else {
                    $indexExists = $master_pdo->query("SHOW INDEX FROM schools WHERE Key_name = 'db_name'")->fetch();
                    if ($indexExists) {
                        $master_pdo->exec("ALTER TABLE schools DROP INDEX db_name");
                    }
                }
            } catch (PDOException $dropEx) {
                // Ignore if constraint name is different or already dropped
            }

            // Dynamically add school_id column if it doesn't exist
            $columnExists = false;
            try {
                $master_pdo->query("SELECT school_id FROM schools LIMIT 1");
                $columnExists = true;
            } catch (PDOException $e) {
                $columnExists = false;
            }

            if (!$columnExists) {
                try {
                    $rowCount = (int)$master_pdo->query("SELECT COUNT(*) FROM schools")->fetchColumn();
                    if ($rowCount === 0) {
                        $master_pdo->exec("ALTER TABLE schools ADD COLUMN school_id VARCHAR(255) UNIQUE NOT NULL");
                    } else {
                        // Table is not empty. Add as nullable first.
                        $master_pdo->exec("ALTER TABLE schools ADD COLUMN school_id VARCHAR(255) UNIQUE");
                        // Populate existing schools
                        $stmt = $master_pdo->query("SELECT id, domain FROM schools");
                        $schoolsList = $stmt->fetchAll();
                        foreach ($schoolsList as $schoolRow) {
                            $autoId = 'SCH-' . strtoupper($schoolRow['domain']) . '-' . $schoolRow['id'];
                            $upd = $master_pdo->prepare("UPDATE schools SET school_id = ? WHERE id = ?");
                            $upd->execute([$autoId, $schoolRow['id']]);
                        }
                        // Set column to NOT NULL
                        if ($connection === 'pgsql') {
                            $master_pdo->exec("ALTER TABLE schools ALTER COLUMN school_id SET NOT NULL");
                        } else {
                            $master_pdo->exec("ALTER TABLE schools MODIFY school_id VARCHAR(255) UNIQUE NOT NULL");
                        }
                    }
                } catch (PDOException $alterEx) {
                    error_log("Failed to alter schools table to add school_id: " . $alterEx->getMessage());
                }
            }
        } catch (PDOException $e) {
            error_log("Master database connection failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    return $master_pdo;
}

function resolve_tenant() {
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        session_start();
    }
    
    // Clear school if clear is explicitly passed
    if (isset($_GET['school']) && $_GET['school'] === 'clear') {
        unset($_SESSION['active_school_id']);
        unset($_SESSION['active_school_db']);
        unset($_SESSION['active_school_name']);
        unset($_SESSION['active_school_lookup']);
        unset($_SESSION['user']);
        $_SESSION['active_school_cleared'] = true;
        return;
    }
    
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $subdomain = '';
    if ($host && $host !== 'localhost' && $host !== '127.0.0.1') {
        $parts = explode('.', $host);
        if (count($parts) > 1) {
            $subdomain = $parts[0];
        }
    }
    
    $query_school = $_GET['school'] ?? null;
    if ($query_school || $subdomain) {
        $lookup = $query_school ?: $subdomain;
        unset($_SESSION['active_school_cleared']); // Explicit selection resets manual clear state
        
        // Avoid redundant master DB queries if lookup domain hasn't changed
        if (isset($_SESSION['active_school_lookup']) && $_SESSION['active_school_lookup'] === $lookup && isset($_SESSION['active_school_db'])) {
            return;
        }
        
        try {
            $master = db_master();
            $stmt = $master->prepare("SELECT * FROM schools WHERE domain = ?");
            $stmt->execute([$lookup]);
            $school = $stmt->fetch();
            if ($school) {
                if (isset($_SESSION['active_school_db']) && $_SESSION['active_school_db'] !== $school['db_name']) {
                    unset($_SESSION['user']);
                }
                $_SESSION['active_school_id'] = $school['id'];
                $_SESSION['active_school_db'] = $school['db_name'];
                $_SESSION['active_school_name'] = $school['name'];
                $_SESSION['active_school_lookup'] = $lookup;
            }
        } catch (Exception $e) {
            error_log("Failed to resolve school tenant: " . $e->getMessage());
        }
    } else {
        // Single Database Mode: If no school specified and no school is active,
        // auto-load if there is exactly 1 school in the registry, unless manual clear was requested.
        if (!isset($_SESSION['active_school_db']) && !isset($_SESSION['active_school_cleared'])) {
            try {
                $master = db_master();
                $stmt = $master->query("SELECT * FROM schools");
                $allSchools = $stmt->fetchAll();
                if (count($allSchools) === 1) {
                    $school = $allSchools[0];
                    $_SESSION['active_school_id'] = $school['id'];
                    $_SESSION['active_school_db'] = $school['db_name'];
                    $_SESSION['active_school_name'] = $school['name'];
                    $_SESSION['active_school_lookup'] = $school['domain'];
                }
            } catch (Exception $e) {
                // Ignore DB query errors
            }
        }
    }
}

function db() {
    static $pdo = null;
    
    if ($pdo === null) {
        resolve_tenant();
        
        $singleDbMode = false;
        if (function_exists('env')) {
            $singleDbMode = filter_var(env('SINGLE_DATABASE_MODE', false), FILTER_VALIDATE_BOOLEAN);
        }
        
        $tenant_db = $_SESSION['active_school_db'] ?? null;
        if ($singleDbMode) {
            $tenant_db = $_ENV['DB_NAME'] ?? 'elms';
        }
        
        $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? ($connection === 'pgsql' ? '5432' : '3306');
        $dbname = $tenant_db ?: ($_ENV['DB_NAME'] ?? 'elms');
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        
        if ($connection === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            if (strpos($host, 'neon.tech') !== false) {
                $parts = explode('.', $host);
                $endpointId = $parts[0] ?? '';
                if (!empty($endpointId)) {
                    $dsn .= ";options='endpoint=$endpointId'";
                }
            }
        } else {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        }
        
        // REUSE MASTER CONNECTION IF DATABASE IS THE MASTER DB (saves 1 full second of SSL handshake)
        $master_db_name = $_ENV['DB_NAME'] ?? 'neondb';
        if ($dbname === $master_db_name) {
            $pdo = db_master();
            return $pdo;
        }

        try {
            $pdo = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true,
                ]
            );
            
            
            // Cache the self-healing status in session to avoid running slow catalog queries on every request.
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
            $healSessionKey = 'heal_done_' . $dbname;
            
            if (!isset($_SESSION[$healSessionKey])) {
                $healed = false;
                // Persistent DB check: Query the settings table to see if database has been healed
                try {
                    $checkHeal = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'database_healed'")->fetchColumn();
                    if ($checkHeal === '1') {
                        $healed = true;
                    }
                } catch (Exception $e) {
                    // Settings table doesn't exist yet, run the healing process
                }

                if (!$healed) {
                    // Self-healing 1: Check if the number of tables is less than 32. If so, run migrations to heal any missing tables!
                    try {
                        $schemaFilter = ($connection === 'pgsql') ? "table_schema = 'public'" : "table_schema = DATABASE()";
                        $tableCount = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE $schemaFilter")->fetchColumn();
                        if ($tableCount < 32) {
                            error_log("Database '$dbname' has only $tableCount tables. Executing self-healing migrations...");
                            $migrationsDir = __DIR__ . '/../../migrations';
                            if (is_dir($migrationsDir)) {
                                $files = scandir($migrationsDir);
                                sort($files);
                                foreach ($files as $file) {
                                    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql' && strpos($file, 'infinityfree') === false) {
                                        $sqlContent = file_get_contents($migrationsDir . '/' . $file);
                                        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
                                        foreach ($statements as $statement) {
                                            if (!empty($statement)) {
                                                if ($connection === 'pgsql') {
                                                    $statement = str_replace('`', '', $statement);
                                                    $statement = preg_replace('/COMMENT\s+\'([^\']*)\'/i', '', $statement);
                                                    $statement = preg_replace('/ON UPDATE\s+current_timestamp\(\)/i', '', $statement);
                                                    $statement = preg_replace('/ON UPDATE\s+CURRENT_TIMESTAMP/i', '', $statement);
                                                    $statement = preg_replace('/\bint\(\d+\)/i', 'INT', $statement);
                                                    $statement = preg_replace('/\btinyint\(\d+\)/i', 'SMALLINT', $statement);
                                                    $statement = preg_replace('/\bdatetime\b/i', 'TIMESTAMP', $statement);
                                                    $statement = preg_replace('/\btimestamp\b/i', 'TIMESTAMP', $statement);
                                                    $statement = preg_replace('/\bdouble\b/i', 'DOUBLE PRECISION', $statement);
                                                    $statement = preg_replace('/\benum\([^)]+\)/i', 'VARCHAR(255)', $statement);
                                                    $statement = preg_replace('/current_timestamp\(\)/i', 'CURRENT_TIMESTAMP', $statement);
                                                    $statement = preg_replace('/\bid\s+INT\s+AUTO_INCREMENT\s+PRIMARY\s+KEY/i', 'id SERIAL PRIMARY KEY', $statement);
                                                    $statement = preg_replace('/\bid\s+INT\s+NOT\s+NULL\s+AUTO_INCREMENT/i', 'id SERIAL PRIMARY KEY', $statement);
                                                    $statement = preg_replace('/\bid\s+INT\s+AUTO_INCREMENT/i', 'id SERIAL PRIMARY KEY', $statement);
                                                    $statement = preg_replace('/UNIQUE KEY\s+(\w+)\s*\(([^)]+)\)/i', 'CONSTRAINT $1 UNIQUE ($2)', $statement);
                                                    $statement = preg_replace('/^\s*(?:INDEX|KEY)\s+\w+\s*\([^)]+\),?/im', '', $statement);
                                                    $statement = preg_replace('/,\s*(?:INDEX|KEY)\s+\w+\s*\([^)]+\)/i', '', $statement);
                                                    $statement = preg_replace('/\bAFTER\s+\w+/i', '', $statement);
                                                    $statement = preg_replace('/\bCHANGE\s+(?:COLUMN\s+)?(\w+)\s+(\w+)\s+[^,;]+/i', 'RENAME COLUMN $1 TO $2', $statement);
                                                    
                                                    $statement = str_replace(["'0000-00-00 00:00:00'", '"0000-00-00 00:00:00"', "'0000-00-00'", '"0000-00-00"'], 'NULL', $statement);
                                                    if (stripos($statement, 'ENGINE=') !== false) {
                                                        $statement = preg_replace('/\) ENGINE=.*/is', ')', $statement);
                                                    }
                                                }
                                                try {
                                                    $pdo->exec($statement);
                                                } catch (PDOException $migEx) {
                                                    // Ignore duplicate table or column warnings, we only want to build missing ones
                                                    error_log("Self-healing statement skipped: " . $migEx->getMessage());
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (Exception $migErrEx) {
                        error_log("Self-healing migration failed: " . $migErrEx->getMessage());
                    }

                    // Self-healing 2: Check if subjects table is missing the 'archived' column and add it automatically.
                    try {
                        $checkStmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'subjects' AND column_name = 'archived'");
                        $hasArchived = $checkStmt->fetch();
                        if (!$hasArchived) {
                            $colType = ($connection === 'pgsql') ? 'SMALLINT DEFAULT 0' : 'TINYINT(1) DEFAULT 0';
                            $pdo->exec("ALTER TABLE subjects ADD COLUMN archived $colType");
                        }
                    } catch (Exception $colEx) {
                        // Ignore validation errors to proceed safely
                    }

                    // Persist the status in settings table so we never run catalog checks again
                    try {
                        $exists = $pdo->query("SELECT COUNT(*) FROM settings WHERE setting_key = 'database_healed'")->fetchColumn();
                        if ($exists) {
                            $pdo->exec("UPDATE settings SET setting_value = '1' WHERE setting_key = 'database_healed'");
                        } else {
                            $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('database_healed', '1')");
                        }
                    } catch (Exception $setEx) {
                        // Ignore
                    }
                }
                
                $_SESSION[$healSessionKey] = true;
            }
        } catch (PDOException $e) {
            // If database doesn't exist on the active database server, clear the tenant session and redirect
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                session_start();
            }
            if (isset($_SESSION['active_school_db'])) {
                unset($_SESSION['active_school_id']);
                unset($_SESSION['active_school_db']);
                unset($_SESSION['active_school_name']);
                unset($_SESSION['user']);
                header('Location: ../index.php');
                exit;
            }
            error_log("Tenant Database connection failed to '$dbname': " . $e->getMessage());
            throw $e;
        }
    }
    
    return $pdo;
}

/**
 * Get dashboard analytics for admin users
 */
function getAdminDashboardAnalytics() {
    $db = db();
    
    // Total counts
    $totalUsers = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $totalSubjects = $db->query('SELECT COUNT(*) FROM subjects WHERE archived = 0')->fetchColumn();
    $totalActivities = $db->query('SELECT COUNT(*) FROM activities')->fetchColumn();
    $totalQuizzes = $db->query('SELECT COUNT(*) FROM quizzes')->fetchColumn();
    
    // Counts by role
    $totalTeachers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
    $totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $totalAdmins = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $totalIT = $db->query("SELECT COUNT(*) FROM users WHERE role = 'it_personnel'")->fetchColumn();
    
    // Recent activity counts
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    if ($connection === 'pgsql') {
        $recentActivities = $db->query("SELECT COUNT(*) FROM activities WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn();
        $recentQuizzes = $db->query("SELECT COUNT(*) FROM quizzes WHERE created_at >= NOW() - INTERVAL '7 days'")->fetchColumn();
        $recentSubmissions = $db->query("SELECT COUNT(*) FROM activity_submissions WHERE submitted_at >= NOW() - INTERVAL '7 days'")->fetchColumn();
        $recentQuizAttempts = $db->query("SELECT COUNT(*) FROM quiz_attempts WHERE submitted_at >= NOW() - INTERVAL '7 days'")->fetchColumn();
    } else {
        $recentActivities = $db->query('SELECT COUNT(*) FROM activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
        $recentQuizzes = $db->query('SELECT COUNT(*) FROM quizzes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
        $recentSubmissions = $db->query('SELECT COUNT(*) FROM activity_submissions WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
        $recentQuizAttempts = $db->query('SELECT COUNT(*) FROM quiz_attempts WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)')->fetchColumn();
    }
    
    return [
        'total' => [
            'users' => (int)$totalUsers,
            'subjects' => (int)$totalSubjects,
            'activities' => (int)$totalActivities,
            'quizzes' => (int)$totalQuizzes,
        ],
        'by_role' => [
            'teachers' => (int)$totalTeachers,
            'students' => (int)$totalStudents,
            'admins' => (int)$totalAdmins,
            'it_personnel' => (int)$totalIT,
        ],
        'recent' => [
            'activities' => (int)$recentActivities,
            'quizzes' => (int)$recentQuizzes,
            'submissions' => (int)$recentSubmissions,
            'quiz_attempts' => (int)$recentQuizAttempts,
        ]
    ];
}

/**
 * Get recent activity logs for dashboard
 */
function getRecentActivityLogs(int $limit = 10) {
    $db = db();
    
    $stmt = $db->prepare('
        SELECT 
            al.*, 
            u.name as user_name,
            u.role as user_role
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.timestamp DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    
    return $stmt->fetchAll();
}

/**
 * Get user analytics by role
 */
function getUserAnalyticsByRole() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '30 days'" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $db->query("
        SELECT 
            role,
            COUNT(*) as count,
            COUNT(CASE WHEN created_at >= $interval THEN 1 END) as recent_count
        FROM users
        GROUP BY role
    ");
    
    return $stmt->fetchAll();
}

/**
 * Get subject analytics
 */
function getSubjectAnalytics() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '30 days'" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_subjects,
            COUNT(CASE WHEN created_at >= $interval THEN 1 END) as recent_subjects
        FROM subjects
    ");
    
    return $stmt->fetch();
}

/**
 * Get activity analytics
 */
function getActivityAnalytics() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '30 days'" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_activities,
            COUNT(CASE WHEN created_at >= $interval THEN 1 END) as recent_activities,
            COUNT(CASE WHEN deadline IS NOT NULL AND deadline >= NOW() THEN 1 END) as pending_activities
        FROM activities
    ");
    
    return $stmt->fetch();
}

/**
 * Get quiz analytics
 */
function getQuizAnalytics() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '30 days'" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_quizzes,
            COUNT(CASE WHEN created_at >= $interval THEN 1 END) as recent_quizzes
        FROM quizzes
    ");
    
    return $stmt->fetch();
}

/**
 * Get enrollment analytics
 */
function getEnrollmentAnalytics() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '30 days'" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_enrollments,
            COUNT(CASE WHEN enrolled_at >= $interval THEN 1 END) as recent_enrollments
        FROM enrollments
    ");
    
    return $stmt->fetch();
}

/**
 * Get grade analytics
 */
function getGradeAnalytics() {
    $db = db();
    
    $stmt = $db->query('
        SELECT 
            COUNT(*) as total_grades,
            AVG(final_grade) as average_grade,
            COUNT(CASE WHEN final_grade >= 90 THEN 1 END) as excellent_count,
            COUNT(CASE WHEN final_grade >= 80 AND final_grade < 90 THEN 1 END) as good_count,
            COUNT(CASE WHEN final_grade >= 70 AND final_grade < 80 THEN 1 END) as average_count,
            COUNT(CASE WHEN final_grade < 70 THEN 1 END) as poor_count
        FROM grades
        WHERE final_grade IS NOT NULL
    ');
    
    $result = $stmt->fetch();
    
    // Ensure average_grade is a float for number_format function
    $result['average_grade'] = $result['average_grade'] !== null ? (float)$result['average_grade'] : null;
    
    return $result;
}

/**
 * Get chart data for user distribution by role
 */
function getUserDistributionChartData() {
    $db = db();
    
    $stmt = $db->query('
        SELECT 
            role,
            COUNT(*) as count
        FROM users
        GROUP BY role
    ');
    
    $data = $stmt->fetchAll();
    
    $labels = [];
    $counts = [];
    $colors = [];
    
    foreach ($data as $row) {
        $labels[] = ucfirst($row['role']);
        $counts[] = (int)$row['count'];
        
        // Assign colors based on role
        switch ($row['role']) {
            case 'admin':
                $colors[] = '#ef4444'; // red
                break;
            case 'teacher':
                $colors[] = '#3b82f6'; // blue
                break;
            case 'student':
                $colors[] = '#10b981'; // green
                break;
            case 'it_personnel':
                $colors[] = '#f59e0b'; // yellow
                break;
            default:
                $colors[] = '#64748b'; // gray
        }
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Users by Role',
                'data' => $counts,
                'backgroundColor' => $colors,
                'borderColor' => '#ffffff',
                'borderWidth' => 2
            ]
        ]
    ];
}

/**
 * Get chart data for content creation over time (last 7 days)
 */
function getContentCreationChartData() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '7 days'" : "DATE_SUB(NOW(), INTERVAL 7 DAY)";
    
    // Get activities created in the last 7 days
    $stmt = $db->query("
        SELECT 
            CAST(created_at AS DATE) as date,
            COUNT(*) as count
        FROM activities 
        WHERE created_at >= $interval
        GROUP BY CAST(created_at AS DATE)
        ORDER BY CAST(created_at AS DATE)
    ");
    $activities = $stmt->fetchAll();
    
    // Get quizzes created in the last 7 days
    $stmt = $db->query("
        SELECT 
            CAST(created_at AS DATE) as date,
            COUNT(*) as count
        FROM quizzes 
        WHERE created_at >= $interval
        GROUP BY CAST(created_at AS DATE)
        ORDER BY CAST(created_at AS DATE)
    ");
    $quizzes = $stmt->fetchAll();
    
    // Get submissions in the last 7 days
    $stmt = $db->query("
        SELECT 
            CAST(submitted_at AS DATE) as date,
            COUNT(*) as count
        FROM activity_submissions 
        WHERE submitted_at >= $interval
        GROUP BY CAST(submitted_at AS DATE)
        ORDER BY CAST(submitted_at AS DATE)
    ");
    $submissions = $stmt->fetchAll();
    
    // Create date range for the last 7 days
    $dates = [];
    for ($i = 6; $i >= 0; $i--) {
        $dates[] = date('Y-m-d', strtotime("-$i days"));
    }
    
    // Prepare data arrays
    $activityData = [];
    $quizData = [];
    $submissionData = [];
    
    foreach ($dates as $date) {
        $activityCount = 0;
        $quizCount = 0;
        $submissionCount = 0;
        
        foreach ($activities as $activity) {
            if ($activity['date'] === $date) {
                $activityCount = (int)$activity['count'];
                break;
            }
        }
        
        foreach ($quizzes as $quiz) {
            if ($quiz['date'] === $date) {
                $quizCount = (int)$quiz['count'];
                break;
            }
        }
        
        foreach ($submissions as $submission) {
            if ($submission['date'] === $date) {
                $submissionCount = (int)$submission['count'];
                break;
            }
        }
        
        $activityData[] = $activityCount;
        $quizData[] = $quizCount;
        $submissionData[] = $submissionCount;
    }
    
    return [
        'labels' => array_map(function($date) {
            return date('M j', strtotime($date));
        }, $dates),
        'datasets' => [
            [
                'label' => 'Activities Created',
                'data' => $activityData,
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ],
            [
                'label' => 'Quizzes Created',
                'data' => $quizData,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ],
            [
                'label' => 'Submissions',
                'data' => $submissionData,
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ]
        ]
    ];
}

/**
 * Get chart data for grade distribution
 */
function getGradeDistributionChartData() {
    $db = db();
    
    $stmt = $db->query("
        SELECT 
            CASE 
                WHEN final_grade >= 90 THEN 'Excellent (90-100)'
                WHEN final_grade >= 80 THEN 'Good (80-89)'
                WHEN final_grade >= 70 THEN 'Average (70-79)'
                WHEN final_grade >= 60 THEN 'Below Average (60-69)'
                ELSE 'Needs Improvement (<60)'
            END as grade_range,
            COUNT(*) as count
        FROM grades
        WHERE final_grade IS NOT NULL
        GROUP BY 
            CASE 
                WHEN final_grade >= 90 THEN 'Excellent (90-100)'
                WHEN final_grade >= 80 THEN 'Good (80-89)'
                WHEN final_grade >= 70 THEN 'Average (70-79)'
                WHEN final_grade >= 60 THEN 'Below Average (60-69)'
                ELSE 'Needs Improvement (<60)'
            END
        ORDER BY 
            CASE 
                WHEN CASE 
                    WHEN final_grade >= 90 THEN 'Excellent (90-100)'
                    WHEN final_grade >= 80 THEN 'Good (80-89)'
                    WHEN final_grade >= 70 THEN 'Average (70-79)'
                    WHEN final_grade >= 60 THEN 'Below Average (60-69)'
                    ELSE 'Needs Improvement (<60)'
                END = 'Excellent (90-100)' THEN 1
                WHEN CASE 
                    WHEN final_grade >= 90 THEN 'Excellent (90-100)'
                    WHEN final_grade >= 80 THEN 'Good (80-89)'
                    WHEN final_grade >= 70 THEN 'Average (70-79)'
                    WHEN final_grade >= 60 THEN 'Below Average (60-69)'
                    ELSE 'Needs Improvement (<60)'
                END = 'Good (80-89)' THEN 2
                WHEN CASE 
                    WHEN final_grade >= 90 THEN 'Excellent (90-100)'
                    WHEN final_grade >= 80 THEN 'Good (80-89)'
                    WHEN final_grade >= 70 THEN 'Average (70-79)'
                    WHEN final_grade >= 60 THEN 'Below Average (60-69)'
                    ELSE 'Needs Improvement (<60)'
                END = 'Average (70-79)' THEN 3
                WHEN CASE 
                    WHEN final_grade >= 90 THEN 'Excellent (90-100)'
                    WHEN final_grade >= 80 THEN 'Good (80-89)'
                    WHEN final_grade >= 70 THEN 'Average (70-79)'
                    WHEN final_grade >= 60 THEN 'Below Average (60-69)'
                    ELSE 'Needs Improvement (<60)'
                END = 'Below Average (60-69)' THEN 4
                ELSE 5
            END
    ");
    
    $data = $stmt->fetchAll();
    
    $labels = [];
    $counts = [];
    $colors = [];
    
    foreach ($data as $row) {
        $labels[] = $row['grade_range'];
        $counts[] = (int)$row['count'];
        
        // Assign colors based on grade range
        if (strpos($row['grade_range'], 'Excellent') !== false) {
            $colors[] = '#10b981'; // green
        } elseif (strpos($row['grade_range'], 'Good') !== false) {
            $colors[] = '#22c55e'; // light green
        } elseif (strpos($row['grade_range'], 'Average') !== false) {
            $colors[] = '#f59e0b'; // yellow
        } elseif (strpos($row['grade_range'], 'Below Average') !== false) {
            $colors[] = '#f97316'; // orange
        } else {
            $colors[] = '#ef4444'; // red
        }
    }
    
    return [
        'labels' => $labels,
        'datasets' => [
            [
                'label' => 'Grade Distribution',
                'data' => $counts,
                'backgroundColor' => $colors,
                'borderColor' => '#ffffff',
                'borderWidth' => 2
            ]
        ]
    ];
}

/**
 * Get chart data for enrollment trends over time (last 30 days)
 */
function getEnrollmentTrendsChartData() {
    $db = db();
    $connection = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $interval = $connection === 'pgsql' ? "NOW() - INTERVAL '30 days'" : "DATE_SUB(NOW(), INTERVAL 30 DAY)";
    
    // Get enrollments in the last 30 days
    $stmt = $db->query("
        SELECT 
            CAST(enrolled_at AS DATE) as date,
            COUNT(*) as count
        FROM enrollments 
        WHERE enrolled_at >= $interval
        GROUP BY CAST(enrolled_at AS DATE)
        ORDER BY CAST(enrolled_at AS DATE)
    ");
    $enrollments = $stmt->fetchAll();
    
    // Create date range for the last 30 days
    $dates = [];
    for ($i = 29; $i >= 0; $i--) {
        $dates[] = date('Y-m-d', strtotime("-$i days"));
    }
    
    // Prepare data array
    $enrollmentData = [];
    
    foreach ($dates as $date) {
        $enrollmentCount = 0;
        
        foreach ($enrollments as $enrollment) {
            if ($enrollment['date'] === $date) {
                $enrollmentCount = (int)$enrollment['count'];
                break;
            }
        }
        
        $enrollmentData[] = $enrollmentCount;
    }
    
    return [
        'labels' => array_map(function($date) {
            return date('M j', strtotime($date));
        }, $dates),
        'datasets' => [
            [
                'label' => 'Enrollments',
                'data' => $enrollmentData,
                'borderColor' => '#8b5cf6',
                'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                'borderWidth' => 3,
                'tension' => 0.4,
                'fill' => true
            ]
        ]
    ];
}

/**
 * Retrieve the active tenant's school logo path.
 * Resolves context level and dynamically prefixes public/ when loaded from root folder.
 */
function get_school_logo(): string {
    $logo = null;
    
    if (isset($_SESSION['active_school_db'])) {
        try {
            // Retrieve from settings table in tenant database
            $db = db();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'school_logo'");
            $stmt->execute();
            $logo = $stmt->fetchColumn();
        } catch (Exception $e) {
            // Fallback
        }
    }
    
    if (!$logo) {
        $logo = 'image/new.svg'; // default fallback logo path
    }
    
    // Check if the current executing file is in the public directory
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $isInPublic = (strpos($scriptName, '/public/') !== false);
    
    if ($isInPublic) {
        return $logo;
    } else {
        return 'public/' . $logo;
    }
}

/**
 * Computes an acronym for a school name
 */
function get_school_acronym(string $name): string {
    $acronym = '';
    foreach (explode(' ', $name) as $word) {
        $acronym .= strtoupper($word[0] ?? '');
    }
    return !empty($acronym) ? $acronym : 'LMS';
}