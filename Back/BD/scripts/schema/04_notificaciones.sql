-- =====================================================
-- TABLA: tbl_notification
-- Descripción: Sistema de notificaciones para usuarios del sistema
-- =====================================================

CREATE TABLE IF NOT EXISTS tbl_notification (
    id_notification SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    related_id INTEGER,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES tbl_user(id_user) ON DELETE CASCADE
);

-- =====================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

CREATE INDEX IF NOT EXISTS idx_notification_user ON tbl_notification(user_id);
CREATE INDEX IF NOT EXISTS idx_notification_read ON tbl_notification(is_read);
CREATE INDEX IF NOT EXISTS idx_notification_created ON tbl_notification(created_at);
CREATE INDEX IF NOT EXISTS idx_notification_type ON tbl_notification(type);

-- =====================================================
-- COMENTARIOS
-- =====================================================

COMMENT ON TABLE tbl_notification IS 'Sistema de notificaciones para usuarios (admin y vendedores)';
COMMENT ON COLUMN tbl_notification.type IS 'Tipo de notificación: new_order, order_update, payment, commission, etc.';
COMMENT ON COLUMN tbl_notification.related_id IS 'ID del registro relacionado (pedido, pago, etc.)';
COMMENT ON COLUMN tbl_notification.is_read IS 'Indica si la notificación ha sido leída';

-- =====================================================
-- TIPOS DE NOTIFICACIONES SOPORTADOS
-- =====================================================
-- 'new_order'       - Nuevo pedido creado por vendedor
-- 'order_update'    - Actualización de estado de pedido
-- 'payment'         - Nuevo comprobante de pago
-- 'commission'      - Pago de comisión realizado
-- 'system'          - Notificación del sistema
