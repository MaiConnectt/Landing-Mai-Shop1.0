<?php
require_once 'Front/conexion.php';

try {
    // Read SQL file
    $sql = file_get_contents('Back/BD/scripts/migraciones/update_commission_5.sql');

    if (!$sql) {
        throw new Exception("Could not read SQL file.");
    }

    // Execute SQL
    $pdo->exec($sql);

    echo "SUCCESS: Commission rate updated to 5.0% for all members.";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>