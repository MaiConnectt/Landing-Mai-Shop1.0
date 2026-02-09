-- Add commission_payout_id to tbl_order
ALTER TABLE tbl_order
ADD COLUMN IF NOT EXISTS commission_payout_id INTEGER REFERENCES tbl_payment_proof(id_payment_proof);

COMMENT ON COLUMN tbl_order.commission_payout_id IS 'ID del registro de pago (tbl_payment_proof) que cubrió la comisión de este pedido';

-- Update view vw_seller_commissions to account for specific payouts
-- The logic needs to be smarter now.
-- Balance Pending = Total Commissions Earned - (Sum of Orders Marked as Paid)
-- Wait, if we mark orders as paid, they are Paid.
-- Total Paid = Sum of Commission Value of orders where commission_payout_id IS NOT NULL.
-- Or better: 
-- Pending Orders = Orders where status=2 AND commission_payout_id IS NULL.
-- Paid Orders = Orders where status=2 AND commission_payout_id IS NOT NULL.

CREATE OR REPLACE VIEW vw_seller_pending_commissions AS
SELECT 
    m.id_member,
    u.first_name,
    u.last_name,
    m.commission_percentage,
    COUNT(o.id_order) as pending_order_count,
    COALESCE(SUM(ot.total * m.commission_percentage / 100), 0) as pending_amount
FROM tbl_member m
JOIN tbl_user u ON m.id_user = u.id_user
JOIN tbl_order o ON m.id_member = o.seller_id
JOIN vw_order_totals ot ON o.id_order = ot.id_order
WHERE o.status = 2 -- Completed
AND o.commission_payout_id IS NULL
GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage;
