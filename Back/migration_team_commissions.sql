-- =====================================================
-- MIGRACIÓN: Agregar campos para sistema de comisiones
-- Fecha: 2026-02-06
-- Descripción: Agrega campos necesarios para gestionar
--              vendedores universitarios con comisiones
-- =====================================================

-- Agregar campos a tbl_member para vendedores universitarios
ALTER TABLE tbl_member 
ADD COLUMN IF NOT EXISTS commission_percentage DECIMAL(5,2) DEFAULT 10.00 
    CHECK (commission_percentage >= 0 AND commission_percentage <= 100),
ADD COLUMN IF NOT EXISTS university VARCHAR(200),
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active' 
    CHECK (status IN ('active', 'inactive')),
ADD COLUMN IF NOT EXISTS phone VARCHAR(15);

-- Agregar comentarios
COMMENT ON COLUMN tbl_member.commission_percentage IS 'Porcentaje de comisión que gana el vendedor por cada venta completada';
COMMENT ON COLUMN tbl_member.university IS 'Universidad o institución educativa del vendedor';
COMMENT ON COLUMN tbl_member.status IS 'Estado del vendedor: active (activo) o inactive (inactivo)';
COMMENT ON COLUMN tbl_member.phone IS 'Teléfono de contacto del vendedor';

-- Agregar campo seller_id a tbl_order para vincular pedidos con vendedores
ALTER TABLE tbl_order 
ADD COLUMN IF NOT EXISTS seller_id INTEGER REFERENCES tbl_member(id_member) ON DELETE SET NULL;

COMMENT ON COLUMN tbl_order.seller_id IS 'Vendedor universitario que gestionó este pedido';

-- Crear índice para mejorar consultas por vendedor
CREATE INDEX IF NOT EXISTS idx_order_seller ON tbl_order(seller_id);

-- Actualizar tbl_payment_proof para vincular con miembros del equipo
-- (ya existe id_order, pero agregamos referencia directa al miembro)
ALTER TABLE tbl_payment_proof 
ADD COLUMN IF NOT EXISTS team_member_id INTEGER REFERENCES tbl_member(id_member) ON DELETE SET NULL;

COMMENT ON COLUMN tbl_payment_proof.team_member_id IS 'Miembro del equipo al que se le realizó el pago (para pagos de comisiones)';

-- Crear vista para calcular comisiones de vendedores
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
    COUNT(DISTINCT o.id_order) as total_orders,
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total ELSE 0 END), 0) as total_sales,
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) as total_commissions_earned,
    COALESCE(SUM(pp.amount), 0) as total_paid,
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total * m.commission_percentage / 100 ELSE 0 END), 0) - COALESCE(SUM(pp.amount), 0) as balance_pending
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.seller_id
LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
LEFT JOIN tbl_payment_proof pp ON m.id_member = pp.team_member_id
GROUP BY m.id_member, u.first_name, u.last_name, u.email, m.university, m.phone, m.commission_percentage, m.status, m.hire_date;

COMMENT ON VIEW vw_seller_commissions IS 'Vista que calcula automáticamente las comisiones, pagos y balance pendiente de cada vendedor';

-- Migrar datos existentes (si los hay)
-- Actualizar commission_percentage basado en el campo commission existente
UPDATE tbl_member 
SET commission_percentage = 
    CASE 
        WHEN commission > 0 AND commission < 1 THEN commission * 100  -- Si está en decimal (0.10 = 10%)
        WHEN commission >= 1 AND commission <= 100 THEN commission    -- Si ya está en porcentaje
        ELSE 10.00  -- Default 10%
    END
WHERE commission_percentage IS NULL;

-- Establecer estado activo para todos los miembros existentes
UPDATE tbl_member 
SET status = 'active' 
WHERE status IS NULL;

-- =====================================================
-- VERIFICACIÓN DE LA MIGRACIÓN
-- =====================================================

-- Verificar que los campos se agregaron correctamente
SELECT 
    column_name, 
    data_type, 
    column_default,
    is_nullable
FROM information_schema.columns
WHERE table_name = 'tbl_member' 
  AND column_name IN ('commission_percentage', 'university', 'status', 'phone')
ORDER BY ordinal_position;

-- Verificar que seller_id se agregó a tbl_order
SELECT 
    column_name, 
    data_type, 
    is_nullable
FROM information_schema.columns
WHERE table_name = 'tbl_order' 
  AND column_name = 'seller_id';

-- Verificar que la vista se creó correctamente
SELECT COUNT(*) as total_sellers FROM vw_seller_commissions;

PRINT 'Migración completada exitosamente';
