<?php
session_start();
require_once __DIR__ . '/../conexion.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = '⚠️ Email y contraseña son obligatorios';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    u.id_user,
                    u.email,
                    u.password,
                    u.role_id,
                    r.role_name
                FROM tbl_user u
                INNER JOIN tbl_role r ON r.id_role = u.role_id
                WHERE u.email = :email
                LIMIT 1
            ");

            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $message = '❌ Usuario no encontrado';
            } elseif (!password_verify($password, $user['password'])) {
                $message = '❌ Contraseña incorrecta';
            } else {
                // LOGIN OK
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['role'] = $user['role_name'];

                // Redirigir según el rol
                if ($user['role_id'] == 1) {
                    // Administrador
                    header('Location: ../dashboard/dash.php');
                } elseif ($user['role_id'] == 2) {
                    // Miembro del equipo (vendedor)
                    header('Location: ../seller/seller_dash.php');
                } else {
                    // Otro rol (cliente u otro)
                    header('Location: ../dashboard/dash.php');
                }
                exit;
            }

        } catch (PDOException $e) {
            $message = '❌ Error en login';
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