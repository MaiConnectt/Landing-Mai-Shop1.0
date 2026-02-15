<?php
session_start();
require_once __DIR__ . '/../conexion.php';

// Enable debug mode (set to false in production)
define('DEBUG_MODE', true);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = null;
$debug_info = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = '⚠️ Email y contraseña son obligatorios';
    } else {
        try {
            // Step 1: Find user in tbl_usuario
            $stmt = $pdo->prepare("
                SELECT 
                    u.id_usuario,
                    u.nombre,
                    u.apellido,
                    u.email,
                    u.contrasena,
                    u.id_rol,
                    r.nombre_rol
                FROM tbl_usuario u
                INNER JOIN tbl_rol r ON r.id_role = u.id_rol
                WHERE u.email = :email
                LIMIT 1
            ");

            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (DEBUG_MODE) {
                $debug_info[] = "Email buscado: $email";
                $debug_info[] = "Usuario encontrado: " . ($user ? 'SÍ' : 'NO');
            }

            if (!$user) {
                $message = '❌ Usuario no encontrado';
            } else {
                if (DEBUG_MODE) {
                    $debug_info[] = "Rol: {$user['nombre_rol']} (ID: {$user['id_rol']})";
                    $debug_info[] = "Hash existe: " . (!empty($user['contrasena']) ? 'SÍ' : 'NO');
                }

                // Step 2: Verify password
                $password_valid = password_verify($password, $user['contrasena']);

                if (DEBUG_MODE) {
                    $debug_info[] = "Password verify: " . ($password_valid ? 'CORRECTO' : 'INCORRECTO');
                }

                if (!$password_valid) {
                    $message = '❌ Contraseña incorrecta';
                } else {
                    // Step 3: If VENDEDOR, validate exists in tbl_miembro
                    $member_id = null;
                    $commission_percentage = null;

                    if ($user['nombre_rol'] === 'VENDEDOR') {
                        $stmt_member = $pdo->prepare("
                            SELECT 
                                id_miembro,
                                porcentaje_comision,
                                estado
                            FROM tbl_miembro
                            WHERE id_usuario = :id_usuario
                            LIMIT 1
                        ");

                        $stmt_member->execute(['id_usuario' => $user['id_usuario']]);
                        $member = $stmt_member->fetch();

                        if (DEBUG_MODE) {
                            $debug_info[] = "Vendedor en tbl_miembro: " . ($member ? 'SÍ' : 'NO');
                        }

                        if (!$member) {
                            $message = '❌ Este usuario vendedor no está registrado en tbl_miembro. Contacta al administrador.';
                        } elseif ($member['estado'] !== 'activo') {
                            $message = '❌ Tu cuenta de vendedor está inactiva. Contacta al administrador.';
                        } else {
                            $member_id = $member['id_miembro'];
                            $commission_percentage = $member['porcentaje_comision'];

                            if (DEBUG_MODE) {
                                $debug_info[] = "ID Miembro: $member_id";
                                $debug_info[] = "Comisión: $commission_percentage%";
                            }
                        }
                    }

                    // Step 4: If all validations passed, create session
                    if ($message === null) {
                        session_regenerate_id(true);

                        // Basic session data
                        $_SESSION['user_id'] = $user['id_usuario'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['nombre'] = $user['nombre'];
                        $_SESSION['apellido'] = $user['apellido'];
                        $_SESSION['first_name'] = $user['nombre'];  // Compatibility
                        $_SESSION['last_name'] = $user['apellido']; // Compatibility
                        $_SESSION['role_id'] = $user['id_rol'];
                        $_SESSION['role'] = $user['nombre_rol'];
                        $_SESSION['role_name'] = $user['nombre_rol'];

                        // Vendor-specific session data
                        if ($user['nombre_rol'] === 'VENDEDOR') {
                            $_SESSION['member_id'] = $member_id;
                            $_SESSION['commission_percentage'] = $commission_percentage;
                        }

                        if (DEBUG_MODE) {
                            error_log("Login exitoso: {$user['email']} ({$user['nombre_rol']})");
                        }

                        // Step 5: Redirect based on role
                        if ($user['nombre_rol'] === 'ADMIN') {
                            header('Location: ../dashboard/dash.php');
                            exit;
                        } elseif ($user['nombre_rol'] === 'VENDEDOR') {
                            header('Location: ../seller/seller_dash.php');
                            exit;
                        } else {
                            // Fallback for unknown roles
                            header('Location: ../dashboard/dash.php');
                            exit;
                        }
                    }
                }
            }

        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage() . " | Code: " . $e->getCode());
            $message = '❌ Error técnico en el login. Por favor, reporta el problema.';

            if (DEBUG_MODE) {
                $debug_info[] = "Error DB: " . $e->getMessage();
            }
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
    <link rel="stylesheet" href="../landing/style.css?v=2.6">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <style>
        .debug-info {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
        }

        .debug-info div {
            margin: 3px 0;
        }
    </style>
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
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@correo.com"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="********"
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