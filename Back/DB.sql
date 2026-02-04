-- =====================================================
-- BASE DE DATOS MAI SHOP - NORMALIZADA A 3FN
-- =====================================================
-- Versión normalizada manteniendo todas las reglas de negocio
-- Cambios aplicados:
-- 1. Creadas tablas de referencia (roles, estados, métodos de pago, tipos de catálogo)
-- 2. Eliminado email redundante de tbl_client (se obtiene desde tbl_user)
-- 3. Eliminado id_client redundante de tbl_invoice_header (se obtiene desde tbl_order)
-- 4. Eliminado campo total de tbl_order (se calcula con vista vw_order_totals)
-- 5. Unificados invoice_date e invoice_time en invoice_datetime
-- 6. Agregados índices de optimización
-- 7. Agregados triggers para actualización automática de timestamps
-- =====================================================

-- Eliminar vistas primero (antes de las tablas)
DROP VIEW IF EXISTS vw_invoice_client;
DROP VIEW IF EXISTS vw_client_info;
DROP VIEW IF EXISTS vw_invoice_totals;
DROP VIEW IF EXISTS vw_order_totals;

-- Eliminar tablas existentes en orden inverso de dependencias
DROP TABLE IF EXISTS tbl_invoice_detail;
DROP TABLE IF EXISTS tbl_invoice_header;
DROP TABLE IF EXISTS tbl_order_detail;
DROP TABLE IF EXISTS tbl_order;
DROP TABLE IF EXISTS tbl_purchase_order_detail;
DROP TABLE IF EXISTS tbl_purchase_order;
DROP TABLE IF EXISTS tbl_catalog_product;
DROP TABLE IF EXISTS tbl_job_request;
DROP TABLE IF EXISTS tbl_appointment;
DROP TABLE IF EXISTS tbl_member;
DROP TABLE IF EXISTS tbl_client;
DROP TABLE IF EXISTS tbl_supplier;
DROP TABLE IF EXISTS tbl_catalog;
DROP TABLE IF EXISTS tbl_product;
DROP TABLE IF EXISTS tbl_user;
DROP TABLE IF EXISTS tbl_role;
DROP TABLE IF EXISTS tbl_status;
DROP TABLE IF EXISTS tbl_payment_method;
DROP TABLE IF EXISTS tbl_catalog_type;

-- =====================================================
-- TABLAS DE REFERENCIA (LOOKUP TABLES)
-- =====================================================
-- CAMBIO 1: Nueva tabla para normalizar roles
-- BENEFICIO: Elimina valores hardcodeados, facilita agregar nuevos roles
CREATE TABLE tbl_role (
    id_role SMALLINT PRIMARY KEY NOT NULL,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

INSERT INTO tbl_role (id_role, role_name, description) VALUES
(1, 'ADMIN', 'Administrador del sistema con acceso completo'),
(2, 'MEMBER', 'Miembro del equipo de trabajo'),
(3, 'CLIENT', 'Cliente del negocio');

-- CAMBIO 2: Nueva tabla genérica para normalizar estados
-- BENEFICIO: Centraliza todos los estados, facilita mantenimiento y reportes
CREATE TABLE tbl_status (
    id_status SMALLINT PRIMARY KEY NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    status_code SMALLINT NOT NULL,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    UNIQUE (entity_type, status_code)
);

INSERT INTO tbl_status (id_status, entity_type, status_code, status_name, description) VALUES
-- Estados de pedidos (mantiene regla: 0=Pendiente, 1=Confirmado, 2=Cancelado)
(1, 'order', 0, 'Pendiente', 'Pedido en espera de confirmación'),
(2, 'order', 1, 'Confirmado', 'Pedido confirmado y en proceso'),
(3, 'order', 2, 'Cancelado', 'Pedido cancelado'),
-- Estados de citas (mantiene regla: 0=Pendiente, 1=Confirmada, 2=Cancelada)
(4, 'appointment', 0, 'Pendiente', 'Cita pendiente de confirmación'),
(5, 'appointment', 1, 'Confirmada', 'Cita confirmada'),
(6, 'appointment', 2, 'Cancelada', 'Cita cancelada'),
-- Estados de solicitudes de trabajo (mantiene regla: 0=Pendiente, 1=Aprobado, 2=Rechazado)
(7, 'job_request', 0, 'Pendiente', 'Solicitud en revisión'),
(8, 'job_request', 1, 'Aprobado', 'Solicitud aprobada'),
(9, 'job_request', 2, 'Rechazado', 'Solicitud rechazada'),
-- Estados de órdenes de compra (mantiene regla: 0=Pendiente, 1=Aprobada, 2=Rechazada)
(10, 'purchase_order', 0, 'Pendiente', 'Orden pendiente'),
(11, 'purchase_order', 1, 'Aprobada', 'Orden aprobada'),
(12, 'purchase_order', 2, 'Rechazada', 'Orden rechazada');

-- CAMBIO 3: Nueva tabla para normalizar métodos de pago
-- BENEFICIO: Facilita agregar nuevos métodos de pago sin modificar constraints
CREATE TABLE tbl_payment_method (
    id_payment_method SMALLINT PRIMARY KEY NOT NULL,
    method_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

INSERT INTO tbl_payment_method (id_payment_method, method_name, description) VALUES
(1, 'Efectivo', 'Pago en efectivo'),
(2, 'Tarjeta', 'Pago con tarjeta de crédito o débito'),
(3, 'Transferencia', 'Transferencia bancaria');

-- CAMBIO 4: Nueva tabla para normalizar tipos de catálogo
-- BENEFICIO: Elimina valores hardcodeados, facilita extensibilidad
CREATE TABLE tbl_catalog_type (
    id_catalog_type SMALLINT PRIMARY KEY NOT NULL,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

INSERT INTO tbl_catalog_type (id_catalog_type, type_name, description) VALUES
(1, 'Productos', 'Catálogo de productos de pastelería'),
(2, 'Servicios', 'Catálogo de servicios'),
(3, 'Equipo', 'Catálogo de equipo de trabajo');

-- =====================================================
-- TABLAS PRINCIPALES
-- =====================================================

-- Tabla de usuarios del sistema
-- CAMBIO 5: Agregada FK a tbl_role, campos de auditoría
CREATE TABLE tbl_user (
    id_user INTEGER PRIMARY KEY NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE CHECK (
        email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
    ),
    password VARCHAR(100) NOT NULL,
    role_id SMALLINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES tbl_role (id_role)
);

-- Inserts de ejemplo para tbl_user
INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id) VALUES
(1, 'Maira', 'Sierra', 'maira.sierra@email.com', '$2a$10$wH9Q1Qn8Qw1Q1Qn8Qw1Q1u8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1u', 1),
(2, 'Carlos', 'Ramirez', 'carlos.ramirez@email.com', '$2a$10$eJ8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(3, 'Lucia', 'Gomez', 'lucia.gomez@email.com', '$2a$10$zK9Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(4, 'Pedro', 'Martinez', 'pedro.martinez@email.com', '$2a$10$yL0Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(5, 'Ana', 'Lopez', 'ana.lopez@email.com', '$2a$10$xM1Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(6, 'Jorge', 'Fernandez', 'jorge.fernandez@email.com', '$2a$10$wN2Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(7, 'Sofia', 'Castro', 'sofia.castro@email.com', '$2a$10$vO3Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(8, 'Miguel', 'Torres', 'miguel.torres@email.com', '$2a$10$uP4Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(9, 'Valeria', 'Mendoza', 'valeria.mendoza@email.com', '$2a$10$tQ5Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(10, 'Andres', 'Vargas', 'andres.vargas@email.com', '$2a$10$sR6Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(11, 'Laura', 'Morales', 'laura.morales@email.com', '$2a$10$rS7Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(12, 'Diego', 'Rojas', 'diego.rojas@email.com', '$2a$10$qT8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(13, 'Camila', 'Suarez', 'camila.suarez@email.com', '$2a$10$pU9Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(14, 'Esteban', 'Cruz', 'esteban.cruz@email.com', '$2a$10$oV0Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(15, 'Paula', 'Herrera', 'paula.herrera@email.com', '$2a$10$nW1Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(16, 'Ricardo', 'Mendez', 'ricardo.mendez@email.com', '$2a$10$mX2Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(17, 'Daniela', 'Vega', 'daniela.vega@email.com', '$2a$10$lY3Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(18, 'Sebastian', 'Pardo', 'sebastian.pardo@email.com', '$2a$10$kZ4Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3),
(19, 'Natalia', 'Ortega', 'natalia.ortega@email.com', '$2a$10$jA5Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 2),
(20, 'Felipe', 'Salazar', 'felipe.salazar@email.com', '$2a$10$iB6Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw1Q1Qn8Qw', 3);

-- CAMBIO 6: Eliminado campo email (NORMALIZACIÓN 3FN)
-- RAZÓN: email ya existe en tbl_user, creando dependencia transitiva
-- REGLA DE NEGOCIO MANTENIDA: El email se obtiene mediante JOIN con tbl_user o usando la vista vw_client_info
CREATE TABLE tbl_client (
    id_client INTEGER PRIMARY KEY NOT NULL,
    id_user INTEGER NOT NULL UNIQUE,
    address VARCHAR(150) NOT NULL,
    phone VARCHAR(12) NOT NULL UNIQUE CHECK (phone ~ '^[0-9]{7,12}$'),
    CONSTRAINT fk_client_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user)
);

-- Inserts de ejemplo para tbl_client (sin email)
INSERT INTO tbl_client (id_client, id_user, address, phone) VALUES
(1, 2, 'Calle 10 #23-45, Bucaramanga, Santander', '3001234567'),
(2, 3, 'Carrera 15 #45-67, Bucaramanga, Santander', '3012345678'),
(3, 4, 'Avenida 5 #12-34, Floridablanca, Santander', '3023456789'),
(4, 5, 'Calle 8 #56-78, Girón, Santander', '3034567890'),
(5, 6, 'Carrera 20 #34-56, Piedecuesta, Santander', '3045678901'),
(6, 7, 'Calle 12 #78-90, Bucaramanga, Santander', '3056789012'),
(7, 8, 'Avenida 3 #21-43, Bucaramanga, Santander', '3067890123'),
(8, 9, 'Carrera 7 #65-43, Floridablanca, Santander', '3078901234'),
(9, 10, 'Calle 15 #32-10, Bucaramanga, Santander', '3089012345'),
(10, 11, 'Carrera 8 #54-21, Girón, Santander', '3090123456'),
(11, 12, 'Avenida 9 #76-54, Bucaramanga, Santander', '3101234567'),
(12, 13, 'Calle 20 #11-22, Floridablanca, Santander', '3112345678'),
(13, 14, 'Carrera 12 #34-56, Piedecuesta, Santander', '3123456789'),
(14, 15, 'Avenida 7 #89-10, Bucaramanga, Santander', '3134567890'),
(15, 16, 'Calle 18 #23-45, Girón, Santander', '3145678901'),
(16, 17, 'Carrera 5 #67-89, Bucaramanga, Santander', '3156789012'),
(17, 18, 'Avenida 2 #45-67, Floridablanca, Santander', '3167890123'),
(18, 19, 'Calle 22 #12-34, Bucaramanga, Santander', '3178901234'),
(19, 20, 'Carrera 10 #56-78, Piedecuesta, Santander', '3189012345'),
(20, 1, 'Avenida 1 #23-45, Bucaramanga, Santander', '3190123456');

-- Tabla de miembros (empleados o equipo)
CREATE TABLE tbl_member (
    id_member INTEGER PRIMARY KEY NOT NULL,
    id_user INTEGER NOT NULL UNIQUE,
    commission DECIMAL(7, 2) NOT NULL CHECK (commission >= 0),
    hire_date DATE DEFAULT CURRENT_DATE,
    CONSTRAINT fk_member_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user)
);

-- Inserts de ejemplo para tbl_member
INSERT INTO tbl_member (id_member, id_user, commission) VALUES
(1, 1, 1000.00),
(2, 2, 500.00),
(3, 3, 500.00),
(4, 4, 500.00),
(5, 5, 500.00),
(6, 6, 500.00),
(7, 11, 500.00),
(8, 12, 500.00),
(9, 15, 500.00);

-- Tabla de proveedores
CREATE TABLE tbl_supplier (
    id_supplier INTEGER PRIMARY KEY NOT NULL,
    supplier_name VARCHAR(100) NOT NULL,
    contact VARCHAR(15) NOT NULL CHECK (contact ~ '^[0-9]{7,15}$'),
    email VARCHAR(100) NOT NULL UNIQUE CHECK (
        email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
    ),
    is_active BOOLEAN DEFAULT TRUE
);

-- Inserts de ejemplo para tbl_supplier
INSERT INTO tbl_supplier (id_supplier, supplier_name, contact, email) VALUES
(1, 'Huevos Kikes', '3101234567', 'contacto@huevoskikes.com'),
(2, 'Harinera del Valle', '3112345678', 'ventas@harineradelvalle.com'),
(3, 'Colanta', '3123456789', 'info@colanta.com'),
(4, 'Alpina', '3134567890', 'contacto@alpina.com'),
(5, 'Postobón', '3145678901', 'pedidos@postobon.com'),
(6, 'Casa Luker', '3156789012', 'proveedora@casaluker.com'),
(7, 'Levapan', '3167890123', 'ventas@levapan.com'),
(8, 'Productos Ramo', '3178901234', 'info@ramo.com.co'),
(9, 'Nestlé Colombia', '3189012345', 'contacto@nestle.com.co'),
(10, 'Alquería', '3190123456', 'ventas@alqueria.com');

-- CAMBIO 7: Campo type ahora es FK a tbl_catalog_type
CREATE TABLE tbl_catalog (
    id_catalog INTEGER PRIMARY KEY NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type SMALLINT NOT NULL,
    CONSTRAINT fk_catalog_type FOREIGN KEY (type) REFERENCES tbl_catalog_type (id_catalog_type)
);

-- Inserts de ejemplo para tbl_catalog
INSERT INTO tbl_catalog (id_catalog, name, description, type) VALUES
(1, 'Catálogo de Pasteles', 'Catálogo de productos de pastelería y repostería', 1),
(2, 'Catálogo de Servicios', 'Catálogo de servicios de repostería', 2),
(3, 'Catálogo de Equipo', 'Catálogo de equipo de trabajo', 3);

-- Tabla de productos
-- CAMBIO 8: Agregados campos de auditoría
CREATE TABLE tbl_product (
    id_product INTEGER PRIMARY KEY NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
    description TEXT,
    stock INTEGER NOT NULL CHECK (stock >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserts de ejemplo para tbl_product
INSERT INTO tbl_product (id_product, product_name, price, description, stock) VALUES
(1, 'Torta de Chocolate', 35000.00, 'Deliciosa torta de chocolate con cobertura de ganache', 20),
(2, 'Torta de Vainilla', 32000.00, 'Torta esponjosa de vainilla con relleno de crema', 15),
(3, 'Cupcakes de Fresa', 5000.00, 'Cupcakes decorados con crema de fresa natural', 40),
(4, 'Galletas Decoradas', 2500.00, 'Galletas de mantequilla decoradas con glaseado', 100),
(5, 'Brownies', 4000.00, 'Brownies de chocolate con nueces', 30),
(6, 'Cheesecake', 28000.00, 'Cheesecake tradicional con salsa de frutos rojos', 10),
(7, 'Tarta de Limón', 26000.00, 'Tarta de limón con merengue', 12),
(8, 'Alfajores', 2000.00, 'Alfajores rellenos de arequipe y cubiertos de coco', 50),
(9, 'Pan de Banano', 8000.00, 'Pan de banano casero', 25),
(10, 'Torta Red Velvet', 37000.00, 'Torta Red Velvet con crema de queso', 8);

-- Tabla de productos en catálogos
CREATE TABLE tbl_catalog_product (
    id_catalog_product INTEGER PRIMARY KEY NOT NULL,
    id_catalog INTEGER NOT NULL,
    id_product INTEGER NOT NULL,
    CONSTRAINT fk_catalog_product_catalog FOREIGN KEY (id_catalog) REFERENCES tbl_catalog (id_catalog),
    CONSTRAINT fk_catalog_product_product FOREIGN KEY (id_product) REFERENCES tbl_product (id_product)
);

-- Inserts de ejemplo para tbl_catalog_product
INSERT INTO tbl_catalog_product (id_catalog_product, id_catalog, id_product) VALUES
(1, 1, 1), (2, 1, 2), (3, 1, 3), (4, 1, 4), (5, 1, 5),
(6, 1, 6), (7, 1, 7), (8, 1, 8), (9, 1, 9), (10, 1, 10),
(11, 2, 4), (12, 2, 3), (13, 3, 8), (14, 3, 9);

-- Tabla de solicitudes de trabajo
-- REGLA DE NEGOCIO MANTENIDA: status sigue siendo 0=Pendiente, 1=Aprobado, 2=Rechazado
CREATE TABLE tbl_job_request (
    id_job_request INTEGER PRIMARY KEY NOT NULL,
    id_user INTEGER NOT NULL,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_request_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user)
);

-- Inserts de ejemplo para tbl_job_request
INSERT INTO tbl_job_request (id_job_request, id_user, status, created_at) VALUES
(1, 7, 0, '2024-06-01 09:00:00'),
(2, 8, 1, '2024-06-02 10:30:00'),
(3, 9, 2, '2024-06-03 11:15:00'),
(4, 10, 0, '2024-06-04 14:45:00'),
(5, 13, 1, '2024-06-05 16:00:00');

-- Tabla de citas de clientes
-- REGLA DE NEGOCIO MANTENIDA: status sigue siendo 0=Pendiente, 1=Confirmada, 2=Cancelada
CREATE TABLE tbl_appointment (
    id_appointment INTEGER PRIMARY KEY NOT NULL,
    id_client INTEGER NOT NULL,
    scheduled_date TIMESTAMP NOT NULL,
    notes TEXT NOT NULL,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    CONSTRAINT fk_appointment_client FOREIGN KEY (id_client) REFERENCES tbl_client (id_client)
);

-- Inserts de ejemplo para tbl_appointment
INSERT INTO tbl_appointment (id_appointment, id_client, scheduled_date, notes, status) VALUES
(1, 1, '2024-06-10 09:00:00', 'Reunión de planificación de pedidos', 1),
(2, 2, '2024-06-11 10:30:00', 'Capacitación sobre nuevos productos', 0),
(3, 3, '2024-06-12 14:00:00', 'Revisión de inventario', 1),
(4, 4, '2024-06-13 16:00:00', 'Cita para cotización de pastel', 0),
(5, 5, '2024-06-14 11:00:00', 'Entrega de pedido especial', 1),
(6, 6, '2024-06-15 15:30:00', 'Consulta sobre servicios de catering', 0),
(7, 7, '2024-06-16 13:00:00', 'Seguimiento a cliente frecuente', 1);

-- Tabla de órdenes de compra a proveedores
-- REGLA DE NEGOCIO MANTENIDA: status sigue siendo 0=Pendiente, 1=Aprobada, 2=Rechazada
CREATE TABLE tbl_purchase_order (
    id_purchase_order INTEGER PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    id_supplier INTEGER NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expected_date DATE,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    CONSTRAINT fk_purchase_order_supplier FOREIGN KEY (id_supplier) REFERENCES tbl_supplier (id_supplier)
);

-- Inserts de ejemplo para tbl_purchase_order
INSERT INTO tbl_purchase_order (id_supplier, order_date, expected_date, status) OVERRIDING SYSTEM VALUE VALUES
(1, '2025-07-10 08:00:00', '2025-07-15', 0),
(2, '2025-08-12 09:30:00', '2025-08-17', 1),
(3, '2025-09-14 10:15:00', '2025-09-19', 2),
(4, '2025-10-16 11:45:00', '2025-10-21', 1),
(5, '2025-11-18 13:00:00', '2025-11-23', 0);

-- Tabla de detalles de órdenes de compra
CREATE TABLE tbl_purchase_order_detail (
    id_purchase_order INTEGER NOT NULL,
    id_product INTEGER NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL CHECK (quantity >= 1),
    unit_price DECIMAL(10, 2) NOT NULL CHECK (unit_price >= 0),
    PRIMARY KEY (id_purchase_order, id_product),
    CONSTRAINT fk_purchase_order_detail_order FOREIGN KEY (id_purchase_order) REFERENCES tbl_purchase_order (id_purchase_order),
    CONSTRAINT fk_purchase_order_detail_product FOREIGN KEY (id_product) REFERENCES tbl_product (id_product)
);

-- Inserts de ejemplo para tbl_purchase_order_detail
INSERT INTO tbl_purchase_order_detail (id_purchase_order, id_product, quantity, unit_price) VALUES
(1, 1, 10, 30000.00), (1, 4, 20, 2000.00),
(2, 2, 5, 31000.00), (2, 5, 15, 3500.00),
(3, 3, 12, 4500.00), (3, 6, 3, 27000.00),
(4, 7, 8, 25000.00), (4, 8, 30, 1800.00),
(5, 9, 10, 7500.00), (5, 10, 2, 35000.00);

-- CAMBIO 9: Eliminado campo total (NORMALIZACIÓN 3FN)
-- RAZÓN: total es un dato derivado que se calcula desde tbl_order_detail
-- REGLA DE NEGOCIO MANTENIDA: El total se obtiene mediante la vista vw_order_totals
-- REGLA DE NEGOCIO MANTENIDA: status sigue siendo 0=Pendiente, 1=Confirmado, 2=Cancelado
CREATE TABLE tbl_order (
    id_order INTEGER PRIMARY KEY NOT NULL,
    id_client INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    CONSTRAINT fk_order_client FOREIGN KEY (id_client) REFERENCES tbl_client (id_client)
);

-- Inserts de ejemplo para tbl_order (sin total)
INSERT INTO tbl_order (id_order, id_client, created_at, status) VALUES
(1, 1, '2024-06-10 10:00:00', 1),
(2, 2, '2024-06-11 11:30:00', 0),
(3, 3, '2024-06-12 12:45:00', 1),
(4, 4, '2024-06-13 14:00:00', 2),
(5, 5, '2024-06-14 15:15:00', 1),
(6, 6, '2024-06-15 16:30:00', 0),
(7, 7, '2024-06-16 17:45:00', 1),
(8, 8, '2024-06-17 18:00:00', 1),
(9, 9, '2024-06-18 19:15:00', 0),
(10, 10, '2024-06-19 20:30:00', 2);

-- Tabla de detalles de pedidos de clientes
CREATE TABLE tbl_order_detail (
    id_order_detail INTEGER PRIMARY KEY NOT NULL,
    id_order INTEGER NOT NULL,
    id_product INTEGER NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL CHECK (quantity >= 1),
    unit_price DECIMAL(10, 2) NOT NULL CHECK (unit_price > 0),
    CONSTRAINT fk_order_detail_order FOREIGN KEY (id_order) REFERENCES tbl_order (id_order),
    CONSTRAINT fk_order_detail_product FOREIGN KEY (id_product) REFERENCES tbl_product (id_product)
);

-- Inserts de ejemplo para tbl_order_detail
INSERT INTO tbl_order_detail (id_order_detail, id_order, id_product, quantity, unit_price) VALUES
(1, 1, 1, 2, 35000.00), (2, 1, 4, 10, 2500.00),
(3, 2, 3, 3, 5000.00), (4, 2, 5, 2, 4000.00),
(5, 3, 2, 1, 32000.00), (6, 3, 8, 5, 2000.00),
(7, 4, 6, 1, 28000.00), (8, 4, 7, 2, 26000.00),
(9, 5, 9, 5, 8000.00), (10, 5, 10, 1, 37000.00),
(11, 6, 4, 2, 2500.00), (12, 7, 8, 4, 2000.00),
(13, 8, 3, 2, 5000.00), (14, 9, 1, 1, 35000.00),
(15, 10, 7, 1, 26000.00);

-- CAMBIO 10: Eliminado id_client (NORMALIZACIÓN 3FN)
-- RAZÓN: id_client se obtiene desde tbl_order, creando dependencia transitiva
-- CAMBIO 11: Unificados invoice_date e invoice_time en invoice_datetime
-- CAMBIO 12: payment_method ahora es FK a tbl_payment_method
-- REGLAS DE NEGOCIO MANTENIDAS: Todas las validaciones y relaciones se mantienen
CREATE TABLE tbl_invoice_header (
    id_invoice INTEGER PRIMARY KEY NOT NULL,
    id_order INTEGER NOT NULL,
    invoice_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status BOOLEAN DEFAULT TRUE,
    payment_method SMALLINT NOT NULL,
    CONSTRAINT fk_invoice_header_order FOREIGN KEY (id_order) REFERENCES tbl_order (id_order),
    CONSTRAINT fk_invoice_payment_method FOREIGN KEY (payment_method) REFERENCES tbl_payment_method (id_payment_method)
);

-- Inserts de ejemplo para tbl_invoice_header (sin id_client, con invoice_datetime unificado)
INSERT INTO tbl_invoice_header (id_invoice, id_order, invoice_datetime, status, payment_method) VALUES
(1, 1, '2025-06-10 10:05:00', TRUE, 1),
(2, 3, '2025-06-12 12:50:00', TRUE, 2),
(3, 5, '2025-06-14 15:20:00', TRUE, 3),
(4, 7, '2025-06-16 17:50:00', TRUE, 1),
(5, 8, '2025-06-17 18:05:00', TRUE, 2);

-- Tabla de detalles de facturas
CREATE TABLE tbl_invoice_detail (
    id_invoice INTEGER NOT NULL,
    id_product INTEGER NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL CHECK (quantity >= 1),
    discount DECIMAL(10, 2) DEFAULT 0,
    tax DECIMAL(10, 2) NOT NULL CHECK (tax >= 0),
    gross_amount DECIMAL(12, 2) NOT NULL CHECK (gross_amount > 0),
    net_amount DECIMAL(12, 2) NOT NULL CHECK (net_amount > 0),
    PRIMARY KEY (id_invoice, id_product),
    CONSTRAINT fk_invoice_detail_invoice FOREIGN KEY (id_invoice) REFERENCES tbl_invoice_header (id_invoice),
    CONSTRAINT fk_invoice_detail_product FOREIGN KEY (id_product) REFERENCES tbl_product (id_product)
);

-- Inserts de ejemplo para tbl_invoice_detail
INSERT INTO tbl_invoice_detail (id_invoice, id_product, quantity, discount, tax, gross_amount, net_amount) VALUES
(1, 1, 2, 0, 13300.00, 70000.00, 83300.00),
(1, 4, 10, 0, 4750.00, 25000.00, 29750.00),
(2, 2, 1, 0, 6080.00, 32000.00, 38080.00),
(2, 8, 5, 0, 1900.00, 10000.00, 11900.00),
(3, 9, 5, 0, 7600.00, 40000.00, 47600.00),
(3, 10, 1, 0, 7030.00, 37000.00, 44030.00),
(4, 8, 4, 0, 1520.00, 8000.00, 9520.00),
(5, 3, 2, 0, 1900.00, 10000.00, 11900.00);

-- =====================================================
-- VISTAS PARA DATOS CALCULADOS
-- =====================================================

-- CAMBIO 13: Vista para reemplazar el campo total eliminado de tbl_order
-- BENEFICIO: Garantiza que el total siempre esté sincronizado con los detalles
-- USO: SELECT * FROM vw_order_totals WHERE id_order = 1;
CREATE VIEW vw_order_totals AS
SELECT 
    o.id_order,
    o.id_client,
    o.created_at,
    o.status,
    COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
FROM tbl_order o
LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
GROUP BY o.id_order, o.id_client, o.created_at, o.status;

-- CAMBIO 14: Vista para calcular totales de facturas
CREATE VIEW vw_invoice_totals AS
SELECT 
    ih.id_invoice,
    ih.id_order,
    ih.invoice_datetime,
    ih.status,
    ih.payment_method,
    COALESCE(SUM(id.net_amount), 0) AS total
FROM tbl_invoice_header ih
LEFT JOIN tbl_invoice_detail id ON ih.id_invoice = id.id_invoice
GROUP BY ih.id_invoice, ih.id_order, ih.invoice_datetime, ih.status, ih.payment_method;

-- CAMBIO 15: Vista para reemplazar el campo email eliminado de tbl_client
-- BENEFICIO: Proporciona acceso fácil a toda la información del cliente incluyendo email
-- USO: SELECT * FROM vw_client_info WHERE id_client = 1;
CREATE VIEW vw_client_info AS
SELECT 
    c.id_client,
    c.id_user,
    u.first_name,
    u.last_name,
    u.email,
    c.phone,
    c.address,
    u.role_id
FROM tbl_client c
INNER JOIN tbl_user u ON c.id_user = u.id_user;

-- CAMBIO 16: Vista para obtener id_client desde facturas (reemplaza campo eliminado)
-- USO: SELECT * FROM vw_invoice_client WHERE id_invoice = 1;
CREATE VIEW vw_invoice_client AS
SELECT 
    ih.id_invoice,
    ih.id_order,
    o.id_client,
    ih.invoice_datetime,
    ih.status,
    ih.payment_method
FROM tbl_invoice_header ih
INNER JOIN tbl_order o ON ih.id_order = o.id_order;

-- =====================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

-- CAMBIO 17: Índices en claves foráneas para mejorar performance de JOINs
CREATE INDEX idx_client_user ON tbl_client(id_user);
CREATE INDEX idx_member_user ON tbl_member(id_user);
CREATE INDEX idx_order_client ON tbl_order(id_client);
CREATE INDEX idx_order_detail_order ON tbl_order_detail(id_order);
CREATE INDEX idx_order_detail_product ON tbl_order_detail(id_product);
CREATE INDEX idx_invoice_order ON tbl_invoice_header(id_order);
CREATE INDEX idx_purchase_order_supplier ON tbl_purchase_order(id_supplier);
CREATE INDEX idx_catalog_type ON tbl_catalog(type);
CREATE INDEX idx_user_role ON tbl_user(role_id);

-- Índices en campos de búsqueda frecuente
CREATE INDEX idx_user_email ON tbl_user(email);
CREATE INDEX idx_order_created_at ON tbl_order(created_at);
CREATE INDEX idx_appointment_scheduled_date ON tbl_appointment(scheduled_date);
CREATE INDEX idx_product_name ON tbl_product(product_name);

-- =====================================================
-- TRIGGERS Y FUNCIONES
-- =====================================================

-- CAMBIO 18: Función para actualizar timestamp automáticamente
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para actualizar updated_at en tbl_product
CREATE TRIGGER trg_product_updated_at
BEFORE UPDATE ON tbl_product
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- Trigger para actualizar updated_at en tbl_user
CREATE TRIGGER trg_user_updated_at
BEFORE UPDATE ON tbl_user
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

-- =====================================================
-- COMENTARIOS EN TABLAS Y COLUMNAS
-- =====================================================

COMMENT ON TABLE tbl_role IS 'Tabla de roles de usuario del sistema';
COMMENT ON TABLE tbl_status IS 'Tabla genérica de estados para diferentes entidades';
COMMENT ON TABLE tbl_payment_method IS 'Métodos de pago disponibles';
COMMENT ON TABLE tbl_catalog_type IS 'Tipos de catálogos disponibles';
COMMENT ON TABLE tbl_user IS 'Usuarios del sistema (administradores, miembros y clientes)';
COMMENT ON TABLE tbl_client IS 'Información específica de clientes (email se obtiene desde tbl_user)';
COMMENT ON TABLE tbl_member IS 'Información específica de miembros del equipo';
COMMENT ON VIEW vw_order_totals IS 'Vista que calcula automáticamente los totales de pedidos';
COMMENT ON VIEW vw_invoice_totals IS 'Vista que calcula automáticamente los totales de facturas';
COMMENT ON VIEW vw_client_info IS 'Vista que combina información de clientes y usuarios incluyendo email';
COMMENT ON VIEW vw_invoice_client IS 'Vista que obtiene id_client desde facturas mediante tbl_order';