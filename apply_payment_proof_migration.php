<?php
require_once 'Front/dashboard/auth.php'; // Reuse auth/connection logic if possible, or just raw connection
require_once 'conexion.php';

try {
    $sql = file_get_contents('Back/BD/scripts/migraciones/add_payment_proof_to_order.sql');
    $pdo->exec($sql);
    echo "Migration successful: Added payment_proof column to tbl_order.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>