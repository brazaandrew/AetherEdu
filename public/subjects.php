<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

$message = '';
$error = '';

// Handle session messages
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle Archive/Unarchive Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['archive_subject'])) {
    requireCsrf();
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    
    if ($subjectId) {
        $stmt = db()->prepare('UPDATE subjects SET archived = 1 WHERE id = ?');
        if ($stmt->execute([$subjectId])) {
            $_SESSION['success_message'] = 'Subject archived successfully!';
            saveAudit($user['id'], 'archive', 'subject', $subjectId, []);
        } else {
            $_SESSION['error_message'] = 'Failed to archive subject';
        }
    }
    header('Location: subjects.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unarchive_subject'])) {
    requireCsrf();
    $subjectId = (int)($_POST['subject_id'] ?? 0);
    
    if ($subjectId) {
        $stmt = db()->prepare('UPDATE subjects SET archived = 0 WHERE id = ?');
        if ($stmt->execute([$subjectId])) {
            $_SESSION['success_message'] = 'Subject unarchived successfully!';
            saveAudit($user['id'], 'unarchive', 'subject', $subjectId, []);
        } else {
            $_SESSION['error_message'] = 'Failed to unarchive subject';
        }
    }
    header('Location: subjects.php');
    exit;
}

// Check if archived column exists
try {
    $columnCheck = db()->query("SHOW COLUMNS FROM subjects LIKE 'archived'");
    $archivedColumnExists = $columnCheck->rowCount() > 0;
} catch (PDOException $e) {
    $archivedColumnExists = false;
}

// If column doesn't exist, add it
if (!$archivedColumnExists) {
    try {
        db()->exec("ALTER TABLE subjects ADD COLUMN archived TINYINT(1) DEFAULT 0");
        $archivedColumnExists = true;
    } catch (PDOException $e) {
        // Handle error if needed
    }
}

// Check if grade_level column exists
try {
    $columnCheck = db()->query("SHOW COLUMNS FROM subjects LIKE 'grade_level'");
    $gradeLevelColumnExists = $columnCheck->rowCount() > 0;
} catch (PDOException $e) {
    $gradeLevelColumnExists = false;
}

// If column doesn't exist, add it
if (!$gradeLevelColumnExists) {
    try {
        db()->exec("ALTER TABLE subjects ADD COLUMN grade_level VARCHAR(50) DEFAULT NULL AFTER description");
        $gradeLevelColumnExists = true;
    } catch (PDOException $e) {
        // Handle error if needed
    }
}

// Pagination and filtering setup
$perPageOptions = [12, 24, 48];
$perPage = (int) ($_GET['per_page'] ?? 12);
if (!in_array($perPage, $perPageOptions, true)) {
    $perPage = 12;
}

$searchQuery = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? 'active')); // active, archived, all
$subjectNameFilter = trim((string) ($_GET['subject_name'] ?? '')); // subject name filter

// Get existing subject names for dropdown (excluding archived unless viewing archived tab)
$subjectNamesQuery = "SELECT DISTINCT name FROM subjects WHERE archived = " . ($statusFilter === 'archived' ? '1' : '0') . " ORDER BY name";
$subjectNamesStmt = db()->query($subjectNamesQuery);
$existingSubjectNames = $subjectNamesStmt->fetchAll(PDO::FETCH_COLUMN);

// Build WHERE conditions
$where = [];
$params = [];

// Role-based filtering
if ($role === 'teacher') {
    $where[] = 's.id IN (SELECT subject_id FROM folder_teacher WHERE teacher_empidno = ?)';
    $params[] = $user['empidno'];
}

// Status filter
if ($archivedColumnExists) {
    if ($statusFilter === 'active') {
        $where[] = 's.archived = 0';
    } elseif ($statusFilter === 'archived') {
        $where[] = 's.archived = 1';
    }
} else {
    if ($statusFilter === 'archived') {
        $where[] = '1 = 0'; // No results for archived filter
    }
}

// Search filter
if ($searchQuery !== '') {
    $where[] = '(s.code LIKE ? OR s.description LIKE ?)';
    $like = '%' . $searchQuery . '%';
    $params = array_merge($params, [$like, $like]);
}

// Subject name filter
if ($subjectNameFilter !== '') {
    $where[] = 's.name = ?';
    $params[] = $subjectNameFilter;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total subjects for pagination
$countStmt = db()->prepare("SELECT COUNT(*) FROM subjects s {$whereSql}");
$countStmt->execute($params);
$totalSubjects = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalSubjects / $perPage));
$page = max(1, (int) ($_GET['page'] ?? 1));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

// Fetch subjects with pagination
$listStmt = db()->prepare(
    "SELECT s.*, u.name as created_by_name FROM subjects s 
     LEFT JOIN users u ON s.created_by = u.id 
     {$whereSql} ORDER BY s.name LIMIT {$perPage} OFFSET {$offset}"
);
$listStmt->execute($params);
$subjects = $listStmt->fetchAll();

$rangeStart = $totalSubjects > 0 ? $offset + 1 : 0;
$rangeEnd = min($offset + count($subjects), $totalSubjects);

// URL helper function
$subjectListUrl = static function (array $overrides = []) use ($searchQuery, $statusFilter, $subjectNameFilter, $page, $perPage): string {
    $params = array_filter([
        'page' => $page,
        'q' => $searchQuery,
        'status' => $statusFilter !== 'active' ? $statusFilter : null,
        'subject_name' => $subjectNameFilter !== '' ? $subjectNameFilter : null,
        'per_page' => $perPage !== 12 ? $perPage : null,
    ], static fn ($v) => $v !== '' && $v !== null);
    foreach ($overrides as $key => $value) {
        if ($value === '' || $value === null || ($key === 'page' && (int) $value <= 1)) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    $qs = http_build_query($params);
    return 'subjects.php' . ($qs !== '' ? '?' . $qs : '');
};

// Pagination pages calculation
$paginationPages = [];
if ($totalPages > 1) {
    $windowStart = max(1, $page - 2);
    $windowEnd = min($totalPages, $page + 2);
    if ($windowStart > 1) {
        $paginationPages[] = 1;
        if ($windowStart > 2) {
            $paginationPages[] = '…';
        }
    }
    for ($p = $windowStart; $p <= $windowEnd; $p++) {
        $paginationPages[] = $p;
    }
    if ($windowEnd < $totalPages) {
        if ($windowEnd < $totalPages - 1) {
            $paginationPages[] = '…';
        }
        $paginationPages[] = $totalPages;
    }
}

// DepEd Subject Templates
$depedSubjects = [
    'Preschool' => [
        'Nursery 1' => [
            ['code' => 'NURS1-ENG', 'name' => 'English & Literacy 1'],
            ['code' => 'NURS1-FIL', 'name' => 'Filipino at Wika 1'],
            ['code' => 'NURS1-MATH', 'name' => 'Mathematics (Numeracy) 1'],
            ['code' => 'NURS1-SCI', 'name' => 'Science (Sensory) 1'],
            ['code' => 'NURS1-SOC', 'name' => 'Socio-Emotional Development 1'],
            ['code' => 'NURS1-ART', 'name' => 'Creative Arts & Music 1'],
        ],
        'Nursery 2' => [
            ['code' => 'NURS2-ENG', 'name' => 'English & Literacy 2'],
            ['code' => 'NURS2-FIL', 'name' => 'Filipino at Wika 2'],
            ['code' => 'NURS2-MATH', 'name' => 'Mathematics (Numeracy) 2'],
            ['code' => 'NURS2-SCI', 'name' => 'Science (Sensory) 2'],
            ['code' => 'NURS2-SOC', 'name' => 'Socio-Emotional Development 2'],
            ['code' => 'NURS2-ART', 'name' => 'Creative Arts & Music 2'],
        ],
        'Preparatory' => [
            ['code' => 'PREP-ENG', 'name' => 'Reading and Language (English)'],
            ['code' => 'PREP-FIL', 'name' => 'Wika at Komunikasyon (Filipino)'],
            ['code' => 'PREP-MATH', 'name' => 'Mathematics (Early Numeracy)'],
            ['code' => 'PREP-SCI', 'name' => 'Science (Exploring Environment)'],
            ['code' => 'PREP-ESP', 'name' => 'Character Education (EsP)'],
            ['code' => 'PREP-MAPEH', 'name' => 'MAPEH (Arts & Movement)'],
        ],
    ],
    'Elementary' => [
        'Grade 1' => [
            ['code' => 'G1-MT', 'name' => 'Mother Tongue 1'],
            ['code' => 'G1-FIL', 'name' => 'Filipino 1'],
            ['code' => 'G1-ENG', 'name' => 'English 1'],
            ['code' => 'G1-MATH', 'name' => 'Mathematics 1'],
            ['code' => 'G1-AP', 'name' => 'Araling Panlipunan 1'],
            ['code' => 'G1-MAPEH', 'name' => 'MAPEH 1'],
            ['code' => 'G1-ESP', 'name' => 'EsP 1'],
        ],
        'Grade 2' => [
            ['code' => 'G2-MT', 'name' => 'Mother Tongue 2'],
            ['code' => 'G2-FIL', 'name' => 'Filipino 2'],
            ['code' => 'G2-ENG', 'name' => 'English 2'],
            ['code' => 'G2-MATH', 'name' => 'Mathematics 2'],
            ['code' => 'G2-AP', 'name' => 'Araling Panlipunan 2'],
            ['code' => 'G2-MAPEH', 'name' => 'MAPEH 2'],
            ['code' => 'G2-ESP', 'name' => 'EsP 2'],
        ],
        'Grade 3' => [
            ['code' => 'G3-MT', 'name' => 'Mother Tongue 3'],
            ['code' => 'G3-FIL', 'name' => 'Filipino 3'],
            ['code' => 'G3-ENG', 'name' => 'English 3'],
            ['code' => 'G3-MATH', 'name' => 'Mathematics 3'],
            ['code' => 'G3-SCI', 'name' => 'Science 3'],
            ['code' => 'G3-AP', 'name' => 'Araling Panlipunan 3'],
            ['code' => 'G3-MAPEH', 'name' => 'MAPEH 3'],
            ['code' => 'G3-ESP', 'name' => 'EsP 3'],
        ],
        'Grade 4' => [
            ['code' => 'G4-FIL', 'name' => 'Filipino 4'],
            ['code' => 'G4-ENG', 'name' => 'English 4'],
            ['code' => 'G4-MATH', 'name' => 'Mathematics 4'],
            ['code' => 'G4-SCI', 'name' => 'Science 4'],
            ['code' => 'G4-AP', 'name' => 'Araling Panlipunan 4'],
            ['code' => 'G4-EPP', 'name' => 'EPP 4'],
            ['code' => 'G4-MAPEH', 'name' => 'MAPEH 4'],
            ['code' => 'G4-ESP', 'name' => 'EsP 4'],
        ],
        'Grade 5' => [
            ['code' => 'G5-FIL', 'name' => 'Filipino 5'],
            ['code' => 'G5-ENG', 'name' => 'English 5'],
            ['code' => 'G5-MATH', 'name' => 'Mathematics 5'],
            ['code' => 'G5-SCI', 'name' => 'Science 5'],
            ['code' => 'G5-AP', 'name' => 'Araling Panlipunan 5'],
            ['code' => 'G5-EPP', 'name' => 'EPP 5'],
            ['code' => 'G5-MAPEH', 'name' => 'MAPEH 5'],
            ['code' => 'G5-ESP', 'name' => 'EsP 5'],
        ],
        'Grade 6' => [
            ['code' => 'G6-FIL', 'name' => 'Filipino 6'],
            ['code' => 'G6-ENG', 'name' => 'English 6'],
            ['code' => 'G6-MATH', 'name' => 'Mathematics 6'],
            ['code' => 'G6-SCI', 'name' => 'Science 6'],
            ['code' => 'G6-AP', 'name' => 'Araling Panlipunan 6'],
            ['code' => 'G6-TLE', 'name' => 'TLE 6'],
            ['code' => 'G6-MAPEH', 'name' => 'MAPEH 6'],
            ['code' => 'G6-ESP', 'name' => 'EsP 6'],
        ],
    ],
    'Junior High School' => [
        'Grade 7' => [
            ['code' => 'ENG7', 'name' => 'English 7'],
            ['code' => 'FIL7', 'name' => 'Filipino 7'],
            ['code' => 'MATH7', 'name' => 'Mathematics 7'],
            ['code' => 'SCI7', 'name' => 'Science 7'],
            ['code' => 'AP7', 'name' => 'Araling Panlipunan 7'],
            ['code' => 'TLE7', 'name' => 'Technology and Livelihood Education 7'],
            ['code' => 'MAPEH7', 'name' => 'MAPEH 7'],
            ['code' => 'ESP7', 'name' => 'Edukasyon sa Pagpapakatao 7'],
        ],
        'Grade 8' => [
            ['code' => 'ENG8', 'name' => 'English 8'],
            ['code' => 'FIL8', 'name' => 'Filipino 8'],
            ['code' => 'MATH8', 'name' => 'Mathematics 8'],
            ['code' => 'SCI8', 'name' => 'Science 8'],
            ['code' => 'AP8', 'name' => 'Araling Panlipunan 8'],
            ['code' => 'TLE8', 'name' => 'Technology and Livelihood Education 8'],
            ['code' => 'MAPEH8', 'name' => 'MAPEH 8'],
            ['code' => 'ESP8', 'name' => 'Edukasyon sa Pagpapakatao 8'],
        ],
        'Grade 9' => [
            ['code' => 'ENG9', 'name' => 'English 9'],
            ['code' => 'FIL9', 'name' => 'Filipino 9'],
            ['code' => 'MATH9', 'name' => 'Mathematics 9'],
            ['code' => 'SCI9', 'name' => 'Science 9'],
            ['code' => 'AP9', 'name' => 'Araling Panlipunan 9'],
            ['code' => 'TLE9', 'name' => 'Technology and Livelihood Education 9'],
            ['code' => 'MAPEH9', 'name' => 'MAPEH 9'],
            ['code' => 'ESP9', 'name' => 'Edukasyon sa Pagpapakatao 9'],
        ],
        'Grade 10' => [
            ['code' => 'ENG10', 'name' => 'English 10'],
            ['code' => 'FIL10', 'name' => 'Filipino 10'],
            ['code' => 'MATH10', 'name' => 'Mathematics 10'],
            ['code' => 'SCI10', 'name' => 'Science 10'],
            ['code' => 'AP10', 'name' => 'Araling Panlipunan 10'],
            ['code' => 'TLE10', 'name' => 'Technology and Livelihood Education 10'],
            ['code' => 'MAPEH10', 'name' => 'MAPEH 10'],
            ['code' => 'ESP10', 'name' => 'Edukasyon sa Pagpapakatao 10'],
        ],
    ],
    'Senior High School' => [
        'Core Subjects' => [
            ['code' => 'ORALCOM', 'name' => 'Oral Communication'],
            ['code' => 'KOMFIL', 'name' => 'Komunikasyon at Pananaliksik'],
            ['code' => 'GENMATH', 'name' => 'General Mathematics'],
            ['code' => 'STAT', 'name' => 'Statistics and Probability'],
            ['code' => 'EARTHSCI', 'name' => 'Earth and Life Science'],
            ['code' => 'PHYSSCI', 'name' => 'Physical Science'],
            ['code' => 'PEHM', 'name' => 'Physical Education and Health'],
            ['code' => 'UCSP', 'name' => 'Understanding Culture, Society and Politics'],
            ['code' => 'CONTEMP', 'name' => 'Contemporary Philippine Arts'],
            ['code' => 'MEDIA', 'name' => 'Media and Information Literacy'],
        ],
        'STEM Track' => [
            ['code' => 'PRECAL', 'name' => 'Pre-Calculus'],
            ['code' => 'BASICCAL', 'name' => 'Basic Calculus'],
            ['code' => 'GENCHEM', 'name' => 'General Chemistry'],
            ['code' => 'GENPHY', 'name' => 'General Physics'],
            ['code' => 'GENBIO', 'name' => 'General Biology'],
        ],
        'ABM Track' => [
            ['code' => 'FUNACCO', 'name' => 'Fundamentals of Accountancy'],
            ['code' => 'BUSMATH', 'name' => 'Business Mathematics'],
            ['code' => 'BUSFIN', 'name' => 'Business Finance'],
            ['code' => 'ORGMGT', 'name' => 'Organization and Management'],
            ['code' => 'BUSMARK', 'name' => 'Principles of Marketing'],
        ],
        'HUMSS Track' => [
            ['code' => 'CREWRITE', 'name' => 'Creative Writing'],
            ['code' => 'CRENONFIC', 'name' => 'Creative Nonfiction'],
            ['code' => 'PHILHIST', 'name' => 'Philippine History'],
            ['code' => 'WORLDREL', 'name' => 'World Religions'],
            ['code' => 'TRENDS', 'name' => 'Trends, Networks and Critical Thinking'],
        ],
        'TVL Track' => [
            ['code' => 'EMPTECH', 'name' => 'Empowerment Technologies'],
            ['code' => 'ENTRE', 'name' => 'Entrepreneurship'],
            ['code' => 'INQUIRE', 'name' => 'Inquiries, Investigations and Immersion'],
        ],
    ],
];

// Handle Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    requireCsrf();
    $code = trim($_POST['subject_code'] ?? '');
    $name = trim($_POST['subject_name'] ?? '');
    $description = trim($_POST['subject_description'] ?? '');
    $grade_level = trim($_POST['grade_level'] ?? '');
    
    if ($code && $name) {
        try {
            // Generate enrollment key
            $enrollmentKey = strtoupper(bin2hex(random_bytes(4)));
            
            if ($gradeLevelColumnExists) {
                $stmt = db()->prepare('INSERT INTO subjects (code, name, description, grade_level, enrollment_key, created_by, created_at, archived) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)');
                $stmt->execute([$code, $name, $description, !empty($grade_level) ? $grade_level : null, $enrollmentKey, $user['id']]);
            } else {
                $stmt = db()->prepare('INSERT INTO subjects (code, name, description, enrollment_key, created_by, created_at, archived) VALUES (?, ?, ?, ?, ?, NOW(), 0)');
                $stmt->execute([$code, $name, $description, $enrollmentKey, $user['id']]);
            }
            $subjectId = (int)db()->lastInsertId();
            saveAudit($user['id'], 'create', 'subject', $subjectId, compact('code', 'name', 'description'));
            
            // Redirect to prevent duplicate submission and show success message
            $_SESSION['success_message'] = 'Subject added successfully!';
            header('Location: subjects.php');
            exit;
        } catch (Exception $e) {
            $error = 'Failed to add subject. Code may already exist.';
        }
    }
}

// Handle Bulk Add DepEd Subjects
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_add_subjects'])) {
    requireCsrf();
    $selectedSubjects = $_POST['selected_subjects'] ?? [];
    
    if (!empty($selectedSubjects)) {
        $successCount = 0;
        $skippedCount = 0;
        $errors = [];
        
        foreach ($selectedSubjects as $subjectData) {
            $decoded = json_decode($subjectData, true);
            if (!$decoded || !isset($decoded['code']) || !isset($decoded['name'])) {
                continue;
            }
            
            $code = trim($decoded['code']);
            $name = trim($decoded['name']);
            $description = trim($decoded['description'] ?? '');
            $grade_level = trim($decoded['grade_level'] ?? '');
            
            if ($code && $name) {
                try {
                    // Check if subject already exists
                    $checkStmt = db()->prepare('SELECT id FROM subjects WHERE code = ? AND archived = 0');
                    $checkStmt->execute([$code]);
                    if ($checkStmt->fetch()) {
                        $skippedCount++;
                        continue;
                    }
                    
                    // Generate enrollment key
                    $enrollmentKey = strtoupper(bin2hex(random_bytes(4)));
                    
                    if ($gradeLevelColumnExists) {
                        $stmt = db()->prepare('INSERT INTO subjects (code, name, description, grade_level, enrollment_key, created_by, created_at, archived) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)');
                        $stmt->execute([$code, $name, $description, !empty($grade_level) ? $grade_level : null, $enrollmentKey, $user['id']]);
                    } else {
                        $stmt = db()->prepare('INSERT INTO subjects (code, name, description, enrollment_key, created_by, created_at, archived) VALUES (?, ?, ?, ?, ?, NOW(), 0)');
                        $stmt->execute([$code, $name, $description, $enrollmentKey, $user['id']]);
                    }
                    $subjectId = (int)db()->lastInsertId();
                    saveAudit($user['id'], 'create', 'subject', $subjectId, compact('code', 'name', 'description'));
                    $successCount++;
                } catch (Exception $e) {
                    $errors[] = "Failed to add {$code}: " . $e->getMessage();
                }
            }
        }
        
        // Prepare success message
        $messages = [];
        if ($successCount > 0) {
            $messages[] = "{$successCount} subject" . ($successCount > 1 ? 's' : '') . " added successfully";
        }
        if ($skippedCount > 0) {
            $messages[] = "{$skippedCount} subject" . ($skippedCount > 1 ? 's' : '') . " skipped (already exists)";
        }
        if (!empty($errors)) {
            $messages[] = count($errors) . " error" . (count($errors) > 1 ? 's' : '') . " occurred";
        }
        
        if (!empty($messages)) {
            $_SESSION['success_message'] = implode('. ', $messages) . '.';
        }
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('. ', array_slice($errors, 0, 3)) . (count($errors) > 3 ? '...' : '');
        }
    } else {
        $_SESSION['error_message'] = 'No subjects selected for bulk creation.';
    }
    
    header('Location: subjects.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subjects - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        .subject-list-minimal { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .subject-list-minimal .sl-action-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .subject-list-minimal .sl-action-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
        }
        .subject-list-minimal .sl-action-buttons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .subject-list-minimal .sl-tabs {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-bottom: 1.25rem;
            overflow: hidden;
        }
        .subject-list-minimal .sl-tabs .nav-tabs {
            border-bottom: 1px solid #e2e8f0;
            background: #f8f9fa;
            margin: 0;
        }
        .subject-list-minimal .sl-tabs .nav-link {
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 500;
            padding: 1rem 1.5rem;
            position: relative;
        }
        .subject-list-minimal .sl-tabs .nav-link.active {
            background: #fff;
            color: #1e293b;
            border-bottom: 2px solid #3b82f6;
        }
        .subject-list-minimal .sl-tabs .nav-link:hover:not(.active) {
            background: #f1f5f9;
            color: #475569;
        }
        .subject-list-minimal .sl-filter-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .subject-list-minimal .sl-filter-stack {
            width: 100%;
        }
        .subject-list-minimal .sl-filter-stack .form-control,
        .subject-list-minimal .sl-filter-stack .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: #fff;
            color: #374151;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            height: 38px;
        }
        .subject-list-minimal .sl-filter-stack .form-control:focus,
        .subject-list-minimal .sl-filter-stack .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .subject-list-minimal .sl-filter-stack .btn {
            height: 38px;
            display: flex;
            align-items: center;
        }
        .subject-list-minimal .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .subject-list-minimal .subject-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.15s ease;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .subject-list-minimal .subject-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #d1d5db;
            transform: translateY(-1px);
        }
        .subject-list-minimal .subject-code {
            display: inline-block;
            background: #f3f4f6;
            color: #374151;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .subject-list-minimal .subject-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .subject-list-minimal .subject-description {
            color: #6b7280;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            min-height: 2.5rem;
        }
        .subject-list-minimal .subject-meta {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }
        .subject-list-minimal .subject-actions {
            display: flex;
            gap: 0.5rem;
        }
        .subject-list-minimal .btn-view {
            flex: 1;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            text-decoration: none;
            text-align: center;
            transition: all 0.15s ease;
        }
        .subject-list-minimal .btn-view:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
        }
        .subject-list-minimal .btn-archive {
            padding: 0.5rem;
            background: transparent;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            transition: all 0.15s ease;
        }
        .subject-list-minimal .btn-archive:hover {
            background: #f3f4f6;
            color: #374151;
        }
        .subject-list-minimal .archived-card {
            opacity: 0.6;
            background: #f9fafb;
            border-color: #e5e7eb;
        }
        .subject-list-minimal .archived-card .subject-code {
            background: #e5e7eb;
            color: #6b7280;
        }
        .subject-list-minimal .btn-unarchive {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        .subject-list-minimal .btn-unarchive:hover {
            background: #059669;
            border-color: #059669;
        }
        .subject-list-minimal .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        .subject-list-minimal .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .subject-list-minimal .sl-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1rem;
            padding: 1.5rem 1rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .subject-list-minimal .sl-pagination-info {
            font-size: 0.875rem;
            color: #64748b;
        }
        .subject-list-minimal .sl-pagination-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
        }
        .subject-list-minimal .sl-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            font-size: 0.875rem;
            color: #4b5563;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            line-height: 1;
        }
        .subject-list-minimal .sl-page-btn:hover:not(.disabled):not(.active) {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #374151;
        }
        .subject-list-minimal .sl-page-btn.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #fff;
            font-weight: 600;
        }
        .subject-list-minimal .sl-page-btn.disabled {
            opacity: 0.45;
            pointer-events: none;
        }
        @media (max-width: 768px) {
            .subject-list-minimal .subjects-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .subject-list-minimal .sl-filter-bar {
                padding: 0.75rem;
            }
            .subject-list-minimal .sl-filter-stack .d-flex {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }
            .subject-list-minimal .sl-filter-stack .form-control,
            .subject-list-minimal .sl-filter-stack .form-select {
                min-width: unset;
                max-width: unset;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Subjects'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4 subject-list-minimal">
            <?php if ($message): ?>
            <div class="alert alert-success py-2 px-3 small mb-3"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="sl-action-card">
                <h2 class="sl-action-title">Subject Management</h2>
                <div class="sl-action-buttons">
                    <?php if ($role === 'admin'): ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="bi bi-plus-circle me-2"></i>Add Subject
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#depedSubjectsModal">
                        <i class="bi bi-grid me-2"></i>DepEd Templates
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sl-tabs">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link<?= $statusFilter === 'active' ? ' active' : '' ?>" 
                           href="<?= htmlspecialchars($subjectListUrl(['status' => 'active', 'page' => 1])) ?>">
                            <i class="bi bi-book me-2"></i>Active Subjects
                        </a>
                    </li>
                    <?php if ($archivedColumnExists): ?>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link<?= $statusFilter === 'archived' ? ' active' : '' ?>" 
                           href="<?= htmlspecialchars($subjectListUrl(['status' => 'archived', 'page' => 1])) ?>">
                            <i class="bi bi-archive me-2"></i>Archived Subjects
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>

                <div class="p-3">
                    <form method="get" class="sl-filter-bar" id="subjectFilterForm">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                        <div class="sl-filter-stack">
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <input type="text" name="q" id="searchSubjects" class="form-control" 
                                       placeholder="Search by code or description..." value="<?= htmlspecialchars($searchQuery) ?>"
                                       style="flex: 1; min-width: 200px; max-width: 300px;">
                                <select name="subject_name" class="form-select" style="min-width: 180px; max-width: 250px;">
                                    <option value="">All Subject Names</option>
                                    <?php foreach ($existingSubjectNames as $subjectName): ?>
                                    <option value="<?= htmlspecialchars($subjectName) ?>" <?= $subjectNameFilter === $subjectName ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($subjectName) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($subjectNameFilter !== '' || $searchQuery !== ''): ?>
                                <a href="<?= htmlspecialchars($subjectListUrl(['q' => '', 'subject_name' => '', 'page' => 1])) ?>" 
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($subjects)): ?>
                    <div class="subjects-grid">
                        <?php foreach ($subjects as $subject): ?>
                        <div class="subject-card<?= isset($subject['archived']) && $subject['archived'] ? ' archived-card' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="subject-code"><?= htmlspecialchars($subject['code']) ?></div>
                                <?php if (isset($subject['grade_level']) && $subject['grade_level']): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($subject['grade_level']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h3 class="subject-name"><?= htmlspecialchars($subject['name']) ?></h3>
                            <p class="subject-description">
                                <?= htmlspecialchars($subject['description'] ?: 'No description available') ?>
                            </p>
                            <?php if (isset($subject['created_by_name'])): ?>
                            <div class="subject-meta">
                                <i class="bi bi-person me-1"></i>Created by <?= htmlspecialchars($subject['created_by_name'] ?: 'Unknown') ?>
                                <?php if ($subject['created_at']): ?>
                                • <?= date('M j, Y', strtotime($subject['created_at'])) ?>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="subject-actions">
                                <a href="subject-detail.php?id=<?= $subject['id'] ?>" class="btn-view">
                                    <i class="bi bi-eye me-1"></i>View Details
                                </a>
                                <?php if ($role === 'admin'): ?>
                                    <?php if (isset($subject['archived']) && $subject['archived']): ?>
                                    <button class="btn btn-archive btn-unarchive" 
                                            onclick="confirmUnarchive(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['name']) ?>')"
                                            title="Unarchive Subject">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <?php else: ?>
                                    <button class="btn btn-archive" 
                                            onclick="confirmArchive(<?= $subject['id'] ?>, '<?= htmlspecialchars($subject['name']) ?>')"
                                            title="Archive Subject">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h4>No subjects found</h4>
                        <p class="mb-0">
                            <?php if ($statusFilter === 'archived'): ?>
                            No archived subjects to display.
                            <?php elseif ($searchQuery): ?>
                            No subjects match your search criteria.
                            <?php else: ?>
                            <?= $role === 'admin' ? 'Get started by adding your first subject.' : 'No subjects have been assigned to you yet.' ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ($totalSubjects > 0 && $totalPages > 1): ?>
                    <nav class="sl-pagination">
                        <span class="sl-pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
                        <div class="sl-pagination-nav">
                            <a href="<?= htmlspecialchars($subjectListUrl(['page' => max(1, $page - 1)])) ?>" 
                               class="sl-page-btn<?= $page <= 1 ? ' disabled' : '' ?>">&lsaquo;</a>
                            <?php foreach ($paginationPages as $p): ?>
                                <?php if ($p === '…'): ?>
                                <span class="sl-page-btn disabled">…</span>
                                <?php else: ?>
                                <a href="<?= htmlspecialchars($subjectListUrl(['page' => $p])) ?>" 
                                   class="sl-page-btn<?= (int) $p === $page ? ' active' : '' ?>"><?= (int) $p ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="<?= htmlspecialchars($subjectListUrl(['page' => min($totalPages, $page + 1)])) ?>" 
                               class="sl-page-btn<?= $page >= $totalPages ? ' disabled' : '' ?>">&rsaquo;</a>
                        </div>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="archiveSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Archive Subject</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" id="archive_subject_id" name="subject_id">
                    <div class="modal-body">
                        <p>Are you sure you want to archive: <strong id="archive_subject_name"></strong>?</p>
                        <p class="text-warning"><small>This subject will be moved to the archived section but can be restored later.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="archive_subject" class="btn btn-warning">
                            <i class="bi bi-archive me-2"></i>Archive Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Unarchive Confirmation Modal -->
    <div class="modal fade" id="unarchiveSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-arrow-clockwise me-2"></i>Unarchive Subject</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" id="unarchive_subject_id" name="subject_id">
                    <div class="modal-body">
                        <p>Are you sure you want to unarchive: <strong id="unarchive_subject_name"></strong>?</p>
                        <p class="text-success"><small>This subject will be moved back to the active subjects section.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="unarchive_subject" class="btn btn-success">
                            <i class="bi bi-arrow-clockwise me-2"></i>Unarchive Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subject_code" class="form-label">Subject Code</label>
                            <input type="text" class="form-control" id="subject_code" name="subject_code" required>
                            <small class="text-muted">e.g., ENG7, MATH10, STEM-PRECAL</small>
                        </div>
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
                        </div>
                        <?php if ($gradeLevelColumnExists): ?>
                        <div class="mb-3">
                            <label for="subject_grade_level" class="form-label">Grade Level</label>
                            <select class="form-select" id="subject_grade_level" name="grade_level">
                                <option value="">Select Grade Level (Optional)</option>
                                <optgroup label="Preschool / Kindergarten">
                                    <option value="Nursery 1">Nursery 1</option>
                                    <option value="Nursery 2">Nursery 2</option>
                                    <option value="Preparatory">Preparatory</option>
                                </optgroup>
                                <optgroup label="Elementary">
                                    <option value="Grade 1">Grade 1</option>
                                    <option value="Grade 2">Grade 2</option>
                                    <option value="Grade 3">Grade 3</option>
                                    <option value="Grade 4">Grade 4</option>
                                    <option value="Grade 5">Grade 5</option>
                                    <option value="Grade 6">Grade 6</option>
                                </optgroup>
                                <optgroup label="Junior High School">
                                    <option value="Grade 7">Grade 7</option>
                                    <option value="Grade 8">Grade 8</option>
                                    <option value="Grade 9">Grade 9</option>
                                    <option value="Grade 10">Grade 10</option>
                                </optgroup>
                                <optgroup label="Senior High School Tracks">
                                    <option value="Core Subjects">Core Subjects</option>
                                    <option value="STEM Track">STEM Track</option>
                                    <option value="ABM Track">ABM Track</option>
                                    <option value="HUMSS Track">HUMSS Track</option>
                                    <option value="TVL Track">TVL Track</option>
                                </optgroup>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="subject_description" class="form-label">Description</label>
                            <textarea class="form-control" id="subject_description" name="subject_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_subject" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Add Subject
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DepEd Subjects Modal -->
    <div class="modal fade" id="depedSubjectsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-grid me-2"></i>DepEd Subject Templates</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="subjects.php" id="bulkDepedForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                        <input type="hidden" name="bulk_add_subjects" value="1">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllVisible()">
                                    <i class="bi bi-check-all"></i> Select All Visible
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllSelected()">
                                    <i class="bi bi-x-circle"></i> Clear All
                                </button>
                                <div class="btn-group ms-2" role="group" id="preschoolGradeButtons">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Nursery 1')">Nursery 1</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Nursery 2')">Nursery 2</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Preparatory')">Preparatory</button>
                                </div>
                                <div class="btn-group ms-2" role="group" id="elementaryGradeButtons" style="display:none;">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 1')">Grade 1</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 2')">Grade 2</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 3')">Grade 3</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 4')">Grade 4</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 5')">Grade 5</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 6')">Grade 6</button>
                                </div>
                                <div class="btn-group ms-2" role="group" id="jhsGradeButtons" style="display:none;">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 7')">Grade 7</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 8')">Grade 8</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 9')">Grade 9</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="selectByGrade('Grade 10')">Grade 10</button>
                                </div>
                                <div class="btn-group ms-2" role="group" id="shsTrackButtons" style="display:none;">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectByGrade('Core Subjects')">Core</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectByGrade('STEM Track')">STEM</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectByGrade('ABM Track')">ABM</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectByGrade('HUMSS Track')">HUMSS</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectByGrade('TVL Track')">TVL</button>
                                </div>
                            </div>
                            <div>
                                <span id="selectedCount">0</span> subjects selected
                                <button type="submit" class="btn btn-primary ms-2" id="bulkAddBtn" disabled>
                                    <i class="bi bi-plus-circle"></i> Add Selected Subjects
                                </button>
                            </div>
                        </div>
                    <ul class="nav nav-tabs mb-4" id="depedTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#preschool-tab" type="button">Preschool</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#elementary-tab" type="button">Elementary</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#jhs-tab" type="button">Junior High School</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shs-tab" type="button">Senior High School</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Preschool Tab -->
                        <div class="tab-pane fade show active" id="preschool-tab">
                            <div class="row g-4">
                                <?php foreach ($depedSubjects['Preschool'] as $grade => $subjects_list): ?>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-info text-dark">
                                            <h6 class="mb-0"><i class="bi bi-bookmark-fill me-2"></i><?= $grade ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($subjects_list as $subj): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check d-flex align-items-center">
                                                            <input class="form-check-input me-3" type="checkbox" 
                                                                   name="selected_subjects[]" 
                                                                   value='<?= json_encode(['code' => $subj['code'], 'name' => $subj['name'], 'grade_level' => $grade, 'description' => 'DepEd ' . $grade . ' Subject']) ?>'
                                                                   onchange="updateSelectedCount()">
                                                            <div>
                                                                <strong><?= htmlspecialchars($subj['code']) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($subj['name']) ?></small>
                                                            </div>
                                                        </div>
                                                        <button type="button" 
                                                                onclick="addDepEdSubject('<?= htmlspecialchars($subj['code']) ?>', '<?= htmlspecialchars($subj['name']) ?>', 'DepEd <?= $grade ?> Subject', '<?= htmlspecialchars($grade) ?>')"
                                                                class="btn btn-sm btn-outline-info">
                                                            <i class="bi bi-plus-circle"></i> Add Individual
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Elementary Tab -->
                        <div class="tab-pane fade" id="elementary-tab">
                            <div class="row g-4">
                                <?php foreach ($depedSubjects['Elementary'] as $grade => $subjects_list): ?>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h6 class="mb-0"><i class="bi bi-bookmark-fill me-2"></i><?= $grade ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($subjects_list as $subj): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check d-flex align-items-center">
                                                            <input class="form-check-input me-3" type="checkbox" 
                                                                   name="selected_subjects[]" 
                                                                   value='<?= json_encode(['code' => $subj['code'], 'name' => $subj['name'], 'grade_level' => $grade, 'description' => 'DepEd ' . $grade . ' Subject']) ?>'
                                                                   onchange="updateSelectedCount()">
                                                            <div>
                                                                <strong><?= htmlspecialchars($subj['code']) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($subj['name']) ?></small>
                                                            </div>
                                                        </div>
                                                        <button type="button" 
                                                                onclick="addDepEdSubject('<?= htmlspecialchars($subj['code']) ?>', '<?= htmlspecialchars($subj['name']) ?>', 'DepEd <?= $grade ?> Subject', '<?= htmlspecialchars($grade) ?>')"
                                                                class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-plus-circle"></i> Add Individual
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Junior High School Tab -->
                        <div class="tab-pane fade" id="jhs-tab">
                            <div class="row g-4">
                                <?php foreach ($depedSubjects['Junior High School'] as $grade => $subjects_list): ?>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bi bi-bookmark-fill me-2"></i><?= $grade ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($subjects_list as $subj): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check d-flex align-items-center">
                                                            <input class="form-check-input me-3" type="checkbox" 
                                                                   name="selected_subjects[]" 
                                                                   value='<?= json_encode(['code' => $subj['code'], 'name' => $subj['name'], 'grade_level' => $grade, 'description' => 'DepEd ' . $grade . ' Subject']) ?>'
                                                                   onchange="updateSelectedCount()">
                                                            <div>
                                                                <strong><?= htmlspecialchars($subj['code']) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($subj['name']) ?></small>
                                                            </div>
                                                        </div>
                                                        <button type="button" 
                                                                onclick="addDepEdSubject('<?= htmlspecialchars($subj['code']) ?>', '<?= htmlspecialchars($subj['name']) ?>', 'DepEd <?= $grade ?> Subject', '<?= htmlspecialchars($grade) ?>')"
                                                                class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-plus-circle"></i> Add Individual
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Senior High School Tab -->
                        <div class="tab-pane fade" id="shs-tab">
                            <div class="row g-4">
                                <?php foreach ($depedSubjects['Senior High School'] as $track => $subjects_list): ?>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="bi bi-bookmark-fill me-2"></i><?= $track ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($subjects_list as $subj): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="form-check d-flex align-items-center">
                                                            <input class="form-check-input me-3" type="checkbox" 
                                                                   name="selected_subjects[]" 
                                                                   value='<?= json_encode(['code' => $subj['code'], 'name' => $subj['name'], 'grade_level' => $track, 'description' => 'DepEd SHS - ' . $track]) ?>'
                                                                   onchange="updateSelectedCount()">
                                                            <div>
                                                                <strong><?= htmlspecialchars($subj['code']) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars($subj['name']) ?></small>
                                                            </div>
                                                        </div>
                                                        <button type="button" 
                                                                onclick="addDepEdSubject('<?= htmlspecialchars($subj['code']) ?>', '<?= htmlspecialchars($subj['name']) ?>', 'DepEd SHS - <?= $track ?>', '<?= htmlspecialchars($track) ?>')"
                                                                class="btn btn-sm btn-outline-success">
                                                            <i class="bi bi-plus-circle"></i> Add Individual
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Existing functions
        function confirmArchive(subjectId, subjectName) {
            document.getElementById('archive_subject_id').value = subjectId;
            document.getElementById('archive_subject_name').textContent = subjectName;
            new bootstrap.Modal(document.getElementById('archiveSubjectModal')).show();
        }
        
        function confirmUnarchive(subjectId, subjectName) {
            document.getElementById('unarchive_subject_id').value = subjectId;
            document.getElementById('unarchive_subject_name').textContent = subjectName;
            new bootstrap.Modal(document.getElementById('unarchiveSubjectModal')).show();
        }
        
        function addDepEdSubject(code, name, description, gradeLevel) {
            document.getElementById('subject_code').value = code;
            document.getElementById('subject_name').value = name;
            document.getElementById('subject_description').value = description;
            
            const gradeSelect = document.getElementById('subject_grade_level');
            if (gradeSelect && gradeLevel) {
                gradeSelect.value = gradeLevel;
            }
            
            var depedModal = bootstrap.Modal.getInstance(document.getElementById('depedSubjectsModal'));
            depedModal.hide();
            
            var addModal = new bootstrap.Modal(document.getElementById('addSubjectModal'));
            addModal.show();
        }
        
        // Auto-submit filter form
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('subjectFilterForm');
            const searchInput = document.getElementById('searchSubjects');
            const subjectNameSelect = document.querySelector('select[name="subject_name"]');
            
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = function() {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            
            if (searchInput) {
                searchInput.addEventListener('input', debounce(function() {
                    filterForm.submit();
                }, 500));
            }
            
            if (subjectNameSelect) {
                subjectNameSelect.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });
        
        // Bulk DepEd Subjects functionality
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('input[name="selected_subjects[]"]:checked');
            const count = checkboxes.length;
            document.getElementById('selectedCount').textContent = count;
            document.getElementById('bulkAddBtn').disabled = count === 0;
        }
        
        function selectAllVisible() {
            const activeTab = document.querySelector('.tab-pane.active');
            const checkboxes = activeTab.querySelectorAll('input[name="selected_subjects[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            updateSelectedCount();
        }
        
        function clearAllSelected() {
            const selected = document.querySelectorAll('input[name="selected_subjects[]"]:checked');
            selected.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedCount();
        }
        
        function selectByGrade(gradeName) {
            // First clear all selections
            clearAllSelected();
            
            // Find the card header with the specific grade name
            const activeTab = document.querySelector('.tab-pane.active');
            const gradeHeaders = activeTab.querySelectorAll('.card-header h6');
            
            gradeHeaders.forEach(header => {
                if (header.textContent.trim().includes(gradeName)) {
                    const card = header.closest('.card');
                    const checkboxes = card.querySelectorAll('input[name="selected_subjects[]"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = true;
                    });
                }
            });
            
            updateSelectedCount();
        }
        
        // Handle form submission with confirmation
        document.getElementById('bulkDepedForm').addEventListener('submit', function(e) {
            const checkedCount = document.querySelectorAll('input[name="selected_subjects[]"]:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Please select at least one subject to add.');
                return false;
            }
            
            const confirmation = confirm(`Are you sure you want to add ${checkedCount} selected subjects?`);
            if (!confirmation) {
                e.preventDefault();
                return false;
            }
        });
        
        // Handle tab switching to show appropriate selection buttons
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('#depedTabs button[data-bs-toggle="tab"]');
            const preschoolButtons = document.getElementById('preschoolGradeButtons');
            const elementaryButtons = document.getElementById('elementaryGradeButtons');
            const jhsButtons = document.getElementById('jhsGradeButtons');
            const shsButtons = document.getElementById('shsTrackButtons');
            
            function toggleButtons() {
                const activeTab = document.querySelector('.tab-pane.active');
                if (preschoolButtons) preschoolButtons.style.display = 'none';
                if (elementaryButtons) elementaryButtons.style.display = 'none';
                if (jhsButtons) jhsButtons.style.display = 'none';
                if (shsButtons) shsButtons.style.display = 'none';
                
                if (activeTab) {
                    if (activeTab.id === 'preschool-tab') {
                        if (preschoolButtons) preschoolButtons.style.display = 'inline-block';
                    } else if (activeTab.id === 'elementary-tab') {
                        if (elementaryButtons) elementaryButtons.style.display = 'inline-block';
                    } else if (activeTab.id === 'jhs-tab') {
                        if (jhsButtons) jhsButtons.style.display = 'inline-block';
                    } else if (activeTab.id === 'shs-tab') {
                        if (shsButtons) shsButtons.style.display = 'inline-block';
                    }
                }
            }
            
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', toggleButtons);
            });
            
            // Initialize on page load
            toggleButtons();
        });
    </script>
</body>
</html>
