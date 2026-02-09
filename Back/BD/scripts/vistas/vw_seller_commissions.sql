-- Vista para comisiones de vendedores
-- Calcula totales de ventas y comisiones por vendedor

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
    COALESCE(SUM(ot.total * m.commission_percentage / 100), 0) as total_commissions_earned,
    0 as total_paid, -- Por implementar sistema de pagos
    COALESCE(SUM(ot.total * m.commission_percentage / 100), 0) as balance_pending
FROM tbl_member m
JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.id_member AND o.status = 2 -- Solo pedidos completados cuentan para comisión? O todos?
-- Generalmente comisiones son sobre pedidos pagados/completados. Asumiremos completados (status=2) para 'earned'.
-- Pero para 'total_orders' quizás queramos todos.
-- Ajuste: Haremos LEFT JOIN general y usaremos CASE para comisiones.
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
