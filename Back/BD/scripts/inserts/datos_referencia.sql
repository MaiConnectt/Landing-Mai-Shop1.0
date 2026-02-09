-- =====================================================
-- DATOS DE REFERENCIA (SEED DATA)
-- Descripción: Datos iniciales necesarios para el funcionamiento del sistema
-- =====================================================

-- =====================================================
-- ROLES
-- =====================================================

INSERT INTO tbl_role (id_role, role_name, description) VALUES
(1, 'Administrador', 'Acceso completo al sistema, gestión de usuarios y configuración'),
(2, 'Miembro', 'Miembro del equipo, puede gestionar pedidos y catálogos'),
(3, 'Cliente', 'Cliente del negocio, puede realizar pedidos y ver su historial')
ON CONFLICT (id_role) DO NOTHING;

-- =====================================================
-- ESTADOS
-- =====================================================

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
(14, 'payment_proof', 2, 'Rechazado', 'Comprobante rechazado')
ON CONFLICT (id_status) DO NOTHING;

-- =====================================================
-- MÉTODOS DE PAGO
-- =====================================================

INSERT INTO tbl_payment_method (id_payment_method, method_name, description, is_active) VALUES
(1, 'Efectivo', 'Pago en efectivo al momento de la entrega', true),
(2, 'Transferencia Bancaria', 'Transferencia electrónica a cuenta bancaria', true),
(3, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito o débito', true),
(4, 'Nequi', 'Pago mediante aplicación Nequi', true),
(5, 'Daviplata', 'Pago mediante aplicación Daviplata', true)
ON CONFLICT (id_payment_method) DO NOTHING;

-- =====================================================
-- TIPOS DE CATÁLOGO
-- =====================================================

INSERT INTO tbl_catalog_type (id_catalog_type, type_name, description) VALUES
(1, 'Tortas', 'Catálogo de tortas y pasteles'),
(2, 'Galletas', 'Catálogo de galletas y cookies'),
(3, 'Postres', 'Catálogo de postres variados'),
(4, 'Panes', 'Catálogo de panes artesanales'),
(5, 'Especiales', 'Productos especiales y personalizados')
ON CONFLICT (id_catalog_type) DO NOTHING;

-- =====================================================
-- NOTAS
-- =====================================================
-- Este script usa ON CONFLICT DO NOTHING para evitar errores
-- si los datos ya existen en la base de datos.
-- Es seguro ejecutarlo múltiples veces.
