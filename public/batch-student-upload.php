<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Helpers/audit.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = requireRole(['admin', 'it_personnel']);

$message = '';
$error = '';
$uploadResults = [];

// Handle CSV file upload and processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_students'])) {
    requireCsrf();
    
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileSize = $_FILES['excel_file']['size'];
        $fileType = $_FILES['excel_file']['type'];
        
        // Check if it's a CSV file
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $validFile = false;
        
        if ($fileType === 'text/csv' || $fileType === 'application/vnd.ms-excel' || $fileType === 'text/plain' || $fileExtension === 'csv') {
            $validFile = true;
        }
        
        if (!$validFile) {
            $error = 'Invalid file type. Please upload a CSV file.';
        } elseif ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            $error = 'File size exceeds 10MB limit.';
        } else {
            // Process the CSV file
            $file = fopen($fileTmpPath, 'r');
            
            if ($file === false) {
                $error = 'Could not open the uploaded file.';
            } else {
                // Read the header row
                $headerRow = fgetcsv($file);
                
                if ($headerRow === false) {
                    $error = 'CSV file is empty.';
                    fclose($file);
                } else {
                    // Normalize headers (lowercase, remove spaces)
                    $normalizedHeaders = array_map(function($header) {
                        return strtolower(trim(str_replace([' ', '-', '_'], '', $header)));
                    }, $headerRow);
                    
                    // Check if required headers exist
                    $fullnameIndex = array_search('fullname', $normalizedHeaders);
                    $emailIndex = array_search('email', $normalizedHeaders);
                    $passwordIndex = array_search('password', $normalizedHeaders);
                    $gradeLevelIndex = array_search('gradelevel', $normalizedHeaders);
                    
                    if ($fullnameIndex === false || $emailIndex === false || $passwordIndex === false) {
                        $error = 'CSV file must contain columns: Fullname, Email, Password';
                        fclose($file);
                    } else {
                        // Process each row (skip header row)
                        $successCount = 0;
                        $errorCount = 0;
                        $rowNumber = 1; // Start at 1 for the header
                        
                        while (($row = fgetcsv($file)) !== false) {
                            $rowNumber++;
                            
                            if (empty($row[$fullnameIndex]) || empty($row[$emailIndex]) || empty($row[$passwordIndex])) {
                                $uploadResults[] = [
                                    'row' => $rowNumber,
                                    'fullname' => isset($row[$fullnameIndex]) ? trim($row[$fullnameIndex]) : '',
                                    'email' => isset($row[$emailIndex]) ? trim($row[$emailIndex]) : '',
                                    'grade_level' => isset($row[$gradeLevelIndex]) ? trim($row[$gradeLevelIndex]) : '',
                                    'status' => 'error',
                                    'message' => 'Missing required data in row'
                                ];
                                $errorCount++;
                                continue;
                            }
                            
                            $fullname = trim($row[$fullnameIndex]);
                            $email = trim($row[$emailIndex]);
                            $password = trim($row[$passwordIndex]);
                            $gradeLevel = isset($row[$gradeLevelIndex]) ? trim($row[$gradeLevelIndex]) : '';
                            
                            // Validate email format
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $uploadResults[] = [
                                    'row' => $rowNumber,
                                    'fullname' => $fullname,
                                    'email' => $email,
                                    'grade_level' => $gradeLevel,
                                    'status' => 'error',
                                    'message' => 'Invalid email format'
                                ];
                                $errorCount++;
                                continue;
                            }
                            
                            // Generate student ID
                            $stmt = db()->prepare("SELECT empidno FROM users WHERE role = 'student' AND empidno REGEXP ? ORDER BY CAST(SUBSTRING(empidno, 2) AS UNSIGNED) DESC LIMIT 1");
                            $stmt->execute(['^S[0-9]+$']);
                            $lastStudent = $stmt->fetch();
                            
                            if ($lastStudent) {
                                $lastNumber = (int)substr($lastStudent['empidno'], 1);
                                $nextNumber = $lastNumber + 1;
                            } else {
                                $nextNumber = 1;
                            }
                            
                            $studentId = 'S' . str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);
                            
                            // Register the student
                            $result = registerUser($studentId, $fullname, $email, $password, 'student', $gradeLevel);
                            
                            if ($result['success']) {
                                $uploadResults[] = [
                                    'row' => $rowNumber,
                                    'fullname' => $fullname,
                                    'email' => $email,
                                    'grade_level' => $gradeLevel,
                                    'status' => 'success',
                                    'message' => 'Student registered successfully'
                                ];
                                $successCount++;
                                
                                // Log the registration action
                                saveAudit($user['id'], 'create', 'user', $result['user_id'], [
                                    'empidno' => $studentId, 
                                    'name' => $fullname, 
                                    'email' => $email, 
                                    'role' => 'student',
                                    'grade_level' => $gradeLevel
                                ]);
                            } else {
                                $uploadResults[] = [
                                    'row' => $rowNumber,
                                    'fullname' => $fullname,
                                    'email' => $email,
                                    'grade_level' => $gradeLevel,
                                    'status' => 'error',
                                    'message' => $result['error'] ?? 'Failed to register student'
                                ];
                                $errorCount++;
                            }
                        }
                        
                        fclose($file);
                        
                        if ($successCount > 0) {
                            $message = "Successfully registered $successCount student(s). $errorCount failed.";
                        } else {
                            $error = "No students were registered. $errorCount errors occurred.";
                        }
                    }
                }
            }
        }
    } else {
        $error = 'Please select a CSV file to upload.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Student Upload - eLMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css?v=2028">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php $pageTitle = 'Batch Student Upload'; include __DIR__ . '/includes/topbar.php'; ?>
        
        <div class="container-fluid p-4">
            <div class="page-header">
                <h2><i class="bi bi-file-earmark-spreadsheet"></i> Batch Student Upload</h2>
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

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-upload me-2"></i>Upload CSV File</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="excel_file" class="form-label">CSV File (.csv)</label>
                                    <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".csv" required>
                                    <div class="form-text">
                                        CSV file must contain columns: Fullname, Email, Password. Grade Level is optional<br>
                                        <a href="#" onclick="downloadSampleFile()">Download Sample File</a>
                                    </div>
                                </div>
                                <button type="submit" name="upload_students" class="btn btn-primary">
                                    <i class="bi bi-upload me-2"></i>Upload Students
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (!empty($uploadResults)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-clipboard-check me-2"></i>Upload Results</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Row</th>
                                            <th>Fullname</th>
                                            <th>Email</th>
                                            <th>Grade Level</th>
                                            <th>Status</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($uploadResults as $result): ?>
                                        <tr class="<?= $result['status'] === 'success' ? 'table-success' : 'table-danger' ?>">
                                            <td><?= $result['row'] ?></td>
                                            <td><?= htmlspecialchars($result['fullname']) ?></td>
                                            <td><?= htmlspecialchars($result['email']) ?></td>
                                            <td><?= htmlspecialchars($result['grade_level']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $result['status'] === 'success' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($result['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($result['message']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Instructions</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Prepare a CSV file with columns: <strong>Fullname</strong>, <strong>Email</strong>, <strong>Password</strong></li>
                                <li>Optionally include a <strong>Grade Level</strong> column</li>
                                <li>Email addresses must be valid</li>
                                <li>Passwords should meet security requirements</li>
                                <li>Student IDs will be auto-generated (S001, S002, etc.)</li>
                                <li>Maximum file size: 10MB</li>
                                <li>Supported format: .csv</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-file-earmark-excel me-2"></i>Sample Format</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Fullname</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Grade Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Doe</td>
                                        <td>john.doe@example.com</td>
                                        <td>password123</td>
                                        <td>Grade 10</td>
                                    </tr>
                                    <tr>
                                        <td>Jane Smith</td>
                                        <td>jane.smith@example.com</td>
                                        <td>password456</td>
                                        <td>Grade 11</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function downloadSampleFile() {
            // Create a sample CSV file and trigger download
            const csvContent = "data:text/csv;charset=utf-8,Fullname,Email,Password,Grade Level\nJohn Doe,john.doe@example.com,password123,Grade 10\nJane Smith,jane.smith@example.com,password456,Grade 11";
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "sample-students.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>