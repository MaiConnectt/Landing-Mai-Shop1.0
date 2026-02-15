<?php
require_once 'seller_auth.php';

// Obtener estadísticas de comisiones
try {
    $stats = $pdo->prepare("SELECT * FROM vw_seller_commissions WHERE id_member = ?");
    $stats->execute([$_SESSION['member_id']]);
    $commission_stats = $stats->fetch();

    if (!$commission_stats) {
        $commission_stats = ['total_sales' => 0, 'commissions_earned' => 0, 'total_paid' => 0, 'balance_pending' => 0];
    }
} catch (PDOException $e) {
    $commission_stats = ['total_sales' => 0, 'commissions_earned' => 0, 'total_paid' => 0, 'balance_pending' => 0];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Comisiones - Mai Shop</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="seller.css">
</head>

<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>Mis Comisiones</h1>
                <p>Resumen de tus ganancias</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($commission_stats['commissions_earned'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Total Ganado</div>
                        </div>
                        <div class="stat-icon success"><i class="fas fa-coins"></i></div>
                    </div>
                    <div class="stat-change positive"><i class="fas fa-percentage"></i>
                        <?php echo number_format($_SESSION['commission_percentage'], 1); ?>% por venta
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($commission_stats['total_paid'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Total Pagado</div>
                        </div>
                        <div class="stat-icon warning"><i class="fas fa-wallet"></i></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($commission_stats['balance_pending'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Pendiente por Cobrar</div>
                        </div>
                        <div class="stat-icon danger"><i class="fas fa-clock"></i></div>
                    </div>
                    <?php if ($commission_stats['balance_pending'] > 0): ?>
                        <div class="stat-change negative"><i class="fas fa-exclamation-circle"></i> Por pagar</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Información de Comisiones</h3>
                </div>
                <div style="padding: 1.5rem; background: var(--gray-50); border-radius: 12px;">
                    <p style="margin-bottom: 1rem; color: var(--gray-600);">
                        <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                        Tu porcentaje de comisión actual es del <strong>
                            <?php echo number_format($_SESSION['commission_percentage'], 1); ?>%
                        </strong>
                    </p>
                    <p style="margin-bottom: 1rem; color: var(--gray-600);">
                        <i class="fas fa-check-circle" style="color: var(--success);"></i>
                        Las comisiones se calculan sobre el total de pedidos <strong>completados</strong>
                    </p>
                    <p style="color: var(--gray-600);">
                        <i class="fas fa-dollar-sign" style="color: var(--warning);"></i>
                        Los pagos son procesados por el administrador
                    </p>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Resumen de Ventas</h3>
                </div>
                <table class="table">
                    <tr>
                        <td style="font-weight: 600;">Ventas Totales</td>
                        <td style="text-align: right; font-size: 1.125rem; color: var(--primary); font-weight: 700;">
                            $
                            <?php echo number_format($commission_stats['total_sales'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Comisión (
                            <?php echo number_format($_SESSION['commission_percentage'], 1); ?>%)
                        </td>
                        <td style="text-align: right; font-size: 1.125rem; color: var(--success); font-weight: 700;">
                            $
                            <?php echo number_format($commission_stats['commissions_earned'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">Pagado</td>
                        <td style="text-align: right; font-size: 1.125rem; color: var(--warning); font-weight: 700;">
                            $
                            <?php echo number_format($commission_stats['total_paid'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <tr style="background: var(--gray-50);">
                        <td style="font-weight: 700; font-size: 1.125rem;">Pendiente</td>
                        <td style="text-align: right; font-size: 1.5rem; color: var(--danger); font-weight: 700;">
                            $
                            <?php echo number_format($commission_stats['balance_pending'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                </table>
            </div>
        </main>
    </div>
    <script src="seller.js"></script>
</body>

</html>