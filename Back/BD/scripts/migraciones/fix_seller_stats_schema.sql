-- Add commission_amount column
ALTER TABLE tbl_order
ADD COLUMN IF NOT EXISTS commission_amount DECIMAL(10, 2) DEFAULT 0 CHECK (commission_amount >= 0);

-- Add commission_payout_id column
ALTER TABLE tbl_order
ADD COLUMN IF NOT EXISTS commission_payout_id INTEGER REFERENCES tbl_payment_proof(id_payment_proof);

-- Comments
COMMENT ON COLUMN tbl_order.commission_amount IS 'Monto de la comisión ganada por el vendedor en este pedido';
COMMENT ON COLUMN tbl_order.commission_payout_id IS 'ID del registro de pago (tbl_payment_proof) que cubrió la comisión de este pedido';

-- Backfill data
-- Calculate commission for existing orders based on member's current specific commission or default
-- We need to join with tbl_member (via seller_id or id_member) and vw_order_totals

UPDATE tbl_order o
SET commission_amount = (
    SELECT 
        (ot.total * COALESCE(m.commission_percentage, 5.00) / 100)
    FROM vw_order_totals ot
    JOIN tbl_member m ON (o.seller_id = m.id_member OR o.id_member = m.id_member)
    WHERE ot.id_order = o.id_order
    LIMIT 1
)
WHERE commission_amount IS NULL OR commission_amount = 0;
