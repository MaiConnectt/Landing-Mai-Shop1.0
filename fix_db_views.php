<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== RECREANDO VISTAS ===\n\n";

try {
    // 1. vw_order_totals
    echo "1. Creando vw_order_totals...\n";
    $pdo->exec("DROP VIEW IF EXISTS vw_order_totals CASCADE");
    $pdo->exec("
        CREATE VIEW vw_order_totals AS
        SELECT 
            o.id_order,
            o.id_client,
            COALESCE(o.seller_id, o.id_member) as seller_id,
            o.created_at,
            o.status,
            COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
        FROM tbl_order o
        LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
        GROUP BY o.id_order, o.id_client, o.seller_id, o.id_member, o.created_at, o.status
    ");
    echo "   ✓ vw_order_totals\n\n";

    // 2. vw_client_info
    echo "2. Creando vw_client_info...\n";
    $pdo->exec("
        CREATE OR REPLACE VIEW vw_client_info AS
        SELECT 
            c.id_client,
            c.phone,
            c.address,
            u.id_user,
            u.first_name,
            u.last_name,
            u.email,
            CONCAT(u.first_name, ' ', u.last_name) as full_name
        FROM tbl_client c
        INNER JOIN tbl_user u ON c.id_user = u.id_user
    ");
    echo "   ✓ vw_client_info\n\n";

    // 3. vw_member_info
    echo "3. Creando vw_member_info...\n";
    $pdo->exec("
        CREATE OR REPLACE VIEW vw_member_info AS
        SELECT 
            m.id_member,
            m.status,
            m.commission_percentage,
            u.id_user,
            u.first_name,
            u.last_name,
            u.email,
            CONCAT(u.first_name, ' ', u.last_name) as full_name
        FROM tbl_member m
        INNER JOIN tbl_user u ON m.id_user = u.id_user
    ");
    echo "   ✓ vw_member_info\n\n";

    // 4. vw_seller_commissions
    echo "4. Creando vw_seller_commissions...\n";
    $pdo->exec("
        CREATE OR REPLACE VIEW vw_seller_commissions AS
        SELECT 
            m.id_member,
            CONCAT(u.first_name, ' ', u.last_name) as seller_name,
            m.commission_percentage,
            COUNT(o.id_order) as total_orders,
            COALESCE(SUM(ot.total), 0) as total_sales,
            COALESCE(SUM(ot.total * m.commission_percentage / 100), 0) as total_commissions,
            COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) as commissions_earned,
            COALESCE(SUM(CASE WHEN cp.payment_status = 'paid' THEN cp.amount ELSE 0 END), 0) as total_paid,
            COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) - 
            COALESCE(SUM(CASE WHEN cp.payment_status = 'paid' THEN cp.amount ELSE 0 END), 0) as balance_pending
        FROM tbl_member m
        INNER JOIN tbl_user u ON m.id_user = u.id_user
        LEFT JOIN tbl_order o ON m.id_member = COALESCE(o.seller_id, o.id_member)
        LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
        LEFT JOIN tbl_commission_payment cp ON m.id_member = cp.id_member
        GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage
    ");
    echo "   ✓ vw_seller_commissions\n\n";

    echo "✅ TODAS LAS VISTAS RECREADAS!\n\n";

    // Verificar
    $views = $pdo->query("
        SELECT table_name 
        FROM information_schema.views 
        WHERE table_schema = 'public' 
        AND table_name LIKE 'vw_%'
        ORDER BY table_name
    ")->fetchAll(PDO::FETCH_COLUMN);

    echo "Vistas disponibles:\n";
    foreach ($views as $view) {
        echo "  ✓ $view\n";
    }

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
