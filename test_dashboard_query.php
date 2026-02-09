<?php
require_once 'Front/conexion.php';

// Simular la sesión del vendedor
$seller_id = 1; // ID del vendedor de prueba

echo "=== PRUEBA DE CONSULTA DEL DASHBOARD ===\n\n";
echo "Vendedor ID: $seller_id\n\n";

$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM tbl_order WHERE id_member = ? AND status != 3) as total_orders,
        COALESCE((SELECT SUM(ot.total) FROM tbl_order o JOIN vw_order_totals ot ON o.id_order = ot.id_order WHERE o.id_member = ? AND o.status = 2), 0) as total_sales,
        COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2), 0) as commissions_earned,
        COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2 AND commission_payout_id IS NOT NULL), 0) as total_paid,
        COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2 AND commission_payout_id IS NULL), 0) as balance_pending
";

$stmt = $pdo->prepare($stats_query);
$stmt->execute([$seller_id, $seller_id, $seller_id, $seller_id, $seller_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "RESULTADOS:\n";
echo "  Total de pedidos: {$stats['total_orders']}\n";
echo "  Ventas totales: $" . number_format($stats['total_sales'], 2) . "\n";
echo "  Comisiones ganadas: $" . number_format($stats['commissions_earned'], 2) . "\n";
echo "  Total pagado: $" . number_format($stats['total_paid'], 2) . "\n";
echo "  Pendiente por cobrar: $" . number_format($stats['balance_pending'], 2) . "\n";

echo "\n✅ La consulta funciona correctamente!\n";
