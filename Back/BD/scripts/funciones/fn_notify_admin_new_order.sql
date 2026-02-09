-- =====================================================
-- FUNCIÓN: notify_admin_new_order
-- Descripción: Crea notificación para admin cuando vendedor crea pedido
-- =====================================================

CREATE OR REPLACE FUNCTION notify_admin_new_order()
RETURNS TRIGGER AS $$
DECLARE
    admin_id INTEGER;
    seller_name TEXT;
    order_number TEXT;
BEGIN
    -- Obtener ID del admin (role_id = 1)
    SELECT id_user INTO admin_id 
    FROM tbl_user 
    WHERE role_id = 1 
    LIMIT 1;
    
    -- Obtener nombre del vendedor
    SELECT CONCAT(u.first_name, ' ', u.last_name) INTO seller_name
    FROM tbl_member m
    INNER JOIN tbl_user u ON m.id_user = u.id_user
    WHERE m.id_member = NEW.seller_id;
    
    -- Formatear número de pedido
    order_number := LPAD(NEW.id_order::TEXT, 4, '0');
    
    -- Crear notificación para el admin solo si el pedido fue creado por un vendedor
    IF admin_id IS NOT NULL AND NEW.seller_id IS NOT NULL THEN
        INSERT INTO tbl_notification (user_id, type, title, message, related_id)
        VALUES (
            admin_id,
            'new_order',
            'Nuevo Pedido #' || order_number,
            seller_name || ' ha creado un nuevo pedido',
            NEW.id_order
        );
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Comentario
COMMENT ON FUNCTION notify_admin_new_order() IS 'Función trigger que crea notificación automática para admin cuando un vendedor crea un pedido';
