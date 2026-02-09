<?php
require_once 'Front/conexion.php';

try {
    $response = [];

    // 0. Check columns metadata
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_order'");
    $response['columns'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('commission_amount', $response['columns'])) {
        throw new Exception("Column commission_amount NOT FOUND");
    }

    // 1. Check Orders raw data
    $stmt = $pdo->query("SELECT id_order, id_member, seller_id, status, created_at, commission_amount FROM tbl_order ORDER BY created_at DESC LIMIT 5");
    $response['orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Stats Query Simulation
    $seller_id_stmt = $pdo->query("SELECT id_member FROM tbl_member LIMIT 1");
    $seller_id = $seller_id_stmt->fetchColumn();

    if ($seller_id) {
        $stats_query = "
            SELECT 
                (SELECT COUNT(*) FROM tbl_order WHERE id_member = ? AND status != 3) as total_orders,
                COALESCE((SELECT SUM(commission_amount) FROM tbl_order WHERE id_member = ? AND status = 2), 0) as commissions_earned
        ";
        $stmt = $pdo->prepare($stats_query);
        $stmt->execute([$seller_id, $seller_id]);
        $response['simulated_stats'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    file_put_contents('debug_output.txt', print_r($response, true));
    echo "Debug output written to debug_output.txt";

} catch (Exception $e) {
    file_put_contents('debug_output.txt', "Error: " . $e->getMessage());
}
