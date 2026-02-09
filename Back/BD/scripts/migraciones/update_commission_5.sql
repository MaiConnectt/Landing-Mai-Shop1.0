-- Actualizar el valor por defecto a 5.0%
ALTER TABLE tbl_member ALTER COLUMN commission_percentage SET DEFAULT 5.0;

-- Actualizar todos los miembros existentes al 5.0%
UPDATE tbl_member SET commission_percentage = 5.0;
