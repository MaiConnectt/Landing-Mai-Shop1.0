<?php
require_once 'seller_auth.php';

$success_message = null;
$error_message = null;

// Actualizar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $phone = trim($_POST['phone'] ?? '');
        $university = trim($_POST['university'] ?? '');

        $stmt = $pdo->prepare("UPDATE tbl_member SET phone = ?, university = ? WHERE id_member = ?");
        $stmt->execute([$phone, $university, $_SESSION['seller_id']]);

        $success_message = "Perfil actualizado exitosamente";
    } catch (PDOException $e) {
        $error_message = "Error al actualizar perfil";
    }
}

// Obtener información del vendedor
try {
    $stmt = $pdo->prepare("SELECT * FROM vw_seller_commissions WHERE id_member = ?");
    $stmt->execute([$_SESSION['seller_id']]);
    $seller_info = $stmt->fetch();
} catch (PDOException $e) {
    $seller_info = null;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Mai Shop</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="seller.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-input {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.9375rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }

        .form-input:disabled {
            background: var(--gray-100);
            cursor: not-allowed;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #e6f9f0;
            color: #22543d;
        }

        .alert-error {
            background: #ffe6e6;
            color: #c53030;
        }
    </style>
</head>

<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>Mi Perfil</h1>
                <p>Información personal y estadísticas</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($seller_info): ?>
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($seller_info['first_name'], 0, 1) . substr($seller_info['last_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h2 style="margin: 0; font-size: 1.75rem;">
                            <?php echo htmlspecialchars($seller_info['first_name'] . ' ' . $seller_info['last_name']); ?>
                        </h2>
                        <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Vendedor desde
                            <?php echo date('F Y', strtotime($seller_info['hire_date'])); ?>
                        </p>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Información Personal</h3>
                    </div>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nombre</label>
                                <input type="text" class="form-input"
                                    value="<?php echo htmlspecialchars($seller_info['first_name']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Apellido</label>
                                <input type="text" class="form-input"
                                    value="<?php echo htmlspecialchars($seller_info['last_name']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input"
                                    value="<?php echo htmlspecialchars($seller_info['email']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" name="phone" class="form-input"
                                    value="<?php echo htmlspecialchars($seller_info['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Universidad</label>
                                <input type="text" name="university" class="form-input"
                                    value="<?php echo htmlspecialchars($seller_info['university'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Comisión</label>
                                <input type="text" class="form-input"
                                    value="<?php echo number_format($seller_info['commission_percentage'], 1); ?>%"
                                    disabled>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                    </form>
                </div>

                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Estadísticas</h3>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value">
                                <?php echo $seller_info['total_orders']; ?>
                            </div>
                            <div class="stat-label">Pedidos Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">$
                                <?php echo number_format($seller_info['total_sales'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Ventas Totales</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">$
                                <?php echo number_format($seller_info['commissions_earned'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Comisiones Ganadas</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="seller.js"></script>
</body>

</html>