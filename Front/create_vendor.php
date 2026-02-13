<?php
/**
 * Script para crear un usuario vendedor de prueba
 * Ejecutar: php create_vendor.php
 */

require_once __DIR__ . '/conexion.php';

// Datos del vendedor
$nombre = 'Juan';
$apellido = 'Pérez';
$email = 'vendedor@maishop.com';
$password = 'vendedor123';
$porcentaje_comision = 15.00;

echo "=== CREACIÓN DE VENDEDOR DE PRUEBA ===\n\n";

try {
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id_usuario FROM tbl_usuario WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        echo "⚠️  El usuario $email ya existe\n";
        echo "Actualizando contraseña...\n\n";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = :email");
        $stmt->execute(['hash' => $hash, 'email' => $email]);

        echo "✓ Contraseña actualizada\n";
    } else {
        // Crear usuario
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol)
            VALUES (:nombre, :apellido, :email, :contrasena, 2)
            RETURNING id_usuario
        ");

        $stmt->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'contrasena' => $hash
        ]);

        $user = $stmt->fetch();
        $id_usuario = $user['id_usuario'];

        echo "✓ Usuario creado (ID: $id_usuario)\n";

        // Crear miembro
        $stmt = $pdo->prepare("
            INSERT INTO tbl_miembro (id_usuario, porcentaje_comision, estado)
            VALUES (:id_usuario, :porcentaje_comision, 'activo')
            RETURNING id_miembro
        ");

        $stmt->execute([
            'id_usuario' => $id_usuario,
            'porcentaje_comision' => $porcentaje_comision
        ]);

        $member = $stmt->fetch();
        $id_miembro = $member['id_miembro'];

        echo "✓ Miembro creado (ID: $id_miembro)\n";
    }

    echo "\n=== CREDENCIALES DEL VENDEDOR ===\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "Comisión: $porcentaje_comision%\n";
    echo "\n✓ Vendedor listo para usar\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
