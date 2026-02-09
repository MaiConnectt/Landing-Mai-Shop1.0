<?php
require_once 'Front/conexion.php';

try {
    echo "Checking columns for tbl_order...\n";
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'tbl_order'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Columns found: " . implode(", ", $columns) . "\n";

    if (in_array('commission_amount', $columns)) {
        echo "SUCCESS: commission_amount exists.\n";

        // Check data
        $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_order WHERE commission_amount > 0");
        $count = $stmt->fetchColumn();
        echo "Orders with commission > 0: $count\n";

    } else {
        echo "FAILURE: commission_amount MISSING.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
