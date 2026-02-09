<?php
require_once 'Front/conexion.php';

try {
    $sql = file_get_contents('Back/BD/scripts/migraciones/add_commission_payout_id.sql');
    $pdo->exec($sql);
    echo "Migration applied successfully.\n";
} catch (PDOException $e) {
    echo "Error applying migration: " . $e->getMessage() . "\n";
}
?>