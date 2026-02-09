<?php
require_once 'Front/conexion.php';

$log = "--- Debug Log V3 ---\n";

try {
    // 1. Get ALL orders with raw data
    $sql = "
        SELECT 
            o.id_order,
            o.status,
            o.created_at,
            COALESCE(vw.total, 0) as total
        FROM tbl_order o
        LEFT JOIN vw_order_totals vw ON o.id_order = vw.id_order
        ORDER BY o.id_order ASC
    ";

    $orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $log .= "Total Orders Found: " . count($orders) . "\n";
    $log .= str_pad("ID", 6) . str_pad("Status", 8) . str_pad("Date", 25) . "Total\n";

    $sum_all = 0;
    $sum_status_2 = 0;

    foreach ($orders as $o) {
        $id = $o['id_order'];
        $status = $o['status']; // Check type
        $date = $o['created_at'];
        $total = $o['total'];

        $log .= str_pad("#" . $id, 6) . str_pad("($status)", 8) . str_pad($date, 25) . $total . "\n";

        $sum_all += $total;
        if ($status == 2) {
            $sum_status_2 += $total;
        }
    }

    $log .= "--------------------------------------------------\n";
    $log .= "Sum ALL: " . $sum_all . "\n";
    $log .= "Sum Status=2: " . $sum_status_2 . "\n";

    // Check Dash Query
    $dash_query = "
        SELECT COALESCE(SUM(vw.total), 0) 
        FROM vw_order_totals vw
        JOIN tbl_order o ON vw.id_order = o.id_order
        WHERE o.status = 2 
        AND o.created_at >= DATE_TRUNC('month', CURRENT_DATE)
    ";
    $dash_result = $pdo->query($dash_query)->fetchColumn();
    $log .= "Dashboard Query Result (Status=2 + Month): " . $dash_result . "\n";

    // Check Month Date
    $month_start = $pdo->query("SELECT DATE_TRUNC('month', CURRENT_DATE)")->fetchColumn();
    $log .= "Month Start Date: " . $month_start . "\n";

} catch (Exception $e) {
    $log .= "Error: " . $e->getMessage() . "\n";
}

file_put_contents('debug_log.txt', $log);
echo "Log written to debug_log.txt";
?>