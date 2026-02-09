<?php
/**
 * Script para ejecutar los inserts de productos
 * Ejecuta: Back/BD/scripts/inserts/datos_productos.sql
 */

require_once __DIR__ . '/Front/conexion.php';

echo "=== EJECUTANDO INSERTS DE PRODUCTOS ===\n\n";

try {
    $sql_file = __DIR__ . '/Back/BD/scripts/inserts/datos_productos.sql';

    if (!file_exists($sql_file)) {
        die("❌ No se encontró el archivo: $sql_file\n");
    }

    echo "1. Leyendo archivo SQL...\n";
    $sql = file_get_contents($sql_file);
    echo "   ✓ Archivo leído\n\n";

    echo "2. Ejecutando inserts...\n";
    $pdo->exec($sql);
    echo "   ✓ Inserts ejecutados\n\n";

    // Verificar
    $count = $pdo->query("SELECT COUNT(*) FROM tbl_product")->fetchColumn();
    echo "✅ PRODUCTOS INSERTADOS!\n\n";
    echo "Total de productos: $count\n\n";

    // Mostrar productos
    $products = $pdo->query("
        SELECT id_product, product_name, price, stock 
        FROM tbl_product 
        ORDER BY id_product
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "Productos en catálogo:\n";
    foreach ($products as $p) {
        echo sprintf(
            "  [%d] %s - $%s (stock: %d)\n",
            $p['id_product'],
            $p['product_name'],
            number_format($p['price'], 0, ',', '.'),
            $p['stock']
        );
    }

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
