-- Migración: Eliminar concepto de Clientes y mantener soporte para Pedidos Directos

-- 1. Eliminar la restricción de llave foránea en tbl_pedido
ALTER TABLE tbl_pedido DROP CONSTRAINT IF EXISTS fk_pedido_cliente;
ALTER TABLE tbl_order DROP CONSTRAINT IF EXISTS fk_order_client; -- Por si acaso queda con nombre viejo

-- 2. Eliminar la columna id_cliente de tbl_pedido
ALTER TABLE tbl_pedido DROP COLUMN IF EXISTS id_cliente;

-- 3. Eliminar la tabla de clientes
DROP TABLE IF EXISTS tbl_cliente;

-- 4. Eliminar usuarios con rol de cliente (id_rol = 3)
DELETE FROM tbl_usuario WHERE id_rol = 3;

-- 5. Asegurar que las columnas de contacto en tbl_pedido sean NOT NULL donde aplique
-- Nota: Primero nos aseguramos de que no haya nulos si hay datos previos
UPDATE tbl_pedido SET telefono_contacto = 'N/A' WHERE telefono_contacto IS NULL;
UPDATE tbl_pedido SET direccion_entrega = 'N/A' WHERE direccion_entrega IS NULL;

ALTER TABLE tbl_pedido ALTER COLUMN telefono_contacto SET NOT NULL;
ALTER TABLE tbl_pedido ALTER COLUMN direccion_entrega SET NOT NULL;
ALTER TABLE tbl_pedido ALTER COLUMN fecha_entrega SET NOT NULL;

-- 6. El estado_pago ya existe, nos aseguramos de que inicie en 0
ALTER TABLE tbl_pedido ALTER COLUMN estado_pago SET DEFAULT 0;
UPDATE tbl_pedido SET estado_pago = 0 WHERE estado_pago IS NULL;

-- 7. Limpiar IDs de miembros/vendedores si es necesario (legacy check)
-- Mantenemos id_member como el FK principal al vendedor
