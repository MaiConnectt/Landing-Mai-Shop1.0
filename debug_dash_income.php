<?php
require_once 'Front/conexion.php';

try {
    echo "--- Debugging Dashboard Income (Round 2) ---\n";

    // Check DATE_TRUNC
    $stmt_date = $pdo->query("SELECT DATE_TRUNC('month', CURRENT_DATE)");
    echo "Current Month Start: " . $stmt_date->fetchColumn() . "\n";

    // 1. List ALL orders (no filter)
    $sql = "
        SELECT 
            o.id_order,
            o.status,
            o.created_at,
            COALESCE(vw.total, 0) as total
        FROM tbl_order o
        LEFT JOIN vw_order_totals vw ON o.id_order = vw.id_order
        ORDER BY o.id_order DESC
        LIMIT 10
    ";

    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Listing Last 10 Orders:\n";
    foreach ($orders as $o) {
        $id = $o['id_order'];
        $status = $o['status'];
        $date = $o['created_at'];
        $total = $o['total'];

        echo "#{$id} - Status: {$status} - Date: {$date} - Total: {$total}\n";
    }

    echo "--- Calculating Sum of Status=2 with PHP ---\n";
    $sum = 0;
    foreach ($orders as $o) {
        if ($o['status'] == 2) { // Allow string '2' or int 2
            $sum += $o['total'];
        }
    }
    echo "PHP Sum (Last 10 only): " . number_format($sum) . "\n";

    // Check Dash Query again
    $dash_query = "
        SELECT COALESCE(SUM(vw.total), 0) 
        FROM vw_order_totals vw
        JOIN tbl_order o ON vw.id_order = o.id_order
        WHERE o.status = 2 
        AND o.created_at >= DATE_TRUNC('month', CURRENT_DATE)
    ";
    $stmt2 = $pdo->query($dash_query);
    $dash_result = $stmt2->fetchColumn();

    echo "Dashboard Query Result: " . number_format($dash_result) . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>