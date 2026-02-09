<?php
require_once 'conexion.php';

try {
    $sql = "
    CREATE OR REPLACE VIEW vw_seller_commissions AS
    SELECT 
        m.id_member,
        u.first_name,
        u.last_name,
        u.email,
        m.university,
        m.phone,
        m.commission_percentage,
        m.status,
        m.hire_date,
        COUNT(o.id_order) as total_orders,
        COALESCE(SUM(ot.total), 0) as total_sales,
        COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) as total_commissions_earned,
        0 as total_paid,
        COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) as balance_pending
    FROM tbl_member m
    JOIN tbl_user u ON m.id_user = u.id_user
    LEFT JOIN tbl_order o ON m.id_member = o.id_member
    LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
    GROUP BY 
        m.id_member, 
        u.first_name, 
        u.last_name, 
        u.email, 
        m.university, 
        m.phone, 
        m.commission_percentage, 
        m.status, 
        m.hire_date;
    ";

    $pdo->exec($sql);
    echo "Vista vw_seller_commissions creada exitosamente.";
} catch (PDOException $e) {
    echo "Error al crear la vista: " . $e->getMessage();
}
?>