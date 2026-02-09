<?php
require_once '../auth.php';
require_once '../../conexion.php';

// Get order ID
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (empty($order_id)) {
    header('Location: index.php');
    exit;
}

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            u.first_name as customer_first_name,
            u.last_name as customer_last_name,
            u.email as customer_email,
            c.phone as customer_phone,
            c.address as customer_address,
            m.id_member,
            su.first_name as seller_first_name,
            su.last_name as seller_last_name,
            su.email as seller_email,
            vw.total as total_amount
        FROM tbl_order o
        LEFT JOIN tbl_client c ON o.id_client = c.id_client
        LEFT JOIN tbl_user u ON c.id_user = u.id_user
        LEFT JOIN tbl_member m ON o.id_member = m.id_member
        LEFT JOIN tbl_user su ON m.id_user = su.id_user
        LEFT JOIN vw_order_totals vw ON o.id_order = vw.id_order
        WHERE o.id_order = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: index.php');
        exit;
    }

    // Get order items
    $stmt = $pdo->prepare("
        SELECT 
            od.*,
            p.product_name,
            (od.quantity * od.unit_price) as subtotal
        FROM tbl_order_detail od
        LEFT JOIN tbl_product p ON od.id_product = p.id_product
        WHERE od.id_order = ?
        ORDER BY od.id_order_detail
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Get order history (Disabled)
    /*
    $stmt = $pdo->prepare("
        SELECT 
            h.*,
            u.email as changed_by_email
        FROM tbl_order_history h
        LEFT JOIN tbl_user u ON h.changed_by = u.id_user
        WHERE h.order_id = ?
        ORDER BY h.changed_at DESC
    ");
    $stmt->execute([$order_id]);
    $history = $stmt->fetchAll();
    */
    $history = [];

} catch (PDOException $e) {
    $error = "Error al cargar el pedido: " . $e->getMessage();
}

$success_message = isset($_GET['success']) ? 'Pedido creado exitosamente' : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido - Mai Shop</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="pedidos.css">

    <style>
        .order-header {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--spacing-md);
        }

        .order-header-left h1 {
            font-family: var(--font-heading);
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .order-meta {
            display: flex;
            gap: var(--spacing-md);
            color: var(--gray);
            font-size: 0.9rem;
        }

        .order-actions {
            display: flex;
            gap: var(--spacing-sm);
        }

        .btn-action-large {
            padding: 0.8rem 1.5rem;
            border-radius: var(--radius-md);
            border: none;
            font-family: var(--font-body);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-action-large.primary {
            background: var(--gradient-primary);
            color: var(--white);
        }

        .btn-action-large.secondary {
            background: var(--white);
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-action-large:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
        }

        .detail-card {
            background: var(--white);
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .detail-card-title {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--accent-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--gray-dark);
        }

        .info-value {
            color: var(--gray);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--spacing-sm);
        }

        .items-table th {
            background: var(--accent-color);
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
        }

        .items-table td {
            padding: 0.8rem;
            border-bottom: 1px solid var(--gray-light);
            color: var(--gray-dark);
        }

        .total-row {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline-item {
            position: relative;
            padding-bottom: var(--spacing-md);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 3px solid var(--white);
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            left: -1.55rem;
            top: 1.5rem;
            width: 2px;
            height: calc(100% - 1rem);
            background: var(--gray-light);
        }

        .timeline-item:last-child::after {
            display: none;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 0.3rem;
        }

        .timeline-content {
            color: var(--gray-dark);
        }

        .alert-success {
            background: rgba(37, 211, 102, 0.1);
            color: #20ba5a;
            border: 2px solid #20ba5a;
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
        }

        @media print {

            .sidebar,
            .menu-toggle,
            .order-actions,
            .btn-action-large {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
            }
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-actions {
                width: 100%;
                flex-direction: column;
            }

            .btn-action-large {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php $base = '..';
        include '../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Order Header -->
            <div class="order-header">
                <div class="order-header-left">
                    <h1>
                        #<?php echo str_pad($order['id_order'], 4, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <div class="order-meta">
                        <span><i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                        </span>
                        <span><i class="fas fa-user-tag"></i> Vendedor:
                            <?php echo htmlspecialchars($order['seller_first_name'] . ' ' . $order['seller_last_name']); ?>
                        </span>
                    </div>
                </div>
                <div class="order-actions">
                    <a href="pedidos.php" class="btn-action-large secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <!-- Eliminar botón de editar -->
                    <!--
                    <a href="editar.php?id=<?php echo $order['id_order']; ?>" class="btn-action-large primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    -->
                    <button onclick="window.print()" class="btn-action-large secondary">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="details-grid">
                <!-- Left Column -->
                <div>
                    <!-- Seller Info (Show Seller instead of Customer) -->
                    <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                        <h2 class="detail-card-title">
                            <i class="fas fa-user-tie"></i> Información del Vendedor
                        </h2>
                        <div class="info-row">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['seller_first_name'] . ' ' . $order['seller_last_name']); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['seller_email'] ?? 'No disponible'); ?>
                            </span>
                        </div>
                        <!-- Client Contact (Secondary) -->
                        <div class="info-row"
                            style="margin-top: 1rem; border-top: 2px dashed var(--gray-light); padding-top: 1rem;">
                            <span class="info-label">Teléfono Cliente (Entrega):</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dirección (Entrega):</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['customer_address'] ?? 'N/A'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <i class="fas fa-cookie-bite"></i> Productos
                        </h2>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                        </td>
                                        <td>
                                            <?php echo $item['quantity']; ?>
                                        </td>
                                        <td>$
                                            <?php echo number_format($item['unit_price'], 0, ',', '.'); ?>
                                        </td>
                                        <td>$
                                            <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="3" style="text-align: right;">Total:</td>
                                    <td>$
                                        <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <!-- Order Status -->
                    <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                        <h2 class="detail-card-title">
                            <i class="fas fa-info-circle"></i> Estado del Pedido
                        </h2>
                        <div style="text-align: center; padding: var(--spacing-md) 0;">
                            <?php
                            $status_class = '';
                            $status_text = '';
                            switch ($order['status']) {
                                case 0:
                                    $status_class = 'pending';
                                    $status_text = 'Pendiente';
                                    break;
                                case 1:
                                    $status_class = 'processing';
                                    $status_text = 'En Proceso';
                                    break;
                                case 2:
                                    $status_class = 'completed';
                                    $status_text = 'Completado';
                                    break;
                                default:
                                    $status_class = 'cancelled';
                                    $status_text = 'Cancelado/Otro';
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"
                                style="font-size: 1.2rem; padding: 1rem 1.5rem;">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                        <?php if (!empty($order['notes'])): ?>
                            <div class="info-row">
                                <span class="info-label">Notas:</span>
                            </div>
                            <div
                                style="padding: var(--spacing-sm); background: var(--cream); border-radius: var(--radius-sm); margin-top: 0.5rem;">
                                <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payment Proof -->
                    <?php if (!empty($order['payment_proof'])): ?>
                        <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                            <h2 class="detail-card-title">
                                <i class="fas fa-receipt"></i> Comprobante de Pago
                            </h2>
                            <div style="text-align: center; padding: var(--spacing-sm);">
                                <a href="../../../<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank">
                                    <img src="../../../<?php echo htmlspecialchars($order['payment_proof']); ?>"
                                        alt="Comprobante de Pago"
                                        style="max-width: 100%; max-height: 300px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); cursor: pointer; transition: transform 0.2s;"
                                        onmouseover="this.style.transform='scale(1.02)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                </a>
                                <p style="margin-top: 0.5rem; color: var(--gray); font-size: 0.85rem;">
                                    <i class="fas fa-search-plus"></i> Clic para ampliar
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Order History (Disabled) -->
                    <!--
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <i class="fas fa-history"></i> Historial
                        </h2>
                        <div class="timeline">
                            <?php foreach ($history as $h): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?php echo date('d/m/Y H:i', strtotime($h['changed_at'])); ?>
                                    </div>
                                    <div class="timeline-content">
                                        <strong>
                                            <?php echo htmlspecialchars($h['changed_by_email']); ?>
                                        </strong>
                                        <?php if ($h['old_status']): ?>
                                            cambió el estado de <strong>
                                                <?php echo $h['old_status']; ?>
                                            </strong> a <strong>
                                                <?php echo $h['new_status']; ?>
                                            </strong>
                                        <?php else: ?>
                                            creó el pedido
                                        <?php endif; ?>
                                        <?php if (!empty($h['notes'])): ?>
                                            <br><em>
                                                <?php echo htmlspecialchars($h['notes']); ?>
                                            </em>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    -->
                </div>
            </div>
        </main>
    </div>

    <script src="../dashboard.js"></script>
</body>

</html>