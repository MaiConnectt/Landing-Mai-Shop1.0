<?php
require_once 'auth.php';
require_once '../conexion.php';

$message = '';
$messageType = '';

// Obtener datos actuales del usuario
// Obtener datos actuales del usuario
try {
    $stmt = $pdo->prepare("SELECT nombre, apellido, email FROM tbl_usuario WHERE id_usuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
} catch (PDOException $e) {
    $message = 'Error al cargar datos: ' . $e->getMessage();
    $messageType = 'error';
    $user_data = ['nombre' => '', 'apellido' => '', 'email' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Actualizar Perfil
        $nombre = trim($_POST['first_name']);
        $apellido = trim($_POST['last_name']);
        $email = trim($_POST['email']);

        try {
            $update = $pdo->prepare("UPDATE tbl_usuario SET nombre = ?, apellido = ?, email = ? WHERE id_usuario = ?");
            $update->execute([$nombre, $apellido, $email, $_SESSION['user_id']]);

            // Actualizar datos locales para reflejar cambios
            $user_data['nombre'] = $nombre;
            $user_data['apellido'] = $apellido;
            $user_data['email'] = $email;

            $message = 'Perfil actualizado correctamente.';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Error al actualizar perfil: ' . $e->getMessage();
            $messageType = 'error';
        }

    } elseif (isset($_POST['update_password'])) {
        // Actualizar Contraseña
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $message = 'Las nuevas contraseñas no coinciden.';
            $messageType = 'error';
        } else {
            try {
                // Obtener contraseña actual
                $stmt = $pdo->prepare("SELECT contrasena FROM tbl_usuario WHERE id_usuario = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $db_user = $stmt->fetch();

                if ($db_user && password_verify($current_password, $db_user['contrasena'])) {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE tbl_usuario SET contrasena = ? WHERE id_usuario = ?");
                    $update->execute([$new_hash, $_SESSION['user_id']]);

                    $message = 'Contraseña actualizada correctamente.';
                    $messageType = 'success';
                } else {
                    $message = 'La contraseña actual es incorrecta.';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Error al actualizar contraseña: ' . $e->getMessage();
                $messageType = 'error';
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
    <title>Configuración - Mai Shop</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            max-width: 1200px;
        }

        .settings-card {
            background: var(--white);
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            transition: border-color 0.3s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn-submit {
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius-md);
            cursor: pointer;
            font-family: var(--font-body);
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
        }

        .alert.success {
            background: rgba(32, 186, 90, 0.1);
            color: #20ba5a;
            border: 1px solid #20ba5a;
        }

        .alert.error {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php $base = '.';
        include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Configuración</h1>
                    <p>Gestiona tu perfil y preferencias</p>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="settings-grid">
                <!-- Perfil -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3 class="card-title">Información Personal</h3>
                    </div>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="first_name" class="form-input"
                                value="<?php echo htmlspecialchars($user_data['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="last_name" class="form-input"
                                value="<?php echo htmlspecialchars($user_data['apellido']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-input"
                                value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn-submit">
                            Actualizar Perfil
                        </button>
                    </form>
                </div>

                <!-- Contraseña -->
                <div class="settings-card">
                    <div class="card-header">
                        <h3 class="card-title">Seguridad</h3>
                    </div>
                    <form method="POST" action="settings.php">
                        <input type="hidden" name="update_password" value="1">
                        <div class="form-group">
                            <label class="form-label">Contraseña Actual</label>
                            <input type="password" name="current_password" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="new_password" class="form-input" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" name="confirm_password" class="form-input" required minlength="6">
                        </div>
                        <button type="submit" class="btn-submit">
                            Cambiar Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="dashboard.js"></script>
</body>

</html>