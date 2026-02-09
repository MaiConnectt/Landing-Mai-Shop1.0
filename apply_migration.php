<?php
require_once 'Front/conexion.php';

try {
    $sql = file_get_contents('Back/BD/scripts/migraciones/fix_seller_stats_schema.sql');
    $pdo->exec($sql);
    echo "Migration applied successfully.\n";

    // Verify
    $stmt = $pdo->query("SELECT id_order, total, commission_amount FROM tbl_order JOIN vw_order_totals USING(id_order) LIMIT 5");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($orders);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
