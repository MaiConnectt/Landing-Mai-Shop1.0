<?php
require_once 'conexion.php';
$tables = ['tbl_pedido', 'tbl_detalle_pedido', 'tbl_comprobante_pago', 'tbl_usuario', 'tbl_miembro', 'tbl_producto', 'tbl_rol', 'tbl_historial_pedido', 'tbl_cliente'];
$schema = [];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position");
        $stmt->execute([$t]);
        $schema[$t] = $stmt->fetchAll();
    } catch (Exception $e) {
        $schema[$t] = "ERROR: " . $e->getMessage();
    }
}
header('Content-Type: application/json');
echo json_encode($schema, JSON_PRETTY_PRINT);
