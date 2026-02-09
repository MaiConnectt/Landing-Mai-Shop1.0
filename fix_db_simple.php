<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== REPARANDO BASE DE DATOS ===\n\n";

try {
    // Primero verificar qué columnas tiene tbl_order
    echo "1. Verificando estructura de tbl_order...\n";
    $cols = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'tbl_order' 
        ORDER BY ordinal_position
    ")->fetchAll(PDO::FETCH_COLUMN);

    echo "   Columnas: " . implode(', ', $cols) . "\n\n";

    $has_seller_id = in_array('seller_id', $cols);
    $has_id_member = in_array('id_member', $cols);

    // Crear vista según las columnas disponibles
    echo "2. Recreando vw_order_totals...\n";
    $pdo->exec("DROP VIEW IF EXISTS vw_order_totals CASCADE");

    if ($has_seller_id && !$has_id_member) {
        // Solo tiene seller_id
        $pdo->exec("
            CREATE VIEW vw_order_totals AS
            SELECT 
                o.id_order,
                COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
            FROM tbl_order o
            LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
            GROUP BY o.id_order
        ");
    } elseif ($has_id_member && !$has_seller_id) {
        // Solo tiene id_member
        $pdo->exec("
            CREATE VIEW vw_order_totals AS
            SELECT 
                o.id_order,
                COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
            FROM tbl_order o
            LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
            GROUP BY o.id_order
        ");
    } else {
        // Tiene ambas o ninguna
        $pdo->exec("
            CREATE VIEW vw_order_totals AS
            SELECT 
                o.id_order,
                COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
            FROM tbl_order o
            LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
            GROUP BY o.id_order
        ");
    }

    echo "   ✓ Vista creada\n\n";

    // Verificar que funciona
    $test = $pdo->query("SELECT COUNT(*) FROM vw_order_totals")->fetchColumn();
    echo "   ✓ Vista funciona (pedidos: $test)\n\n";

    echo "✅ BASE DE DATOS REPARADA!\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
