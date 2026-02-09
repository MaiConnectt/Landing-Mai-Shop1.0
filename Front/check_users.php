<?php
require_once 'conexion.php';

try {
    $stmt = $pdo->query("SELECT id_user, first_name, last_name, email, role_id FROM tbl_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Usuarios en la Base de Datos</h1>";
    echo "<table border='1'><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id_user'] . "</td>";
        echo "<td>" . $user['first_name'] . " " . $user['last_name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['role_id'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>