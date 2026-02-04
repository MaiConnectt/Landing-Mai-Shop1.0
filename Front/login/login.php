<?php
// Iniciar sesión PHP
session_start();

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    try {
        // Incluir la conexión a la base de datos
        // La ruta es relativa: salimos de login/ y entramos a conexion.php
        require_once '../conexion.php';

        // Consulta segura
        $stmt = $pdo->prepare("SELECT id_user, first_name, password, role_id FROM tbl_user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificación de contraseña
            // NOTA: Si en tu base de datos las contraseñas están en texto plano, usa: if ($pass == $user['password'])
            // Si están hasheadas (recomendado), usa: if (password_verify($pass, $user['password']))

            // Asumiendo texto plano por ahora según tu DB.sql:
            if ($pass === $user['password']) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_name'] = $user['first_name'];
                $_SESSION['user_role'] = $user['role_id'];

                // Redirigir según rol o al inicio
                header("Location: ../../index.html"); // O a un dashboard
                exit();
            } else {
                $message = "Contraseña incorrecta.";
            }
        } else {
            $message = "Usuario no registrado.";
        }
    } catch (PDOException $e) {
        $message = "Error de conexión: " . $e->getMessage();
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