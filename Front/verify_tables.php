<?php
require_once 'conexion.php';
$tables = ['tbl_pedido', 'tbl_order', 'tbl_detalle_pedido', 'tbl_order_detail', 'tbl_comprobante_pago', 'tbl_payment_proof'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $t LIMIT 1");
        echo "Table $t: EXISTS\n";
    } catch (Exception $e) {
        echo "Table $t: MISSING\n";
    }
}
