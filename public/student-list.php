<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/student.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();

// Only allow registrar and admin roles
if (!in_array($user['role'], ['registrar', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

$docLabels = [
    'birth_certificate' => 'Birth Certificate (PSA/NSO)',
    'psa_nso' => 'PSA / NSO',
    'sf10' => 'SF10 / Form 137',
    'peac' => 'PEAC / ESC',
];

function uploadStudentEnrollmentDocuments(int $studentId, int $uploadedBy): void
{
    $uploadDir = __DIR__ . '/uploads/enrollment_documents/' . $studentId . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    $maxSize = 10 * 1024 * 1024;
    $documentFiles = [
        'birth_certificate' => 'birth_certificate',
        'psa_nso' => 'psa_nso',
        'sf10' => 'sf10',
        'peac' => 'peac',
    ];

    foreach ($documentFiles as $inputName => $docType) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            continue;
        }
        $file = $_FILES[$inputName];
        if (!in_array($file['type'], $allowedTypes, true) || $file['size'] > $maxSize) {
            continue;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = $docType . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $uploadDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $relPath = 'uploads/enrollment_documents/' . $studentId . '/' . $safeName;
            $docStmt = db()->prepare(
                'INSERT INTO student_enrollment_documents
                (student_id, document_type, file_name, file_path, file_size, mime_type, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $docStmt->execute([
                $studentId,
                $docType,
                $file['name'],
                $relPath,
                $file['size'],
                $file['type'],
                $uploadedBy,
            ]);
        }
    }
}

$error = null;
$success = isset($_GET['updated']) ? 'Student updated successfully.' : null;

// Handle retention status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_retention_status'])) {
    requireCsrf();
    $studentId = (int)($_POST['student_id'] ?? 0);
    $retentionStatus = trim($_POST['retention_status'] ?? '');
    $retentionReason = trim($_POST['retention_reason'] ?? '');
    $schoolYear = trim($_POST['school_year'] ?? '');
    
    if ($studentId && $retentionStatus) {
        try {
            // Check if retention_status column exists, if not add it
            $columnCheck = db()->query("SHOW COLUMNS FROM users LIKE 'retention_status'");
            if ($columnCheck->rowCount() === 0) {
                db()->exec("ALTER TABLE users ADD COLUMN retention_status ENUM('promoted', 'retained', 'irregular') DEFAULT 'promoted'");
                db()->exec("ALTER TABLE users ADD COLUMN retention_reason TEXT NULL");
                db()->exec("ALTER TABLE users ADD COLUMN retention_school_year VARCHAR(20) NULL");
                db()->exec("ALTER TABLE users ADD COLUMN retention_updated_at TIMESTAMP NULL");
                db()->exec("ALTER TABLE users ADD COLUMN retention_updated_by INT NULL");
            }
            
            $stmt = db()->prepare('UPDATE users SET 
                retention_status = ?, 
                retention_reason = ?, 
                retention_school_year = ?,
                retention_updated_at = NOW(),
                retention_updated_by = ?
                WHERE id = ? AND role = "student"');
            
            if ($stmt->execute([$retentionStatus, $retentionReason, $schoolYear, $user['id'], $studentId])) {
                $success = 'Student retention status updated successfully.';
                saveAudit($user['id'], 'update_retention', 'student', $studentId, [
                    'retention_status' => $retentionStatus,
                    'retention_reason' => $retentionReason,
                    'school_year' => $schoolYear
                ]);
            } else {
                $error = 'Failed to update retention status';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $studentId = $_POST['student_id'] ?? '';
    if ($studentId) {
        $updateName = trim((string) ($_POST['name'] ?? ''));
        $updateEmail = trim((string) ($_POST['email'] ?? ''));
        $updateLrn = isset($_POST['has_lrn']) ? trim((string) ($_POST['lrn_number'] ?? '')) : '';
        $duplicate = studentFindEnrollmentDuplicate($updateName, $updateEmail, $updateLrn, (int) $studentId);
        if ($duplicate !== null) {
            $error = studentEnrollmentDuplicateMessage($duplicate);
        } else {
        try {
            $sql = "UPDATE users SET 
                    name = ?, middle_name = ?, email = ?, grade_level = ?, date_of_birth = ?, gender = ?, age = ?,
                    place_of_birth = ?, nationality = ?, religion = ?, home_address = ?, contact_number = ?,
                    father_name = ?, father_occupation = ?, father_contact = ?, mother_name = ?, mother_occupation = ?,
                    mother_contact = ?, guardian_name = ?, guardian_contact = ?, guardian_relationship = ?,
                    last_school_attended = ?, last_school_address = ?, school_year_completed = ?, general_average = ?,
                    has_lrn = ?, lrn_number = ?, is_returnee = ?, is_transfer_in = ?, has_special_needs = ?,
                    special_needs_type = ?, is_4ps_beneficiary = ?, is_indigenous = ?, indigenous_group = ?, mother_tongue = ?,
                    retention_status = ?, retention_reason = ?, retention_school_year = ?
                    WHERE id = ?";
            
            $stmt = db()->prepare($sql);
            $stmt->execute([
                $_POST['name'], $_POST['middle_name'], $_POST['email'], $_POST['grade_level'],
                $_POST['date_of_birth'] ?: null, $_POST['gender'], $_POST['age'] ?: null,
                $_POST['place_of_birth'], $_POST['nationality'], $_POST['religion'], $_POST['home_address'], $_POST['contact_number'],
                $_POST['father_name'], $_POST['father_occupation'], $_POST['father_contact'],
                $_POST['mother_name'], $_POST['mother_occupation'], $_POST['mother_contact'],
                $_POST['guardian_name'], $_POST['guardian_contact'], $_POST['guardian_relationship'],
                $_POST['last_school_attended'], $_POST['last_school_address'], $_POST['school_year_completed'], $_POST['general_average'],
                isset($_POST['has_lrn']) ? 1 : 0, $_POST['lrn_number'], isset($_POST['is_returnee']) ? 1 : 0,
                isset($_POST['is_transfer_in']) ? 1 : 0, isset($_POST['has_special_needs']) ? 1 : 0,
                $_POST['special_needs_type'], isset($_POST['is_4ps_beneficiary']) ? 1 : 0,
                isset($_POST['is_indigenous']) ? 1 : 0, $_POST['indigenous_group'], $_POST['mother_tongue'],
                $_POST['retention_status'] ?? 'promoted', $_POST['retention_reason'] ?? '', $_POST['retention_school_year'] ?? '',
                $studentId
            ]);

            uploadStudentEnrollmentDocuments((int) $studentId, (int) $user['id']);

            $redirectParams = ['updated' => '1'];
            if (!empty($_POST['return_query'])) {
                parse_str((string) $_POST['return_query'], $redirectParams);
                $redirectParams['updated'] = '1';
            }
            header('Location: student-list.php?' . http_build_query($redirectParams));
            exit;
        } catch (Exception $e) {
            $error = 'Failed to update student: ' . $e->getMessage();
        }
        }
    }
}

$perPageOptions = [10, 20, 50];
$perPage = (int) ($_GET['per_page'] ?? 20);
if (!in_array($perPage, $perPageOptions, true)) {
    $perPage = 20;
}

$searchQuery = trim((string) ($_GET['q'] ?? ''));
$gradeFilter = trim((string) ($_GET['grade'] ?? ''));

$where = ["role = 'student'", 'archived = 0'];
$params = [];

if ($searchQuery !== '') {
    $where[] = '(name LIKE ? OR middle_name LIKE ? OR empidno LIKE ? OR email LIKE ?)';
    $like = '%' . $searchQuery . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($gradeFilter !== '') {
    if ($gradeFilter === 'Unassigned') {
        $where[] = "(grade_level IS NULL OR TRIM(grade_level) = '')";
    } else {
        $where[] = 'grade_level = ?';
        $params[] = $gradeFilter;
    }
}

$whereSql = implode(' AND ', $where);

$countStmt = db()->prepare("SELECT COUNT(*) FROM users WHERE {$whereSql}");
$countStmt->execute($params);
$totalStudents = (int) $countStmt->fetchColumn();

$totalPages = max(1, (int) ceil($totalStudents / $perPage));
$page = max(1, (int) ($_GET['page'] ?? 1));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$listStmt = db()->prepare(
    "SELECT * FROM users WHERE {$whereSql} ORDER BY name LIMIT {$perPage} OFFSET {$offset}"
);
$listStmt->execute($params);
$students = $listStmt->fetchAll();

$rangeStart = $totalStudents > 0 ? $offset + 1 : 0;
$rangeEnd = min($offset + count($students), $totalStudents);

$listReturnQuery = http_build_query(array_filter([
    'page' => $page > 1 ? (string) $page : null,
    'q' => $searchQuery !== '' ? $searchQuery : null,
    'grade' => $gradeFilter !== '' ? $gradeFilter : null,
    'per_page' => $perPage !== 20 ? (string) $perPage : null,
]));

$studentListUrl = static function (array $overrides = []) use ($searchQuery, $gradeFilter, $page, $perPage): string {
    $params = array_filter([
        'page' => $page,
        'q' => $searchQuery,
        'grade' => $gradeFilter,
        'per_page' => $perPage !== 20 ? $perPage : null,
    ], static fn ($v) => $v !== '' && $v !== null);
    foreach ($overrides as $key => $value) {
        if ($value === '' || $value === null || ($key === 'page' && (int) $value <= 1)) {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    $qs = http_build_query($params);

    return 'student-list.php' . ($qs !== '' ? '?' . $qs : '');
};

// Fetch enrollment documents for students on this page
$studentIds = array_column($students, 'id');
$studentDocuments = [];
if (!empty($studentIds)) {
    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $docStmt = db()->prepare("SELECT * FROM student_enrollment_documents WHERE student_id IN ($placeholders) ORDER BY uploaded_at DESC");
    $docStmt->execute($studentIds);
    $allDocs = $docStmt->fetchAll();
    foreach ($allDocs as $doc) {
        $studentDocuments[$doc['student_id']][] = $doc;
    }
}

// Generate school year options
$currentYear = date('Y');
$schoolYears = [];
for ($i = 5; $i >= 0; $i--) {
    $startYear = $currentYear - $i;
    $endYear = $startYear + 1;
    $schoolYears[] = "$startYear-$endYear";
}

// Grade level counts (all students, for filter dropdown)
$gradeCounts = [];
$gradeStmt = db()->query(
    "SELECT COALESCE(NULLIF(TRIM(grade_level), ''), 'Unassigned') AS grade_level, COUNT(*) AS cnt
     FROM users WHERE role = 'student' AND archived = 0
     GROUP BY COALESCE(NULLIF(TRIM(grade_level), ''), 'Unassigned')
     ORDER BY grade_level"
);
foreach ($gradeStmt->fetchAll() as $gradeRow) {
    $gradeCounts[$gradeRow['grade_level']] = (int) $gradeRow['cnt'];
}

$duplicateNameKeys = array_flip(studentDuplicateNormalizedNames());

function studentProfileImage(array $student): string
{
    $img = trim((string) ($student['image'] ?? ''));
    return $img !== '' ? $img : 'assets/images/default-avatar.php';
}

function studentDisplayValue(mixed $value): string
{
    $text = trim((string) ($value ?? ''));
    return $text === '' ? '—' : htmlspecialchars($text);
}

function studentGradeLabel(array $student): string
{
    $grade = trim((string) ($student['grade_level'] ?? ''));
    return $grade !== '' ? 'Grade ' . htmlspecialchars($grade) : 'Unassigned';
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
    <style>
        .student-list-minimal { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .student-list-minimal .sl-action-card {
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
        .student-list-minimal .sl-action-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
        }
        .student-list-minimal .sl-action-buttons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .student-list-minimal .btn-sl-enroll,
        .student-list-minimal .btn-sl-print {
            padding: 0.45rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            border-radius: 6px;
            line-height: 1.4;
        }
        .student-list-minimal .btn-sl-enroll {
            background: #fff;
            border: 1px solid #3182ce;
            color: #3182ce;
        }
        .student-list-minimal .btn-sl-enroll:hover {
            background: #ebf8ff;
            border-color: #2c5282;
            color: #2c5282;
        }
        .student-list-minimal .btn-sl-print {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }
        .student-list-minimal .btn-sl-print:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
            color: #2d3748;
        }
        .student-list-minimal .sl-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem 1rem;
            margin-bottom: 0.75rem;
            padding: 0 0.15rem;
        }
        .student-list-minimal .sl-filter-count {
            font-size: 0.875rem;
            color: #718096;
            flex-shrink: 0;
            white-space: nowrap;
            margin: 0;
        }
        .student-list-minimal .sl-filter-stack {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            flex: 1 1 auto;
            justify-content: flex-end;
            margin-left: auto;
            min-width: 0;
        }
        .student-list-minimal .sl-filter-stack .form-control,
        .student-list-minimal .sl-filter-stack .form-select {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fff;
            color: #2d3748;
            box-shadow: none;
            height: 2.25rem;
        }
        .student-list-minimal .sl-filter-stack #searchStudents {
            flex: 1 1 10rem;
            min-width: 10rem;
            max-width: 16rem;
            width: auto;
        }
        .student-list-minimal .sl-filter-stack #filterGrade {
            flex: 0 1 auto;
            min-width: 9rem;
            max-width: 11rem;
            width: auto;
        }
        .student-list-minimal .sl-filter-stack #perPage {
            flex: 0 0 auto;
            width: auto;
            min-width: 7.5rem;
        }
        .student-list-minimal .sl-filter-stack .form-control:focus,
        .student-list-minimal .sl-filter-stack .form-select:focus {
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.15);
        }
        .student-list-minimal .table-wrap {
            background: #fff;
            border: none;
            overflow: hidden;
        }
        @media (max-width: 768px) {
            .student-list-minimal .sl-filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .student-list-minimal .sl-filter-stack {
                margin-left: 0;
                justify-content: flex-start;
            }
            .student-list-minimal .sl-filter-stack #searchStudents {
                flex: 1 1 100%;
                max-width: none;
            }
        }
        @media (max-width: 480px) {
            .student-list-minimal .sl-filter-stack {
                flex-direction: column;
                align-items: stretch;
            }
            .student-list-minimal .sl-filter-stack #searchStudents,
            .student-list-minimal .sl-filter-stack #filterGrade,
            .student-list-minimal .sl-filter-stack #perPage {
                flex: 1 1 auto;
                width: 100%;
                max-width: none;
                min-width: 0;
            }
        }
        .student-list-minimal #studentsTable {
            margin: 0;
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9375rem;
            color: #2d3748;
        }
        .student-list-minimal #studentsTable thead th {
            font-weight: 600;
            font-size: 0.6875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4a5568;
            background: #f1f3f5;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            text-align: left;
            vertical-align: middle;
        }
        .student-list-minimal #studentsTable tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
            border-bottom: 1px solid #edf2f7;
            text-align: left;
        }
        .student-list-minimal #studentsTable tbody tr:last-child td { border-bottom: none; }
        .student-list-minimal #studentsTable tbody tr:hover { background: #fafbfc; }
        .student-list-minimal .cell-id { color: #2d3748; font-variant-numeric: tabular-nums; }
        .student-list-minimal .cell-name .name-primary {
            font-weight: 600;
            color: #1a202c;
            line-height: 1.35;
        }
        .student-list-minimal .cell-name .name-sub {
            font-size: 0.8125rem;
            color: #718096;
            margin-top: 0.2rem;
            line-height: 1.3;
        }
        .student-list-minimal .text-muted-cell { color: #a0aec0; }
        .student-list-minimal .status-pill {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.2;
        }
        .student-list-minimal .status-pill.status-active {
            background: #e6fffa;
            color: #234e52;
        }
        .student-list-minimal .status-pill.status-promoted {
            background: #e6fffa;
            color: #234e52;
        }
        .student-list-minimal .status-pill.status-retained {
            background: #fef2f2;
            color: #991b1b;
        }
        .student-list-minimal .status-pill.status-irregular {
            background: #fffbeb;
            color: #92400e;
        }
        .student-list-minimal .action-link-warning {
            color: #f59e0b;
            margin-left: 0.85rem;
        }
        .student-list-minimal .action-link-warning:hover { 
            text-decoration: underline; 
            color: #d97706; 
        }
        .student-list-minimal .status-pill.status-duplicate {
            background: #fffaf0;
            color: #c05621;
            margin-left: 0.35rem;
        }
        .student-list-minimal .table-text-link {
            color: #3182ce;
            text-decoration: none;
            font-weight: 400;
        }
        .student-list-minimal .table-text-link:hover { text-decoration: underline; color: #2c5282; }
        .student-list-minimal .table-text-btn {
            color: #3182ce;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            font-size: inherit;
            font-weight: 400;
            cursor: pointer;
        }
        .student-list-minimal .table-text-btn:hover { text-decoration: underline; color: #2c5282; }
        .student-list-minimal .cell-actions { white-space: nowrap; }
        .student-list-minimal .action-link {
            font-size: 0.9375rem;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            font-weight: 400;
        }
        .student-list-minimal .action-link-primary { color: #3182ce; }
        .student-list-minimal .action-link-primary:hover { text-decoration: underline; color: #2c5282; }
        .student-list-minimal .action-link-muted {
            color: #718096;
            margin-left: 0.85rem;
        }
        .student-list-minimal .action-link-muted:hover { text-decoration: underline; color: #4a5568; }
        .student-list-minimal .edit-docs-existing { font-size: 0.875rem; }
        .student-list-minimal .edit-docs-existing a { text-decoration: none; color: #3182ce; }
        .student-list-minimal .sl-pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1rem;
            padding: 1rem;
            background: #fff;
            border-top: 1px solid #edf2f7;
        }
        .student-list-minimal .sl-pagination-info {
            font-size: 0.875rem;
            color: #718096;
        }
        .student-list-minimal .sl-pagination-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
        }
        .student-list-minimal .sl-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.5rem;
            font-size: 0.875rem;
            color: #4a5568;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none;
            line-height: 1;
        }
        .student-list-minimal .sl-page-btn:hover:not(.disabled):not(.active) {
            background: #f7fafc;
            border-color: #cbd5e0;
            color: #2d3748;
        }
        .student-list-minimal .sl-page-btn.active {
            background: #3182ce;
            border-color: #3182ce;
            color: #fff;
            font-weight: 600;
        }
        .student-list-minimal .sl-page-btn.disabled {
            opacity: 0.45;
            pointer-events: none;
        }
        .student-list-minimal .sl-page-ellipsis {
            padding: 0 0.25rem;
            color: #a0aec0;
            font-size: 0.875rem;
        }
        @media print {
            .student-list-minimal .sl-filter-stack,
            .student-list-minimal .sl-action-buttons,
            .student-list-minimal .sl-pagination,
            .student-list-minimal .cell-actions { display: none !important; }
        }
        /* View student modal */
        .view-student-modal .modal-header {
            background: #fff;
            border-bottom: 1px solid #edf2f7;
            padding: 1rem 1.25rem;
        }
        .view-student-modal .modal-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a202c;
        }
        .view-student-modal .modal-body { padding: 0; }
        .view-student-modal .vs-profile-banner {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #ebf8ff 0%, #f7fafc 55%, #fff 100%);
            border-bottom: 1px solid #edf2f7;
        }
        .view-student-modal .vs-avatar-wrap {
            flex-shrink: 0;
            width: 96px;
            height: 96px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(49, 130, 206, 0.2);
            background: #e2e8f0;
        }
        .view-student-modal .vs-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .view-student-modal .vs-profile-meta { min-width: 0; flex: 1; }
        .view-student-modal .vs-student-name {
            margin: 0 0 0.25rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a202c;
            line-height: 1.3;
        }
        .view-student-modal .vs-student-id {
            font-size: 0.875rem;
            color: #718096;
            font-variant-numeric: tabular-nums;
        }
        .view-student-modal .vs-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.65rem;
        }
        .view-student-modal .vs-badge {
            display: inline-block;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .view-student-modal .vs-badge-grade {
            background: #ebf8ff;
            color: #2c5282;
        }
        .view-student-modal .vs-badge-active {
            background: #e6fffa;
            color: #234e52;
        }
        .view-student-modal .vs-body-content { padding: 1rem 1.5rem 1.25rem; }
        .view-student-modal .vs-section { margin-bottom: 1.25rem; }
        .view-student-modal .vs-section:last-child { margin-bottom: 0; }
        .view-student-modal .vs-section-title {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4a5568;
            margin: 0 0 0.65rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid #edf2f7;
        }
        .view-student-modal .vs-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem 1.25rem;
        }
        @media (max-width: 576px) {
            .view-student-modal .vs-detail-grid { grid-template-columns: 1fr; }
            .view-student-modal .vs-profile-banner {
                flex-direction: column;
                text-align: center;
            }
        }
        .view-student-modal .vs-detail-item { min-width: 0; }
        .view-student-modal .vs-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #a0aec0;
            margin-bottom: 0.15rem;
        }
        .view-student-modal .vs-value {
            font-size: 0.9375rem;
            color: #2d3748;
            word-break: break-word;
        }
        .view-student-modal .vs-value.vs-value-empty { color: #cbd5e0; }
        .view-student-modal .vs-docs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .view-student-modal .vs-doc-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.8125rem;
            border-radius: 6px;
            text-decoration: none;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #2d3748;
            transition: border-color 0.15s, background 0.15s;
        }
        .view-student-modal .vs-doc-chip:hover {
            border-color: #3182ce;
            background: #ebf8ff;
            color: #2c5282;
        }
        .view-student-modal .vs-doc-chip.doc-image { border-color: #9ae6b4; color: #22543d; }
        .view-student-modal .vs-doc-chip.doc-pdf { border-color: #feb2b2; color: #9b2c2c; }
        .view-student-modal .vs-no-docs {
            font-size: 0.875rem;
            color: #a0aec0;
        }
        .view-student-modal .modal-footer.vs-footer {
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.85rem 1.25rem;
            background: #f7fafc;
            border-top: 1px solid #edf2f7;
        }
        .view-student-modal .vs-footer-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-left: auto;
        }
        .view-student-modal .vs-footer .btn {
            font-size: 0.8125rem;
            padding: 0.4rem 0.75rem;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Student List'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4 student-list-minimal">
            <?php if ($success): ?>
            <div class="alert alert-success py-2 px-3 small mb-3"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="sl-action-card">
                <h2 class="sl-action-title">Student List</h2>
                <div class="sl-action-buttons">
                    <a href="enrollment.php" class="btn btn-sl-enroll">Enroll</a>
                    <?php
                    $rosterQuery = array_filter(['grade' => $gradeFilter !== '' ? $gradeFilter : null]);
                    $rosterUrl = 'print-student-roster.php' . ($rosterQuery ? '?' . http_build_query($rosterQuery) : '');
                    ?>
                    <a href="<?= htmlspecialchars($rosterUrl) ?>" class="btn btn-sl-print" target="_blank">Print Roster</a>
                </div>
            </div>

            <form method="get" class="sl-filter-bar" id="studentFilterForm">
                <span class="sl-filter-count">
                    <?php if ($totalStudents > 0): ?>
                    Showing <?= $rangeStart ?>–<?= $rangeEnd ?> of <?= $totalStudents ?> student<?= $totalStudents === 1 ? '' : 's' ?>
                    <?php else: ?>
                    0 students
                    <?php endif; ?>
                </span>
                <div class="sl-filter-stack">
                    <input type="text" name="q" id="searchStudents" class="form-control" placeholder="Search..." aria-label="Search students" value="<?= htmlspecialchars($searchQuery) ?>">
                    <select name="grade" id="filterGrade" class="form-select" aria-label="Filter by grade">
                        <option value="">All grades</option>
                        <?php foreach (array_keys($gradeCounts) as $grade): ?>
                        <option value="<?= htmlspecialchars((string) $grade) ?>"<?= $gradeFilter === (string) $grade ? ' selected' : '' ?>>
                            <?= $grade === 'Unassigned' ? 'Unassigned' : 'Grade ' . htmlspecialchars((string) $grade) ?>
                            (<?= $gradeCounts[$grade] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="per_page" id="perPage" class="form-select" aria-label="Rows per page">
                        <?php foreach ($perPageOptions as $option): ?>
                        <option value="<?= $option ?>"<?= $perPage === $option ? ' selected' : '' ?>><?= $option ?> per page</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <div class="table-wrap">
                <div class="table-responsive">
                    <table class="mb-0" id="studentsTable">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Age / Gender</th>
                                <th>Grade</th>
                                <th>Retention Status</th>
                                <th>Documents</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student):
                                $docCount = count($studentDocuments[$student['id']] ?? []);
                                if ($student['age'] && $student['gender']) {
                                    $ageGender = $student['age'] . ' / ' . $student['gender'];
                                } elseif (!empty($student['gender'])) {
                                    $ageGender = $student['gender'];
                                } elseif (!empty($student['age'])) {
                                    $ageGender = (string) $student['age'];
                                } else {
                                    $ageGender = null;
                                }
                            ?>
                            <?php $isDuplicateName = isset($duplicateNameKeys[studentNormalizeName($student['name'])]); ?>
                            <tr data-grade="<?= htmlspecialchars($student['grade_level'] ?? '') ?>"<?= $isDuplicateName ? ' class="row-duplicate-name"' : '' ?>>
                                <td class="cell-id"><?= htmlspecialchars($student['empidno']) ?></td>
                                <td class="cell-name">
                                    <div class="name-primary">
                                        <?= htmlspecialchars($student['name']) ?>
                                        <?php if ($isDuplicateName): ?>
                                        <span class="status-pill status-duplicate" title="Another active student has the same name">Duplicate name</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($student['email'])): ?>
                                    <div class="name-sub"><?= htmlspecialchars($student['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ageGender): ?>
                                    <?= htmlspecialchars($ageGender) ?>
                                    <?php else: ?>
                                    <span class="text-muted-cell">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($student['grade_level']): ?>
                                    Grade <?= htmlspecialchars($student['grade_level']) ?>
                                    <?php else: ?>
                                    <span class="text-muted-cell">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $retentionStatus = $student['retention_status'] ?? 'promoted';
                                    $statusClass = match($retentionStatus) {
                                        'retained' => 'status-retained',
                                        'irregular' => 'status-irregular',
                                        default => 'status-promoted'
                                    };
                                    $statusLabel = match($retentionStatus) {
                                        'retained' => 'Retained',
                                        'irregular' => 'Irregular',
                                        default => 'Promoted'
                                    };
                                    ?>
                                    <span class="status-pill <?= $statusClass ?>" 
                                          title="<?= $retentionStatus === 'retained' ? 'Student retained in ' . ($student['retention_school_year'] ?? 'current grade') : '' ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                    <?php if ($retentionStatus === 'retained' && !empty($student['retention_school_year'])): ?>
                                    <small class="d-block text-muted mt-1"><?= htmlspecialchars($student['retention_school_year']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($docCount > 0): ?>
                                    <button type="button" class="table-text-btn" data-bs-toggle="modal" data-bs-target="#documentsModal<?= $student['id'] ?>">
                                        View (<?= $docCount ?>)
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted-cell">None</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <button type="button" class="action-link action-link-primary" data-bs-toggle="modal" data-bs-target="#editStudentModal<?= $student['id'] ?>">Edit</button>
                                    <button type="button" class="action-link action-link-muted" data-bs-toggle="modal" data-bs-target="#viewStudentModal<?= $student['id'] ?>">View</button>
                                    <button type="button" class="action-link action-link-warning" data-bs-toggle="modal" data-bs-target="#retentionModal<?= $student['id'] ?>">Retention</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($students)): ?>
                            <tr class="empty-row"><td colspan="7" class="text-center text-muted py-4">No students found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($totalStudents > 0): ?>
                <nav class="sl-pagination" aria-label="Student list pagination">
                    <span class="sl-pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($totalPages > 1): ?>
                    <div class="sl-pagination-nav">
                        <a href="<?= htmlspecialchars($studentListUrl(['page' => max(1, $page - 1)])) ?>" class="sl-page-btn<?= $page <= 1 ? ' disabled' : '' ?>" aria-label="Previous page">&lsaquo;</a>
                        <?php foreach ($paginationPages as $p): ?>
                            <?php if ($p === '…'): ?>
                            <span class="sl-page-ellipsis" aria-hidden="true">…</span>
                            <?php else: ?>
                            <a href="<?= htmlspecialchars($studentListUrl(['page' => $p])) ?>" class="sl-page-btn<?= (int) $p === $page ? ' active' : '' ?>"><?= (int) $p ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="<?= htmlspecialchars($studentListUrl(['page' => min($totalPages, $page + 1)])) ?>" class="sl-page-btn<?= $page >= $totalPages ? ' disabled' : '' ?>" aria-label="Next page">&rsaquo;</a>
                    </div>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- View/Edit Modals for each student -->
    <?php foreach ($students as $student):
        $editDocs = $studentDocuments[$student['id']] ?? [];
    ?>
    <!-- View Student Modal -->
    <?php
        $viewDocs = $studentDocuments[$student['id']] ?? [];
        $profileImg = studentProfileImage($student);
        $dobDisplay = !empty($student['date_of_birth'])
            ? date('M d, Y', strtotime($student['date_of_birth']))
            : '';
    ?>
    <div class="modal fade" id="viewStudentModal<?= $student['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content view-student-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Student Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="vs-profile-banner">
                        <div class="vs-avatar-wrap">
                            <img src="<?= htmlspecialchars($profileImg) ?>" alt="<?= htmlspecialchars($student['name']) ?>" class="vs-avatar" onerror="this.onerror=null;this.src='assets/images/default-avatar.php';">
                        </div>
                        <div class="vs-profile-meta">
                            <h4 class="vs-student-name"><?= htmlspecialchars($student['name']) ?></h4>
                            <div class="vs-student-id"><?= htmlspecialchars($student['empidno']) ?></div>
                            <?php if (!empty($student['email'])): ?>
                            <div class="vs-student-id"><?= htmlspecialchars($student['email']) ?></div>
                            <?php endif; ?>
                            <div class="vs-badges">
                                <span class="vs-badge vs-badge-grade"><?= studentGradeLabel($student) ?></span>
                                <span class="vs-badge vs-badge-active">Active</span>
                            </div>
                        </div>
                    </div>
                    <div class="vs-body-content">
                        <section class="vs-section">
                            <h6 class="vs-section-title">Personal Information</h6>
                            <div class="vs-detail-grid">
                                <div class="vs-detail-item">
                                    <span class="vs-label">Date of Birth</span>
                                    <span class="vs-value<?= $dobDisplay === '' ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($dobDisplay) ?></span>
                                </div>
                                <div class="vs-detail-item">
                                    <span class="vs-label">Age</span>
                                    <span class="vs-value<?= !isset($student['age']) || $student['age'] === '' || $student['age'] === null ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['age'] ?? '') ?></span>
                                </div>
                                <div class="vs-detail-item">
                                    <span class="vs-label">Gender</span>
                                    <span class="vs-value<?= empty($student['gender']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['gender'] ?? '') ?></span>
                                </div>
                                <div class="vs-detail-item">
                                    <span class="vs-label">Nationality</span>
                                    <span class="vs-value<?= empty($student['nationality']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['nationality'] ?? '') ?></span>
                                </div>
                                <div class="vs-detail-item" style="grid-column: 1 / -1;">
                                    <span class="vs-label">Home Address</span>
                                    <span class="vs-value<?= empty($student['home_address']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['home_address'] ?? '') ?></span>
                                </div>
                            </div>
                        </section>
                        <section class="vs-section">
                            <h6 class="vs-section-title">Family &amp; Guardian</h6>
                            <div class="vs-detail-grid">
                                <div class="vs-detail-item">
                                    <span class="vs-label">Father's Name</span>
                                    <span class="vs-value<?= empty($student['father_name']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['father_name'] ?? '') ?></span>
                                </div>
                                <div class="vs-detail-item">
                                    <span class="vs-label">Mother's Name</span>
                                    <span class="vs-value<?= empty($student['mother_name']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['mother_name'] ?? '') ?></span>
                                </div>
                                <div class="vs-detail-item">
                                    <span class="vs-label">Guardian's Name</span>
                                    <span class="vs-value<?= empty($student['guardian_name']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['guardian_name'] ?? '') ?></span>
                                </div>
                                <div class="vs-detail-item">
                                    <span class="vs-label">Guardian Contact</span>
                                    <span class="vs-value<?= empty($student['guardian_contact']) ? ' vs-value-empty' : '' ?>"><?= studentDisplayValue($student['guardian_contact'] ?? '') ?></span>
                                </div>
                            </div>
                        </section>
                        <section class="vs-section">
                            <h6 class="vs-section-title">Enrollment Documents</h6>
                            <?php if (empty($viewDocs)): ?>
                            <span class="vs-no-docs">No documents uploaded</span>
                            <?php else: ?>
                            <div class="vs-docs">
                                <?php foreach ($viewDocs as $vdoc):
                                    $visImage = strpos($vdoc['mime_type'], 'image/') === 0;
                                    $visPdf = $vdoc['mime_type'] === 'application/pdf';
                                    $chipClass = $visImage ? 'doc-image' : ($visPdf ? 'doc-pdf' : '');
                                ?>
                                <a href="<?= htmlspecialchars($vdoc['file_path']) ?>" target="_blank" rel="noopener" class="vs-doc-chip <?= $chipClass ?>">
                                    <i class="bi <?= $visImage ? 'bi-file-image' : ($visPdf ? 'bi-file-pdf' : 'bi-file-earmark') ?>"></i>
                                    <?= htmlspecialchars($docLabels[$vdoc['document_type']] ?? $vdoc['document_type']) ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>
                <div class="modal-footer vs-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                    <div class="vs-footer-actions">
                        <a href="print-student-form.php?id=<?= $student['id'] ?>" class="btn btn-outline-success btn-sm" target="_blank" rel="noopener">
                            <i class="bi bi-printer"></i> DepEd Form
                        </a>
                        <a href="print-certification.php?id=<?= $student['id'] ?>" class="btn btn-outline-dark btn-sm" target="_blank" rel="noopener">
                            <i class="bi bi-file-earmark-medical"></i> Certification
                        </a>
                        <a href="print-registration.php?id=<?= $student['id'] ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
                            <i class="bi bi-receipt"></i> Registration
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editStudentModal<?= $student['id'] ?>" data-bs-dismiss="modal">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal<?= $student['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="return_query" value="<?= htmlspecialchars($listReturnQuery) ?>">
                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Student Information -->
                            <h6 class="text-primary">Student Information</h6>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Grade Level</label>
                                <select class="form-select" name="grade_level">
                                    <option value="">Select Grade</option>
                                    <?php foreach (['7', '8', '9', '10', '11', '12'] as $grade): ?>
                                    <option value="<?= $grade ?>" <?= $student['grade_level'] == $grade ? 'selected' : '' ?>>Grade <?= $grade ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?= $student['date_of_birth'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" value="<?= $student['age'] ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="">Select</option>
                                    <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nationality</label>
                                <input type="text" class="form-control" name="nationality" value="<?= htmlspecialchars($student['nationality'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control" name="religion" value="<?= htmlspecialchars($student['religion'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Place of Birth</label>
                                <input type="text" class="form-control" name="place_of_birth" value="<?= htmlspecialchars($student['place_of_birth'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number" value="<?= htmlspecialchars($student['contact_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Mother Tongue</label>
                                <input type="text" class="form-control" name="mother_tongue" value="<?= htmlspecialchars($student['mother_tongue'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Home Address</label>
                                <textarea class="form-control" name="home_address" rows="2"><?= htmlspecialchars($student['home_address'] ?? '') ?></textarea>
                            </div>

                            <!-- Parent Information -->
                            <h6 class="text-primary mt-3">Parent/Guardian Information</h6>
                            <div class="col-md-4">
                                <label class="form-label">Father's Name</label>
                                <input type="text" class="form-control" name="father_name" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Father's Occupation</label>
                                <input type="text" class="form-control" name="father_occupation" value="<?= htmlspecialchars($student['father_occupation'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Father's Contact</label>
                                <input type="text" class="form-control" name="father_contact" value="<?= htmlspecialchars($student['father_contact'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mother's Name</label>
                                <input type="text" class="form-control" name="mother_name" value="<?= htmlspecialchars($student['mother_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mother's Occupation</label>
                                <input type="text" class="form-control" name="mother_occupation" value="<?= htmlspecialchars($student['mother_occupation'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Mother's Contact</label>
                                <input type="text" class="form-control" name="mother_contact" value="<?= htmlspecialchars($student['mother_contact'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guardian's Name</label>
                                <input type="text" class="form-control" name="guardian_name" value="<?= htmlspecialchars($student['guardian_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Relationship</label>
                                <input type="text" class="form-control" name="guardian_relationship" value="<?= htmlspecialchars($student['guardian_relationship'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guardian's Contact</label>
                                <input type="text" class="form-control" name="guardian_contact" value="<?= htmlspecialchars($student['guardian_contact'] ?? '') ?>">
                            </div>

                            <!-- Previous School -->
                            <h6 class="text-primary mt-3">Previous School Information</h6>
                            <div class="col-md-6">
                                <label class="form-label">Last School Attended</label>
                                <input type="text" class="form-control" name="last_school_attended" value="<?= htmlspecialchars($student['last_school_attended'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">School Year Completed</label>
                                <select class="form-select" name="school_year_completed">
                                    <option value="">Select School Year</option>
                                    <?php foreach ($schoolYears as $year): ?>
                                    <option value="<?= $year ?>" <?= ($student['school_year_completed'] ?? '') == $year ? 'selected' : '' ?>><?= $year ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">General Average</label>
                                <input type="text" class="form-control" name="general_average" value="<?= htmlspecialchars($student['general_average'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">School Address</label>
                                <textarea class="form-control" name="last_school_address" rows="2"><?= htmlspecialchars($student['last_school_address'] ?? '') ?></textarea>
                            </div>

                            <!-- Additional Enrollment Information -->
                            <h6 class="text-primary mt-3">Additional Information</h6>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_lrn" id="has_lrn_<?= $student['id'] ?>" <?= $student['has_lrn'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_lrn_<?= $student['id'] ?>">
                                        Has LRN (Learner Reference Number)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">LRN Number</label>
                                <input type="text" class="form-control" name="lrn_number" value="<?= htmlspecialchars($student['lrn_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_returnee" id="is_returnee_<?= $student['id'] ?>" <?= $student['is_returnee'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_returnee_<?= $student['id'] ?>">
                                        Returnee
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_transfer_in" id="is_transfer_in_<?= $student['id'] ?>" <?= $student['is_transfer_in'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_transfer_in_<?= $student['id'] ?>">
                                        Transfer In
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_special_needs" id="has_special_needs_<?= $student['id'] ?>" <?= $student['has_special_needs'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_special_needs_<?= $student['id'] ?>">
                                        Special Needs
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_4ps_beneficiary" id="is_4ps_beneficiary_<?= $student['id'] ?>" <?= $student['is_4ps_beneficiary'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_4ps_beneficiary_<?= $student['id'] ?>">
                                        4Ps Beneficiary
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Special Needs Type</label>
                                <input type="text" class="form-control" name="special_needs_type" value="<?= htmlspecialchars($student['special_needs_type'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_indigenous" id="is_indigenous_<?= $student['id'] ?>" <?= $student['is_indigenous'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_indigenous_<?= $student['id'] ?>">
                                        Indigenous
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Indigenous Group</label>
                                <input type="text" class="form-control" name="indigenous_group" value="<?= htmlspecialchars($student['indigenous_group'] ?? '') ?>">
                            </div>

                            <!-- Retention Status -->
                            <h6 class="text-primary mt-3">Academic Status</h6>
                            <div class="col-md-4">
                                <label class="form-label">Retention Status</label>
                                <select class="form-select" name="retention_status">
                                    <option value="promoted" <?= ($student['retention_status'] ?? 'promoted') == 'promoted' ? 'selected' : '' ?>>Promoted</option>
                                    <option value="retained" <?= ($student['retention_status'] ?? '') == 'retained' ? 'selected' : '' ?>>Retained</option>
                                    <option value="irregular" <?= ($student['retention_status'] ?? '') == 'irregular' ? 'selected' : '' ?>>Irregular</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">School Year (if retained)</label>
                                <input type="text" class="form-control" name="retention_school_year" 
                                       value="<?= htmlspecialchars($student['retention_school_year'] ?? '') ?>" 
                                       placeholder="e.g., 2023-2024">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Retention Reason</label>
                                <select class="form-select" name="retention_reason">
                                    <option value="">Select reason</option>
                                    <option value="Academic Performance" <?= ($student['retention_reason'] ?? '') == 'Academic Performance' ? 'selected' : '' ?>>Academic Performance</option>
                                    <option value="Attendance Issues" <?= ($student['retention_reason'] ?? '') == 'Attendance Issues' ? 'selected' : '' ?>>Attendance Issues</option>
                                    <option value="Failed Core Subjects" <?= ($student['retention_reason'] ?? '') == 'Failed Core Subjects' ? 'selected' : '' ?>>Failed Core Subjects</option>
                                    <option value="Incomplete Requirements" <?= ($student['retention_reason'] ?? '') == 'Incomplete Requirements' ? 'selected' : '' ?>>Incomplete Requirements</option>
                                    <option value="Transfer/Migration" <?= ($student['retention_reason'] ?? '') == 'Transfer/Migration' ? 'selected' : '' ?>>Transfer/Migration</option>
                                    <option value="Other" <?= ($student['retention_reason'] ?? '') == 'Other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>

                            <h6 class="text-muted mt-3 mb-2 border-top pt-3">Enrollment documents</h6>
                            <?php if (!empty($editDocs)): ?>
                            <div class="col-12 edit-docs-existing">
                                <p class="small text-muted mb-2">Uploaded files</p>
                                <ul class="list-unstyled mb-3">
                                    <?php foreach ($editDocs as $edoc): ?>
                                    <li class="mb-1">
                                        <a href="<?= htmlspecialchars($edoc['file_path']) ?>" target="_blank">
                                            <?= htmlspecialchars($docLabels[$edoc['document_type']] ?? $edoc['document_type']) ?>
                                        </a>
                                        <span class="text-muted small"> — <?= htmlspecialchars($edoc['file_name']) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <label class="form-label">Birth Certificate (PSA / NSO)</label>
                                <input type="file" class="form-control form-control-sm" name="birth_certificate" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">PDF, JPG, or PNG — max 10MB</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PSA / NSO (additional)</label>
                                <input type="file" class="form-control form-control-sm" name="psa_nso" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SF10 / Form 137</label>
                                <input type="file" class="form-control form-control-sm" name="sf10" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PEAC / ESC</label>
                                <input type="file" class="form-control form-control-sm" name="peac" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_student" class="btn btn-primary btn-sm">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Documents Modal -->
    <div class="modal fade" id="documentsModal<?= $student['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-folder me-2"></i>Enrollment Documents - <?= htmlspecialchars($student['name']) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $docs = $studentDocuments[$student['id']] ?? [];
                    if (empty($docs)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No enrollment documents uploaded yet.
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($docs as $doc): 
                                $isImage = strpos($doc['mime_type'], 'image/') === 0;
                                $isPdf = $doc['mime_type'] === 'application/pdf';
                            ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="bi <?= $isImage ? 'bi-file-image' : ($isPdf ? 'bi-file-pdf' : 'bi-file-earmark') ?> fs-4 me-3 text-<?= $isImage ? 'success' : ($isPdf ? 'danger' : 'secondary') ?>"></i>
                                    <div>
                                        <div class="fw-bold"><?= $docLabels[$doc['document_type']] ?? $doc['document_type'] ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($doc['file_name']) ?> &middot; <?= number_format($doc['file_size'] / 1024, 1) ?> KB &middot; <?= date('M d, Y', strtotime($doc['uploaded_at'])) ?></small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <?php if ($isImage): ?>
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php elseif ($isPdf): ?>
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-eye"></i> View PDF
                                    </a>
                                    <?php endif; ?>
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" download class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Retention Status Modal -->
    <div class="modal fade" id="retentionModal<?= $student['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>Update Retention Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong><i class="bi bi-info-circle me-1"></i>Student:</strong> <?= htmlspecialchars($student['name']) ?><br>
                            <strong>Current Grade:</strong> <?= $student['grade_level'] ? 'Grade ' . htmlspecialchars($student['grade_level']) : 'Unassigned' ?><br>
                            <strong>Current Status:</strong> <?= ucfirst($student['retention_status'] ?? 'promoted') ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Retention Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="retention_status" required>
                                <option value="">Select status</option>
                                <option value="promoted">Promoted - Student advances to next grade level</option>
                                <option value="retained">Retained - Student repeats current grade level</option>
                                <option value="irregular">Irregular - Student has special enrollment status</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">School Year</label>
                            <input type="text" class="form-control" name="school_year" 
                                   placeholder="e.g., 2023-2024" value="<?= date('Y') ?>-<?= date('Y') + 1 ?>">
                            <small class="text-muted">School year when this status applies</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reason for Status Change</label>
                            <select class="form-select" name="retention_reason">
                                <option value="">Select reason (optional)</option>
                                <option value="Academic Performance">Academic Performance</option>
                                <option value="Attendance Issues">Attendance Issues</option>
                                <option value="Failed Core Subjects">Failed Core Subjects</option>
                                <option value="Incomplete Requirements">Incomplete Requirements</option>
                                <option value="Transfer/Migration">Transfer/Migration</option>
                                <option value="Medical Reasons">Medical Reasons</option>
                                <option value="Personal/Family Issues">Personal/Family Issues</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_retention_status" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            const form = document.getElementById('studentFilterForm');
            const search = document.getElementById('searchStudents');
            let searchTimer = null;

            function submitFilters() {
                const pageInput = form.querySelector('input[name="page"]');
                if (pageInput) {
                    pageInput.remove();
                }
                form.submit();
            }

            search.addEventListener('input', function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(submitFilters, 400);
            });

            document.getElementById('filterGrade').addEventListener('change', submitFilters);
            document.getElementById('perPage').addEventListener('change', submitFilters);
        })();
    </script>
</body>
</html>
