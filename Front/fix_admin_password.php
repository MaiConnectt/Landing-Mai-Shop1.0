<?php
/**
 * Fix admin password - regenerate hash and update in DB
 */

require_once __DIR__ . '/conexion.php';

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "=== REGENERACIÓN DE CONTRASEÑA ADMIN ===\n\n";
echo "Password: $password\n";
echo "New Hash: $hash\n\n";

// Verify the hash works
if (password_verify($password, $hash)) {
    echo "✓ Hash verificado correctamente\n\n";

    try {
        // Update admin password
        $stmt = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = 'admin@maishop.com'");
        $stmt->execute(['hash' => $hash]);

        echo "✓ Contraseña del admin actualizada\n\n";

        // Verify update
        $stmt = $pdo->prepare("SELECT contrasena FROM tbl_usuario WHERE email = 'admin@maishop.com'");
        $stmt->execute();
        $user = $stmt->fetch();

        if (password_verify($password, $user['contrasena'])) {
            echo "✓ Verificación final: Contraseña funciona correctamente\n";
            echo "\nCredenciales:\n";
            echo "  Email: admin@maishop.com\n";
            echo "  Password: $password\n";
        } else {
            echo "✗ ERROR: La verificación final falló\n";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ ERROR: El hash generado no verifica\n";
}
