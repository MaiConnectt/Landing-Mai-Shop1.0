<?php
// Script de auto-reparación
require 'Front/conexion.php';

echo "Conectando a la base de datos...\n";

$sqlFile = 'Back/BD/scripts/fix_full_database.sql';

if (!file_exists($sqlFile)) {
    die("Error: No encuentro el archivo SQL en $sqlFile");
}

$sql = file_get_contents($sqlFile);

try {
    // Ejecutar el script SQL completo
    $pdo->exec($sql);
    echo "\n---------------------------------------------------\n";
    echo "¡LISTO! La base de datos ha sido reparada. ✅\n";
    echo "Se aseguraron las tablas, columnas y vistas.\n";
    echo "---------------------------------------------------\n";
} catch (PDOException $e) {
    echo "Error al ejecutar SQL: " . $e->getMessage();
}
?>