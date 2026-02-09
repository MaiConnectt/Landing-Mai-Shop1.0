-- Actualizar el valor por defecto para nuevos miembros
ALTER TABLE tbl_member ALTER COLUMN commission_percentage SET DEFAULT 0.5;

-- Actualizar todos los miembros existentes al 0.5%
UPDATE tbl_member SET commission_percentage = 0.5;

-- Confirmación
SELECT id_member, commission_percentage FROM tbl_member;
