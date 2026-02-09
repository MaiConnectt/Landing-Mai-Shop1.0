<?php
require_once 'Front/conexion.php';

echo "=== DIAGNÓSTICO COMPLETO ===\n\n";

// 1. Verificar que las columnas existen
echo "1. VERIFICANDO COLUMNAS:\n";
$cols = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_order' AND column_name IN ('commission_amount', 'commission_payout_id')")->fetchAll(PDO::FETCH_COLUMN);
echo "   Columnas encontradas: " . implode(", ", $cols) . "\n\n";

// 2. Contar pedidos totales
echo "2. PEDIDOS EN LA BASE DE DATOS:\n";
$total = $pdo->query("SELECT COUNT(*) FROM tbl_order")->fetchColumn();
echo "   Total de pedidos: $total\n";

if ($total > 0) {
    // 3. Ver pedidos con sus datos
    echo "\n3. DATOS DE PEDIDOS:\n";
    $orders = $pdo->query("SELECT id_order, id_member, seller_id, status, commission_amount FROM tbl_order LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($orders as $order) {
        echo sprintf(
            "   Pedido #%d: id_member=%s, seller_id=%s, status=%d, commission=$%s\n",
            $order['id_order'],
            $order['id_member'] ?? 'NULL',
            $order['seller_id'] ?? 'NULL',
            $order['status'],
            $order['commission_amount'] ?? '0'
        );
    }

    // 4. Ver vendedores
    echo "\n4. VENDEDORES:\n";
    $sellers = $pdo->query("SELECT id_member FROM tbl_member")->fetchAll(PDO::FETCH_COLUMN);
    echo "   IDs de vendedores: " . implode(", ", $sellers) . "\n";

    // 5. Estadísticas simples
    echo "\n5. ESTADÍSTICAS SIMPLES:\n";
    $seller_id = $sellers[0] ?? 1;

    $count = $pdo->prepare("SELECT COUNT(*) FROM tbl_order WHERE id_member = ?");
    $count->execute([$seller_id]);
    echo "   Pedidos del vendedor $seller_id: " . $count->fetchColumn() . "\n";

    $sum = $pdo->prepare("SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ?");
    $sum->execute([$seller_id]);
    echo "   Total comisiones: $" . number_format($sum->fetchColumn() ?? 0, 2) . "\n";

} else {
    echo "   No hay pedidos en la base de datos.\n";
}
