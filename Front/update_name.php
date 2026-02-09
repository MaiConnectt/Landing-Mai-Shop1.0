<?php
require_once 'conexion.php';

try {
    // Actualizar usuario ID 2 (Vendedor/Usuario Demo)
    $sql = "UPDATE tbl_user SET first_name = 'Carla', last_name = 'Sofia' WHERE id_user = 2";
    $pdo->exec($sql);
    echo "Actualización exitosa: Usuario 2 ahora es Carla Sofia.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>