-- =====================================================
-- Trigger: trg_product_updated_at
-- Descripción: Actualiza automáticamente updated_at en tbl_product
-- Tabla: tbl_product
-- Evento: BEFORE UPDATE
-- =====================================================

CREATE TRIGGER trg_product_updated_at
BEFORE UPDATE ON tbl_product
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Nota: Requiere que la función update_timestamp() exista
-- Ejecutar primero: funciones/fn_update_timestamp.sql
