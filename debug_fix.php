<?php
require 'Front/conexion.php';

echo "=== COLUMNS IN TBL_USER ===\n";
try {
    $stm = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_user' ORDER BY ordinal_position");
    $cols = $stm->fetchAll(PDO::FETCH_COLUMN);
    echo implode(", ", $cols) . "\n\n";
} catch (Exception $e) {
    echo "Error querying schema: " . $e->getMessage() . "\n";
}

echo "=== EXECUTING FIX VALIDATION ===\n";
$sql = file_get_contents('Back/BD/scripts/fix_full_database.sql');
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec($sql);
    echo "SUCCESS: Fix script ran without error.\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>