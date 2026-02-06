<?php
// VERSIÓN: 2026-02-05 18:30 (Si ves esto, el código está actualizado)
/**
 * Sistema de Login - Mai Shop
 * Utiliza función PL/pgSQL para validación segura
 */

// Iniciar sesión PHP con configuración segura
ini_set('session.cookie_httponly', 1); // Prevenir acceso a cookies via JavaScript
ini_set('session.use_only_cookies', 1); // Solo usar cookies para sesiones
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
session_start();

$message = '';
$messageType = 'error'; // 'error' o 'success'

// Generar token CSRF si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Solicitud inválida. Por favor, recargue la página.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        try {
            // Incluir la conexión a la base de datos
            require_once '../conexion.php';

            // Verificar si la conexión fue exitosa
            if (!$pdo) {
                $message = "DEBUG: Falló la conexión a la base de datos. Error: " . ($error_msg ?? 'Desconocido');
            } else {
                // Consultar el usuario por email
                $stmt = $pdo->prepare("
                    SELECT u.id_user, u.first_name, u.last_name, u.email, u.password, u.role_id, r.role_name
                    FROM tbl_user u
                    INNER JOIN tbl_role r ON u.role_id = r.id_role
                    WHERE u.email = :email
                ");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                // DEBUG: Verificar presencia y detalles
                if (!$userData) {
                    // Verificación secundaria sin el JOIN de roles
                    $stmt_basic = $pdo->prepare("SELECT email FROM tbl_user WHERE email = :email");
                    $stmt_basic->execute(['email' => $email]);
                    if ($stmt_basic->fetch()) {
                        $message = "DEBUG: Usuario encontrado pero el JOIN con tbl_role falló.";
                    } else {
                        $message = "DEBUG: Usuario '$email' no existe en la base de datos.";
                    }
                } else {
                    if (password_verify($password, $userData['password'])) {
                        // Login exitoso
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $userData['id_user'];
                        $_SESSION['user_name'] = $userData['first_name'];
                        $_SESSION['user_full_name'] = $userData['first_name'] . ' ' . $userData['last_name'];
                        $_SESSION['user_email'] = $userData['email'];
                        $_SESSION['user_role'] = $userData['role_id'];
                        $_SESSION['role_name'] = $userData['role_name'];
                        $_SESSION['login_time'] = time();

                        if ($userData['role_id'] == 1) {
                            header("Location: ../../index.php");
                        } else if ($userData['role_id'] == 2) {
                            header("Location: ../../index.php");
                        } else {
                            header("Location: ../../index.php");
                        }
                    } else {
                        $p_len = strlen($password);
                        $p_hex = bin2hex($password);
                        $h_val = substr($userData['password'], 0, 15);
                        $message = "DEBUG: P: $p_len ($p_hex), Hash inicia con: $h_val";
                    }
                }

            }

            if (empty($message)) {
                $message = "Credenciales incorrectas. Por favor, verifique su email y contraseña.";
            }

        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            $message = "DEBUG Error: " . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mai Shop</title>
    <!-- Usamos los estilos de la landing -->
    <link rel="stylesheet" href="../landing/style.css?v=2.6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <div class="login-container">

        <?php if (!empty($message)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="login-header">
            <i class="fas fa-birthday-cake"></i>
            <h2>Bienvenido</h2>
            <p style="color: var(--gray);">Ingresa a tu cuenta Mai Shop</p>
        </div>

        <form method="POST" action="login.php">
            <!-- Token CSRF para seguridad -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@correo.com"
                        required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="text" id="password" name="password" class="form-control" placeholder="********"
                        required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Ingresar <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </button>
        </form>

        <a href="../../index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio
        </a>
    </div>

</body>

</html>