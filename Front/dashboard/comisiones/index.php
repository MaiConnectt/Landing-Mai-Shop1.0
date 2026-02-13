<?php
session_start();
require_once '../../conexion.php';

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../../login/login.php');
    exit;
}

$current_user = [
    'id' => $_SESSION['user_id'] ?? 0,
    'name' => ($_SESSION['first_name'] ?? 'Usuario') . ' ' . ($_SESSION['last_name'] ?? ''),
    'email' => $_SESSION['email'] ?? '',
    'role' => 'Administrador'
];

// Fetch sellers pending commissions
try {
    $sql = "SELECT * FROM vw_seller_commissions ORDER BY balance_pending DESC";
    $stmt = $pdo->query($sql);
    $sellers = $stmt->fetchAll();
} catch (PDOException $e) {
    $sellers = [];
    $error = "Error al cargar datos: " . $e->getMessage();
}
// Fetch payment history
try {
    $sql_history = "
        SELECT pp.*, u.nombre, u.apellido
        FROM tbl_comprobante_pago pp
        JOIN tbl_miembro m ON pp.id_miembro = m.id_miembro
        JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        ORDER BY pp.fecha_subida DESC
        LIMIT 5
    ";
    $history_stmt = $pdo->query($sql_history);
    $history = $history_stmt->fetchAll();
} catch (PDOException $e) {
    $history = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comisiones - Mai Shop</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="comisiones.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Removed as per user request -->

        <!-- Main Content -->
        <main class="main-content" style="margin-left: 0; width: 100%;">
            <div class="dashboard-header">
                <div class="header-left">
                    <a href="../dash.php" class="btn btn-secondary"
                        style="margin-bottom: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.5rem 1rem; border-radius: 8px; background: #e2e8f0; color: #4a5568;">
                        <i class="fas fa-arrow-left"></i> Volver al Dashboard
                    </a>
                    <h1>Gestión de Comisiones</h1>
                    <p>Administra los pagos pendientes a tu equipo de ventas</p>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                        <span
                            style="font-family: var(--font-heading); font-size: 2rem; color: var(--primary); font-weight: 700;">Admin</span>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Saldos Pendientes por Vendedor</h2>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Comisión (%)</th>
                            <th>Pedidos Pendientes</th>
                            <th>Total Pendiente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sellers)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem;">
                                    No hay comisiones pendientes por pagar.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sellers as $seller): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 500;">
                                            <?php echo htmlspecialchars($seller['first_name'] . ' ' . $seller['last_name']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $seller['commission_percentage']; ?>%
                                    </td>
                                    <td>
                                        <?php echo $seller['total_orders']; ?> pedidos
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--danger); font-size: 1.1rem;">
                                            $
                                            <?php echo number_format($seller['balance_pending'] ?? 0, 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($seller['balance_pending'] ?? 0) > 0): ?>
                                            <a href="pagar.php?id_member=<?php echo $seller['id_member']; ?>" class="pay-btn">
                                                <i class="fas fa-money-bill-wave"></i> Registrar Pago
                                            </a>
                                        <?php else: ?>
                                            <span class="status-badge completed">Al día</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recently Paid (Optional, could query limits) -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">Historial Reciente de Pagos</h2>
                </div>
                <?php if (empty($history)): ?>
                    <div style="text-align: center; color: var(--gray-500); padding: 1rem;">
                        (Aquí aparecerán los pagos que realices)
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Vendedor</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $pay): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pay['fecha_subida'])); ?></td>
                                    <td><?php echo htmlspecialchars($pay['nombre'] . ' ' . $pay['apellido']); ?></td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--success); font-size: 1rem;">
                                            $<?php echo number_format($pay['monto'] ?? 0, 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge completed">Pagado</span>
                                    </td>
                                    <td>
                                        <a href="recibo.php?id=<?php echo $pay['id_comprobante_pago']; ?>" class="action-btn"
                                            title="Ver Recibo" target="_blank">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </main>
    </div>
</body>

</html>