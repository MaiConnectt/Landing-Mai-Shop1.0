<?php
require 'conexion.php';
try {
    $file = $argv[1] ?? '../Back/migrations/20260213_order_history.sql';
    if (!file_exists($file)) {
        die("Error: El archivo $file no existe\n");
    }
    $sql = file_get_contents($file);
    $pdo->exec($sql);
    echo "Migración '$file' completada exitosamente\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
