<?php
/**
 * SCRIPT FINAL - Generar hashes verificados y actualizar BD
 */

require_once __DIR__ . '/conexion.php';

// Generar hashes
$admin_password = 'admin123';
$vendor_password = 'vendedor123';

$admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);
$vendor_hash = password_hash($vendor_password, PASSWORD_BCRYPT);

// Verificar
$admin_verified = password_verify($admin_password, $admin_hash);
$vendor_verified = password_verify($vendor_password, $vendor_hash);

echo "=== HASHES GENERADOS Y VERIFICADOS ===\n\n";

echo "ADMIN:\n";
echo "Hash: $admin_hash\n";
echo "Length: " . strlen($admin_hash) . " chars\n";
echo "Verified: " . ($admin_verified ? "✓ YES" : "✗ NO") . "\n\n";

echo "VENDOR:\n";
echo "Hash: $vendor_hash\n";
echo "Length: " . strlen($vendor_hash) . " chars\n";
echo "Verified: " . ($vendor_verified ? "✓ YES" : "✗ NO") . "\n\n";

if ($admin_verified && $vendor_verified) {
    echo "=== ACTUALIZANDO BASE DE DATOS ===\n";

    try {
        // Update admin
        $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = 'admin@maishop.com'");
        $stmt->execute(['hash' => $admin_hash]);
        echo "✓ Admin password updated\n";

        // Update vendor
        $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = 'vendedor@maishop.com'");
        $stmt->execute(['hash' => $vendor_hash]);
        echo "✓ Vendor password updated\n\n";

        echo "=== SQL PARA ARCHIVO .sql ===\n";
        echo "INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES\n";
        echo "('Admin', 'Sistema', 'admin@maishop.com', '$admin_hash', 1),\n";
        echo "('Juan', 'Pérez', 'vendedor@maishop.com', '$vendor_hash', 2);\n";

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
