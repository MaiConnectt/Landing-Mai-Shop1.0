-- Vista para mostrar vendedores con comisiones pendientes de pago
CREATE OR REPLACE VIEW vw_seller_pending_commissions AS
SELECT 
    m.id_member,
    u.first_name,
    u.last_name,
    m.commission_percentage,
    -- Contar pedidos completados sin pagar
    COUNT(o.id_order) as pending_order_count,
    -- Suma de comisiones pendientes (completados sin commission_payout_id)
    COALESCE(SUM(o.commission_amount), 0) as pending_amount
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.id_member
    AND o.status = 2  -- Solo pedidos completados
    AND o.commission_payout_id IS NULL  -- Sin pago registrado
WHERE u.role_id = 2  -- Solo vendedores
GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage
HAVING COUNT(o.id_order) > 0  -- Solo mostrar si tienen pedidos pendientes
ORDER BY pending_amount DESC;
