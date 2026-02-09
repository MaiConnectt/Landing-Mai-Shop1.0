<?php
require_once __DIR__ . '/Front/conexion.php';

try {
    $products = $pdo->query("
        SELECT id_product, product_name, price, stock 
        FROM tbl_product 
        ORDER BY id_product
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "=== PRODUCTOS EN tbl_product ===\n\n";

    if (empty($products)) {
        echo "❌ No hay productos\n";
    } else {
        foreach ($products as $p) {
            echo sprintf(
                "[%d] %s - $%s (stock: %d)\n",
                $p['id_product'],
                $p['product_name'],
                number_format($p['price'], 0, ',', '.'),
                $p['stock']
            );
        }
        echo "\nTotal: " . count($products) . " productos\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
