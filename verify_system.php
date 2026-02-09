<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== SETUP FINAL ===\n\n";

try {
    // 1. Verificar qué tablas de productos existen
    echo "1. Verificando tablas...\n";
    $tables = ['tbl_category', 'tbl_product', 'tbl_product_image', 'tbl_product_variant'];
    $existing = [];

    foreach ($tables as $table) {
        $exists = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')")->fetchColumn();
        if ($exists) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "   ✓ $table ($count registros)\n";
            $existing[] = $table;
        } else {
            echo "   ✗ $table (no existe)\n";
        }
    }
    echo "\n";

    // 2. Verificar vendedor
    echo "2. Verificando vendedor...\n";
    $seller = $pdo->query("
        SELECT u.email, m.status 
        FROM tbl_member m
        INNER JOIN tbl_user u ON m.id_user = u.id_user
        WHERE u.role_id = 2
        LIMIT 1
    ")->fetch();

    if ($seller) {
        echo "   ✓ Vendedor: {$seller['email']} (status: {$seller['status']})\n\n";
    } else {
        echo "   ✗ No hay vendedor configurado\n\n";
    }

    // 3. Verificar vista vw_order_totals
    echo "3. Verificando vista vw_order_totals...\n";
    $view_exists = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.views WHERE table_name = 'vw_order_totals')")->fetchColumn();
    if ($view_exists) {
        echo "   ✓ Vista existe\n\n";
    } else {
        echo "   ✗ Vista no existe - ejecutando fix...\n";
        $pdo->exec("
            CREATE VIEW vw_order_totals AS
            SELECT 
                o.id_order,
                COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
            FROM tbl_order o
            LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
            GROUP BY o.id_order
        ");
        echo "   ✓ Vista creada\n\n";
    }

    echo "=== RESUMEN ===\n\n";
    echo "✓ Base de datos: OK\n";
    echo "✓ Vendedor: " . ($seller ? "OK" : "FALTA") . "\n";
    echo "✓ Vista vw_order_totals: OK\n";
    echo "✓ Productos: " . (in_array('tbl_product', $existing) ? "OK" : "FALTAN") . "\n\n";

    if ($seller && $view_exists) {
        echo "✅ SISTEMA LISTO!\n\n";
        echo "Prueba el login:\n";
        echo "- Vendedor: usuario@maishop.com / User@2026!\n";
        echo "- Admin: admin@maishop.com / Admin@2026!\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
