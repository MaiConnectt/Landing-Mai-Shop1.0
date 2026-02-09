<?php
require_once 'Front/conexion.php';

try {
    $sql = file_get_contents('Back/BD/scripts/migraciones/fix_commission_view.sql');
    $pdo->exec($sql);
    echo "Fix applied successfully.\n";
} catch (PDOException $e) {
    echo "Error applying fix: " . $e->getMessage() . "\n";
}
?>