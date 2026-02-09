<?php
/**
 * Script para limpiar COMPLETAMENTE la base de datos
 * Esto permite ejecutar MaiConnect.sql desde cero
 */

require_once __DIR__ . '/Front/conexion.php';

echo "=== LIMPIEZA COMPLETA DE BASE DE DATOS ===\n\n";
echo "ADVERTENCIA: Esto eliminará TODAS las tablas y vistas\n";
echo "Presiona Ctrl+C para cancelar o Enter para continuar...\n";
// readline(); // Comentado para auto-ejecución

try {
    echo "\n1. Eliminando tablas de productos (con dependencias)...\n";

    // Eliminar en orden inverso de dependencias
    $pdo->exec("DROP TABLE IF EXISTS tbl_product_variant CASCADE");
    echo "   ✓ tbl_product_variant\n";

    $pdo->exec("DROP TABLE IF EXISTS tbl_product_image CASCADE");
    echo "   ✓ tbl_product_image\n";

    $pdo->exec("DROP TABLE IF EXISTS tbl_product CASCADE");
    echo "   ✓ tbl_product\n";

    $pdo->exec("DROP TABLE IF EXISTS tbl_category CASCADE");
    echo "   ✓ tbl_category\n";

    echo "\n2. Eliminando vistas...\n";
    $pdo->exec("DROP VIEW IF EXISTS vw_seller_commissions CASCADE");
    $pdo->exec("DROP VIEW IF EXISTS vw_payment_proof_details CASCADE");
    $pdo->exec("DROP VIEW IF EXISTS vw_member_info CASCADE");
    $pdo->exec("DROP VIEW IF EXISTS vw_client_info CASCADE");
    $pdo->exec("DROP VIEW IF EXISTS vw_order_totals CASCADE");
    echo "   ✓ Todas las vistas eliminadas\n";

    echo "\n3. Eliminando tablas principales...\n";
    $pdo->exec("DROP TABLE IF EXISTS tbl_payment_proof CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_commission_payment CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_order_detail CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_order CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_appointment CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_client CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_member CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_user CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_role CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_catalog_type CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_payment_method CASCADE");
    $pdo->exec("DROP TABLE IF EXISTS tbl_order_status CASCADE");
    echo "   ✓ Todas las tablas eliminadas\n";

    echo "\n4. Eliminando funciones...\n";
    $pdo->exec("DROP FUNCTION IF EXISTS fn_update_timestamp() CASCADE");
    $pdo->exec("DROP FUNCTION IF EXISTS notify_admin_new_order() CASCADE");
    echo "   ✓ Funciones eliminadas\n";

    echo "\n5. Eliminando tabla de notificaciones (si existe)...\n";
    $pdo->exec("DROP TABLE IF EXISTS tbl_notification CASCADE");
    echo "   ✓ tbl_notification eliminada\n";

    echo "\n✅ BASE DE DATOS LIMPIA!\n\n";
    echo "Ahora puedes ejecutar:\n";
    echo "1. MaiConnect.sql (base principal)\n";
    echo "2. php setup_products.php (productos)\n";

} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
