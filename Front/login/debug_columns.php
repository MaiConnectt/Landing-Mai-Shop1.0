<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexionPath = __DIR__ . '/../conexion.php';
require_once $conexionPath;

$output = "";
try {
    foreach (['tbl_pedido', 'tbl_comprobante_pago'] as $table) {
        $output .= "\nColumns for $table:\n";
        $stmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = :table AND table_schema = 'public'");
        $stmt->execute(['table' => $table]);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($columns as $col) {
            $output .= "- $col\n";
        }
    }
} catch (PDOException $e) {
    $output .= "PDO Error: " . $e->getMessage() . "\n";
}
file_put_contents('columns_debug.txt', $output);
echo "Columns dumped to columns_debug.txt\n";
