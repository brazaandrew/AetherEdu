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

if ($role !== 'student') {
    header('Location: dashboard.php');
    exit;
}

$clinicController = new ClinicController();
$studentId = $_SESSION['user']['id'];

$profile = $clinicController->getMedicalProfile($studentId);
$visits = $clinicController->getStudentVisits($studentId);
$immunizations = $clinicController->getStudentImmunizations($studentId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Clinic Profile - TLCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php require_once 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php require_once 'includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <h2 class="mb-4">My Clinic Profile</h2>
            
            <div class="row">
                <!-- Medical Info -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-file-medical"></i> Medical Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!$profile): ?>
                                <p class="text-muted">No medical profile on file. Please contact the school clinic.</p>
                            <?php else: ?>
                                <table class="table table-borderless">
                                    <tr>
                                        <td width="35%"><strong>Blood Type:</strong></td>
                                        <td><?= $profile['blood_type'] ?: 'Not recorded' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Height:</strong></td>
                                        <td><?= $profile['height_cm'] ? $profile['height_cm'] . ' cm' : 'Not recorded' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Weight:</strong></td>
                                        <td><?= $profile['weight_kg'] ? $profile['weight_kg'] . ' kg' : 'Not recorded' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>BMI:</strong></td>
                                        <td><?= $profile['bmi'] ? number_format((float) $profile['bmi'], 1) : 'Not calculated' ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Medical Conditions:</strong></td>
                                        <td><?= nl2br(htmlspecialchars($profile['medical_conditions'] ?: 'None recorded')) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Allergies:</strong></td>
                                        <td><?= nl2br(htmlspecialchars($profile['allergies'] ?: 'None recorded')) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Medications:</strong></td>
                                        <td><?= nl2br(htmlspecialchars($profile['medications'] ?: 'None recorded')) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Emergency Contact:</strong></td>
                                        <td>
                                            <?= htmlspecialchars($profile['emergency_contact_name'] ?: 'Not recorded') ?><br>
                                            <?= htmlspecialchars($profile['emergency_contact_phone'] ?: '') ?><br>
                                            <?= htmlspecialchars($profile['emergency_contact_relationship'] ?: '') ?>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Visits & Immunizations -->
                <div class="col-md-6">
                    <!-- Clinic Visits -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-heart-pulse"></i> Clinic Visit History</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($visits)): ?>
                                <p class="text-muted">No clinic visits recorded.</p>
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
                                    <p class="mb-1"><strong>Complaint:</strong> <?= htmlspecialchars($visit['complaint']) ?></p>
                                    <?php if ($visit['diagnosis']): ?>
                                    <p class="mb-1 text-muted"><strong>Diagnosis:</strong> <?= htmlspecialchars($visit['diagnosis']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($visit['treatment']): ?>
                                    <p class="mb-1 text-muted"><strong>Treatment:</strong> <?= htmlspecialchars($visit['treatment']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">Attended by: <?= htmlspecialchars($visit['attended_by_name'] ?? 'N/A') ?></small>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Immunizations -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-shield-plus"></i> Immunization Records</h5>
                        </div>
                        <div class="card-body">
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
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
