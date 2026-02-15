<?php
require_once __DIR__ . '/conexion.php';

$stmt = $pdo->prepare("SELECT contrasena FROM tbl_usuario WHERE email = 'admin@maishop.com'");
$stmt->execute();
$admin = $stmt->fetch();

$password = 'admin123';
$hash = $admin['contrasena'];

echo "Testing password: $password\n";
echo "Hash in DB: $hash\n";
echo "Hash length: " . strlen($hash) . "\n\n";

if (password_verify($password, $hash)) {
    echo "SUCCESS: Password admin123 verifies correctly!\n";
} else {
    echo "FAIL: Password admin123 does NOT verify!\n";
    echo "\nGenerating NEW hash to test:\n";
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "New hash: $new_hash\n";

    if (password_verify($password, $new_hash)) {
        echo "New hash works. Updating database...\n";
        $update = $pdo->prepare("UPDATE tbl_usuario SET contrasena = :hash WHERE email = 'admin@maishop.com'");
        $update->execute(['hash' => $new_hash]);
        echo "Database updated. Try login again.\n";
    }
}
