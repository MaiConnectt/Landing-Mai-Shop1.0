-- =====================================================
-- Migración: Remover UNIQUE de tbl_client.phone
-- =====================================================
-- Fecha: 2026-02-07
-- Descripción: Permite múltiples clientes con el mismo teléfono
-- =====================================================

-- Eliminar la restricción UNIQUE del teléfono
ALTER TABLE tbl_client DROP CONSTRAINT IF EXISTS tbl_client_phone_key;

-- Verificar que la restricción fue eliminada
SELECT 
    conname as constraint_name,
    contype as constraint_type
FROM pg_constraint
WHERE conrelid = 'tbl_client'::regclass
  AND conname LIKE '%phone%';

-- Resultado esperado: No debe aparecer ninguna restricción de phone
