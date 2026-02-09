<?php
/**
 * Script para remover la restricción UNIQUE del teléfono
 */

require_once __DIR__ . '/Front/conexion.php';

echo "=== REMOVIENDO RESTRICCIÓN UNIQUE DE TELÉFONO ===\n\n";

try {
    echo "1. Eliminando restricción tbl_client_phone_key...\n";
    $pdo->exec("ALTER TABLE tbl_client DROP CONSTRAINT IF EXISTS tbl_client_phone_key");
    echo "   ✓ Restricción eliminada\n\n";

    echo "2. Verificando restricciones restantes...\n";
    $constraints = $pdo->query("
        SELECT conname as constraint_name
        FROM pg_constraint
        WHERE conrelid = 'tbl_client'::regclass
          AND conname LIKE '%phone%'
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($constraints)) {
        echo "   ✓ No hay restricciones UNIQUE en phone\n\n";
    } else {
        echo "   ⚠️ Restricciones encontradas:\n";
        foreach ($constraints as $c) {
            echo "     - {$c['constraint_name']}\n";
        }
        echo "\n";
    }

    echo "✅ CAMBIO APLICADO!\n\n";
    echo "Ahora puedes crear múltiples clientes con el mismo teléfono.\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
