<?php
require_once __DIR__ . '/conexion.php';

echo "=== VERIFICACIÓN DE USUARIOS Y HASHES ===\n\n";

try {
    // Obtener todos los usuarios
    $stmt = $pdo->query("
        SELECT 
            u.id_usuario, 
            u.nombre, 
            u.apellido, 
            u.email, 
            u.contrasena,
            r.nombre_rol
        FROM tbl_usuario u
        JOIN tbl_rol r ON u.id_rol = r.id_role
        ORDER BY u.id_usuario
    ");

    $users = $stmt->fetchAll();

    foreach ($users as $user) {
        echo "ID: {$user['id_usuario']}\n";
        echo "Nombre: {$user['nombre']} {$user['apellido']}\n";
        echo "Email: {$user['email']}\n";
        echo "Rol: {$user['nombre_rol']}\n";
        echo "Hash: " . substr($user['contrasena'], 0, 20) . "...\n";
        echo "Hash length: " . strlen($user['contrasena']) . " chars\n";

        // Verificar contraseñas conocidas
        $passwords_to_test = ['admin123', 'vendedor123'];
        foreach ($passwords_to_test as $pwd) {
            $result = password_verify($pwd, $user['contrasena']);
            echo "  password_verify('$pwd'): " . ($result ? "✓ MATCH" : "✗ NO MATCH") . "\n";
        }

        echo "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
