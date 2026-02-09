-- =====================================================
-- Vista: vw_order_totals
-- Descripción: Calcula automáticamente los totales de pedidos
-- =====================================================

CREATE OR REPLACE VIEW vw_order_totals AS
SELECT 
    o.id_order,
    o.id_client,
    o.id_member,
    o.created_at,
    o.status,
    COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
FROM tbl_order o
LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
GROUP BY o.id_order, o.id_client, o.id_member, o.created_at, o.status;

-- Comentario
COMMENT ON VIEW vw_order_totals IS 'Vista que calcula automáticamente los totales de pedidos';
