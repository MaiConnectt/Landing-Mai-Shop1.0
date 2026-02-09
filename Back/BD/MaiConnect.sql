-- Eliminar vistas primero (CASCADE para dependencias)
DROP VIEW IF EXISTS vw_invoice_client CASCADE;
DROP VIEW IF EXISTS vw_invoice_totals CASCADE;
DROP VIEW IF EXISTS vw_payment_proof_details CASCADE;
DROP VIEW IF EXISTS vw_member_info CASCADE;
DROP VIEW IF EXISTS vw_client_info CASCADE;
DROP VIEW IF EXISTS vw_order_totals CASCADE;
DROP VIEW IF EXISTS vw_seller_commissions CASCADE;
DROP VIEW IF EXISTS vw_seller_pending_commissions CASCADE;

-- Eliminar constraints circulares antes de eliminar tablas
ALTER TABLE IF EXISTS tbl_order DROP CONSTRAINT IF EXISTS fk_order_commission_payout;
ALTER TABLE IF EXISTS tbl_order DROP CONSTRAINT IF EXISTS tbl_order_commission_payout_id_fkey;

-- Eliminar tablas en orden inverso de dependencias
-- Nivel 4: Tablas que dependen de invoice_header y order
DROP TABLE IF EXISTS tbl_invoice_detail;

-- Nivel 3: Tablas que dependen de order y otras
DROP TABLE IF EXISTS tbl_invoice_header;
DROP TABLE IF EXISTS tbl_payment_proof;
DROP TABLE IF EXISTS tbl_order_detail;

-- Nivel 2: Tablas que dependen de client, member, supplier, product
DROP TABLE IF EXISTS tbl_order;
DROP TABLE IF EXISTS tbl_purchase_order_detail;
DROP TABLE IF EXISTS tbl_appointment;
DROP TABLE IF EXISTS tbl_catalog_product;

-- Nivel 1: Tablas que dependen de user, catalog, supplier
DROP TABLE IF EXISTS tbl_purchase_order;
DROP TABLE IF EXISTS tbl_job_request;
DROP TABLE IF EXISTS tbl_member;
DROP TABLE IF EXISTS tbl_client;

-- Nivel 0: Tablas base
DROP TABLE IF EXISTS tbl_supplier;
DROP TABLE IF EXISTS tbl_catalog;
DROP TABLE IF EXISTS tbl_product;
DROP TABLE IF EXISTS tbl_user;

-- Tablas de referencia
DROP TABLE IF EXISTS tbl_role;
DROP TABLE IF EXISTS tbl_status;
DROP TABLE IF EXISTS tbl_payment_method;
DROP TABLE IF EXISTS tbl_catalog_type;


-- TABLAS DE REFERENCIA

CREATE TABLE tbl_role (
    id_role SMALLINT PRIMARY KEY NOT NULL,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE tbl_status (
    id_status SMALLINT PRIMARY KEY NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    status_code SMALLINT NOT NULL,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    UNIQUE (entity_type, status_code)
);

CREATE TABLE tbl_payment_method (
    id_payment_method SMALLINT PRIMARY KEY NOT NULL,
    method_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE tbl_catalog_type (
    id_catalog_type SMALLINT PRIMARY KEY NOT NULL,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
);


-- TABLAS PRINCIPALES

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

CREATE TABLE tbl_client (
    id_client INTEGER PRIMARY KEY NOT NULL,
    id_user INTEGER NOT NULL UNIQUE,
    address VARCHAR(150) NOT NULL,
    phone VARCHAR(12) NOT NULL CHECK (phone ~ '^[0-9]{7,12}$'),
    CONSTRAINT fk_client_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user)
);

CREATE TABLE tbl_member (
    id_member INTEGER PRIMARY KEY NOT NULL,
    id_user INTEGER NOT NULL UNIQUE,
    commission DECIMAL(7, 2) NOT NULL CHECK (commission >= 0), -- Campo legacy, se usará commission_percentage
    commission_percentage DECIMAL(5,2) DEFAULT 5.00 CHECK (commission_percentage >= 0 AND commission_percentage <= 100),
    university VARCHAR(200),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    phone VARCHAR(15),
    hire_date DATE DEFAULT CURRENT_DATE,
    CONSTRAINT fk_member_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user)
);

CREATE TABLE tbl_catalog (
    id_catalog INTEGER PRIMARY KEY NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type SMALLINT NOT NULL,
    CONSTRAINT fk_catalog_type FOREIGN KEY (type) REFERENCES tbl_catalog_type (id_catalog_type)
);

CREATE TABLE tbl_product (
    id_product INTEGER PRIMARY KEY NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL CHECK (price >= 0),
    description TEXT,
    stock INTEGER NOT NULL CHECK (stock >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tbl_catalog_product (
    id_catalog_product INTEGER PRIMARY KEY NOT NULL,
    id_catalog INTEGER NOT NULL,
    id_product INTEGER NOT NULL,
    CONSTRAINT fk_catalog_product_catalog FOREIGN KEY (id_catalog) REFERENCES tbl_catalog (id_catalog),
    CONSTRAINT fk_catalog_product_product FOREIGN KEY (id_product) REFERENCES tbl_product (id_product)
);

CREATE TABLE tbl_job_request (
    id_job_request INTEGER PRIMARY KEY NOT NULL,
    id_user INTEGER NOT NULL,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_request_user FOREIGN KEY (id_user) REFERENCES tbl_user (id_user)
);

CREATE TABLE tbl_appointment (
    id_appointment INTEGER PRIMARY KEY NOT NULL,
    id_client INTEGER NOT NULL,
    scheduled_date TIMESTAMP NOT NULL,
    notes TEXT NOT NULL,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    CONSTRAINT fk_appointment_client FOREIGN KEY (id_client) REFERENCES tbl_client (id_client)
);

CREATE TABLE tbl_order (
    id_order INTEGER PRIMARY KEY NOT NULL,
    id_client INTEGER NOT NULL,
    id_member INTEGER,
    seller_id INTEGER, -- Vendedor asignado (nuevo sistema)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    commission_amount DECIMAL(10, 2) DEFAULT 0 CHECK (commission_amount >= 0),
    commission_payout_id INTEGER,
    CONSTRAINT fk_order_client FOREIGN KEY (id_client) REFERENCES tbl_client (id_client),
    CONSTRAINT fk_order_member FOREIGN KEY (id_member) REFERENCES tbl_member (id_member),
    CONSTRAINT fk_order_seller FOREIGN KEY (seller_id) REFERENCES tbl_member (id_member)
);

CREATE TABLE tbl_order_detail (
    id_order_detail INTEGER PRIMARY KEY NOT NULL,
    id_order INTEGER NOT NULL,
    id_product INTEGER NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL CHECK (quantity >= 1),
    unit_price DECIMAL(10, 2) NOT NULL CHECK (unit_price > 0),
    CONSTRAINT fk_order_detail_order FOREIGN KEY (id_order) REFERENCES tbl_order (id_order),
    CONSTRAINT fk_order_detail_product FOREIGN KEY (id_product) REFERENCES tbl_product (id_product)
);

CREATE TABLE tbl_payment_proof (
    id_payment_proof INTEGER PRIMARY KEY GENERATED ALWAYS AS IDENTITY,
    id_order INTEGER, -- Opcional si es pago de comision
    payment_method SMALLINT NOT NULL, -- Defaults to transfer usually? Or allow null?
    proof_image_path VARCHAR(255),
    amount DECIMAL(10, 2) NOT NULL CHECK (amount > 0),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status SMALLINT NOT NULL DEFAULT 0 CHECK (status IN (0, 1, 2)),
    reviewed_by INTEGER,
    reviewed_at TIMESTAMP,
    notes TEXT,
    team_member_id INTEGER, -- Para pagos de comisiones
    CONSTRAINT fk_payment_proof_order FOREIGN KEY (id_order) REFERENCES tbl_order (id_order),
    CONSTRAINT fk_payment_proof_method FOREIGN KEY (payment_method) REFERENCES tbl_payment_method (id_payment_method),
    CONSTRAINT fk_payment_proof_reviewer FOREIGN KEY (reviewed_by) REFERENCES tbl_user (id_user),
    CONSTRAINT fk_payment_proof_teammember FOREIGN KEY (team_member_id) REFERENCES tbl_member (id_member)
);


-- VISTAS PARA DATOS CALCULADOS


CREATE VIEW vw_order_totals AS
SELECT 
    o.id_order,
    o.id_client,
    COALESCE(o.seller_id, o.id_member) as seller_id, -- Unificar vendedor
    o.created_at,
    o.status,
    COALESCE(SUM(od.quantity * od.unit_price), 0) AS total
FROM tbl_order o
LEFT JOIN tbl_order_detail od ON o.id_order = od.id_order
GROUP BY o.id_order, o.id_client, o.seller_id, o.id_member, o.created_at, o.status;

CREATE VIEW vw_client_info AS
SELECT 
    c.id_client,
    c.phone,
    c.address,
    u.id_user,
    u.first_name,
    u.last_name,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as full_name
FROM tbl_client c
INNER JOIN tbl_user u ON c.id_user = u.id_user;

CREATE VIEW vw_member_info AS
SELECT 
    m.id_member,
    m.status,
    m.commission_percentage,
    u.id_user,
    u.first_name,
    u.last_name,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as full_name
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user;

CREATE VIEW vw_seller_commissions AS
SELECT 
    m.id_member,
    u.first_name,
    u.last_name,
    u.email,
    CONCAT(u.first_name, ' ', u.last_name) as seller_name,
    m.commission_percentage,
    m.phone,
    m.university,
    m.hire_date,
    COUNT(CASE WHEN o.status != 3 THEN 1 END) as total_orders,
    -- Total sales from completed orders (status = 2)
    COALESCE(SUM(CASE WHEN o.status = 2 THEN ot.total ELSE 0 END), 0) as total_sales,
    -- Total commissions earned from completed orders
    COALESCE(SUM(CASE WHEN o.status = 2 THEN o.commission_amount ELSE 0 END), 0) as commissions_earned,
    -- Total paid (where commission_payout_id is not null)
    COALESCE(SUM(CASE WHEN o.status = 2 AND o.commission_payout_id IS NOT NULL THEN o.commission_amount ELSE 0 END), 0) as total_paid,
    -- Balance pending (commissions earned but not yet paid)
    COALESCE(SUM(CASE WHEN o.status = 2 AND o.commission_payout_id IS NULL THEN o.commission_amount ELSE 0 END), 0) as balance_pending
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.id_member
LEFT JOIN vw_order_totals ot ON o.id_order = ot.id_order
GROUP BY m.id_member, u.first_name, u.last_name, u.email, m.commission_percentage, m.phone, m.university, m.hire_date;

-- Vista para mostrar vendedores con comisiones pendientes de pago (para admin)
CREATE VIEW vw_seller_pending_commissions AS
SELECT 
    m.id_member,
    u.first_name,
    u.last_name,
    m.commission_percentage,
    -- Contar pedidos completados sin pagar
    COUNT(o.id_order) as pending_order_count,
    -- Suma de comisiones pendientes (completados sin commission_payout_id)
    COALESCE(SUM(o.commission_amount), 0) as pending_amount
FROM tbl_member m
INNER JOIN tbl_user u ON m.id_user = u.id_user
LEFT JOIN tbl_order o ON m.id_member = o.id_member
    AND o.status = 2  -- Solo pedidos completados
    AND o.commission_payout_id IS NULL  -- Sin pago registrado
WHERE u.role_id = 2  -- Solo vendedores
GROUP BY m.id_member, u.first_name, u.last_name, m.commission_percentage
HAVING COUNT(o.id_order) > 0  -- Solo mostrar si tienen pedidos pendientes
ORDER BY pending_amount DESC;


CREATE VIEW vw_payment_proof_details AS
SELECT 
    pp.id_payment_proof,
    pp.team_member_id as id_member,
    pp.amount,
    pp.uploaded_at as payment_date,
    pp.proof_image_path as proof_image,
    pp.notes,
    CASE 
        WHEN pp.status = 0 THEN 'pending'
        WHEN pp.status = 1 THEN 'approved'
        WHEN pp.status = 2 THEN 'paid'
        ELSE 'unknown'
    END as payment_status,
    pp.uploaded_at as created_at,
    CONCAT(u.first_name, ' ', u.last_name) as member_name,
    u.email as member_email,
    m.commission_percentage
FROM tbl_payment_proof pp
INNER JOIN tbl_member m ON pp.team_member_id = m.id_member
INNER JOIN tbl_user u ON m.id_user = u.id_user;

-- ÍNDICES PARA OPTIMIZACIÓN

CREATE INDEX idx_client_user ON tbl_client(id_user);
CREATE INDEX idx_member_user ON tbl_member(id_user);
CREATE INDEX idx_order_client ON tbl_order(id_client);
CREATE INDEX idx_order_member ON tbl_order(id_member);
CREATE INDEX idx_order_detail_order ON tbl_order_detail(id_order);
CREATE INDEX idx_order_detail_product ON tbl_order_detail(id_product);
CREATE INDEX idx_payment_proof_order ON tbl_payment_proof(id_order);
CREATE INDEX idx_payment_proof_status ON tbl_payment_proof(status);
CREATE INDEX idx_catalog_type ON tbl_catalog(type);
CREATE INDEX idx_user_role ON tbl_user(role_id);
CREATE INDEX idx_user_email ON tbl_user(email);
CREATE INDEX idx_order_created_at ON tbl_order(created_at);
CREATE INDEX idx_appointment_scheduled_date ON tbl_appointment(scheduled_date);
CREATE INDEX idx_product_name ON tbl_product(product_name);

-- =====================================================
-- TRIGGERS Y FUNCIONES
-- =====================================================

CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_product_updated_at
BEFORE UPDATE ON tbl_product
FOR EACH ROW EXECUTE FUNCTION update_timestamp();

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
COMMENT ON TABLE tbl_user IS 'Usuarios del sistema (administradores y miembros)';
COMMENT ON TABLE tbl_client IS 'Información específica de clientes (email se obtiene desde tbl_user)';
COMMENT ON TABLE tbl_member IS 'Información específica de miembros del equipo';
COMMENT ON TABLE tbl_order IS 'Pedidos realizados por clientes, opcionalmente gestionados por miembros del equipo';
COMMENT ON TABLE tbl_payment_proof IS 'Comprobantes de pago (pantallazos de transferencias) subidos por miembros del equipo';
COMMENT ON VIEW vw_order_totals IS 'Vista que calcula automáticamente los totales de pedidos';
COMMENT ON VIEW vw_client_info IS 'Vista que combina información de clientes y usuarios incluyendo email';
COMMENT ON VIEW vw_member_info IS 'Vista que combina información de miembros del equipo y usuarios';
COMMENT ON VIEW vw_payment_proof_details IS 'Vista detallada de comprobantes de pago con información relacionada';

-- =====================================================
-- DATOS INICIaALES (SEED DATA)
-- =====================================================

-- Insertar roles
INSERT INTO tbl_role (id_role, role_name, description) VALUES
(1, 'Administrador', 'Acceso completo al sistema, gestión de usuarios y configuración'),
(2, 'Miembro', 'Miembro del equipo, puede gestionar pedidos y catálogos'),
(3, 'Cliente', 'Cliente del negocio, puede realizar pedidos y ver su historial');

-- Insertar estados para diferentes entidades
INSERT INTO tbl_status (id_status, entity_type, status_code, status_name, description) VALUES
-- Estados para pedidos (order)
(1, 'order', 0, 'Pendiente', 'Pedido recibido, pendiente de procesamiento'),
(2, 'order', 1, 'En Proceso', 'Pedido en preparación'),
(3, 'order', 2, 'Completado', 'Pedido completado y entregado'),
(4, 'order', 3, 'Cancelado', 'Pedido cancelado'),

-- Estados para solicitudes de trabajo (job_request)
(5, 'job_request', 0, 'Pendiente', 'Solicitud pendiente de revisión'),
(6, 'job_request', 1, 'Aprobada', 'Solicitud aprobada'),
(7, 'job_request', 2, 'Rechazada', 'Solicitud rechazada'),

-- Estados para citas (appointment)
(8, 'appointment', 0, 'Programada', 'Cita programada'),
(9, 'appointment', 1, 'Confirmada', 'Cita confirmada por el cliente'),
(10, 'appointment', 2, 'Completada', 'Cita realizada'),
(11, 'appointment', 3, 'Cancelada', 'Cita cancelada'),

-- Estados para comprobantes de pago (payment_proof)
(12, 'payment_proof', 0, 'Pendiente', 'Comprobante pendiente de revisión'),
(13, 'payment_proof', 1, 'Aprobado', 'Comprobante aprobado'),
(14, 'payment_proof', 2, 'Rechazado', 'Comprobante rechazado');

-- Insertar métodos de pago
INSERT INTO tbl_payment_method (id_payment_method, method_name, description, is_active) VALUES
(1, 'Efectivo', 'Pago en efectivo al momento de la entrega', true),
(2, 'Transferencia Bancaria', 'Transferencia electrónica a cuenta bancaria', true),
(3, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito o débito', true),
(4, 'Nequi', 'Pago mediante aplicación Nequi', true),
(5, 'Daviplata', 'Pago mediante aplicación Daviplata', true);

-- Insertar tipos de catálogo
INSERT INTO tbl_catalog_type (id_catalog_type, type_name, description) VALUES
(1, 'Tortas', 'Catálogo de tortas y pasteles'),
(2, 'Galletas', 'Catálogo de galletas y cookies'),
(3, 'Postres', 'Catálogo de postres variados'),
(4, 'Panes', 'Catálogo de panes artesanales'),
(5, 'Especiales', 'Productos especiales y personalizados');

-- =====================================================
-- USUARIOS INICIALES
-- =====================================================

-- Insertar usuario administrador
-- Email: admin@maishop.com
-- Password: Admin@2026!
INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id) VALUES
(1, 'Administrador', 'Sistema', 'admin@maishop.com', '$2y$12$.qvTYLAsLFrqeqD6dDwcu.zP/Qpc8Q2g0HAA5ZW64cKyz4AzKT8Um', 1);

-- Insertar usuario regular (miembro)
-- Email: usuario@maishop.com
-- Password: User@2026!
INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id) VALUES
(2, 'Carla', 'Sofia', 'usuario@maishop.com', '$2y$10$FvaBRZSf1SwZx2DI78NTBe6GUyifIszdR5tJ1Lf3YCBT4LLvMVav2', 2);

-- Crear registro de miembro para el usuario regular
INSERT INTO tbl_member (id_member, id_user, commission, commission_percentage, hire_date) VALUES
(1, 2, 0.00, 5.00, CURRENT_DATE);

-- =====================================================
-- INFORMACIÓN DE CREDENCIALES INICIALES
-- =====================================================

-- IMPORTANTE: Cambiar estas contraseñas en producción
-- 
-- Credenciales de Administrador:
--   Email: admin@maishop.com
--   Password: Admin@2026!
--
-- Credenciales de Usuario:
--   Email: usuario@maishop.com
--   Password: User@2026!
--
-- Para crear nuevos usuarios, usar la función fn_hash_password:
-- INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id) 
-- VALUES (3, 'Nombre', 'Apellido', 'email@ejemplo.com', fn_hash_password('tu_contraseña'), 3);

-- =====================================================
-- PRODUCTOS INICIALES
-- =====================================================

-- Insertar productos de ejemplo
INSERT INTO tbl_product (id_product, product_name, price, description, stock) VALUES
(1, 'Torta de Chocolate Premium', 85000, 'Torta de tres capas de chocolate húmedo con relleno de ganache de chocolate belga y cobertura de chocolate negro. Perfecta para celebraciones especiales.', 10),
(2, 'Cupcakes de Vainilla (x12)', 45000, 'Set de 12 cupcakes esponjosos de vainilla decorados con buttercream de colores y sprinkles. Ideales para fiestas infantiles.', 20),
(3, 'Cheesecake de Frutos Rojos', 55000, 'Cheesecake suave y cremoso sobre base de galleta, cubierto con una deliciosa salsa de frutos rojos naturales.', 15),
(4, 'Brownies Clásicos (x6)', 28000, 'Brownies de chocolate intenso, húmedos por dentro y crujientes por fuera. Perfectos para acompañar con café.', 25),
(5, 'Galletas Decoradas (x20)', 50000, 'Galletas de mantequilla decoradas con glaseado real. Diseños personalizables según la ocasión.', 30),
(6, 'Torta Red Velvet', 75000, 'Clásica torta red velvet con capas suaves y húmedas, rellena y cubierta con frosting de queso crema. Un clásico irresistible.', 8),
(7, 'Mini Cheesecakes (x6)', 38000, 'Set de 6 mini cheesecakes individuales. Disponibles en varios sabores: natural, frutos rojos, chocolate.', 12),
(8, 'Torta de Zanahoria', 68000, 'Torta húmeda de zanahoria con nueces, canela y especias, cubierta con frosting de queso crema.', 10),
(9, 'Macarons Franceses (x12)', 42000, 'Docena de macarons franceses en variedad de sabores: vainilla, chocolate, frambuesa, limón, pistacho.', 15),
(10, 'Pie de Limón', 48000, 'Pie de limón con base crujiente de galleta, relleno cremoso de limón y merengue italiano tostado.', 10);


-- Agregar constraint de comisiones después de crear tbl_payment_proof
ALTER TABLE tbl_order 
ADD CONSTRAINT fk_order_commission_payout 
FOREIGN KEY (commission_payout_id) REFERENCES tbl_payment_proof (id_payment_proof);

