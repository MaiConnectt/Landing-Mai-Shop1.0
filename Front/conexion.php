<?php
/**
 * Conexión Mai Shop - Modo de Resolución Automática
 */

$host = 'localhost';
$user = 'postgres';
$password = '3205560180'; // Probaremos ambas versiones
$databases = 'MaiShop';
$port = 5432;
$pdo = null;
$error_msg = '';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$databases";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Si conecta, configuramos encoding
    $pdo->exec("SET client_encoding TO 'UTF8'");

} catch (PDOException $e) {
    $error_msg = $e->getMessage();
}

?>