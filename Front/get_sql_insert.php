<?php
// Simple hash extraction
$admin_hash = password_hash('admin123', PASSWORD_BCRYPT);
$vendor_hash = password_hash('vendedor123', PASSWORD_BCRYPT);

// Just output the exact INSERT statement for SQL
echo "-- Verified hashes generated with password_hash()\n";
echo "INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES\n";
echo "('Admin', 'Sistema', 'admin@maishop.com', '$admin_hash', 1),\n";
echo "('Juan', 'Pérez', 'vendedor@maishop.com', '$vendor_hash', 2);";
