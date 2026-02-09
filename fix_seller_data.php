<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== VERIFICANDO Y ARREGLANDO DATOS DE VENDEDOR ===\n\n";

try {
    // 1. Verificar usuarios con role_id = 2 (vendedor)
    echo "1. Buscando usuarios vendedores...\n";
    $sellers = $pdo->query("
        SELECT id_user, email, first_name, last_name 
        FROM tbl_user 
        WHERE role_id = 2
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($sellers)) {
        echo "   ❌ No hay usuarios con rol de vendedor\n\n";
        exit(1);
    }

    echo "   ✓ Encontrados " . count($sellers) . " vendedores\n\n";

    // 2. Verificar si tienen registro en tbl_member
    echo "2. Verificando registros en tbl_member...\n";
    foreach ($sellers as $seller) {
        $member = $pdo->prepare("SELECT id_member FROM tbl_member WHERE id_user = ?");
        $member->execute([$seller['id_user']]);
        $exists = $member->fetch();

        if (!$exists) {
            echo "   → Creando registro para: {$seller['email']}\n";
            $pdo->prepare("
                INSERT INTO tbl_member (id_user, commission_percentage, status, hire_date)
                VALUES (?, 10, 'active', NOW())
            ")->execute([$seller['id_user']]);
            echo "     ✓ Registro creado (comisión: 10%)\n";
        } else {
            echo "   ✓ {$seller['email']} ya tiene registro\n";
        }
    }

    echo "\n3. Verificando estado de vendedores...\n";
    $active_sellers = $pdo->query("
        SELECT 
            u.email,
            m.commission_percentage,
            m.status
        FROM tbl_member m
        INNER JOIN tbl_user u ON m.id_user = u.id_user
        WHERE u.role_id = 2
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($active_sellers as $s) {
        echo "   ✓ {$s['email']} - Comisión: {$s['commission_percentage']}% - Estado: {$s['status']}\n";
    }

    echo "\n✅ VENDEDORES CONFIGURADOS CORRECTAMENTE!\n";
    echo "\nAhora puedes hacer login como vendedor.\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
