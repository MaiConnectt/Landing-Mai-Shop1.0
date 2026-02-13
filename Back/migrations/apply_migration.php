<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../Front/conexion.php';

$migration_file = __DIR__ . '/remove_clients.sql';

if (!file_exists($migration_file)) {
    die("Migration file not found: $migration_file\n");
}

$sql = file_get_contents($migration_file);

try {
    $pdo->exec($sql);
    echo "Migration applied successfully!\n";
} catch (PDOException $e) {
    echo "SQL ERROR: " . $e->getMessage() . "\n";
}
