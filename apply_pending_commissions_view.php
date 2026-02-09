<?php
require_once __DIR__ . '/Front/conexion.php';

echo "Aplicando creación de vista vw_seller_pending_commissions...\n";

try {
    $sql = file_get_contents(__DIR__ . '/Back/BD/scripts/migraciones/create_vw_seller_pending_commissions.sql');
    $pdo->exec($sql);
    echo "✓ Vista vw_seller_pending_commissions creada exitosamente\n";

    // Verificar data
    $stmt = $pdo->query("SELECT * FROM vw_seller_pending_commissions");
    $sellers = $stmt->fetchAll();

    echo "\n--- Vendedores con comisiones pendientes ---\n";
    if (empty($sellers)) {
        echo "No hay vendedores con comisiones pendientes\n";
    } else {
        foreach ($sellers as $seller) {
            echo "- {$seller['first_name']} {$seller['last_name']}: ";
            echo "{$seller['pending_order_count']} pedidos, ";
            echo "$" . number_format($seller['pending_amount'], 0, ',', '.') . " pendiente\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
