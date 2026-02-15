<?php
/**
 * Generate FIXED hashes for SQL file
 * These will be pasted directly into the SQL
 */

// Generate fixed hashes
$admin_hash = password_hash('admin123', PASSWORD_BCRYPT);
$vendor_hash = password_hash('vendedor123', PASSWORD_BCRYPT);

// Verify they work
$admin_verified = password_verify('admin123', $admin_hash);
$vendor_verified = password_verify('vendedor123', $vendor_hash);

echo "=== FIXED HASHES FOR SQL ===\n\n";

echo "ADMIN (admin123):\n";
echo "Hash: $admin_hash\n";
echo "Length: " . strlen($admin_hash) . " chars\n";
echo "Verified: " . ($admin_verified ? "✓ YES" : "✗ NO") . "\n\n";

echo "VENDOR (vendedor123):\n";
echo "Hash: $vendor_hash\n";
echo "Length: " . strlen($vendor_hash) . " chars\n";
echo "Verified: " . ($vendor_verified ? "✓ YES" : "✗ NO") . "\n\n";

if ($admin_verified && $vendor_verified) {
    echo "=== COPY THIS TO SQL (UPSERT) ===\n\n";
    echo "-- Insert or update default users (idempotent)\n";
    echo "INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES\n";
    echo "('Admin', 'Sistema', 'admin@maishop.com', '$admin_hash', 1),\n";
    echo "('Juan', 'Pérez', 'vendedor@maishop.com', '$vendor_hash', 2)\n";
    echo "ON CONFLICT (email) DO UPDATE SET\n";
    echo "    contrasena = EXCLUDED.contrasena,\n";
    echo "    nombre = EXCLUDED.nombre,\n";
    echo "    apellido = EXCLUDED.apellido,\n";
    echo "    id_rol = EXCLUDED.id_rol;\n";
}
