-- =====================================================
-- SCRIPT DE REPARACIÓN INTEGRAL DE BASE DE DATOS (V4 FINAL)
-- Adaptado al esquema existente (status integer)
-- =====================================================

-- 0. ASEGURAR COLUMNAS BASE EN TABLAS PRINCIPALES
ALTER TABLE tbl_user ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE tbl_order ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 1. ASEGURAR TABLA DE PAGOS (Si no existe)
CREATE TABLE IF NOT EXISTS tbl_payment_proof (
    id_payment_proof SERIAL PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status SMALLINT DEFAULT 0, -- 0: Pending, 1: Approved, 2: Paid
    proof_image_path VARCHAR(255),
    notes TEXT,
    team_member_id INTEGER REFERENCES tbl_member(id_member)
);

-- 2. ASEGURAR COLUMNAS NECESARIAS (Solo si faltan)
ALTER TABLE tbl_payment_proof ADD COLUMN IF NOT EXISTS team_member_id INTEGER REFERENCES tbl_member(id_member);
-- Si existen con otro nombre, las vistas las manejarán.

-- 3. LIMPIEZA DE VISTAS
DROP VIEW IF EXISTS vw_payment_proof_details CASCADE;
DROP VIEW IF EXISTS vw_seller_commissions CASCADE;
DROP VIEW IF EXISTS vw_member_info CASCADE;
DROP VIEW IF EXISTS vw_client_info CASCADE;
DROP VIEW IF EXISTS vw_order_totals CASCADE;

-- 4. RECREAR VISTA BASE
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

-- 5. RECREAR VISTAS DEPENDIENTES
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

CREATE OR REPLACE VIEW vw_seller_commissions AS
SELECT 
    m.id_member,
    CONCAT(u.first_name, ' ', u.last_name) as seller_name,
    m.commission_percentage,
    COUNT(o.id_order) as total_orders,
    COALESCE(SUM(ot.total), 0) as total_sales,
    COALESCE(SUM(ot.total * m.commission_percentage / 100), 0) as total_commissions,
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) as commissions_earned,
    COALESCE(SUM(CASE WHEN pp.status IN (1, 2) THEN pp.amount ELSE 0 END), 0) as total_paid, -- Asumiendo 1 o 2 es pagado
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) - 
    COALESCE(SUM(CASE WHEN pp.status IN (1, 2) THEN pp.amount ELSE 0 END), 0) as balance_pending
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.seller_id
LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
LEFT JOIN tbl_payment_proof pp ON m.id_member = pp.team_member_id
GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage;

CREATE OR REPLACE VIEW vw_payment_proof_details AS
SELECT 
    pp.id_payment_proof,
    pp.team_member_id as id_member,
    pp.amount,
    pp.uploaded_at as payment_date,
    pp.proof_image_path as proof_image,
    pp.notes,
    CASE 
        WHEN pp.status = 0 THEN 'pending'
        WHEN pp.status = 1 THEN 'approved'
        WHEN pp.status = 2 THEN 'paid'
        ELSE 'unknown'
    END as payment_status, -- Casting status int to readable text
    pp.uploaded_at as created_at,
    CONCAT(u.first_name, ' ', u.last_name) as member_name,
    u.email as member_email,
    m.commission_percentage
FROM tbl_payment_proof pp
INNER JOIN tbl_member m ON pp.team_member_id = m.id_member
INNER JOIN tbl_user u ON m.id_user = u.id_user;
