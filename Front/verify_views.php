<?php
require_once 'conexion.php';
$views = ['vw_totales_pedido', 'vw_comisiones_vendedor', 'vw_comisiones_pendientes_vendedor'];
foreach ($views as $v) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $v LIMIT 1");
        echo "View $v: EXISTS\n";
    } catch (Exception $e) {
        echo "View $v: MISSING or ERROR: " . $e->getMessage() . "\n";
    }
}
