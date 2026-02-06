<?php
/**
 * Script de prueba de conexión a PostgreSQL
 * Este archivo ayuda a diagnosticar problemas de conexión
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Prueba de Conexión a PostgreSQL</h2>";

// Configuración de la base de datos
$host = '10.5.213.111';
$dbname = 'db_evolution';
$username = 'mdavid';
$password = '3205560180m';
$port = '5432';

echo "<h3>Configuración:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Puerto:</strong> $port</li>";
echo "<li><strong>Base de datos:</strong> $dbname</li>";
echo "<li><strong>Usuario:</strong> $username</li>";
echo "<li><strong>Contraseña:</strong> " . str_repeat('*', strlen($password)) . "</li>";
echo "</ul>";

// Verificar si la extensión PDO PostgreSQL está instalada
echo "<h3>1. Verificando extensión PDO PostgreSQL...</h3>";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ <span style='color: green;'>Extensión pdo_pgsql está instalada</span><br>";
} else {
    echo "❌ <span style='color: red;'>ERROR: Extensión pdo_pgsql NO está instalada</span><br>";
    echo "<p>Solución: Instalar php-pgsql o habilitar la extensión en php.ini</p>";
    exit;
}

// Intentar conexión
echo "<h3>2. Intentando conectar a la base de datos...</h3>";

echo password_hash('3205560180m', PASSWORD_BCRYPT);


$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;options='--client_encoding=UTF8'";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✅ <span style='color: green;'><strong>CONEXIÓN EXITOSA</strong></span><br><br>";

    // Obtener versión de PostgreSQL
    echo "<h3>3. Información de la base de datos:</h3>";
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "<strong>Versión PostgreSQL:</strong> $version<br><br>";

    // Verificar si existe la extensión pgcrypto
    echo "<h3>4. Verificando extensión pgcrypto:</h3>";
    $stmt = $pdo->query("SELECT * FROM pg_extension WHERE extname = 'pgcrypto'");
    $pgcrypto = $stmt->fetch();

    if ($pgcrypto) {
        echo "✅ <span style='color: green;'>Extensión pgcrypto está instalada</span><br>";
    } else {
        echo "❌ <span style='color: red;'>Extensión pgcrypto NO está instalada</span><br>";
        echo "<p>Ejecutar: <code>CREATE EXTENSION IF NOT EXISTS pgcrypto;</code></p>";
    }

    // Verificar si existen las funciones
    echo "<h3>5. Verificando funciones PL/pgSQL:</h3>";

    $stmt = $pdo->query("SELECT proname FROM pg_proc WHERE proname = 'fn_hash_password'");
    if ($stmt->fetch()) {
        echo "✅ <span style='color: green;'>Función fn_hash_password existe</span><br>";
    } else {
        echo "❌ <span style='color: red;'>Función fn_hash_password NO existe</span><br>";
    }

    $stmt = $pdo->query("SELECT proname FROM pg_proc WHERE proname = 'fn_validate_login'");
    if ($stmt->fetch()) {
        echo "✅ <span style='color: green;'>Función fn_validate_login existe</span><br>";
    } else {
        echo "❌ <span style='color: red;'>Función fn_validate_login NO existe</span><br>";
    }

    // Verificar si existen usuarios
    echo "<h3>6. Verificando usuarios en la base de datos:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tbl_user");
    $result = $stmt->fetch();
    echo "Total de usuarios: <strong>" . $result['total'] . "</strong><br>";

    if ($result['total'] > 0) {
        $stmt = $pdo->query("SELECT id_user, email, first_name, last_name, role_id, password FROM tbl_user");
        echo "<table border='1' cellpadding='5' style='margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Nombre</th><th>Hash almacenado (Resumido)</th></tr>";
        while ($user = $stmt->fetch()) {
            echo "<tr>";
            echo "<td>{$user['id_user']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['first_name']} {$user['last_name']}</td>";
            echo "<td>" . substr($user['password'], 0, 15) . "...</td>";
            echo "</tr>";

            // Si es el admin, guardamos su hash para la prueba del punto 7
            if ($user['email'] === 'admin@maishop.com') {
                $stored_admin_hash = $user['password'];
            }
        }
        echo "</table>";
    }

    // PRUEBA DE VALIDACIÓN MANUAL EN PHP
    echo "<h3>7. Prueba de validación manual en PHP (admin@maishop.com):</h3>";
    $test_pass = 'Admin@2026!';

    if (isset($stored_admin_hash)) {
        if (password_verify($test_pass, $stored_admin_hash)) {
            echo "✅ <span style='color: green;'><strong>VALIDACIÓN EXITOSA:</strong> PHP password_verify() reconoce la contraseña 'Admin@2026!' correctamente.</span><br>";
        } else {
            echo "❌ <span style='color: red;'><strong>VALIDACIÓN FALLIDA:</strong> El hash en la BD no coincide con la contraseña 'Admin@2026!'.</span><br>";
            echo "<p>Esto puede pasar si no has re-ejecutado el script <code>MaiConnect.sql</code> con los nuevos hashes.</p>";
        }
    } else {
        echo "❌ <span style='color: red;'>No se encontró el usuario admin@maishop.com para realizar la prueba.</span>";
    }

    echo "<br><h3 style='color: green;'>✅ DIAGNÓSTICO COMPLETO</h3>";

} catch (PDOException $e) {
    echo "❌ <span style='color: red;'><strong>ERROR DE CONEXIÓN</strong></span><br><br>";
    echo "<strong>Código de error:</strong> " . $e->getCode() . "<br>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br><br>";

    echo "<h3>Posibles causas:</h3>";
    echo "<ul>";
    echo "<li><strong>Credenciales incorrectas:</strong> Verifica usuario y contraseña</li>";
    echo "<li><strong>Base de datos no existe:</strong> Verifica que 'db_evolution' existe</li>";
    echo "<li><strong>PostgreSQL no está corriendo:</strong> Verifica el servicio</li>";
    echo "<li><strong>Firewall bloqueando:</strong> Verifica puerto 5432</li>";
    echo "<li><strong>pg_hba.conf:</strong> Verifica permisos de acceso remoto</li>";
    echo "</ul>";

    echo "<h3>Comandos para verificar (en el servidor PostgreSQL):</h3>";
    echo "<pre>";
    echo "# Verificar si PostgreSQL está corriendo\n";
    echo "sudo systemctl status postgresql\n\n";
    echo "# Listar bases de datos\n";
    echo "psql -U postgres -c \"\\l\"\n\n";
    echo "# Probar conexión\n";
    echo "psql -h 10.5.213.111 -U mdavid -d db_evolution\n";
    echo "</pre>";
}
?>