<?php
/**
 * Script para generar y actualizar el hash del admin
 * Ejecutar: php update_admin_password.php
 */

require_once __DIR__ . '/conexion.php';

// Contraseña deseada
$password = 'admin123';

// Generar hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "=== ACTUALIZACIÓN DE CONTRASEÑA ADMIN ===\n\n";
echo "Contraseña: $password\n";
echo "Hash generado: $hash\n\n";

// Verificar que el hash funciona
if (password_verify($password, $hash)) {
    echo "✓ Hash verificado correctamente\n\n";
} else {
    echo "✗ ERROR: El hash no verifica correctamente\n";
    exit(1);
}

// Actualizar en la base de datos
try {
    $stmt = $pdo->prepare("
        UPDATE tbl_usuario 
        SET contrasena = :hash 
        WHERE email = 'admin@maishop.com'
    ");

    $stmt->execute(['hash' => $hash]);

    if ($stmt->rowCount() > 0) {
        echo "✓ Contraseña actualizada en la base de datos\n";
        echo "\nCredenciales de login:\n";
        echo "  Email: admin@maishop.com\n";
        echo "  Password: $password\n";
    } else {
        echo "✗ No se encontró el usuario admin@maishop.com\n";
        echo "Verifica que el usuario existe en tbl_usuario\n";
    }

} catch (PDOException $e) {
    echo "✗ Error al actualizar: " . $e->getMessage() . "\n";
    exit(1);
}
