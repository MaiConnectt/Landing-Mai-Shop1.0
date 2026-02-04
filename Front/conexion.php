<?php
// Configuración de la base de datos
$host = 'localhost'; // O la IP del servidor de base de datos
$dbname = 'mai_shop_db'; // Nombre de tu base de datos (cámbialo por el real)
$username = 'postgres'; // Tu usuario de PostgreSQL
$password = 'admin'; // Tu contraseña de PostgreSQL
$port = '5432'; // Puerto por defecto de PostgreSQL

// Cadena de conexión (DSN)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

// Opciones para PDO (Manejo de errores y persistencia)
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en caso de error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devuelve arrays asociativos
    PDO::ATTR_EMULATE_PREPARES => false, // Usa sentencias preparadas nativas
];

// Crear la instancia de PDO
$pdo = new PDO($dsn, $username, $password, $options);
?>