<?php
require 'Front/conexion.php';

function dumpTableTypes($pdo, $table)
{
    echo "TABLE: $table\n";
    $stm = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$table' ORDER BY ordinal_position");
    $cols = $stm->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "{$col['column_name']} ({$col['data_type']})\n";
    }
    echo "\n";
}

ob_start();
dumpTableTypes($pdo, 'tbl_payment_proof');
file_put_contents('schema_type_dump.txt', ob_get_clean());
?>