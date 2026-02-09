-- =====================================================
-- Función: update_timestamp
-- Descripción: Actualiza automáticamente el campo updated_at
-- Uso: Se utiliza en triggers para actualizar timestamps
-- =====================================================

CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Comentario
COMMENT ON FUNCTION update_timestamp() IS 'Función para actualizar automáticamente el campo updated_at en triggers';
