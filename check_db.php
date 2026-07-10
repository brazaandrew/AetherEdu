<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=elms_school_tlca', 'root', '');
    $fees = $pdo->query("SELECT sf.*, u.name as student_name FROM student_fees sf LEFT JOIN users u ON u.id = sf.student_id")->fetchAll(PDO::FETCH_ASSOC);
    print_r($fees);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
