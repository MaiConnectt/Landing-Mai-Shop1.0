<?php
session_start();
require_once 'Front/conexion.php';

echo "=== VERIFICACIÓN DE DATOS DEL VENDEDOR ===\n\n";

// Simular sesión del vendedor (usando el primer vendedor que encuentre)
$seller_query = $pdo->query("SELECT m.id_member, u.first_name, u.last_name, m.commission_percentage 
                             FROM tbl_member m 
                             JOIN tbl_user u ON m.id_user = u.id_user 
                             LIMIT 1");
$seller = $seller_query->fetch(PDO::FETCH_ASSOC);

if (!$seller) {
    echo "ERROR: No se encontró ningún vendedor en la base de datos.\n";
    exit;
}

echo "VENDEDOR: {$seller['first_name']} {$seller['last_name']}\n";
echo "ID: {$seller['id_member']}\n";
echo "Comisión: {$seller['commission_percentage']}%\n\n";

// Verificar pedidos
$orders_query = $pdo->prepare("
    SELECT o.id_order, o.status, o.commission_amount, ot.total
    FROM tbl_order o
    LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
    WHERE o.id_member = ?
    ORDER BY o.created_at DESC
");
$orders_query->execute([$seller['id_member']]);
$orders = $orders_query->fetchAll(PDO::FETCH_ASSOC);

echo "PEDIDOS ENCONTRADOS: " . count($orders) . "\n\n";

if (count($orders) > 0) {
    echo "Detalle de pedidos:\n";
    foreach ($orders as $order) {
        echo sprintf(
            "  Pedido #%d - Status: %d - Total: $%s - Comisión: $%s\n",
            $order['id_order'],
            $order['status'],
            number_format($order['total'] ?? 0, 2),
            number_format($order['commission_amount'] ?? 0, 2)
        );
    }
    echo "\n";
}

// Ejecutar la misma consulta que seller_dash.php
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM tbl_order WHERE id_member = ? AND status != 3) as total_orders,
        COALESCE((SELECT SUM(total) FROM vw_order_totals WHERE id_member = ? AND status = 2), 0) as total_sales,
        COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2), 0) as commissions_earned,
        COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2 AND commission_payout_id IS NOT NULL), 0) as total_paid,
        COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2 AND commission_payout_id IS NULL), 0) as balance_pending
";

$stmt = $pdo->prepare($stats_query);
$stmt->execute([$seller['id_member'], $seller['id_member'], $seller['id_member'], $seller['id_member'], $seller['id_member']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "ESTADÍSTICAS CALCULADAS:\n";
echo "  Total de pedidos: {$stats['total_orders']}\n";
echo "  Ventas totales: $" . number_format($stats['total_sales'], 2) . "\n";
echo "  Comisiones ganadas: $" . number_format($stats['commissions_earned'], 2) . "\n";
echo "  Total pagado: $" . number_format($stats['total_paid'], 2) . "\n";
echo "  Pendiente por cobrar: $" . number_format($stats['balance_pending'], 2) . "\n";
