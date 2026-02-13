-- =====================================================
-- MAI SHOP - INICIALIZACIÓN COMPLETA EN ESPAÑOL
-- =====================================================
-- Sistema de ventas para VENDEDORES únicamente (sin clientes)
-- Script idempotente para PostgreSQL
-- Ejecutar con: psql -U postgres -d maiconnect -f MaiShop_Spanish_Init.sql
-- =====================================================

-- Eliminar vistas primero (dependen de las tablas)
DROP VIEW IF EXISTS vw_comisiones_vendedor CASCADE;
DROP VIEW IF EXISTS vw_totales_pedido CASCADE;

-- Eliminar tablas en orden inverso de dependencias
DROP TABLE IF EXISTS tbl_historial_pedido CASCADE;
DROP TABLE IF EXISTS tbl_comprobante_pago CASCADE;
DROP TABLE IF EXISTS tbl_detalle_pedido CASCADE;
DROP TABLE IF EXISTS tbl_pedido CASCADE;
DROP TABLE IF EXISTS tbl_pago_comision CASCADE;
DROP TABLE IF EXISTS tbl_producto CASCADE;
DROP TABLE IF EXISTS tbl_miembro CASCADE;
DROP TABLE IF EXISTS tbl_usuario CASCADE;
DROP TABLE IF EXISTS tbl_rol CASCADE;

-- =====================================================
-- TABLAS DE REFERENCIA
-- =====================================================

-- Tabla de roles (solo ADMIN y VENDEDOR)
CREATE TABLE tbl_rol (
    id_role SMALLINT PRIMARY KEY NOT NULL,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
);

INSERT INTO tbl_rol (id_role, nombre_rol, descripcion) VALUES
(1, 'ADMIN', 'Administrador del sistema con acceso completo'),
(2, 'VENDEDOR', 'Miembro del equipo de ventas');

-- =====================================================
-- TABLAS PRINCIPALES
-- =====================================================

-- Tabla de usuarios del sistema
CREATE TABLE tbl_usuario (
    id_usuario SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE CHECK (
        email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
    ),
    contrasena VARCHAR(255) NOT NULL,
    id_rol SMALLINT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) REFERENCES tbl_rol (id_role)
);

-- Usuario admin por defecto (contraseña: admin123)
-- Hash generado con password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES
('Admin', 'Sistema', 'admin@maishop.com', '$2y$10$.zqN1KAAa8I8NWiCrz5gI4NCNjYGUqNm', 1);

-- Tabla de miembros del equipo (vendedores)
CREATE TABLE tbl_miembro (
    id_miembro SERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL UNIQUE,
    porcentaje_comision DECIMAL(5, 2) NOT NULL DEFAULT 10.00 CHECK (porcentaje_comision >= 0 AND porcentaje_comision <= 100),
    universidad VARCHAR(150),
    telefono VARCHAR(15) CHECK (telefono ~ '^[0-9]{7,15}$'),
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_contratacion DATE DEFAULT CURRENT_DATE,
    notas TEXT,
    CONSTRAINT fk_miembro_usuario FOREIGN KEY (id_usuario) REFERENCES tbl_usuario (id_usuario) ON DELETE CASCADE
);

-- Tabla de productos
CREATE TABLE tbl_producto (
    id_producto SERIAL PRIMARY KEY,
    nombre_producto VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL CHECK (precio >= 0),
    stock INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
    imagen_principal VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Productos de ejemplo
INSERT INTO tbl_producto (nombre_producto, descripcion, precio, stock) VALUES
('Torta de Chocolate', 'Deliciosa torta de chocolate con cobertura de ganache', 35000.00, 20),
('Cupcakes de Fresa', 'Cupcakes decorados con crema de fresa natural', 5000.00, 40);

-- Tabla de pagos de comisión (admin → vendedor)
CREATE TABLE tbl_pago_comision (
    id_pago_comision SERIAL PRIMARY KEY,
    id_vendedor INTEGER NOT NULL,
    monto DECIMAL(10, 2) NOT NULL CHECK (monto > 0),
    ruta_archivo VARCHAR(255),
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'completado' CHECK (estado IN ('pendiente', 'completado', 'cancelado')),
    notas TEXT,
    CONSTRAINT fk_pago_vendedor FOREIGN KEY (id_vendedor) REFERENCES tbl_miembro (id_miembro) ON DELETE CASCADE
);

-- Tabla de pedidos (incluye datos del formulario "Nuevo Pedido")
CREATE TABLE tbl_pedido (
    id_pedido SERIAL PRIMARY KEY,
    id_vendedor INTEGER,
    -- Datos del formulario de pedido
    telefono_contacto VARCHAR(20) NOT NULL,
    direccion_entrega TEXT NOT NULL,
    fecha_entrega DATE NOT NULL,
    notas TEXT,
    -- Control de estado
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado SMALLINT NOT NULL DEFAULT 0 CHECK (estado IN (0, 1, 2, 3)),
    -- Estados: 0=Pendiente, 1=En Proceso/Producción, 2=Completado, 3=Cancelado
    estado_pago SMALLINT DEFAULT 0 CHECK (estado_pago IN (0, 1, 2, 3)),
    -- Estados pago: 0=Sin Comprobante, 1=Comprobante Subido, 2=Aprobado, 3=Rechazado
    -- Comisiones
    monto_comision DECIMAL(10, 2),
    id_pago_comision INTEGER,
    CONSTRAINT fk_pedido_vendedor FOREIGN KEY (id_vendedor) REFERENCES tbl_miembro (id_miembro) ON DELETE SET NULL,
    CONSTRAINT fk_pedido_pago_comision FOREIGN KEY (id_pago_comision) REFERENCES tbl_pago_comision (id_pago_comision) ON DELETE SET NULL
);

-- Tabla de detalles de pedidos
CREATE TABLE tbl_detalle_pedido (
    id_detalle_pedido SERIAL PRIMARY KEY,
    id_pedido INTEGER NOT NULL,
    id_producto INTEGER NOT NULL,
    cantidad DECIMAL(10, 2) NOT NULL CHECK (cantidad >= 1),
    precio_unitario DECIMAL(10, 2) NOT NULL CHECK (precio_unitario > 0),
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido) REFERENCES tbl_pedido (id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES tbl_producto (id_producto) ON DELETE RESTRICT
);

-- Tabla de comprobantes de pago DEL PEDIDO (subidos por vendedor, aprobados por admin)
CREATE TABLE tbl_comprobante_pago (
    id_comprobante_pago SERIAL PRIMARY KEY,
    id_pedido INTEGER NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'aprobado', 'rechazado')),
    notas TEXT,
    CONSTRAINT fk_comprobante_pedido FOREIGN KEY (id_pedido) REFERENCES tbl_pedido (id_pedido) ON DELETE CASCADE
);

-- Tabla de historial de cambios de estado de pedidos
CREATE TABLE tbl_historial_pedido (
    id_historial SERIAL PRIMARY KEY,
    id_pedido INTEGER NOT NULL,
    estado_anterior SMALLINT,
    estado_nuevo SMALLINT NOT NULL,
    usuario_cambio INTEGER,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT,
    CONSTRAINT fk_historial_pedido FOREIGN KEY (id_pedido) REFERENCES tbl_pedido (id_pedido) ON DELETE CASCADE,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_cambio) REFERENCES tbl_usuario (id_usuario) ON DELETE SET NULL
);

-- =====================================================
-- VISTAS
-- =====================================================

-- Vista para calcular totales de pedidos
CREATE OR REPLACE VIEW vw_totales_pedido AS
SELECT 
    p.id_pedido,
    p.id_vendedor,
    p.telefono_contacto,
    p.direccion_entrega,
    p.fecha_entrega,
    p.fecha_creacion,
    p.estado,
    p.estado_pago,
    p.notas,
    COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) AS total
FROM tbl_pedido p
LEFT JOIN tbl_detalle_pedido dp ON p.id_pedido = dp.id_pedido
GROUP BY p.id_pedido, p.id_vendedor, p.telefono_contacto, p.direccion_entrega, 
         p.fecha_entrega, p.fecha_creacion, p.estado, p.estado_pago, p.notas;

-- Vista para comisiones de vendedores (nombres en español)
CREATE OR REPLACE VIEW vw_comisiones_vendedor AS
SELECT 
    m.id_miembro,
    u.nombre,
    u.apellido,
    u.email,
    m.universidad,
    m.telefono,
    m.porcentaje_comision,
    m.estado,
    m.fecha_contratacion,
    COUNT(DISTINCT CASE WHEN o.estado != 3 THEN o.id_pedido END) AS total_pedidos,
    COALESCE(SUM(CASE WHEN o.estado = 2 THEN ot.total ELSE 0 END), 0) AS total_ventas,
    COALESCE(SUM(CASE WHEN o.estado = 2 THEN ot.total * m.porcentaje_comision / 100 ELSE 0 END), 0) AS total_comisiones_ganadas,
    COALESCE(SUM(CASE WHEN o.estado = 2 AND o.id_pago_comision IS NOT NULL THEN o.monto_comision ELSE 0 END), 0) AS total_pagado,
    COALESCE(SUM(CASE WHEN o.estado = 2 AND o.id_pago_comision IS NULL THEN o.monto_comision ELSE 0 END), 0) AS saldo_pendiente
FROM tbl_miembro m
JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
LEFT JOIN tbl_pedido o ON m.id_miembro = o.id_vendedor
LEFT JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
GROUP BY 
    m.id_miembro, 
    u.nombre, 
    u.apellido, 
    u.email, 
    m.universidad, 
    m.telefono, 
    m.porcentaje_comision, 
    m.estado, 
    m.fecha_contratacion;

-- =====================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

CREATE INDEX idx_usuario_email ON tbl_usuario(email);
CREATE INDEX idx_usuario_rol ON tbl_usuario(id_rol);
CREATE INDEX idx_miembro_usuario ON tbl_miembro(id_usuario);
CREATE INDEX idx_pago_comision_vendedor ON tbl_pago_comision(id_vendedor);
CREATE INDEX idx_pedido_vendedor ON tbl_pedido(id_vendedor);
CREATE INDEX idx_pedido_pago_comision ON tbl_pedido(id_pago_comision);
CREATE INDEX idx_pedido_estado ON tbl_pedido(estado);
CREATE INDEX idx_pedido_estado_pago ON tbl_pedido(estado_pago);
CREATE INDEX idx_pedido_fecha_creacion ON tbl_pedido(fecha_creacion);
CREATE INDEX idx_pedido_fecha_entrega ON tbl_pedido(fecha_entrega);
CREATE INDEX idx_detalle_pedido ON tbl_detalle_pedido(id_pedido);
CREATE INDEX idx_detalle_producto ON tbl_detalle_pedido(id_producto);
CREATE INDEX idx_comprobante_pedido ON tbl_comprobante_pago(id_pedido);
CREATE INDEX idx_historial_pedido ON tbl_historial_pedido(id_pedido);

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Función para actualizar timestamp automáticamente
CREATE OR REPLACE FUNCTION actualizar_fecha_modificacion()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para tbl_usuario
CREATE TRIGGER trg_usuario_actualizado
BEFORE UPDATE ON tbl_usuario
FOR EACH ROW EXECUTE FUNCTION actualizar_fecha_modificacion();

-- Trigger para tbl_producto
CREATE TRIGGER trg_producto_actualizado
BEFORE UPDATE ON tbl_producto
FOR EACH ROW EXECUTE FUNCTION actualizar_fecha_modificacion();

-- Función para registrar cambios de estado en historial
CREATE OR REPLACE FUNCTION registrar_cambio_estado_pedido()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.estado IS DISTINCT FROM NEW.estado THEN
        INSERT INTO tbl_historial_pedido (id_pedido, estado_anterior, estado_nuevo, motivo)
        VALUES (NEW.id_pedido, OLD.estado, NEW.estado, 'Cambio automático de estado');
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para registrar cambios de estado
CREATE TRIGGER trg_pedido_cambio_estado
AFTER UPDATE ON tbl_pedido
FOR EACH ROW EXECUTE FUNCTION registrar_cambio_estado_pedido();

-- Función para actualizar estado_pago cuando se sube comprobante
CREATE OR REPLACE FUNCTION actualizar_estado_pago_pedido()
RETURNS TRIGGER AS $$
BEGIN
    -- Cuando se inserta un comprobante, actualizar estado_pago a 1 (Comprobante Subido)
    UPDATE tbl_pedido 
    SET estado_pago = 1 
    WHERE id_pedido = NEW.id_pedido AND estado_pago = 0;
    
    -- Cuando se aprueba/rechaza el comprobante, actualizar estado_pago
    IF TG_OP = 'UPDATE' THEN
        IF NEW.estado = 'aprobado' AND OLD.estado != 'aprobado' THEN
            UPDATE tbl_pedido SET estado_pago = 2 WHERE id_pedido = NEW.id_pedido;
        ELSIF NEW.estado = 'rechazado' AND OLD.estado != 'rechazado' THEN
            UPDATE tbl_pedido SET estado_pago = 3 WHERE id_pedido = NEW.id_pedido;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para sincronizar estado_pago con comprobantes
CREATE TRIGGER trg_comprobante_actualiza_estado
AFTER INSERT OR UPDATE ON tbl_comprobante_pago
FOR EACH ROW EXECUTE FUNCTION actualizar_estado_pago_pedido();

-- Función para calcular automáticamente la comisión cuando se completa un pedido
CREATE OR REPLACE FUNCTION calcular_comision_pedido()
RETURNS TRIGGER AS $$
DECLARE
    v_total_pedido DECIMAL(10, 2);
    v_porcentaje_comision DECIMAL(5, 2);
    v_comision_calculada DECIMAL(10, 2);
BEGIN
    -- Solo ejecutar cuando el estado cambia a 2 (Completado)
    IF OLD.estado IS DISTINCT FROM NEW.estado AND NEW.estado = 2 THEN
        -- Solo calcular si no hay comisión o es cero
        IF NEW.monto_comision IS NULL OR NEW.monto_comision = 0 THEN
            -- Obtener el total del pedido desde los detalles
            SELECT COALESCE(SUM(cantidad * precio_unitario), 0)
            INTO v_total_pedido
            FROM tbl_detalle_pedido
            WHERE id_pedido = NEW.id_pedido;
            
            -- Validación de seguridad: no calcular comisión si el total es 0 o negativo
            IF v_total_pedido <= 0 THEN
                RETURN NEW;
            END IF;
            
            -- Obtener el porcentaje de comisión del vendedor
            IF NEW.id_vendedor IS NOT NULL THEN
                SELECT porcentaje_comision
                INTO v_porcentaje_comision
                FROM tbl_miembro
                WHERE id_miembro = NEW.id_vendedor;
                
                -- Calcular la comisión si se encontró el vendedor
                IF v_porcentaje_comision IS NOT NULL THEN
                    v_comision_calculada := ROUND(v_total_pedido * v_porcentaje_comision / 100, 2);
                    NEW.monto_comision := v_comision_calculada;
                END IF;
            END IF;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para calcular comisión automáticamente
CREATE TRIGGER trg_pedido_calcular_comision
BEFORE UPDATE OF estado ON tbl_pedido
FOR EACH ROW EXECUTE FUNCTION calcular_comision_pedido();

-- =====================================================
-- COMENTARIOS
-- =====================================================

COMMENT ON TABLE tbl_rol IS 'Roles de usuario del sistema (ADMIN, VENDEDOR)';
COMMENT ON TABLE tbl_usuario IS 'Usuarios del sistema (administradores y vendedores)';
COMMENT ON TABLE tbl_miembro IS 'Información de miembros del equipo de ventas';
COMMENT ON TABLE tbl_producto IS 'Catálogo de productos disponibles';
COMMENT ON TABLE tbl_pago_comision IS 'Pagos de comisión realizados por admin a vendedores';
COMMENT ON TABLE tbl_pedido IS 'Pedidos realizados por vendedores con datos de entrega';
COMMENT ON TABLE tbl_detalle_pedido IS 'Detalles de productos en cada pedido';
COMMENT ON TABLE tbl_comprobante_pago IS 'Comprobantes de pago del pedido subidos por vendedor';
COMMENT ON TABLE tbl_historial_pedido IS 'Historial de cambios de estado de pedidos';
COMMENT ON VIEW vw_totales_pedido IS 'Vista que calcula totales de pedidos automáticamente';
COMMENT ON VIEW vw_comisiones_vendedor IS 'Vista que calcula comisiones y estadísticas de vendedores';

COMMENT ON COLUMN tbl_pedido.estado IS '0=Pendiente, 1=En Proceso, 2=Completado, 3=Cancelado';
COMMENT ON COLUMN tbl_pedido.estado_pago IS '0=Sin Comprobante, 1=Comprobante Subido, 2=Aprobado, 3=Rechazado';

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
-- Verificar que todo se creó correctamente:
-- SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE' ORDER BY table_name;
-- SELECT table_name FROM information_schema.views WHERE table_schema = 'public' ORDER BY table_name;
