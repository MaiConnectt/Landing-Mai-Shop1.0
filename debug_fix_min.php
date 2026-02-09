<?php
require 'Front/conexion.php';
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = file_get_contents('Back/BD/scripts/fix_full_database.sql');
    $pdo->exec($sql);
    echo "SUCCESS_FIX";
} catch (PDOException $e) {
    echo "SQL_FAIL: " . $e->getMessage();
}
?>