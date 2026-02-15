<?php
/**
 * Generación de hash REAL para admin123
 * Usando password_hash() de PHP
 */

$password = 'admin123';

// Generar hash con PASSWORD_BCRYPT
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "=== GENERACIÓN DE HASH VERIFICADO ===\n\n";
echo "Password: $password\n";
echo "Hash generado: $hash\n";
echo "Longitud: " . strlen($hash) . " caracteres\n\n";

// Verificar que el hash funciona
if (password_verify($password, $hash)) {
    echo "✓ VERIFICACIÓN EXITOSA: password_verify() confirma que el hash es válido\n\n";

    // Generar hash para vendedor también
    $vendor_hash = password_hash('vendedor123', PASSWORD_BCRYPT);
    echo "=== HASH VENDEDOR ===\n";
    echo "Password: vendedor123\n";
    echo "Hash: $vendor_hash\n";
    echo "Longitud: " . strlen($vendor_hash) . " caracteres\n\n";

    if (password_verify('vendedor123', $vendor_hash)) {
        echo "✓ Hash vendedor también verificado\n\n";
    }

    echo "=== SQL INSERT STATEMENT ===\n";
    echo "INSERT INTO tbl_usuario (nombre, apellido, email, contrasena, id_rol) VALUES\n";
    echo "('Admin', 'Sistema', 'admin@maishop.com', '$hash', 1),\n";
    echo "('Juan', 'Pérez', 'vendedor@maishop.com', '$vendor_hash', 2);\n";

} else {
    echo "✗ ERROR: El hash generado no verifica (esto no debería pasar)\n";
}
