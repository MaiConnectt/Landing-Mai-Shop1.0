<?php
/**
 * Script para ejecutar migraciones de base de datos
 * Ejecuta las migraciones necesarias para el sistema de notificaciones
 */

require_once 'Front/conexion.php';

echo "=== MIGRACIÓN: Sistema de Pedidos y Notificaciones ===\n\n";

try {
    // 1. Migración del schema de tbl_order
    echo "1. Ejecutando migración de tbl_order...\n";
    $migration1 = file_get_contents('Back/scripts/migraciones/2026-02-07_fix_order_schema.sql');
    $pdo->exec($migration1);
    echo "   ✓ Migración de tbl_order completada\n\n";

    // 2. Crear tabla de notificaciones
    echo "2. Creando tabla de notificaciones...\n";
    $schema_notifications = file_get_contents('Back/scripts/schema/04_notificaciones.sql');
    $pdo->exec($schema_notifications);
    echo "   ✓ Tabla tbl_notification creada\n\n";

    // 3. Crear función para notificaciones
    echo "3. Creando función de notificación...\n";
    $function = file_get_contents('Back/scripts/funciones/fn_notify_admin_new_order.sql');
    $pdo->exec($function);
    echo "   ✓ Función notify_admin_new_order() creada\n\n";

    // 4. Crear trigger
    echo "4. Creando trigger de notificación...\n";
    $trigger = file_get_contents('Back/scripts/triggers/trg_notify_new_order.sql');
    $pdo->exec($trigger);
    echo "   ✓ Trigger trg_notify_new_order creado\n\n";

    echo "=== ✓ MIGRACIÓN COMPLETADA EXITOSAMENTE ===\n\n";

    // Verificar estructura
    echo "Verificando estructura de tbl_order:\n";
    $stmt = $pdo->query("
        SELECT column_name, data_type, is_nullable
        FROM information_schema.columns
        WHERE table_name = 'tbl_order'
        ORDER BY ordinal_position
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['column_name']} ({$row['data_type']}) " .
            ($row['is_nullable'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }

    echo "\nVerificando tabla tbl_notification:\n";
    $count = $pdo->query("SELECT COUNT(*) FROM tbl_notification")->fetchColumn();
    echo "  - Tabla creada correctamente (registros: $count)\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}
