<?php
require_once 'seller_auth.php';

// Obtener estadísticas del vendedor
try {
    // Estadísticas generales
    // Estadísticas generales (Consultas directas para precisión)
    $seller_id = $_SESSION['seller_id'];

    $stats_query = "
        SELECT 
            (SELECT COUNT(*) FROM tbl_pedido WHERE id_member = ? AND estado != 3) as total_orders,
            COALESCE((SELECT SUM(ot.total) FROM tbl_pedido o JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido WHERE o.id_member = ? AND o.estado = 2), 0) as total_sales,
            COALESCE((SELECT SUM(monto_comision) FROM tbl_pedido WHERE id_member = ? AND estado = 2), 0) as commissions_earned,
            COALESCE((SELECT SUM(monto_comision) FROM tbl_pedido WHERE id_member = ? AND estado = 2 AND id_pago_comision IS NOT NULL), 0) as total_paid,
            COALESCE((SELECT SUM(monto_comision) FROM tbl_pedido WHERE id_member = ? AND estado = 2 AND id_pago_comision IS NULL), 0) as balance_pending
    ";

    $stmt = $pdo->prepare($stats_query);
    $stmt->execute([$seller_id, $seller_id, $seller_id, $seller_id, $seller_id]);
    $stats = $stmt->fetch();

    if (!$stats) {
        $stats = [
            'total_orders' => 0,
            'total_sales' => 0,
            'commissions_earned' => 0,
            'total_paid' => 0,
            'balance_pending' => 0
        ];
    }

    // Últimos pedidos
    $orders_query = "
        SELECT 
            o.id_pedido,
            o.fecha_creacion,
            o.estado,
            ot.total,
            o.telefono_contacto as client_name,
            (ot.total * ? / 100) as commission
        FROM tbl_pedido o
        INNER JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
        WHERE o.id_member = ?
        ORDER BY o.fecha_creacion DESC
        LIMIT 5
    ";

    $stmt = $pdo->prepare($orders_query);
    $stmt->execute([$_SESSION['commission_percentage'], $_SESSION['seller_id']]);
    $recent_orders = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Error en seller_dash: " . $e->getMessage());
    $stats = ['total_orders' => 0, 'total_sales' => 0, 'commissions_earned' => 0, 'total_paid' => 0, 'balance_pending' => 0];
    $recent_orders = [];
}

// Función para formatear estado
function getStatusBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge pending">Pendiente</span>';
        case 1:
            return '<span class="badge processing">En Proceso</span>';
        case 2:
            return '<span class="badge completed">Completado</span>';
        default:
            return '<span class="badge">Desconocido</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Dashboard - Mai Shop</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Seller Dashboard Styles -->
    <link rel="stylesheet" href="seller.css">
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="page-header">
                <h1>¡Hola,
                    <?php echo htmlspecialchars(explode(' ', $_SESSION['seller_name'])[0]); ?>! 👋
                </h1>
                <p>Aquí está el resumen de tu actividad de ventas</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($stats['total_sales'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Ventas Totales</div>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $stats['total_orders']; ?> pedidos
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($stats['commissions_earned'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Comisiones Ganadas</div>
                        </div>
                        <div class="stat-icon success">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-check"></i>
                        <?php echo number_format($_SESSION['commission_percentage'], 1); ?>% por venta
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($stats['total_paid'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Total Pagado</div>
                        </div>
                        <div class="stat-icon warning">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($stats['balance_pending'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Pendiente por Cobrar</div>
                        </div>
                        <div class="stat-icon danger">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <?php if ($stats['balance_pending'] > 0): ?>
                        <div class="stat-change negative">
                            <i class="fas fa-exclamation-circle"></i> Por pagar
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Acciones Rápidas</h3>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="nuevo_pedido.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Nuevo Pedido
                    </a>

                    <a href="comisiones.php" class="btn btn-secondary">
                        <i class="fas fa-dollar-sign"></i> Ver Comisiones
                    </a>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Últimos Pedidos</h3>
                    <a href="mis_pedidos.php" class="btn btn-secondary">
                        Ver Todos <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <?php if (empty($recent_orders)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray-500);">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>Aún no has creado ningún pedido</p>
                        <a href="nuevo_pedido.php" class="btn btn-primary" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Crear Primer Pedido
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pedido #</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Comisión</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#
                                        <?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($order['fecha_creacion'])); ?>
                                    </td>
                                    <td>$
                                        <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                    </td>
                                    <td style="color: var(--success); font-weight: 600;">
                                        $
                                        <?php echo number_format($order['commission'], 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($order['estado']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="seller.js"></script>
</body>

</html>