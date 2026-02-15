<?php
/**
 * Script para actualizar contraseña del vendedor
 */

require_once __DIR__ . '/conexion.php';

$password = 'vendedor123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "=== ACTUALIZACIÓN DE CONTRASEÑA VENDEDOR ===\n\n";

try {
    $stmt = $pdo->prepare("
        UPDATE tbl_usuario 
        SET contrasena = :hash 
        WHERE email = 'vendedor@maishop.com'
    ");

    $stmt->execute(['hash' => $hash]);

    if ($stmt->rowCount() > 0) {
        echo "✓ Contraseña del vendedor actualizada\n\n";
        echo "Credenciales:\n";
        echo "  Email: vendedor@maishop.com\n";
        echo "  Password: $password\n";
    } else {
        echo "⚠️ No se encontró el usuario vendedor\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
