<?php
// ====== CONFIGURA ESTO ======
$host = 'localhost';
$port = 5432;
$dbname = 'MaiShop';
$user = 'postgres';
$password = '3205560180';
// ============================

echo "<h2>DB CHECK – MaiShop</h2>";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✅ <b>Conectó a PostgreSQL</b><br><br>";

    // DB actual
    $db = $pdo->query("SELECT current_database()")->fetchColumn();
    echo "📦 Base de datos actual: <b>$db</b><br>";

    // Versión Postgres
    $version = $pdo->query("SELECT version()")->fetchColumn();
    echo "🐘 PostgreSQL: <pre>$version</pre>";

    // Listar tablas
    $tables = $pdo->query("
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ORDER BY table_name
    ")->fetchAll(PDO::FETCH_COLUMN);

    echo "📋 Tablas en schema public (" . count($tables) . "):<br>";
    if (count($tables) === 0) {
        echo "⚠️ No hay tablas en esta base.<br>";
    } else {
        echo "<ul>";
        foreach ($tables as $t) {
            echo "<li>$t</li>";
        }
        echo "</ul>";
    }

    // Chequeos clave para el login
    echo "<hr><h3>Chequeo Login</h3>";

    // tbl_user
    $userCount = $pdo->query("SELECT COUNT(*) FROM tbl_user")->fetchColumn();
    echo "👤 Registros en <b>tbl_user</b>: $userCount<br>";

    // tbl_role
    $roleCount = $pdo->query("SELECT COUNT(*) FROM tbl_role")->fetchColumn();
    echo "🎭 Registros en <b>tbl_role</b>: $roleCount<br>";

    // Join usuarios + roles
    if ($userCount > 0 && $roleCount > 0) {
        echo "<br>🔗 Usuarios + Roles:<br>";
        $rows = $pdo->query("
            SELECT u.email, r.role_name
            FROM tbl_user u
            JOIN tbl_role r ON r.id_role = u.role_id
            ORDER BY u.id_user
        ")->fetchAll();

        echo "<ul>";
        foreach ($rows as $r) {
            echo "<li>{$r['email']} → {$r['role_name']}</li>";
        }
        echo "</ul>";
    }

} catch (Throwable $e) {
    echo "❌ <b>Error de conexión</b><br>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
