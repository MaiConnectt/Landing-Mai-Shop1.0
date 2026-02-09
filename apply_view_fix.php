<?php
require_once 'Front/conexion.php';

try {
    echo "Actualizando vista vw_seller_commissions...\n";

    $sql = file_get_contents('Back/BD/scripts/migraciones/fix_vw_seller_commissions.sql');
    $pdo->exec($sql);

    echo "✅ Vista actualizada exitosamente!\n\n";

    // Verificar la vista
    echo "Verificando datos de la vista:\n";
    $stmt = $pdo->query("SELECT * FROM vw_seller_commissions LIMIT 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "Vendedor: {$result['seller_name']}\n";
        echo "Pedidos: {$result['total_orders']}\n";
        echo "Ventas totales: $" . number_format($result['total_sales'], 2) . "\n";
        echo "Comisiones ganadas: $" . number_format($result['commissions_earned'], 2) . "\n";
        echo "Total pagado: $" . number_format($result['total_paid'], 2) . "\n";
        echo "Pendiente: $" . number_format($result['balance_pending'], 2) . "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
