<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();

// Only allow registrar and admin roles
if (!in_array($user['role'], ['registrar', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

// Get student ID from query parameter
$studentId = $_GET['id'] ?? '';

if (empty($studentId)) {
    die('Student ID is required');
}

// Fetch student details
$stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    die('Student not found');
}

// Parse name (stored as "Last, First" or "Last, First Middle")
$rawName = trim((string) ($student['name'] ?? ''));
$middleName = trim((string) ($student['middle_name'] ?? ''));
$lastName = '';
$firstName = '';
if (str_contains($rawName, ',')) {
    [$lastName, $rest] = array_map('trim', explode(',', $rawName, 2));
    $firstName = $rest;
} else {
    $nameParts = preg_split('/\s+/', $rawName) ?: [];
    $firstName = $nameParts[0] ?? '';
    $lastName = count($nameParts) > 1 ? (string) end($nameParts) : '';
}

// Format date
$enrollmentDate = date('F d, Y', strtotime($student['created_at']));

$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$schoolYear = $currentMonth >= 6
    ? $currentYear . '-' . ($currentYear + 1)
    : ($currentYear - 1) . '-' . $currentYear;

$schoolName = 'THE LIGHT CHRISTIAN ACADEMY';
$schoolSub = 'Department of Education · Private Educational Institution';
$logoFile = __DIR__ . '/image/new.png';
$logoSrc = file_exists($logoFile) ? 'image/new.png' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DepEd Enrollment Form - <?= htmlspecialchars($student['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .print-container {
            max-width: 8.5in;
            margin: 0 auto;
            background: white;
            padding: 0.5in;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        
        .school-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .school-header img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .school-header .mid { text-align: center; line-height: 1.25; }
        .school-header .school-name {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .school-header .school-sub { font-size: 9pt; color: #333; }
        .form-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 8px 0 4px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-subtitle {
            font-size: 8pt;
            text-align: center;
            color: #333;
        }
        
        .section-title {
            background: #333;
            color: white;
            padding: 3px 8px;
            font-size: 9pt;
            font-weight: bold;
            margin: 15px 0 8px 0;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 5px;
            align-items: flex-end;
        }
        
        .form-field {
            flex: 1;
            margin-right: 10px;
        }
        
        .form-field:last-child {
            margin-right: 0;
        }
        
        .field-label {
            font-size: 7pt;
            color: #333;
            display: block;
            margin-bottom: 2px;
        }
        
        .field-value {
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 2px 4px;
            font-size: 9pt;
        }
        
        .checkbox-group {
            display: flex;
            gap: 15px;
            font-size: 8pt;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 3px;
        }
        
        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            display: inline-block;
        }
        
        .checkbox.checked {
            background: #000;
        }
        
        .lrn-boxes {
            display: flex;
            gap: 2px;
        }
        
        .lrn-box {
            width: 20px;
            height: 22px;
            border: 1px solid #000;
            text-align: center;
            line-height: 20px;
            font-size: 10pt;
            font-weight: bold;
        }
        
        .two-column {
            display: flex;
            gap: 20px;
        }
        
        .column {
            flex: 1;
        }
        
        .print-btn {
            background: #40A2E3;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 12pt;
            cursor: pointer;
            border-radius: 5px;
            margin: 20px auto;
            display: block;
        }
        
        .print-btn:hover {
            background: #2E8BC0;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 11pt;
            cursor: pointer;
            border-radius: 5px;
            margin: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .button-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8pt;
        }
        
        .small-text {
            font-size: 7pt;
        }
    </style>
</head>
<body>
    <div class="button-container no-print">
        <a href="student-list.php" class="back-btn"><i class="bi bi-arrow-left"></i> Back to Student List</a>
        <button class="print-btn" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Form
        </button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <header class="school-header">
            <?php if ($logoSrc): ?>
            <img src="<?= htmlspecialchars($logoSrc) ?>" alt="School Logo">
            <?php endif; ?>
            <div class="mid">
                <div class="school-name"><?= htmlspecialchars($schoolName) ?></div>
                <div class="school-sub"><?= htmlspecialchars($schoolSub) ?></div>
            </div>
            <?php if ($logoSrc): ?>
            <img src="<?= htmlspecialchars($logoSrc) ?>" alt="" aria-hidden="true" style="visibility:hidden;width:72px;height:72px;">
            <?php endif; ?>
        </header>
        <div class="form-title">Basic Education Enrollment Form</div>
        <div class="form-subtitle">This form is NOT for sale. Any erasure or alteration made on this form invalidates this document.</div>

        <!-- School Year and Date -->
        <div class="form-row" style="margin-top: 12px;">
            <div class="form-field" style="flex: 0.5;">
                <span class="field-label">School Year:</span>
                <span class="field-value"><?= htmlspecialchars($schoolYear) ?></span>
            </div>
            <div class="form-field" style="flex: 0.5;">
                <span class="field-label">Date of Enrollment:</span>
                <span class="field-value"><?= $enrollmentDate ?></span>
            </div>
            <div class="form-field" style="flex: 0.3;">
                <span class="field-label">Student ID:</span>
                <span class="field-value"><?= htmlspecialchars($student['empidno']) ?></span>
            </div>
        </div>

        <!-- LRN Section -->
        <div class="section-title">LEARNER INFORMATION</div>
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">1. LRN (Learner Reference Number):</span>
                <div class="lrn-boxes">
                    <?php 
                    $lrn = str_pad($student['lrn_number'] ?? '', 12, ' ', STR_PAD_LEFT);
                    for ($i = 0; $i < 12; $i++): 
                        $char = $lrn[$i] ?? '';
                        $char = $char === ' ' ? '&nbsp;' : htmlspecialchars($char);
                    ?>
                    <div class="lrn-box"><?= $char ?></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Name Fields -->
        <div class="form-row">
            <div class="form-field" style="flex: 1.5;">
                <span class="field-label">Last Name:</span>
                <div class="field-value"><?= htmlspecialchars($lastName) ?></div>
            </div>
            <div class="form-field" style="flex: 1.5;">
                <span class="field-label">First Name:</span>
                <div class="field-value"><?= htmlspecialchars($firstName) ?></div>
            </div>
            <div class="form-field" style="flex: 1;">
                <span class="field-label">Middle Name:</span>
                <div class="field-value"><?= htmlspecialchars($middleName) ?></div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="form-row">
            <div class="form-field" style="flex: 0.8;">
                <span class="field-label">Date of Birth (MM/DD/YYYY):</span>
                <div class="field-value">
                    <?= $student['date_of_birth'] ? date('m/d/Y', strtotime($student['date_of_birth'])) : '' ?>
                </div>
            </div>
            <div class="form-field" style="flex: 0.4;">
                <span class="field-label">Age:</span>
                <div class="field-value"><?= $student['age'] ?? '' ?></div>
            </div>
            <div class="form-field" style="flex: 0.6;">
                <span class="field-label">Sex:</span>
                <div class="field-value"><?= $student['gender'] ?? '' ?></div>
            </div>
            <div class="form-field" style="flex: 1;">
                <span class="field-label">Place of Birth:</span>
                <div class="field-value"><?= htmlspecialchars($student['place_of_birth'] ?? '') ?></div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="form-row">
            <div class="form-field" style="flex: 1;">
                <span class="field-label">Mother Tongue:</span>
                <div class="field-value"><?= htmlspecialchars($student['mother_tongue'] ?? '') ?></div>
            </div>
            <div class="form-field" style="flex: 1;">
                <span class="field-label">Religion:</span>
                <div class="field-value"><?= htmlspecialchars($student['religion'] ?? '') ?></div>
            </div>
            <div class="form-field" style="flex: 1;">
                <span class="field-label">Nationality:</span>
                <div class="field-value"><?= htmlspecialchars($student['nationality'] ?? '') ?></div>
            </div>
        </div>

        <!-- Address -->
        <div class="form-row">
            <div class="form-field" style="flex: 3;">
                <span class="field-label">Home Address:</span>
                <div class="field-value"><?= htmlspecialchars($student['home_address'] ?? '') ?></div>
            </div>
        </div>

        <!-- Contact -->
        <div class="form-row">
            <div class="form-field" style="flex: 1;">
                <span class="field-label">Contact Number:</span>
                <div class="field-value"><?= htmlspecialchars($student['contact_number'] ?? '') ?></div>
            </div>
            <div class="form-field" style="flex: 2;">
                <span class="field-label">Email Address:</span>
                <div class="field-value"><?= htmlspecialchars($student['email'] ?? '') ?></div>
            </div>
        </div>

        <!-- Returnee/Transfer -->
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">Returning Learner (Balik-Aral):</span>
                <div class="checkbox-group">
                    <span class="checkbox-item">
                        <span class="checkbox <?= $student['is_returnee'] ? 'checked' : '' ?>"></span> Yes
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox <?= !$student['is_returnee'] ? 'checked' : '' ?>"></span> No
                    </span>
                </div>
            </div>
            <div class="form-field">
                <span class="field-label">Transfer In:</span>
                <div class="checkbox-group">
                    <span class="checkbox-item">
                        <span class="checkbox <?= $student['is_transfer_in'] ? 'checked' : '' ?>"></span> Yes
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox <?= !$student['is_transfer_in'] ? 'checked' : '' ?>"></span> No
                    </span>
                </div>
            </div>
        </div>

        <!-- Grade Level -->
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">Grade Level to Enroll:</span>
                <div class="field-value">Grade <?= htmlspecialchars($student['grade_level'] ?? '') ?></div>
            </div>
        </div>

        <!-- Special Needs -->
        <div class="section-title">SPECIAL NEEDS INFORMATION</div>
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">Is the child a Learner with Disability?</span>
                <div class="checkbox-group">
                    <span class="checkbox-item">
                        <span class="checkbox <?= $student['has_special_needs'] ? 'checked' : '' ?>"></span> Yes
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox <?= !$student['has_special_needs'] ? 'checked' : '' ?>"></span> No
                    </span>
                </div>
            </div>
        </div>
        <?php if ($student['has_special_needs']): ?>
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">Type of Disability:</span>
                <div class="field-value"><?= htmlspecialchars($student['special_needs_type'] ?? '') ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 4Ps and Indigenous -->
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">4Ps Beneficiary:</span>
                <div class="checkbox-group">
                    <span class="checkbox-item">
                        <span class="checkbox <?= $student['is_4ps_beneficiary'] ? 'checked' : '' ?>"></span> Yes
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox <?= !$student['is_4ps_beneficiary'] ? 'checked' : '' ?>"></span> No
                    </span>
                </div>
            </div>
            <div class="form-field">
                <span class="field-label">Indigenous People:</span>
                <div class="checkbox-group">
                    <span class="checkbox-item">
                        <span class="checkbox <?= $student['is_indigenous'] ? 'checked' : '' ?>"></span> Yes
                    </span>
                    <span class="checkbox-item">
                        <span class="checkbox <?= !$student['is_indigenous'] ? 'checked' : '' ?>"></span> No
                    </span>
                </div>
            </div>
        </div>
        <?php if ($student['is_indigenous']): ?>
        <div class="form-row">
            <div class="form-field">
                <span class="field-label">Indigenous Group:</span>
                <div class="field-value"><?= htmlspecialchars($student['indigenous_group'] ?? '') ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Parent Information -->
        <div class="section-title">PARENT/GUARDIAN INFORMATION</div>
        <table>
            <tr>
                <td style="width: 30%;"><strong>Father's Name:</strong><br><?= htmlspecialchars($student['father_name'] ?? '') ?></td>
                <td style="width: 35%;"><strong>Occupation:</strong><br><?= htmlspecialchars($student['father_occupation'] ?? '') ?></td>
                <td style="width: 35%;"><strong>Contact Number:</strong><br><?= htmlspecialchars($student['father_contact'] ?? '') ?></td>
            </tr>
            <tr>
                <td><strong>Mother's Name:</strong><br><?= htmlspecialchars($student['mother_name'] ?? '') ?></td>
                <td><strong>Occupation:</strong><br><?= htmlspecialchars($student['mother_occupation'] ?? '') ?></td>
                <td><strong>Contact Number:</strong><br><?= htmlspecialchars($student['mother_contact'] ?? '') ?></td>
            </tr>
            <tr>
                <td><strong>Guardian's Name:</strong><br><?= htmlspecialchars($student['guardian_name'] ?? '') ?></td>
                <td><strong>Relationship:</strong><br><?= htmlspecialchars($student['guardian_relationship'] ?? '') ?></td>
                <td><strong>Contact Number:</strong><br><?= htmlspecialchars($student['guardian_contact'] ?? '') ?></td>
            </tr>
        </table>

        <!-- Previous School -->
        <div class="section-title">PREVIOUS SCHOOL INFORMATION</div>
        <div class="form-row">
            <div class="form-field" style="flex: 2;">
                <span class="field-label">Last School Attended:</span>
                <div class="field-value"><?= htmlspecialchars($student['last_school_attended'] ?? '') ?></div>
            </div>
            <div class="form-field" style="flex: 1;">
                <span class="field-label">School Year Completed:</span>
                <div class="field-value"><?= htmlspecialchars($student['school_year_completed'] ?? '') ?></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-field" style="flex: 3;">
                <span class="field-label">School Address:</span>
                <div class="field-value"><?= htmlspecialchars($student['last_school_address'] ?? '') ?></div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-field" style="flex: 1;">
                <span class="field-label">General Average:</span>
                <div class="field-value"><?= htmlspecialchars($student['general_average'] ?? '') ?></div>
            </div>
        </div>

        <!-- Certification -->
        <div style="margin-top: 30px; border-top: 1px solid #000; padding-top: 15px;">
            <p class="small-text" style="text-align: justify;">
                <strong>CERTIFICATION:</strong> I hereby certify that the information provided in this form is true and correct to the best of my knowledge. 
                I understand that any false statement may result in the cancellation of my child's enrollment.
            </p>
            <div class="form-row" style="margin-top: 20px;">
                <div class="form-field" style="flex: 1;">
                    <div style="border-top: 1px solid #000; margin-top: 30px; text-align: center;">
                        <span class="field-label">Signature of Parent/Guardian</span>
                    </div>
                </div>
                <div class="form-field" style="flex: 1;">
                    <div style="border-top: 1px solid #000; margin-top: 30px; text-align: center;">
                        <span class="field-label">Date</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Registrar Section -->
        <div style="margin-top: 30px; border-top: 1px solid #000; padding-top: 15px;">
            <p class="small-text"><strong>FOR REGISTRAR'S USE ONLY:</strong></p>
            <div class="form-row">
                <div class="form-field" style="flex: 1;">
                    <span class="field-label">Date Received:</span>
                    <div class="field-value"><?= $enrollmentDate ?></div>
                </div>
                <div class="form-field" style="flex: 1;">
                    <span class="field-label">Verified by:</span>
                    <div class="field-value"><?= htmlspecialchars($user['name'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
