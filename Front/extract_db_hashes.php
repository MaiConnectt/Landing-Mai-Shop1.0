<?php
/**
 * Extract current working hashes from database
 */
require_once __DIR__ . '/conexion.php';

echo "=== EXTRAYENDO HASHES DE LA BASE DE DATOS (FUNCIONANDO) ===\n\n";

// Get admin hash
$stmt = $pdo->prepare("SELECT nombre, apellido, email, contrasena, id_rol FROM tbl_usuario WHERE email = 'admin@maishop.com'");
$stmt->execute();
$admin = $stmt->fetch();

// Get vendor hash  
$stmt = $pdo->prepare("SELECT nombre, apellido, email, contrasena, id_rol FROM tbl_usuario WHERE email = 'vendedor@maishop.com'");
$stmt->execute();
$vendor = $stmt->fetch();

echo "Admin Hash (length " . strlen($admin['contrasena']) . "):\n{$admin['contrasena']}\n\n";
echo "Vendor Hash (length " . strlen($vendor['contrasena']) . "):\n{$vendor['contrasena']}\n\n";

// Verify they work
$admin_ok = password_verify('admin123', $admin['contrasena']);
$vendor_ok = password_verify('vendedor123', $vendor['contrasena']);

echo "=== VERIFICACIÓN ===\n";
echo "Admin (admin123): " . ($admin_ok ? "✓ WORKS" : "✗ FAILS") . "\n";
echo "Vendor (vendedor123): " . ($vendor_ok ? "✓ WORKS" : "✗ FAILS") . "\n\n";

if ($admin_ok && $vendor_ok) {
    echo "=== SQL PARA EL ARCHIVO .sql ===\n";
    echo "INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES\n";
    echo "('{$admin['nombre']}', '{$admin['apellido']}', '{$admin['email']}', '{$admin['contrasena']}', {$admin['id_rol']}),\n";
    echo "('{$vendor['nombre']}', '{$vendor['apellido']}', '{$vendor['email']}', '{$vendor['contrasena']}', {$vendor['id_rol']});\n";
}
