-- =====================================================
-- Trigger: trg_notify_new_order
-- Descripción: Notifica al admin cuando se crea un nuevo pedido por vendedor
-- Tabla: tbl_order
-- Evento: AFTER INSERT
-- =====================================================

CREATE TRIGGER trg_notify_new_order
AFTER INSERT ON tbl_order
FOR EACH ROW
WHEN (NEW.seller_id IS NOT NULL)
EXECUTE FUNCTION notify_admin_new_order();

-- =====================================================
-- NOTAS
-- =====================================================
-- Este trigger solo se ejecuta cuando seller_id NO es NULL
-- Requiere que la función notify_admin_new_order() exista
-- Ejecutar primero: funciones/fn_notify_admin_new_order.sql
-- Requiere que la tabla tbl_notification exista
-- Ejecutar primero: schema/04_notificaciones.sql
