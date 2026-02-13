<?php
/**
 * Script para verificar usuarios en la base de datos
 */

require_once __DIR__ . '/conexion.php';

echo "=== VERIFICACIÓN DE USUARIOS ===\n\n";

try {
    // Verificar si existe la tabla
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_usuario");
    $count = $stmt->fetchColumn();

    echo "Total de usuarios en tbl_usuario: $count\n\n";

    // Listar todos los usuarios
    $stmt = $pdo->query("
        SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            u.id_rol,
            r.nombre_rol
        FROM tbl_usuario u
        LEFT JOIN tbl_rol r ON r.id_role = u.id_rol
        ORDER BY u.id_usuario
    ");

    $users = $stmt->fetchAll();

    if (empty($users)) {
        echo "⚠️ No hay usuarios en la base de datos\n";
        echo "\nEjecuta el script SQL de inicialización:\n";
        echo "psql -U postgres -d maiconnect -f \"Back/BD/MaiShop_Spanish_Init.sql\"\n";
    } else {
        echo "Usuarios encontrados:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-5s %-20s %-30s %-10s %-15s\n", "ID", "Nombre", "Email", "Rol ID", "Rol");
        echo str_repeat("-", 80) . "\n";

        foreach ($users as $user) {
            printf(
                "%-5s %-20s %-30s %-10s %-15s\n",
                $user['id_usuario'],
                $user['nombre'] . ' ' . $user['apellido'],
                $user['email'],
                $user['id_rol'],
                $user['nombre_rol'] ?? 'N/A'
            );
        }
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nVerifica que:\n";
    echo "1. La base de datos 'maiconnect' existe\n";
    echo "2. Las tablas tbl_usuario y tbl_rol existen\n";
    echo "3. La conexión en conexion.php es correcta\n";
}
