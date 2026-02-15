<?php
/**
 * GENERACIÓN FINAL DE HASHES VERIFICADOS
 * Este script genera hashes y los prueba en la BD actual
 */

require_once __DIR__ . '/conexion.php';

$admin_password = 'admin123';
$vendor_password = 'vendedor123';

// Generate hashes
$admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);
$vendor_hash = password_hash($vendor_password, PASSWORD_BCRYPT);

echo "=== HASHES GENERADOS ===\n\n";
echo "Admin Hash:\n$admin_hash\n";
echo "Length: " . strlen($admin_hash) . " chars\n\n";

echo "Vendor Hash:\n$vendor_hash\n";
echo "Length: " . strlen($vendor_hash) . " chars\n\n";

// Verify hashes work
echo "=== VERIFICACIÓN ===\n";
$admin_ok = password_verify($admin_password, $admin_hash);
$vendor_ok = password_verify($vendor_password, $vendor_hash);

echo "Admin verify: " . ($admin_ok ? "✓ PASS" : "✗ FAIL") . "\n";
echo "Vendor verify: " . ($vendor_ok ? "✓ PASS" : "✗ FAIL") . "\n\n";

if ($admin_ok && $vendor_ok) {
    // Update database
    echo "=== ACTUALIZANDO BASE DE DATOS ===\n";

    $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = 'admin@maishop.com'");
    $stmt->execute(['hash' => $admin_hash]);
    echo "✓ Admin updated in DB\n";

    $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = 'vendedor@maishop.com'");
    $stmt->execute(['hash' => $vendor_hash]);
    echo "✓ Vendor updated in DB\n\n";

    // Test login with real DB
    echo "=== PRUEBA CON BD REAL ===\n";
    $stmt = $pdo->prepare("SELECT contrasena FROM tbl_usuario WHERE email = 'admin@maishop.com'");
    $stmt->execute();
    $db_user = $stmt->fetch();

    if (password_verify($admin_password, $db_user['contrasena'])) {
        echo "✓ Login test SUCCESSFUL - admin123 works!\n\n";

        echo "=== COPIAR ESTO AL SQL ===\n";
        echo "INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES\n";
        echo "('Admin', 'Sistema', 'admin@maishop.com', '$admin_hash', 1),\n";
        echo "('Juan', 'Pérez', 'vendedor@maishop.com', '$vendor_hash', 2);\n";
    } else {
        echo "✗ Login test FAILED\n";
    }
}
