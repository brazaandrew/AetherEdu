<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/HRController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'hr'])) {
    header('Location: dashboard.php');
    exit;
}

$hrController = new HRController();

$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;
if (!$employeeId) {
    header('Location: hr-dashboard.php');
    exit;
}

$employee = $hrController->getEmployee($employeeId);
if (!$employee) {
    header('Location: hr-dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'save_201_file':
                $data = array_merge(['employee_id' => $employeeId], $_POST);
                $result = $hrController->save201File($data);
                if ($result['success']) {
                    $_SESSION['hr_message'] = '201 File saved successfully!';
                } else {
                    $_SESSION['hr_error'] = 'Error: ' . $result['error'];
                }
                break;
            case 'add_education':
                $data = array_merge(['employee_id' => $employeeId], $_POST);
                $result = $hrController->addEducation($data);
                break;
            case 'add_experience':
                $data = array_merge(['employee_id' => $employeeId], $_POST);
                $result = $hrController->addWorkExperience($data);
                break;
            case 'add_training':
                $data = array_merge(['employee_id' => $employeeId], $_POST);
                $result = $hrController->addTraining($data);
                break;
        }
    }
    header("Location: employee-201-file.php?employee_id=$employeeId");
    exit;
}

// Handle GET deletions
if (isset($_GET['delete_education'])) {
    $hrController->deleteEducation((int)$_GET['delete_education']);
    $_SESSION['hr_message'] = 'Education record deleted.';
    header("Location: employee-201-file.php?employee_id=$employeeId");
    exit;
}
if (isset($_GET['delete_experience'])) {
    $hrController->deleteWorkExperience((int)$_GET['delete_experience']);
    $_SESSION['hr_message'] = 'Work experience record deleted.';
    header("Location: employee-201-file.php?employee_id=$employeeId");
    exit;
}
if (isset($_GET['delete_training'])) {
    $hrController->deleteTraining((int)$_GET['delete_training']);
    $_SESSION['hr_message'] = 'Training record deleted.';
    header("Location: employee-201-file.php?employee_id=$employeeId");
    exit;
}
if (isset($_GET['delete_document'])) {
    $hrController->deleteDocument((int)$_GET['delete_document']);
    $_SESSION['hr_message'] = 'Document deleted.';
    header("Location: employee-201-file.php?employee_id=$employeeId");
    exit;
}

$file = $hrController->get201File($employeeId);
$education = $hrController->getEducation($employeeId);
$workExp = $hrController->getWorkExperience($employeeId);
$trainings = $hrController->getTrainings($employeeId);
$documents = $hrController->getDocuments($employeeId);

$error = $_SESSION['hr_error'] ?? '';
$message = $_SESSION['hr_message'] ?? '';
unset($_SESSION['hr_error'], $_SESSION['hr_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>201 File - <?= htmlspecialchars($employee['name']) ?> - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        <div class="container-fluid p-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="hr-dashboard.php">HR Dashboard</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($employee['name']) ?> - 201 File</li>
                </ol>
            </nav>
            
            <?php if ($message): ?><div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>201 File</h2>
                <span class="badge bg-primary fs-6"><?= htmlspecialchars($employee['empidno']) ?> | <?= ucfirst(str_replace('_', ' ', $employee['role'])) ?></span>
            </div>
            
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="fileTab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personal">Personal Information</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#family">Family Background</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#education">Education</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#experience">Work Experience</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#trainings">Trainings</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents">Documents</button></li>
            </ul>
            
            <div class="tab-content">
                <!-- Personal Information -->
                <div class="tab-pane fade show active" id="personal">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_201_file">
                        <div class="card">
                            <div class="card-header bg-white"><h5 class="mb-0">Personal Information</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control" value="<?= $file['date_of_birth'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Place of Birth</label>
                                        <input type="text" name="place_of_birth" class="form-control" value="<?= $file['place_of_birth'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Sex</label>
                                        <select name="sex" class="form-select">
                                            <option value="">Select</option>
                                            <option value="male" <?= ($file['sex'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($file['sex'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Civil Status</label>
                                        <select name="civil_status" class="form-select">
                                            <option value="">Select</option>
                                            <option value="single" <?= ($file['civil_status'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                                            <option value="married" <?= ($file['civil_status'] ?? '') === 'married' ? 'selected' : '' ?>>Married</option>
                                            <option value="widowed" <?= ($file['civil_status'] ?? '') === 'widowed' ? 'selected' : '' ?>>Widowed</option>
                                            <option value="separated" <?= ($file['civil_status'] ?? '') === 'separated' ? 'selected' : '' ?>>Separated</option>
                                            <option value="annulled" <?= ($file['civil_status'] ?? '') === 'annulled' ? 'selected' : '' ?>>Annulled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Citizenship</label>
                                        <input type="text" name="citizenship" class="form-control" value="<?= $file['citizenship'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Height (cm)</label>
                                        <input type="number" step="0.01" name="height_cm" class="form-control" value="<?= $file['height_cm'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01" name="weight_kg" class="form-control" value="<?= $file['weight_kg'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Blood Type</label>
                                        <input type="text" name="blood_type" class="form-control" value="<?= $file['blood_type'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">GSIS No.</label>
                                        <input type="text" name="gsis_no" class="form-control" value="<?= $file['gsis_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">PAG-IBIG No.</label>
                                        <input type="text" name="pagibig_no" class="form-control" value="<?= $file['pagibig_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">PhilHealth No.</label>
                                        <input type="text" name="philhealth_no" class="form-control" value="<?= $file['philhealth_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">SSS No.</label>
                                        <input type="text" name="sss_no" class="form-control" value="<?= $file['sss_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">TIN No.</label>
                                        <input type="text" name="tin_no" class="form-control" value="<?= $file['tin_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Agency Employee No.</label>
                                        <input type="text" name="agency_employee_no" class="form-control" value="<?= $file['agency_employee_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Residential Address</label>
                                        <textarea name="residential_address" class="form-control" rows="2"><?= $file['residential_address'] ?? '' ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Permanent Address</label>
                                        <textarea name="permanent_address" class="form-control" rows="2"><?= $file['permanent_address'] ?? '' ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Telephone No.</label>
                                        <input type="text" name="telephone_no" class="form-control" value="<?= $file['telephone_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mobile No.</label>
                                        <input type="text" name="mobile_no" class="form-control" value="<?= $file['mobile_no'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= $file['email'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header bg-white"><h5 class="mb-0">Employment Details</h5></div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Date Hired</label>
                                        <input type="date" name="date_hired" class="form-control" value="<?= $file['date_hired'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Employment Status</label>
                                        <select name="employment_status" class="form-select">
                                            <option value="">Select</option>
                                            <option value="permanent" <?= ($file['employment_status'] ?? '') === 'permanent' ? 'selected' : '' ?>>Permanent</option>
                                            <option value="temporary" <?= ($file['employment_status'] ?? '') === 'temporary' ? 'selected' : '' ?>>Temporary</option>
                                            <option value="contractual" <?= ($file['employment_status'] ?? '') === 'contractual' ? 'selected' : '' ?>>Contractual</option>
                                            <option value="substitute" <?= ($file['employment_status'] ?? '') === 'substitute' ? 'selected' : '' ?>>Substitute</option>
                                            <option value="part_time" <?= ($file['employment_status'] ?? '') === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Position Title</label>
                                        <input type="text" name="position_title" class="form-control" value="<?= $file['position_title'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Department</label>
                                        <input type="text" name="department" class="form-control" value="<?= $file['department'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Salary Grade</label>
                                        <input type="text" name="salary_grade" class="form-control" value="<?= $file['salary_grade'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Monthly Salary</label>
                                        <input type="number" step="0.01" name="monthly_salary" class="form-control" value="<?= $file['monthly_salary'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Skills / Hobbies</label>
                                        <textarea name="skills" class="form-control" rows="2"><?= $file['skills'] ?? '' ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Non-Academic Recognitions</label>
                                        <textarea name="recognitions" class="form-control" rows="2"><?= $file['recognitions'] ?? '' ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Membership in Organizations</label>
                                        <textarea name="organizations" class="form-control" rows="2"><?= $file['organizations'] ?? '' ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Personal Information</button>
                        </div>
                    </form>
                </div>
                
                <!-- Family Background -->
                <div class="tab-pane fade" id="family">
                    <form method="POST">
                        <input type="hidden" name="action" value="save_201_file">
                        <div class="card">
                            <div class="card-header bg-white"><h5 class="mb-0">Family Background</h5></div>
                            <div class="card-body">
                                <h6 class="text-muted">Spouse Information</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">Spouse's Name</label>
                                        <input type="text" name="spouse_name" class="form-control" value="<?= $file['spouse_name'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Spouse's Occupation</label>
                                        <input type="text" name="spouse_occupation" class="form-control" value="<?= $file['spouse_occupation'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Employer/Business Name</label>
                                        <input type="text" name="spouse_employer" class="form-control" value="<?= $file['spouse_employer'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Business Address</label>
                                        <textarea name="spouse_business_address" class="form-control" rows="2"><?= $file['spouse_business_address'] ?? '' ?></textarea>
                                    </div>
                                </div>
                                <h6 class="text-muted">Parents Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Father's Name</label>
                                        <input type="text" name="father_name" class="form-control" value="<?= $file['father_name'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Father's Occupation</label>
                                        <input type="text" name="father_occupation" class="form-control" value="<?= $file['father_occupation'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mother's Name</label>
                                        <input type="text" name="mother_name" class="form-control" value="<?= $file['mother_name'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mother's Occupation</label>
                                        <input type="text" name="mother_occupation" class="form-control" value="<?= $file['mother_occupation'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Family Background</button>
                        </div>
                    </form>
                </div>
                
                <!-- Education -->
                <div class="tab-pane fade" id="education">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Educational Background</h5>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addEducationModal"><i class="bi bi-plus"></i> Add Education</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Level</th><th>School Name</th><th>Degree/Course</th><th>Year Graduated</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($education as $ed): ?>
                                        <tr>
                                            <td><?= ucfirst(str_replace('_', ' ', $ed['level'])) ?></td>
                                            <td><?= htmlspecialchars($ed['school_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($ed['degree_course'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($ed['year_graduated'] ?? '-') ?></td>
                                            <td>
                                                <a href="?employee_id=<?= $employeeId ?>&delete_education=<?= $ed['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; if (empty($education)): ?><tr><td colspan="5" class="text-center text-muted">No education records</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Work Experience -->
                <div class="tab-pane fade" id="experience">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Work Experience</h5>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addExperienceModal"><i class="bi bi-plus"></i> Add Experience</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Date From</th><th>Date To</th><th>Position</th><th>Department</th><th>Status</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($workExp as $exp): ?>
                                        <tr>
                                            <td><?= $exp['date_from'] ?? '-' ?></td>
                                            <td><?= $exp['date_to'] ?? 'Present' ?></td>
                                            <td><?= htmlspecialchars($exp['position_title'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($exp['department_office'] ?? '-') ?></td>
                                            <td><?= ucfirst($exp['status_of_appointment'] ?? '-') ?></td>
                                            <td>
                                                <a href="?employee_id=<?= $employeeId ?>&delete_experience=<?= $exp['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; if (empty($workExp)): ?><tr><td colspan="6" class="text-center text-muted">No work experience records</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Trainings -->
                <div class="tab-pane fade" id="trainings">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Training Programs</h5>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addTrainingModal"><i class="bi bi-plus"></i> Add Training</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Title</th><th>From</th><th>To</th><th>Hours</th><th>Type</th><th>Conducted By</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($trainings as $t): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($t['title']) ?></td>
                                            <td><?= $t['date_from'] ?? '-' ?></td>
                                            <td><?= $t['date_to'] ?? '-' ?></td>
                                            <td><?= $t['hours'] ?? '-' ?></td>
                                            <td><?= ucfirst(str_replace('_', ' ', $t['type_of_ld'] ?? '-')) ?></td>
                                            <td><?= htmlspecialchars($t['conducted_by'] ?? '-') ?></td>
                                            <td>
                                                <a href="?employee_id=<?= $employeeId ?>&delete_training=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; if (empty($trainings)): ?><tr><td colspan="7" class="text-center text-muted">No training records</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Documents -->
                <div class="tab-pane fade" id="documents">
                    <div class="card">
                        <div class="card-header bg-white"><h5 class="mb-0">Uploaded Documents</h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>Document Name</th><th>Type</th><th>Uploaded By</th><th>Date</th><th>Actions</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($doc['document_name']) ?></td>
                                            <td><?= ucfirst(str_replace('_', ' ', $doc['document_type'])) ?></td>
                                            <td><?= htmlspecialchars($doc['uploaded_by_name'] ?? '-') ?></td>
                                            <td><?= $doc['uploaded_at'] ?></td>
                                            <td>
                                                <a href="?employee_id=<?= $employeeId ?>&delete_document=<?= $doc['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this document?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; if (empty($documents)): ?><tr><td colspan="5" class="text-center text-muted">No documents uploaded</td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Education Modal -->
    <div class="modal fade" id="addEducationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Education</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_education">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Level</label><select name="level" class="form-select" required>
                            <option value="elementary">Elementary</option>
                            <option value="secondary">Secondary</option>
                            <option value="vocational">Vocational</option>
                            <option value="college">College</option>
                            <option value="graduate_studies">Graduate Studies</option>
                        </select></div>
                        <div class="mb-3"><label class="form-label">School Name</label><input type="text" name="school_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Degree/Course</label><input type="text" name="degree_course" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Year Graduated</label><input type="text" name="year_graduated" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Highest Level/Units Earned</label><input type="text" name="highest_level" class="form-control"></div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label">Year Attended From</label><input type="text" name="year_attended_from" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Year Attended To</label><input type="text" name="year_attended_to" class="form-control"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Honors Received</label><input type="text" name="honors_received" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Education</button></div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Experience Modal -->
    <div class="modal fade" id="addExperienceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Work Experience</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_experience">
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label">Date From</label><input type="date" name="date_from" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Date To</label><input type="date" name="date_to" class="form-control"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Position Title</label><input type="text" name="position_title" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Department/Office</label><input type="text" name="department_office" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Monthly Salary</label><input type="number" step="0.01" name="monthly_salary" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Salary Grade</label><input type="text" name="salary_grade" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Status of Appointment</label><select name="status_of_appointment" class="form-select">
                            <option value="">Select</option>
                            <option value="permanent">Permanent</option>
                            <option value="temporary">Temporary</option>
                            <option value="contractual">Contractual</option>
                            <option value="substitute">Substitute</option>
                            <option value="part_time">Part Time</option>
                        </select></div>
                        <div class="mb-3"><label class="form-label">Gov't Service</label><select name="gov_service" class="form-select"><option value="0">No</option><option value="1">Yes</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Experience</button></div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Training Modal -->
    <div class="modal fade" id="addTrainingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Training</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_training">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
                        <div class="row g-2">
                            <div class="col-md-6"><label class="form-label">Date From</label><input type="date" name="date_from" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Date To</label><input type="date" name="date_to" class="form-control"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Number of Hours</label><input type="number" name="hours" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Type of L&D</label><select name="type_of_ld" class="form-select">
                            <option value="">Select</option>
                            <option value="managerial">Managerial</option>
                            <option value="supervisory">Supervisory</option>
                            <option value="technical">Technical</option>
                            <option value="others">Others</option>
                        </select></div>
                        <div class="mb-3"><label class="form-label">Conducted By</label><input type="text" name="conducted_by" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Training</button></div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
