<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== VERIFICANDO ESTRUCTURA Y DATOS ===\n\n";

try {
    // 1. Verificar estructura de tbl_member
    echo "1. Verificando estructura de tbl_member...\n";
    $cols = $pdo->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'tbl_member' 
        ORDER BY ordinal_position
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "   Columnas:\n";
    foreach ($cols as $col) {
        echo "     - {$col['column_name']} ({$col['data_type']})\n";
    }
    echo "\n";

    // 2. Verificar usuarios vendedores
    echo "2. Verificando usuarios vendedores (role_id = 2)...\n";
    $sellers = $pdo->query("
        SELECT id_user, email, first_name, last_name 
        FROM tbl_user 
        WHERE role_id = 2
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sellers)) {
        echo "   ❌ No hay usuarios con rol de vendedor\n\n";
    } else {
        echo "   ✓ Encontrados " . count($sellers) . " vendedores:\n";
        foreach ($sellers as $s) {
            echo "     - {$s['email']} ({$s['first_name']} {$s['last_name']})\n";
        }
        echo "\n";
    }

    // 3. Verificar registros en tbl_member
    echo "3. Verificando registros en tbl_member...\n";
    $members = $pdo->query("
        SELECT m.*, u.email 
        FROM tbl_member m
        INNER JOIN tbl_user u ON m.id_user = u.id_user
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($members)) {
        echo "   ❌ No hay registros en tbl_member\n";

        // Crear registros para vendedores
        if (!empty($sellers)) {
            echo "\n4. Creando registros en tbl_member...\n";
            foreach ($sellers as $seller) {
                $pdo->prepare("
                    INSERT INTO tbl_member (id_user, status, hire_date)
                    VALUES (?, 'active', NOW())
                ")->execute([$seller['id_user']]);
                echo "     ✓ Registro creado para: {$seller['email']}\n";
            }
        }
    } else {
        echo "   ✓ Registros encontrados:\n";
        foreach ($members as $m) {
            echo "     - {$m['email']} (status: {$m['status']})\n";
        }
    }

    echo "\n✅ VERIFICACIÓN COMPLETADA!\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
