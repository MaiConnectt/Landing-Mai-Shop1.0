<?php
require 'Front/conexion.php';

function dumpTable($pdo, $table)
{
    echo "TABLE: $table\n";
    $stm = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = '$table' ORDER BY ordinal_position");
    $cols = $stm->fetchAll(PDO::FETCH_COLUMN);
    echo implode(", ", $cols) . "\n\n";
}

ob_start();
dumpTable($pdo, 'tbl_user');
dumpTable($pdo, 'tbl_order');
dumpTable($pdo, 'tbl_payment_proof');
file_put_contents('schema_dump.txt', ob_get_clean());
?>