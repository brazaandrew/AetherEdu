<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/student.php';
require_once __DIR__ . '/../src/Helpers/audit.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();

// Only allow registrar and admin roles
if (!in_array($user['role'], ['registrar', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

$success = null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_student'])) {
    $lastname = trim($_POST['lastname'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gradeLevel = $_POST['grade_level'] ?? '';
    $dateOfBirth = $_POST['date_of_birth'] ?? null;
    $gender = $_POST['gender'] ?? '';
    $age = $_POST['age'] ?? null;
    $placeOfBirth = trim($_POST['place_of_birth'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $religion = trim($_POST['religion'] ?? '');
    $homeAddress = trim($_POST['home_address'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $fatherName = trim($_POST['father_name'] ?? '');
    $fatherOccupation = trim($_POST['father_occupation'] ?? '');
    $fatherContact = trim($_POST['father_contact'] ?? '');
    $motherName = trim($_POST['mother_name'] ?? '');
    $motherOccupation = trim($_POST['mother_occupation'] ?? '');
    $motherContact = trim($_POST['mother_contact'] ?? '');
    $guardianName = trim($_POST['guardian_name'] ?? '');
    $guardianContact = trim($_POST['guardian_contact'] ?? '');
    $guardianRelationship = trim($_POST['guardian_relationship'] ?? '');
    $lastSchoolAttended = trim($_POST['last_school_attended'] ?? '');
    $lastSchoolAddress = trim($_POST['last_school_address'] ?? '');
    $schoolYearCompleted = trim($_POST['school_year_completed'] ?? '');
    $generalAverage = trim($_POST['general_average'] ?? '');
    $hasLrn = isset($_POST['has_lrn']) ? 1 : 0;
    $lrnNumber = trim($_POST['lrn_number'] ?? '');
    $isReturnee = isset($_POST['is_returnee']) ? 1 : 0;
    $isTransferIn = isset($_POST['is_transfer_in']) ? 1 : 0;
    $hasSpecialNeeds = isset($_POST['has_special_needs']) ? 1 : 0;
    $specialNeedsType = trim($_POST['special_needs_type'] ?? '');
    $is4psBeneficiary = isset($_POST['is_4ps_beneficiary']) ? 1 : 0;
    $isIndigenous = isset($_POST['is_indigenous']) ? 1 : 0;
    $indigenousGroup = trim($_POST['indigenous_group'] ?? '');
    $motherTongue = trim($_POST['mother_tongue'] ?? '');
    
    // Retention Status
    $retentionStatus = $_POST['retention_status'] ?? 'Promoted';
    $retentionReason = trim($_POST['retention_reason'] ?? '');
    $retentionSchoolYear = trim($_POST['retention_school_year'] ?? '');
    
    // Validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($gradeLevel)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $fullName = $lastname . ', ' . $firstname;
        if (!empty($middleName)) {
            $fullName .= ' ' . $middleName;
        }

        $duplicate = studentFindEnrollmentDuplicate($fullName, $email, $hasLrn ? $lrnNumber : '');
        if ($duplicate !== null) {
            $error = studentEnrollmentDuplicateMessage($duplicate);
        } else {
            try {
                // Generate student ID (format: STU-YYYY-XXXX)
                $year = date('Y');
                $stmt = db()->query("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND YEAR(created_at) = $year");
                $count = $stmt->fetch()['count'] + 1;
                $studentId = 'STU-' . $year . '-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
                
                // Generate temporary password
                $tempPassword = bin2hex(random_bytes(4));
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                // Simple INSERT with essential fields only
                $sql = "INSERT INTO users (empidno, name, middle_name, email, password_hash, role, grade_level, created_at, archived) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
                $params = [$studentId, $fullName, $middleName, $email, $hashedPassword, 'student', $gradeLevel];
                
                $stmt = db()->prepare($sql);
                $stmt->execute($params);
                $newStudentDbId = (int) db()->lastInsertId();
                
                if ($newStudentDbId > 0) {
                    // Update with additional fields one by one to avoid column issues
                    $updateQueries = [
                        "UPDATE users SET date_of_birth = ? WHERE id = ?" => [$dateOfBirth, $newStudentDbId],
                        "UPDATE users SET gender = ? WHERE id = ?" => [$gender, $newStudentDbId],
                        "UPDATE users SET age = ? WHERE id = ?" => [$age, $newStudentDbId],
                        "UPDATE users SET place_of_birth = ? WHERE id = ?" => [$placeOfBirth, $newStudentDbId],
                        "UPDATE users SET nationality = ? WHERE id = ?" => [$nationality, $newStudentDbId],
                        "UPDATE users SET religion = ? WHERE id = ?" => [$religion, $newStudentDbId],
                        "UPDATE users SET home_address = ? WHERE id = ?" => [$homeAddress, $newStudentDbId],
                        "UPDATE users SET contact_number = ? WHERE id = ?" => [$contactNumber, $newStudentDbId],
                        "UPDATE users SET father_name = ? WHERE id = ?" => [$fatherName, $newStudentDbId],
                        "UPDATE users SET father_occupation = ? WHERE id = ?" => [$fatherOccupation, $newStudentDbId],
                        "UPDATE users SET father_contact = ? WHERE id = ?" => [$fatherContact, $newStudentDbId],
                        "UPDATE users SET mother_name = ? WHERE id = ?" => [$motherName, $newStudentDbId],
                        "UPDATE users SET mother_occupation = ? WHERE id = ?" => [$motherOccupation, $newStudentDbId],
                        "UPDATE users SET mother_contact = ? WHERE id = ?" => [$motherContact, $newStudentDbId],
                        "UPDATE users SET guardian_name = ? WHERE id = ?" => [$guardianName, $newStudentDbId],
                        "UPDATE users SET guardian_contact = ? WHERE id = ?" => [$guardianContact, $newStudentDbId],
                        "UPDATE users SET guardian_relationship = ? WHERE id = ?" => [$guardianRelationship, $newStudentDbId],
                        "UPDATE users SET last_school_attended = ? WHERE id = ?" => [$lastSchoolAttended, $newStudentDbId],
                        "UPDATE users SET last_school_address = ? WHERE id = ?" => [$lastSchoolAddress, $newStudentDbId],
                        "UPDATE users SET school_year_completed = ? WHERE id = ?" => [$schoolYearCompleted, $newStudentDbId],
                        "UPDATE users SET general_average = ? WHERE id = ?" => [$generalAverage, $newStudentDbId],
                        "UPDATE users SET has_lrn = ? WHERE id = ?" => [$hasLrn, $newStudentDbId],
                        "UPDATE users SET lrn_number = ? WHERE id = ?" => [$lrnNumber, $newStudentDbId],
                        "UPDATE users SET is_returnee = ? WHERE id = ?" => [$isReturnee, $newStudentDbId],
                        "UPDATE users SET is_transfer_in = ? WHERE id = ?" => [$isTransferIn, $newStudentDbId],
                        "UPDATE users SET has_special_needs = ? WHERE id = ?" => [$hasSpecialNeeds, $newStudentDbId],
                        "UPDATE users SET special_needs_type = ? WHERE id = ?" => [$specialNeedsType, $newStudentDbId],
                        "UPDATE users SET is_4ps_beneficiary = ? WHERE id = ?" => [$is4psBeneficiary, $newStudentDbId],
                        "UPDATE users SET is_indigenous = ? WHERE id = ?" => [$isIndigenous, $newStudentDbId],
                        "UPDATE users SET indigenous_group = ? WHERE id = ?" => [$indigenousGroup, $newStudentDbId],
                        "UPDATE users SET mother_tongue = ? WHERE id = ?" => [$motherTongue, $newStudentDbId]
                    ];
                    
                    // Execute each update individually, skip if column doesn't exist
                    foreach ($updateQueries as $updateSql => $updateParams) {
                        try {
                            if (!empty($updateParams[0])) {
                                $updateStmt = db()->prepare($updateSql);
                                $updateStmt->execute($updateParams);
                            }
                        } catch (Exception $e) {
                            // Skip if column doesn't exist
                        }
                    }
                    
                    // Try to add retention status if columns exist
                    try {
                        $retentionSql = "UPDATE users SET retention_status = ?, retention_reason = ?, retention_school_year = ?, retention_updated_at = NOW(), retention_updated_by = ? WHERE id = ?";
                        $retentionStmt = db()->prepare($retentionSql);
                        $retentionStmt->execute([$retentionStatus, $retentionReason, $retentionSchoolYear, $user['id'], $newStudentDbId]);
                    } catch (Exception $e) {
                        // Skip if retention columns don't exist
                    }
                }
                
                $success = "Student enrolled successfully! Student ID: $studentId, Temporary Password: $tempPassword";
                
            } catch (Exception $e) {
                $error = 'Failed to enroll student. Email may already exist. ' . $e->getMessage();
            }
        }
    }
}

// Grade levels for dropdown
$gradeLevels = ['Nursery', 'Kinder', 'Preparatory', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

// Generate school year options (current year - 5 to current year + 1)
$currentYear = (int)date('Y');
$schoolYears = [];
for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
    $nextYear = $i + 1;
    $schoolYears[] = "$i-$nextYear";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Student Enrollment'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h4><i class="bi bi-person-plus me-2"></i>Enroll New Student</h4>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="enroll_student" value="1">
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Basic Information</h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="lastname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastname" name="lastname" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="firstname" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstname" name="firstname" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="grade_level" class="form-label">Grade Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="grade_level" name="grade_level" required>
                                    <option value="">Select Grade Level</option>
                                    <?php foreach ($gradeLevels as $grade): ?>
                                    <option value="<?= $grade ?>"><?= $grade ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Personal Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Personal Details</h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" min="1" max="100">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="place_of_birth" class="form-label">Place of Birth</label>
                                <input type="text" class="form-control" id="place_of_birth" name="place_of_birth">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nationality" class="form-label">Nationality</label>
                                <input type="text" class="form-control" id="nationality" name="nationality" value="Filipino">
                            </div>
                        </div>

                        <!-- Academic Status -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">Academic Status</h5>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="retention_status" class="form-label">Retention Status</label>
                                <select class="form-select" id="retention_status" name="retention_status">
                                    <option value="Promoted">Promoted - Normal grade progression</option>
                                    <option value="Retained">Retained - Repeating current grade</option>
                                    <option value="Irregular">Irregular - Special enrollment circumstances</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="retention_school_year" class="form-label">School Year (if applicable)</label>
                                <select class="form-select" id="retention_school_year" name="retention_school_year">
                                    <option value="">Select School Year</option>
                                    <?php foreach ($schoolYears as $sy): ?>
                                    <option value="<?= $sy ?>"><?= $sy ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="retention_reason" class="form-label">Reason (if retained/irregular)</label>
                                <select class="form-select" id="retention_reason" name="retention_reason">
                                    <option value="">Select reason</option>
                                    <option value="Academic performance">Academic performance</option>
                                    <option value="Attendance issues">Attendance issues</option>
                                    <option value="Health reasons">Health reasons</option>
                                    <option value="Family circumstances">Family circumstances</option>
                                    <option value="Transfer student">Transfer student</option>
                                    <option value="Late enrollment">Late enrollment</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Enroll Student
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>