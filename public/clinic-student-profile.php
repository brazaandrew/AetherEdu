<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/ClinicController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();
$role = $user['role'];

if (!in_array($role, ['admin', 'teacher', 'nurse'])) {
    header('Location: dashboard.php');
    exit;
}

$clinicController = new ClinicController();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $result = $clinicController->saveMedicalProfile([
            'student_id' => (int) $_POST['student_id'],
            'blood_type' => $_POST['blood_type'] ?? null,
            'height_cm' => $_POST['height_cm'] ?? null,
            'weight_kg' => $_POST['weight_kg'] ?? null,
            'medical_conditions' => $_POST['medical_conditions'] ?? null,
            'allergies' => $_POST['allergies'] ?? null,
            'medications' => $_POST['medications'] ?? null,
            'emergency_contact_name' => $_POST['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $_POST['emergency_contact_phone'] ?? null,
            'emergency_contact_relationship' => $_POST['emergency_contact_relationship'] ?? null,
            'physician_name' => $_POST['physician_name'] ?? null,
            'physician_phone' => $_POST['physician_phone'] ?? null,
            'insurance_provider' => $_POST['insurance_provider'] ?? null,
            'insurance_policy_number' => $_POST['insurance_policy_number'] ?? null,
            'notes' => $_POST['notes'] ?? null
        ]);
        if ($result['success']) {
            $message = 'Medical profile saved successfully!';
        } else {
            $error = $result['error'];
        }
    } elseif (isset($_POST['add_visit'])) {
        $result = $clinicController->addClinicVisit([
            'student_id' => (int) $_POST['student_id'],
            'visit_date' => $_POST['visit_date'],
            'visit_time' => $_POST['visit_time'] ?? null,
            'complaint' => $_POST['complaint'] ?? null,
            'diagnosis' => $_POST['diagnosis'] ?? null,
            'treatment' => $_POST['treatment'] ?? null,
            'medication_given' => $_POST['medication_given'] ?? null,
            'action_taken' => $_POST['action_taken'] ?? 'treated',
            'attended_by' => $_SESSION['user']['id'],
            'notes' => $_POST['visit_notes'] ?? null
        ]);
        if ($result['success']) {
            $message = 'Clinic visit recorded successfully!';
        } else {
            $error = $result['error'];
        }
    } elseif (isset($_POST['add_immunization'])) {
        $result = $clinicController->addImmunization([
            'student_id' => (int) $_POST['student_id'],
            'vaccine_name' => $_POST['vaccine_name'],
            'date_administered' => $_POST['date_administered'] ?? null,
            'dose_number' => $_POST['dose_number'] ?? 1,
            'administering_facility' => $_POST['administering_facility'] ?? null,
            'notes' => $_POST['imm_notes'] ?? null
        ]);
        if ($result['success']) {
            $message = 'Immunization record added successfully!';
        } else {
            $error = $result['error'];
        }
    }
}

// Get selected student
$selectedStudent = null;
$profile = null;
$visits = [];
$immunizations = [];

if (isset($_GET['student_id'])) {
    $studentId = (int) $_GET['student_id'];
    $db = db();
    $stmt = $db->prepare("SELECT id, name, empidno, grade_level FROM users WHERE id = ? AND role = 'student'");
    $stmt->execute([$studentId]);
    $selectedStudent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selectedStudent) {
        $profile = $clinicController->getMedicalProfile($studentId);
        $visits = $clinicController->getStudentVisits($studentId);
        $immunizations = $clinicController->getStudentImmunizations($studentId);
    }
}

// Get all students for dropdown
$db = db();
$stmt = $db->query("SELECT id, name, empidno FROM users WHERE role = 'student' AND archived = 0 ORDER BY name");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Clinic Profile - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Student Clinic Profile</h2>
                <a href="clinic.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Clinic
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?= $message ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?= $error ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            
            <!-- Student Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <select name="student_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Select a student...</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>" <?= ($selectedStudent['id'] ?? '') == $student['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['name']) ?> (<?= $student['empidno'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if ($selectedStudent): ?>
            <div class="row">
                <!-- Medical Profile Form -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-file-medical"></i> Medical Profile</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Blood Type</label>
                                        <select name="blood_type" class="form-select">
                                            <option value="">Unknown</option>
                                            <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt): ?>
                                            <option value="<?= $bt ?>" <?= ($profile['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Height (cm)</label>
                                        <input type="number" name="height_cm" class="form-control" step="0.1" value="<?= $profile['height_cm'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number" name="weight_kg" class="form-control" step="0.1" value="<?= $profile['weight_kg'] ?? '' ?>">
                                    </div>
                                </div>
                                
                                <?php if (!empty($profile['bmi'])): ?>
                                <div class="mb-3">
                                    <span class="badge bg-info">BMI: <?= number_format((float) $profile['bmi'], 1) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Medical Conditions</label>
                                    <textarea name="medical_conditions" class="form-control" rows="2" placeholder="e.g., Asthma, Diabetes"><?= htmlspecialchars($profile['medical_conditions'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Allergies</label>
                                    <textarea name="allergies" class="form-control" rows="2" placeholder="e.g., Penicillin, Peanuts"><?= htmlspecialchars($profile['allergies'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Medications</label>
                                    <textarea name="medications" class="form-control" rows="2" placeholder="e.g., Albuterol inhaler"><?= htmlspecialchars($profile['medications'] ?? '') ?></textarea>
                                </div>
                                
                                <hr>
                                <h6>Emergency Contact</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="emergency_contact_name" class="form-control" value="<?= htmlspecialchars($profile['emergency_contact_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="emergency_contact_phone" class="form-control" value="<?= htmlspecialchars($profile['emergency_contact_phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Relationship</label>
                                    <input type="text" name="emergency_contact_relationship" class="form-control" value="<?= htmlspecialchars($profile['emergency_contact_relationship'] ?? '') ?>">
                                </div>
                                
                                <hr>
                                <h6>Physician & Insurance</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Physician Name</label>
                                        <input type="text" name="physician_name" class="form-control" value="<?= htmlspecialchars($profile['physician_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Physician Phone</label>
                                        <input type="text" name="physician_phone" class="form-control" value="<?= htmlspecialchars($profile['physician_phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Insurance Provider</label>
                                        <input type="text" name="insurance_provider" class="form-control" value="<?= htmlspecialchars($profile['insurance_provider'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Policy Number</label>
                                        <input type="text" name="insurance_policy_number" class="form-control" value="<?= htmlspecialchars($profile['insurance_policy_number'] ?? '') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Additional Notes</label>
                                    <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($profile['notes'] ?? '') ?></textarea>
                                </div>
                                
                                <button type="submit" name="save_profile" class="btn btn-primary w-100">
                                    <i class="bi bi-save"></i> Save Medical Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Clinic Visits & Immunizations -->
                <div class="col-md-6">
                    <!-- Add Visit Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-heart-pulse"></i> Record Clinic Visit</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="visit_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Time</label>
                                        <input type="time" name="visit_time" class="form-control" value="<?= date('H:i') ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Complaint</label>
                                    <textarea name="complaint" class="form-control" rows="2" required placeholder="Student's complaint"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Diagnosis</label>
                                    <textarea name="diagnosis" class="form-control" rows="2" placeholder="Diagnosis"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Treatment</label>
                                    <textarea name="treatment" class="form-control" rows="2" placeholder="Treatment given"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Medication Given</label>
                                    <textarea name="medication_given" class="form-control" rows="2" placeholder="Medications administered"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Action Taken</label>
                                        <select name="action_taken" class="form-select">
                                            <option value="treated">Treated</option>
                                            <option value="referred">Referred</option>
                                            <option value="sent_home">Sent Home</option>
                                            <option value="rested">Rested in Clinic</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="visit_notes" class="form-control" rows="2"></textarea>
                                </div>
                                
                                <button type="submit" name="add_visit" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Record Visit
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Visit History -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Visit History</h5>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                            <?php if (empty($visits)): ?>
                                <p class="text-muted">No visits recorded.</p>
                            <?php else: ?>
                                <?php foreach ($visits as $visit): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= date('M d, Y', strtotime($visit['visit_date'])) ?></strong>
                                        <span class="badge bg-<?= match($visit['action_taken']) {
                                            'treated' => 'success',
                                            'referred' => 'warning',
                                            'sent_home' => 'danger',
                                            'rested' => 'info',
                                            default => 'secondary'
                                        } ?>">
                                            <?= ucfirst(str_replace('_', ' ', $visit['action_taken'])) ?>
                                        </span>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($visit['complaint']) ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Immunizations -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Immunization Records</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="student_id" value="<?= $selectedStudent['id'] ?>">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="text" name="vaccine_name" class="form-control" placeholder="Vaccine name" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" name="date_administered" class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="dose_number" class="form-control" placeholder="Dose" value="1">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="add_immunization" class="btn btn-outline-primary w-100">Add</button>
                                    </div>
                                </div>
                            </form>
                            
                            <?php if (empty($immunizations)): ?>
                                <p class="text-muted">No immunization records.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Vaccine</th>
                                                <th>Date</th>
                                                <th>Dose</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($immunizations as $imm): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($imm['vaccine_name']) ?></td>
                                                <td><?= $imm['date_administered'] ? date('M d, Y', strtotime($imm['date_administered'])) : 'N/A' ?></td>
                                                <td><?= $imm['dose_number'] ?></td>
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
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Select a student to view or edit their clinic profile.
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
