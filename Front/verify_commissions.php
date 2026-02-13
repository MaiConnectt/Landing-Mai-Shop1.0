<?php
require_once 'conexion.php';

function dump_columns($pdo, $name)
{
    echo "\nColumns for $name:\n";
    try {
        $stmt = $pdo->query("SELECT * FROM $name LIMIT 0");
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $col = $stmt->getColumnMeta($i);
            echo "- " . $col['name'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

dump_columns($pdo, 'vw_comisiones_pendientes_vendedor');
echo "\n" . str_repeat("-", 30) . "\n";
dump_columns($pdo, 'tbl_comprobante_pago');
echo "\n" . str_repeat("-", 30) . "\n";
dump_columns($pdo, 'tbl_miembro');
