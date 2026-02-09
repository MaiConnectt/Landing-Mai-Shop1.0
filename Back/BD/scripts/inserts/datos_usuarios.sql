-- =====================================================
-- USUARIOS INICIALES
-- Descripción: Usuarios de prueba y administrador del sistema
-- =====================================================

-- =====================================================
-- USUARIO ADMINISTRADOR
-- =====================================================
-- Email: admin@maishop.com
-- Password: Admin@2026!

INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id) VALUES
(1, 'Administrador', 'Sistema', 'admin@maishop.com', '$2y$12$.qvTYLAsLFrqeqD6dDwcu.zP/Qpc8Q2g0HAA5ZW64cKyz4AzKT8Um', 1)
ON CONFLICT (id_user) DO NOTHING;

-- =====================================================
-- USUARIO DEMO (MIEMBRO)
-- =====================================================
-- Email: usuario@maishop.com
-- Password: User@2026!

INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id) VALUES
(2, 'Usuario', 'Demo', 'usuario@maishop.com', '$2y$10$FvaBRZSf1SwZx2DI78NTBe6GUyifIszdR5tJ1Lf3YCBT4LLvMVav2', 2)
ON CONFLICT (id_user) DO NOTHING;

-- Crear registro de miembro para el usuario regular
INSERT INTO tbl_member (id_member, id_user, commission, hire_date) VALUES
(1, 2, 0.00, CURRENT_DATE)
ON CONFLICT (id_member) DO NOTHING;

-- =====================================================
-- INFORMACIÓN DE CREDENCIALES
-- =====================================================

-- IMPORTANTE: Cambiar estas contraseñas en producción
-- 
-- Credenciales de Administrador:
--   Email: admin@maishop.com
--   Password: Admin@2026!
--
-- Credenciales de Usuario Demo:
--   Email: usuario@maishop.com
--   Password: User@2026!
--
-- Para crear nuevos usuarios, usar password_hash() en PHP:
-- $hash = password_hash('tu_contraseña', PASSWORD_DEFAULT);

-- =====================================================
-- NOTAS
-- =====================================================
-- Este script usa ON CONFLICT DO NOTHING para evitar errores
-- si los usuarios ya existen en la base de datos.
-- Es seguro ejecutarlo múltiples veces.
