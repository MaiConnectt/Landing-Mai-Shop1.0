<?php
require 'conexion.php';
$tables = ['tbl_pedido', 'tbl_comprobante_pago', 'tbl_detalle_pedido', 'tbl_usuario', 'tbl_miembro'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    try {
        $stmt = $pdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? AND table_schema = 'public'");
        $stmt->execute([$table]);
        while ($row = $stmt->fetch()) {
            echo "{$row['column_name']} ({$row['data_type']})\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
