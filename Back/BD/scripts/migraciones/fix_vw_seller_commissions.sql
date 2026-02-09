-- Migration: Fix vw_seller_commissions to use commission_amount column
-- Date: 2026-02-09

-- Drop and recreate the view with correct logic
DROP VIEW IF EXISTS vw_seller_commissions CASCADE;

CREATE VIEW vw_seller_commissions AS
SELECT 
    m.id_member,
    u.first_name,
    u.last_name,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as seller_name,
    m.commission_percentage,
    m.phone,
    m.university,
    m.hire_date,
    COUNT(CASE WHEN o.status != 3 THEN 1 END) as total_orders,
    -- Total sales from completed orders (status = 2)
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total ELSE 0 END), 0) as total_sales,
    -- Total commissions earned from completed orders
    COALESCE(SUM(CASE WHEN o.status = 2 THEN o.commission_amount ELSE 0 END), 0) as commissions_earned,
    -- Total paid (where commission_payout_id is not null)
    COALESCE(SUM(CASE WHEN o.status = 2 AND o.commission_payout_id IS NOT NULL THEN o.commission_amount ELSE 0 END), 0) as total_paid,
    -- Balance pending (commissions earned but not yet paid)
    COALESCE(SUM(CASE WHEN o.status = 2 AND o.commission_payout_id IS NULL THEN o.commission_amount ELSE 0 END), 0) as balance_pending
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.id_member
LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
GROUP BY m.id_member, u.first_name, u.last_name, u.email, m.commission_percentage, m.phone, m.university, m.hire_date;

COMMENT ON VIEW vw_seller_commissions IS 'Vista que muestra las comisiones, pagos y balance pendiente de cada vendedor usando la columna commission_amount';
