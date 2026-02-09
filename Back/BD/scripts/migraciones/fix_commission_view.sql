-- 1. Backfill seller_id from id_member for old orders
UPDATE tbl_order 
SET seller_id = id_member 
WHERE seller_id IS NULL AND id_member IS NOT NULL;

-- 2. Update View to be robust
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
JOIN tbl_order o ON m.id_member = COALESCE(o.seller_id, o.id_member) -- Handle both
JOIN vw_order_totals ot ON o.id_order = ot.id_order
WHERE o.status = 2 -- Completed
AND o.commission_payout_id IS NULL
GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage;
