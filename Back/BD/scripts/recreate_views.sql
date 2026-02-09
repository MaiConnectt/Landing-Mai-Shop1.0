-- =====================================================
-- RECREAR VISTAS ELIMINADAS POR EL SCRIPT DE PRODUCTOS
-- =====================================================

-- 0. LIMPIEZA PREVIA (CASCADE elimina las vistas dependientes como vw_seller_commissions)
DROP VIEW IF EXISTS vw_order_totals CASCADE;
DROP VIEW IF EXISTS vw_client_info CASCADE;
DROP VIEW IF EXISTS vw_member_info CASCADE;
DROP VIEW IF EXISTS vw_seller_commissions CASCADE;
DROP VIEW IF EXISTS vw_payment_proof_details CASCADE;

-- 1. Vista: vw_order_totals (base, sin dependencias)
CREATE OR REPLACE VIEW vw_order_totals AS
SELECT 
    o.id_order,
    o.id_client,
    COALESCE(o.seller_id, o.id_member) as seller_id,
    o.created_at,
    o.status,
    COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
FROM tbl_order o
LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
GROUP BY o.id_order, o.id_client, o.seller_id, o.id_member, o.created_at, o.status;

-- 2. Vista: vw_client_info
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
INNER JOIN tbl_user u ON c.id_user = u.id_user;

-- 3. Vista: vw_member_info
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
INNER JOIN tbl_user u ON m.id_user = u.id_user;

-- 4. Vista: vw_seller_commissions (depende de vw_order_totals)
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
LEFT JOIN tbl_order o ON m.id_member = o.seller_id
LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
LEFT JOIN tbl_commission_payment cp ON m.id_member = cp.id_member
GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage;

-- 5. Vista: vw_payment_proof_details
CREATE OR REPLACE VIEW vw_payment_proof_details AS
SELECT 
    pp.id_payment_proof,
    pp.id_member,
    pp.amount,
    pp.payment_date,
    pp.proof_image,
    pp.notes,
    pp.payment_status,
    pp.created_at,
    CONCAT(u.first_name, ' ', u.last_name) as member_name,
    u.email as member_email,
    m.commission_percentage
FROM tbl_payment_proof pp
INNER JOIN tbl_member m ON pp.id_member = m.id_member
INNER JOIN tbl_user u ON m.id_user = u.id_user;
