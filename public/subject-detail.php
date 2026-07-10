<?php
declare(strict_types=1);

// Temporarily enable error display for debugging
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/file.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireLogin();
$subjectId = (int)($_GET['id'] ?? 0);

// Redirect students to student view
if ($user['role'] === 'student') {
    header('Location: student-subject.php?id=' . $subjectId);
    exit;
}

if (!$subjectId) {
    header('Location: subjects.php');
    exit;
}

// Fetch subject details
$stmt = db()->prepare('SELECT * FROM subjects WHERE id = ?');
$stmt->execute([$subjectId]);
$subject = $stmt->fetch();

if (!$subject) {
    header('Location: subjects.php');
    exit;
}

$message = '';
$error = '';

// Generate/Regenerate Enrollment Key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {
    requireCsrf();
    $user = requireAdmin();
    
    $enrollmentKey = strtoupper(bin2hex(random_bytes(4)));
    $stmt = db()->prepare('UPDATE subjects SET enrollment_key = ? WHERE id = ?');
    if ($stmt->execute([$enrollmentKey, $subjectId])) {
        $message = 'Enrollment key generated successfully!';
        $subject['enrollment_key'] = $enrollmentKey;
        saveAudit($user['id'], 'generate_key', 'subject', $subjectId, ['enrollment_key' => $enrollmentKey]);
    }
}

// Assign Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_teacher'])) {
    requireCsrf();
    $user = requireAdmin();
    
    $teacherEmpidno = trim($_POST['teacher_empidno'] ?? '');
    
    if ($teacherEmpidno) {
        // Check if teacher exists
        $stmt = db()->prepare('SELECT id, name FROM users WHERE empidno = ? AND role = ?');
        $stmt->execute([$teacherEmpidno, 'teacher']);
        $teacher = $stmt->fetch();
        
        if ($teacher) {
            try {
                $stmt = db()->prepare('INSERT INTO folder_teacher (subject_id, teacher_empidno, assigned_by, assigned_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$subjectId, $teacherEmpidno, $user['id']]);
                $message = 'Teacher assigned successfully!';
                saveAudit($user['id'], 'assign', 'folder_teacher', (int)db()->lastInsertId(), ['subject_id' => $subjectId, 'teacher_empidno' => $teacherEmpidno]);
            } catch (PDOException $e) {
                $error = 'Teacher already assigned to this subject';
            }
        } else {
            $error = 'Teacher not found';
        }
    }
}

// Unassign Teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_teacher'])) {
    requireCsrf();
    $user = requireAdmin();
    
    $assignmentId = (int)($_POST['assignment_id'] ?? 0);
    
    if ($assignmentId) {
        $stmt = db()->prepare('DELETE FROM folder_teacher WHERE id = ?');
        if ($stmt->execute([$assignmentId])) {
            $message = 'Teacher unassigned successfully!';
            saveAudit($user['id'], 'unassign', 'folder_teacher', $assignmentId, ['subject_id' => $subjectId]);
        }
    }
}

// Upload File
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_file'])) {
    requireCsrf();
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $maxSize = (int)env('MAX_UPLOAD_BYTES', 20971520); // 20MB default
        $fileSize = $_FILES['file']['size'];
        $fileTmp = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileMime = mime_content_type($fileTmp);
        
        // Validate file type
        if ($fileMime !== 'application/pdf') {
            $error = 'Only PDF files are allowed';
        } else if ($fileSize > $maxSize) {
            $error = 'File size exceeds ' . round($maxSize / 1048576) . 'MB limit';
        } else {
            // Create upload directory structure
            $year = date('Y');
            $month = date('m');
            $uploadDir = __DIR__ . "/uploads/subjects/{$subjectId}/{$year}/{$month}";
            ensureDir($uploadDir);
            
            // Generate unique filename
            $hash = bin2hex(random_bytes(16));
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $safeFileName = sanitizeFilename(pathinfo($fileName, PATHINFO_FILENAME));
            $storedFileName = $hash . '_' . $safeFileName . '.' . $ext;
            $targetPath = $uploadDir . '/' . $storedFileName;
            
            if (move_uploaded_file($fileTmp, $targetPath)) {
                // Save metadata to database
                $stmt = db()->prepare('INSERT INTO files (subject_id, original_filename, stored_filename, mime_type, size, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$subjectId, $fileName, "subjects/{$subjectId}/{$year}/{$month}/" . $storedFileName, $fileMime, $fileSize, $user['id']]);
                
                $message = 'File uploaded successfully!';
                saveAudit($user['id'], 'upload', 'file', (int)db()->lastInsertId(), ['subject_id' => $subjectId, 'filename' => $fileName]);
            } else {
                $error = 'Failed to upload file';
            }
        }
    } else {
        $error = 'No file selected or upload error';
    }
}

// Delete File
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    requireCsrf();
    
    $fileId = (int)($_POST['file_id'] ?? 0);
    
    if ($fileId) {
        $stmt = db()->prepare('SELECT * FROM files WHERE id = ? AND subject_id = ?');
        $stmt->execute([$fileId, $subjectId]);
        $file = $stmt->fetch();
        
        if ($file) {
            // Check permission
            $canDelete = ($user['role'] === 'admin') || ($file['uploaded_by'] == $user['id']);
            
            if ($canDelete) {
                $filePath = __DIR__ . '/uploads/' . $file['stored_filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                $stmt = db()->prepare('DELETE FROM files WHERE id = ?');
                if ($stmt->execute([$fileId])) {
                    $message = 'File deleted successfully!';
                    saveAudit($user['id'], 'delete', 'file', $fileId, ['subject_id' => $subjectId]);
                }
            } else {
                $error = 'You do not have permission to delete this file';
            }
        }
    }
}

// Fetch assigned teachers
$stmt = db()->prepare('SELECT ft.*, u.name as teacher_name, u.email as teacher_email FROM folder_teacher ft JOIN users u ON ft.teacher_empidno = u.empidno WHERE ft.subject_id = ? ORDER BY ft.assigned_at DESC');
$stmt->execute([$subjectId]);
$assignedTeachers = $stmt->fetchAll();

// Fetch files
$stmt = db()->prepare('SELECT f.*, u.name as uploaded_by_name FROM files f JOIN users u ON f.uploaded_by = u.id WHERE f.subject_id = ? ORDER BY f.uploaded_at DESC');
$stmt->execute([$subjectId]);
$files = $stmt->fetchAll();

// Fetch all teachers for assignment dropdown
$stmt = db()->query('SELECT empidno, name, email FROM users WHERE role = "teacher" ORDER BY name');
$allTeachers = $stmt->fetchAll();

// Fetch enrolled students
$stmt = db()->prepare('
    SELECT u.id, u.name, u.email, u.empidno, e.enrolled_at,
        (SELECT COUNT(*) FROM activity_submissions asub 
         JOIN activities a ON asub.activity_id = a.id 
         WHERE a.subject_id = ? AND asub.student_id = u.id) as submitted_activities,
        (SELECT COUNT(*) FROM quiz_attempts qa 
         JOIN quizzes q ON qa.quiz_id = q.id 
         WHERE q.subject_id = ? AND qa.student_id = u.id) as completed_quizzes
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    WHERE e.subject_id = ?
    ORDER BY u.name ASC
');
$stmt->execute([$subjectId, $subjectId, $subjectId]);
$enrolledStudents = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($subject['name']) ?> - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = $subject['name']; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2>
                            <i class="bi bi-book"></i>
                            <?= htmlspecialchars($subject['name']) ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <span class="badge bg-primary"><?= htmlspecialchars($subject['code']) ?></span>
                            <?php if ($subject['description']): ?>
                                - <?= htmlspecialchars($subject['description']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="subjects.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i> Back to Subjects
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Enrollment Key Section -->
            <?php if ($user['role'] === 'admin' || ($user['role'] === 'teacher' && in_array($user['empidno'], array_column($assignedTeachers, 'teacher_empidno')))): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-key me-2"></i>Enrollment Key</h5>
                    <?php if ($user['role'] === 'admin'): ?>
                    <form method="POST" style="display:inline;">
                        <?= csrfField() ?>
                        <button type="submit" name="generate_key" class="btn btn-sm btn-warning" 
                                onclick="return confirm('Generate a new enrollment key? The old key will be invalidated.')">
                            <i class="bi bi-arrow-clockwise me-1"></i>
                            <?= $subject['enrollment_key'] ? 'Regenerate' : 'Generate' ?> Key
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($subject['enrollment_key']): ?>
                        <div class="enrollment-key-display text-center p-4 bg-light rounded">
                            <label class="form-label text-muted mb-2">Share this key with students to enroll:</label>
                            <div class="display-6 fw-bold text-primary mb-3" style="letter-spacing: 8px; font-family: 'Courier New', monospace;">
                                <?= htmlspecialchars($subject['enrollment_key']) ?>
                            </div>
                            <button class="btn btn-outline-primary btn-sm" onclick="copyEnrollmentKey()">
                                <i class="bi bi-clipboard me-1"></i>Copy Key
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0"><?php if ($user['role'] === 'admin'): ?>No enrollment key generated yet. Click "Generate Key" to create one.<?php else: ?>No enrollment key has been set for this subject yet.<?php endif; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- Assigned Teachers Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Assigned Teachers</h5>
                            <?php if ($user['role'] === 'admin'): ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignTeacherModal">
                                <i class="bi bi-plus-circle"></i> Assign
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($assignedTeachers)): ?>
                                <p class="text-muted">No teachers assigned yet.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($assignedTeachers as $at): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($at['teacher_name']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($at['teacher_email']) ?>
                                                <br>
                                                <i class="bi bi-card-text me-1"></i>ID: <?= htmlspecialchars($at['teacher_empidno']) ?>
                                            </small>
                                        </div>
                                        <?php if ($user['role'] === 'admin'): ?>
                                        <form method="POST" style="display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="assignment_id" value="<?= $at['id'] ?>">
                                            <button type="submit" name="unassign_teacher" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Remove this teacher assignment?')">
                                                <i class="bi bi-x-circle"></i> Remove
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Files Section -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Learning Materials</h5>
                            <?php if ($user['role'] === 'admin' || in_array($user['empidno'], array_column($assignedTeachers, 'teacher_empidno'))): ?>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                <i class="bi bi-cloud-upload"></i> Upload
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($files)): ?>
                                <p class="text-muted">No files uploaded yet.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($files as $f): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="bi bi-file-pdf text-danger me-2"></i>
                                                    <?= htmlspecialchars($f['original_filename']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= number_format($f['size'] / 1048576, 2) ?> MB
                                                    • Uploaded by <?= htmlspecialchars($f['uploaded_by_name']) ?>
                                                    • <?= date('M d, Y', strtotime($f['uploaded_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <a href="download.php?id=<?= $f['id'] ?>" class="btn btn-outline-primary">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <?php if ($user['role'] === 'admin' || $f['uploaded_by'] == $user['id']): ?>
                                                <form method="POST" style="display:inline;">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="file_id" value="<?= $f['id'] ?>">
                                                    <button type="submit" name="delete_file" class="btn btn-outline-danger" 
                                                            onclick="return confirm('Delete this file?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enrolled Students Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-people me-2"></i>Enrolled Students
                        <span class="badge bg-primary ms-2"><?= count($enrolledStudents) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($enrolledStudents)): ?>
                        <p class="text-muted mb-0">No students enrolled yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Student ID</th>
                                        <th>Email</th>
                                        <th>Enrolled Date</th>
                                        <th class="text-center">Activities</th>
                                        <th class="text-center">Quizzes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolledStudents as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2">
                                                    <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                                </div>
                                                <strong><?= htmlspecialchars($student['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($student['empidno']) ?></span></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($student['email']) ?></small></td>
                                        <td><small><?= date('M d, Y', strtotime($student['enrolled_at'])) ?></small></td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?= $student['submitted_activities'] ?> submitted</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $student['completed_quizzes'] ?> completed</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Teacher Modal -->
    <div class="modal fade" id="assignTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Assign Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="teacher_empidno" class="form-label">Select Teacher</label>
                            <select class="form-select" id="teacher_empidno" name="teacher_empidno" required>
                                <option value="">Choose a teacher...</option>
                                <?php foreach ($allTeachers as $t): ?>
                                <option value="<?= htmlspecialchars($t['empidno']) ?>">
                                    <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['empidno']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="assign_teacher" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Assign Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-cloud-upload me-2"></i>Upload Learning Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Select PDF File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".pdf" required>
                            <small class="text-muted">Maximum file size: <?= round((int)env('MAX_UPLOAD_BYTES', 20971520) / 1048576) ?>MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="upload_file" class="btn btn-success">
                            <i class="bi bi-cloud-upload me-2"></i>Upload File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyEnrollmentKey() {
            const key = '<?= htmlspecialchars($subject['enrollment_key'] ?? '') ?>';
            navigator.clipboard.writeText(key).then(() => {
                alert('Enrollment key copied to clipboard: ' + key);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>
    <style>
        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</body>
</html>
