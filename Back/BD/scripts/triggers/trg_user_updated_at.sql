-- =====================================================
-- Trigger: trg_user_updated_at
-- Descripción: Actualiza automáticamente updated_at en tbl_user
-- Tabla: tbl_user
-- Evento: BEFORE UPDATE
-- =====================================================

CREATE TRIGGER trg_user_updated_at
BEFORE UPDATE ON tbl_user
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Nota: Requiere que la función update_timestamp() exista
-- Ejecutar primero: funciones/fn_update_timestamp.sql
