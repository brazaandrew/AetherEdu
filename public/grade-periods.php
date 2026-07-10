<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireAdmin();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    
    if ($csrfToken !== $sessionToken) {
        $error = 'Invalid CSRF token';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_periods') {
            try {
                $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];
                
                foreach ($quarters as $quarter) {
                    $enabled = isset($_POST["enable_$quarter"]) ? 1 : 0;
                    $deadline = $_POST["deadline_$quarter"] ?? null;
                    
                    // Convert deadline to MySQL datetime format
                    if ($deadline && $enabled) {
                        $deadline = date('Y-m-d H:i:s', strtotime($deadline));
                    } else {
                        $deadline = null;
                    }
                    
                    $stmt = db()->prepare('UPDATE grade_periods SET is_enabled = ?, deadline = ? WHERE quarter = ?');
                    $stmt->execute([$enabled, $deadline, $quarter]);
                }
                
                saveAudit($user['id'], 'update', 'grade_periods', 0, ['action' => 'update_all_periods']);
                $message = 'Grade periods updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update grade periods: ' . $e->getMessage();
            }
        }
    }
}

// Fetch current grade periods
$stmt = db()->query('SELECT * FROM grade_periods ORDER BY quarter');
$gradePeriods = $stmt->fetchAll();

// Convert to associative array for easier access
$periods = [];
foreach ($gradePeriods as $period) {
    $periods[$period['quarter']] = $period;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Period Management - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Grade Period Management'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header mb-4">
                <h2>
                    <i class="bi bi-calendar-check"></i>
                    Grade Period Management
                </h2>
                <p class="text-muted">Enable/disable quarters and set deadlines for grade encoding</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Configure Grade Periods</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="update_periods">
                        
                        <div class="row">
                            <?php 
                            $quarters = ['Q1' => 'First Quarter', 'Q2' => 'Second Quarter', 'Q3' => 'Third Quarter', 'Q4' => 'Fourth Quarter'];
                            foreach ($quarters as $qKey => $qLabel): 
                                $period = $periods[$qKey] ?? null;
                                $isEnabled = $period && $period['is_enabled'] == 1;
                                $deadline = $period ? $period['deadline'] : null;
                                $deadlineFormatted = $deadline ? date('Y-m-d\TH:i', strtotime($deadline)) : '';
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 <?= $isEnabled ? 'border-success' : 'border-secondary' ?>">
                                    <div class="card-header <?= $isEnabled ? 'bg-success text-white' : 'bg-secondary text-white' ?>">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input quarter-toggle" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   id="enable_<?= $qKey ?>" 
                                                   name="enable_<?= $qKey ?>"
                                                   data-quarter="<?= $qKey ?>"
                                                   <?= $isEnabled ? 'checked' : '' ?>
                                                   style="font-size: 1.5rem; cursor: pointer;">
                                            <label class="form-check-label fs-5 fw-bold" for="enable_<?= $qKey ?>" style="cursor: pointer;">
                                                <?= $qLabel ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="deadline_<?= $qKey ?>" class="form-label">
                                                <i class="bi bi-calendar-event me-2"></i>Encoding Deadline
                                            </label>
                                            <input type="datetime-local" 
                                                   class="form-control deadline-input" 
                                                   id="deadline_<?= $qKey ?>" 
                                                   name="deadline_<?= $qKey ?>"
                                                   value="<?= $deadlineFormatted ?>"
                                                   <?= !$isEnabled ? 'disabled' : '' ?>>
                                            <small class="text-muted">Set the last date and time for teachers to encode grades</small>
                                        </div>
                                        
                                        <div class="alert alert-info">
                                            <strong>Status:</strong> 
                                            <?php if ($isEnabled): ?>
                                                <span class="badge bg-success">ENABLED - Teachers can encode grades</span>
                                                <?php if ($deadline): ?>
                                                    <br><small>Deadline: <?= date('F d, Y - h:i A', strtotime($deadline)) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">DISABLED - Grade encoding locked</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i>Save All Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>How it Works</h5>
                    <ul>
                        <li><strong>Enable Toggle:</strong> Turn on/off the switch to enable or disable grade encoding for each quarter</li>
                        <li><strong>Deadline:</strong> Set a deadline for when teachers must finish encoding grades (optional)</li>
                        <li><strong>Teacher Access:</strong> When a quarter is disabled, teachers cannot edit grades for that quarter</li>
                        <li><strong>Automatic Lock:</strong> Once the deadline passes, the quarter is automatically locked</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle deadline input based on checkbox state
        document.querySelectorAll('.quarter-toggle').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const quarter = this.dataset.quarter;
                const deadlineInput = document.getElementById('deadline_' + quarter);
                const card = this.closest('.card');
                const cardHeader = card.querySelector('.card-header');
                
                if (this.checked) {
                    deadlineInput.disabled = false;
                    card.classList.remove('border-secondary');
                    card.classList.add('border-success');
                    cardHeader.classList.remove('bg-secondary');
                    cardHeader.classList.add('bg-success');
                } else {
                    deadlineInput.disabled = true;
                    card.classList.remove('border-success');
                    card.classList.add('border-secondary');
                    cardHeader.classList.remove('bg-success');
                    cardHeader.classList.add('bg-secondary');
                }
            });
        });
    </script>
</body>
</html>
