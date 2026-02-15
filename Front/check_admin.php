<?php
require_once __DIR__ . '/conexion.php';

echo "=== VERIFICACIÓN DETALLADA ADMIN ===\n\n";

try {
    $stmt = $pdo->prepare("SELECT * FROM tbl_usuario WHERE email = 'admin@maishop.com'");
    $stmt->execute();
    $admin = $stmt->fetch();

    if (!$admin) {
        echo "❌ ERROR: Usuario admin no existe en la BD\n";
        exit(1);
    }

    echo "✓ Usuario encontrado\n";
    echo "ID: {$admin['id_usuario']}\n";
    echo "Nombre: {$admin['nombre']} {$admin['apellido']}\n";
    echo "Email: {$admin['email']}\n";
    echo "Rol ID: {$admin['id_rol']}\n\n";

    echo "Hash almacenado:\n";
    echo "{$admin['contrasena']}\n\n";
    echo "Longitud: " . strlen($admin['contrasena']) . " caracteres\n\n";

    // Test passwords
    $test_passwords = ['admin123', 'Admin123', 'admin', '123'];

    echo "=== PRUEBAS DE CONTRASEÑA ===\n";
    foreach ($test_passwords as $pwd) {
        $result = password_verify($pwd, $admin['contrasena']);
        $icon = $result ? "✓" : "✗";
        echo "$icon password_verify('$pwd'): " . ($result ? "MATCH" : "NO MATCH") . "\n";
    }

    // Check column type
    echo "\n=== ESQUEMA DE COLUMNA ===\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, character_maximum_length 
        FROM information_schema.columns 
        WHERE table_name = 'tbl_usuario' 
        AND column_name = 'contrasena'
    ");
    $col_info = $stmt->fetch();
    echo "Tipo: {$col_info['data_type']}\n";
    echo "Longitud máxima: {$col_info['character_maximum_length']}\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
