<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Helpers/env.php';
require_once __DIR__ . '/../src/Helpers/database.php';
require_once __DIR__ . '/../src/Helpers/session.php';
require_once __DIR__ . '/../src/Helpers/auth.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../src/Controllers/FinanceController.php';

loadEnv(__DIR__ . '/../.env');
startSecureSession();

$user = AuthMiddleware::requireAuth();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid payment ID.");
}

$paymentId = (int)$_GET['id'];
$financeController = new FinanceController();

$payment = $financeController->getPayment($paymentId);

if (!$payment) {
    die("Payment not found.");
}

// Ensure the logged in user is either admin/cashier or the student who made the payment
if (!in_array($user['role'], ['admin', 'cashier']) && $user['id'] !== $payment['student_id']) {
    die("Unauthorized access.");
}

$db = db();
$settings = [];
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

$schoolName = $settings['school_name'] ?? ($_SESSION['active_school_name'] ?? "Treasure Link Cooperative Academy");
$schoolAddress = $settings['school_contact_address'] ?? "Baguio City, Philippines";
$phone = $settings['school_contact_phone'] ?? "(074) 442-1234";
$email = $settings['school_contact_email'] ?? "info@tlca.edu.ph";
$schoolContact = "Contact: " . $phone . " / " . $email;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Invoice - <?= htmlspecialchars($payment['reference_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Courier New', Courier, monospace; /* More like a receipt */
        }
        .receipt-container {
            max-width: 400px; /* Small width for receipt printer */
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .school-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .school-header h4 {
            margin: 0;
            font-weight: bold;
            font-size: 1.2rem;
            line-height: 1.2;
        }
        .school-header p {
            margin: 0;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        .receipt-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            font-size: 1.1rem;
            text-transform: uppercase;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        .item-table {
            width: 100%;
            margin-top: 15px;
            margin-bottom: 15px;
            border-top: 2px dashed #000;
            border-bottom: 2px dashed #000;
        }
        .item-table th, .item-table td {
            padding: 8px 0;
            font-size: 0.9rem;
        }
        .item-table th {
            text-align: left;
        }
        .item-table .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.85rem;
            border-top: 2px dashed #000;
            padding-top: 10px;
        }
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 10px;
                width: 100%;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container text-center mt-3 no-print">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            Close
        </button>
    </div>

    <div class="receipt-container">
        <div class="school-header">
            <h4><?= htmlspecialchars($schoolName) ?></h4>
            <p><?= htmlspecialchars($schoolAddress) ?></p>
            <p><?= htmlspecialchars($schoolContact) ?></p>
        </div>

        <div class="receipt-title">SALES INVOICE</div>

        <div class="info-row">
            <span><strong>Sales Invoice:</strong></span>
            <span><?= htmlspecialchars($payment['reference_number']) ?></span>
        </div>
        <div class="info-row">
            <span><strong>Date:</strong></span>
            <span><?= date('M d, Y h:i A', strtotime($payment['received_at'])) ?></span>
        </div>
        
        <div class="mt-3">
            <div class="info-row">
                <span><strong>Student:</strong></span>
                <span><?= htmlspecialchars($payment['student_name']) ?></span>
            </div>
            <div class="info-row">
                <span><strong>ID No:</strong></span>
                <span><?= htmlspecialchars($payment['empidno']) ?></span>
            </div>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($payment['fee_name']) ?></td>
                    <td class="text-right">₱<?= number_format((float) $payment['amount'], 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>TOTAL</td>
                    <td class="text-right">₱<?= number_format((float) $payment['amount'], 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="info-row">
            <span><strong>Payment Method:</strong></span>
            <span><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></span>
        </div>
        <?php if (!empty($payment['notes'])): ?>
        <div class="info-row mt-2" style="display: block;">
            <strong>Notes:</strong><br>
            <span style="font-size: 0.85rem;"><?= nl2br(htmlspecialchars($payment['notes'])) ?></span>
        </div>
        <?php endif; ?>

        <div class="mt-4 info-row">
            <span><strong>Cashier:</strong></span>
            <span><?= htmlspecialchars($payment['received_by_name']) ?></span>
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p>This document is an official receipt.</p>
        </div>
    </div>
</body>
</html>
