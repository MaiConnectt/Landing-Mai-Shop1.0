<?php
session_start();
require_once 'Front/conexion.php';

echo "--- Debugging Commissions ---\n";

// 1. Session Keys
echo "Session Keys:\n";
print_r($_SESSION);

// 2. Check Orders that SHOULD show up
// Criteria: status=2 AND commission_payout_id IS NULL AND seller_id IS NOT NULL
$sql_check = "
    SELECT id_order, status, seller_id, commission_payout_id, id_member 
    FROM tbl_order 
    WHERE status = 2
    LIMIT 10
";
$orders = $pdo->query($sql_check)->fetchAll(PDO::FETCH_ASSOC);

echo "\nCompleted Orders Check:\n";
foreach ($orders as $o) {
    echo "ID: " . $o['id_order'] .
        " | Status: " . $o['status'] .
        " | SellerID: " . ($o['seller_id'] ?? 'NULL') .
        " | MemberID: " . ($o['id_member'] ?? 'NULL') .
        " | PayoutID: " . ($o['commission_payout_id'] ?? 'NULL') . "\n";
}

// 3. Check View
echo "\nView Output (vw_seller_pending_commissions):\n";
try {
    $view_data = $pdo->query("SELECT * FROM vw_seller_pending_commissions")->fetchAll(PDO::FETCH_ASSOC);
    print_r($view_data);
} catch (Exception $e) {
    echo "View Error: " . $e->getMessage() . "\n";
}

?>