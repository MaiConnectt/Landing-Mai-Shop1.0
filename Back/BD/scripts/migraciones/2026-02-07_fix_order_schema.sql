-- =====================================================
-- MIGRACIÓN: Corrección del Schema de Pedidos
-- Fecha: 2026-02-07
-- Descripción: Unifica campos de tbl_order y agrega campos faltantes
-- =====================================================

-- 1. Agregar campos faltantes a tbl_order
ALTER TABLE tbl_order 
ADD COLUMN IF NOT EXISTS delivery_date DATE,
ADD COLUMN IF NOT EXISTS notes TEXT;

-- 2. Renombrar id_member a seller_id si existe
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'tbl_order' AND column_name = 'id_member'
    ) THEN
        ALTER TABLE tbl_order RENAME COLUMN id_member TO seller_id;
    END IF;
END $$;

-- 3. Asegurar que seller_id existe (por si acaso)
ALTER TABLE tbl_order 
ADD COLUMN IF NOT EXISTS seller_id INTEGER;

-- 4. Agregar foreign key si no existe
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.table_constraints 
        WHERE constraint_name = 'fk_order_seller'
    ) THEN
        ALTER TABLE tbl_order 
        ADD CONSTRAINT fk_order_seller 
        FOREIGN KEY (seller_id) REFERENCES tbl_member(id_member) ON DELETE SET NULL;
    END IF;
END $$;

-- 5. Crear índice para mejorar performance
CREATE INDEX IF NOT EXISTS idx_order_seller ON tbl_order(seller_id);
CREATE INDEX IF NOT EXISTS idx_order_delivery_date ON tbl_order(delivery_date);

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

-- Mostrar estructura actualizada de tbl_order
SELECT 
    column_name, 
    data_type, 
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_name = 'tbl_order'
ORDER BY ordinal_position;

-- =====================================================
-- NOTAS
-- =====================================================
-- Esta migración es idempotente y se puede ejecutar múltiples veces
-- sin causar errores.
