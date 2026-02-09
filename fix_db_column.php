<?php
require_once 'Front/conexion.php';

try {
    echo "Attempting to add payment_proof column...\n";
    $result = $pdo->exec("ALTER TABLE tbl_order ADD COLUMN IF NOT EXISTS payment_proof VARCHAR(255)");

    if ($result !== false) {
        echo "Successfully added 'payment_proof' column to 'tbl_order'.\n";
    } else {
        echo "Failed to execute ALTER TABLE (Result was false). Checking error...\n";
        print_r($pdo->errorInfo());
    }

    // Verify
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_order' AND column_name = 'payment_proof'");
    if ($stmt->fetch()) {
        echo "VERIFIED: Column 'payment_proof' exists in 'tbl_order'.\n";
    } else {
        echo "ERROR: Column 'payment_proof' is STILL MISSING after execution.\n";
    }

} catch (PDOException $e) {
    echo "PDO Error: " . $e->getMessage() . "\n";
}
?>