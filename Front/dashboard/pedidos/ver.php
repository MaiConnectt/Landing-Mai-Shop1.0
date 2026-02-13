<?php
require_once '../auth.php';
require_once '../../conexion.php';

// Get order ID
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (empty($order_id)) {
    header('Location: pedidos.php');
    exit;
}


// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            m.id_miembro,
            u.nombre as nombre_vendedor,
            u.apellido as apellido_vendedor,
            u.email as email_vendedor,
            vw.total as monto_total
        FROM tbl_pedido o
        LEFT JOIN tbl_miembro m ON o.id_member = m.id_miembro
        LEFT JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        LEFT JOIN vw_totales_pedido vw ON o.id_pedido = vw.id_pedido
        WHERE o.id_pedido = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: pedidos.php');
        exit;
    }

    // Get order items (Spanish schema)
    $stmt = $pdo->prepare("
        SELECT 
            od.*,
            p.nombre_producto as nombre_producto,
            (od.cantidad * od.precio_unitario) as subtotal
        FROM tbl_detalle_pedido od
        LEFT JOIN tbl_producto p ON od.id_producto = p.id_producto
        WHERE od.id_pedido = ?
        ORDER BY od.id_detalle_pedido
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    // Get payment proof
    $stmt = $pdo->prepare("SELECT * FROM tbl_comprobante_pago WHERE id_pedido = ? LIMIT 1");
    $stmt->execute([$order_id]);
    $payment_proof = $stmt->fetch();

    // Get order history
    $stmt = $pdo->prepare("
        SELECT 
            h.*,
            u.nombre as nombre_usuario,
            u.apellido as apellido_usuario
        FROM tbl_historial_pedido h
        LEFT JOIN tbl_usuario u ON h.cambiado_por = u.id_usuario
        WHERE h.id_pedido = ?
        ORDER BY h.fecha_cambio DESC
    ");
    $stmt->execute([$order_id]);
    $history = $stmt->fetchAll();

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
                        #<?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <div class="order-meta">
                        <span><i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y H:i', strtotime($order['fecha_creacion'])); ?>
                        </span>
                        <span><i class="fas fa-user-tag"></i> Vendedor:
                            <?php echo htmlspecialchars($order['nombre_vendedor'] . ' ' . $order['apellido_vendedor']); ?>
                        </span>
                    </div>
                </div>
                <div class="order-actions">
                    <a href="pedidos.php" class="btn-action-large secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>

                    <?php if ($order['estado'] == 0 && $order['estado_pago'] == 2): ?>
                        <button onclick="handleAction('mandar_produccion')" class="btn-action-large primary"
                            style="background: var(--gradient-secondary);">
                            <i class="fas fa-industry"></i> Mandar a Producción
                        </button>
                    <?php endif; ?>

                    <?php if ($order['estado'] < 2): ?>
                        <button onclick="handleAction('cancelar_pedido', true)" class="btn-action-large secondary"
                            style="border-color: #ff6b9d; color: #ff6b9d;">
                            <i class="fas fa-ban"></i> Cancelar Pedido
                        </button>
                    <?php endif; ?>

                    <button onclick="window.print()" class="btn-action-large secondary">
                        <i class="fas fa-print"></i> Imprimir
                    </button>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="details-grid">
                <!-- Left Column -->
                <div>
                    <!-- Seller Info -->
                    <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                        <h2 class="detail-card-title">
                            <i class="fas fa-user-tie"></i> Información del Vendedor
                        </h2>
                        <div class="info-row">
                            <span class="info-label">Nombre:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['nombre_vendedor'] . ' ' . $order['apellido_vendedor']); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['email_vendedor'] ?? 'No disponible'); ?>
                            </span>
                        </div>
                        <!-- Client Contact (Secondary) -->
                        <div class="info-row"
                            style="margin-top: 1rem; border-top: 2px dashed var(--gray-light); padding-top: 1rem;">
                            <span class="info-label">Teléfono Contacto:</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['telefono_contacto'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Dirección (Entrega):</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($order['direccion_entrega'] ?? 'N/A'); ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Fecha Programada:</span>
                            <span class="info-value">
                                <?php echo date('d/m/Y', strtotime($order['fecha_entrega'])); ?>
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
                                            <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                        </td>
                                        <td>
                                            <?php echo $item['cantidad']; ?>
                                        </td>
                                        <td>$
                                            <?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?>
                                        </td>
                                        <td>$
                                            <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="3" style="text-align: right;">Total:</td>
                                    <td>$
                                        <?php echo number_format($order['monto_total'], 0, ',', '.'); ?>
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
                            switch ($order['estado']) {
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
                                case 3:
                                    $status_class = 'cancelled';
                                    $status_text = 'Cancelado';
                                    break;
                                default:
                                    $status_class = 'cancelled';
                                    $status_text = 'Desconocido';
                            }
                            ?>
                            <span class="status-badge <?php echo $status_class; ?>"
                                style="font-size: 1.2rem; padding: 1rem 1.5rem;">
                                <?php echo $status_text; ?>
                            </span>

                            <div
                                style="margin-top: 1.5rem; border-top: 1px solid var(--gray-light); padding-top: 1rem;">
                                <div class="info-label" style="margin-bottom: 0.5rem;">Estado de Pago:</div>
                                <?php
                                $pago_class = '';
                                $pago_text = '';
                                switch ($order['estado_pago']) {
                                    case 0:
                                        $pago_class = 'pending';
                                        $pago_text = 'Sin Comprobante';
                                        break;
                                    case 1:
                                        $pago_class = 'processing';
                                        $pago_text = 'Por Validar';
                                        break;
                                    case 2:
                                        $pago_class = 'completed';
                                        $pago_text = 'Aprobado';
                                        break;
                                    case 3:
                                        $pago_class = 'cancelled';
                                        $pago_text = 'Rechazado';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $pago_class; ?>">
                                    <?php echo $pago_text; ?>
                                </span>
                            </div>
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
                    <?php if ($payment_proof): ?>
                        <div class="detail-card" style="margin-bottom: var(--spacing-md);">
                            <h2 class="detail-card-title">
                                <i class="fas fa-receipt"></i> Comprobante de Pago
                            </h2>
                            <div style="text-align: center; padding: var(--spacing-sm);">
                                <a href="../../../<?php echo htmlspecialchars($payment_proof['ruta_imagen']); ?>"
                                    target="_blank">
                                    <img src="../../../<?php echo htmlspecialchars($payment_proof['ruta_imagen']); ?>"
                                        alt="Comprobante de Pago"
                                        style="max-width: 100%; max-height: 300px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); cursor: pointer; transition: transform 0.2s;"
                                        onmouseover="this.style.transform='scale(1.02)'"
                                        onmouseout="this.style.transform='scale(1)'">
                                </a>
                                <div
                                    style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                    <span class="info-label">Monto Reportado:</span>
                                    <span class="info-value"
                                        style="font-weight: 600;">$<?php echo number_format($payment_proof['monto'], 0, ',', '.'); ?></span>
                                </div>

                                <?php if ($order['estado_pago'] == 1): ?>
                                    <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; justify-content: center;">
                                        <button onclick="handleAction('aprobar_pago')" class="btn-action-large primary"
                                            style="background: #20ba5a; padding: 0.5rem 1rem;">
                                            <i class="fas fa-check"></i> Aprobar Pago
                                        </button>
                                        <button onclick="handleAction('rechazar_pago', true)" class="btn-action-large secondary"
                                            style="border-color: #ff6b9d; color: #ff6b9d; padding: 0.5rem 1rem;">
                                            <i class="fas fa-times"></i> Rechazar
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <p style="margin-top: 0.5rem; color: var(--gray); font-size: 0.85rem;">
                                    <i class="fas fa-search-plus"></i> Clic para ampliar
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Order History -->
                    <div class="detail-card">
                        <h2 class="detail-card-title">
                            <i class="fas fa-history"></i> Historial del Pedido
                        </h2>
                        <div class="timeline">
                            <?php if (empty($history)): ?>
                                <p style="color: var(--gray); font-size: 0.9rem; text-align: center;">Sin registros
                                    históricos</p>
                            <?php endif; ?>
                            <?php foreach ($history as $h): ?>
                                <div class="timeline-item">
                                    <div class="timeline-date">
                                        <?php echo date('d/m/Y H:i', strtotime($h['fecha_cambio'])); ?>
                                    </div>
                                    <div class="timeline-content">
                                        <strong><?php echo htmlspecialchars($h['accion']); ?></strong> por
                                        <em><?php echo htmlspecialchars($h['nombre_usuario'] . ' ' . $h['apellido_usuario']); ?></em>
                                        <?php if (!empty($h['motivo'])): ?>
                                            <div
                                                style="background: var(--cream); padding: 0.5rem; border-radius: 8px; font-size: 0.85rem; margin-top: 0.3rem; border-left: 3px solid var(--primary-color);">
                                                <?php echo htmlspecialchars($h['motivo']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <form id="actionForm" method="POST" action="../../pedidos_acciones.php" style="display:none;">
        <input type="hidden" name="id_pedido" value="<?php echo $order_id; ?>">
        <input type="hidden" name="action" id="formAction">
        <input type="hidden" name="notas" id="formNotas">
    </form>

    <script src="../dashboard.js"></script>
    <script>
        function handleAction(action, requiresNote = false) {
            const form = document.getElementById('actionForm');
            const formAction = document.getElementById('formAction');
            const formNotas = document.getElementById('formNotas');

            const executeAction = (note = '') => {
                formAction.value = action;
                formNotas.value = note;
                form.submit();
            };

            if (requiresNote) {
                let title = 'Motivo Requerido';
                let message = 'Por favor, ingresa el motivo para realizar esta acción:';

                if (action === 'cancelar_pedido') title = 'Cancelar Pedido';
                if (action === 'rechazar_pago') title = 'Rechazar Pago';

                MaiModal.prompt({
                    title: title,
                    message: message,
                    label: 'Motivo:',
                    placeholder: 'Escribe el motivo aquí...',
                    onConfirm: (note) => {
                        if (!note || note.trim() === '') {
                            MaiModal.alert({
                                title: 'Campo Requerido',
                                message: 'El motivo es obligatorio para continuar.',
                                type: 'danger'
                            });
                            return;
                        }
                        executeAction(note);
                    }
                });
            } else {
                let title = 'Confirmar Acción';
                let message = '¿Estás seguro de realizar esta acción?';

                if (action === 'mandar_produccion') {
                    title = 'Mandar a Producción';
                    message = '¿Confirmas que deseas enviar este pedido a producción?';
                }
                if (action === 'aprobar_pago') {
                    title = 'Aprobar Pago';
                    message = '¿Confirmas que el pago es correcto y deseas aprobarlo?';
                }

                MaiModal.confirm({
                    title: title,
                    message: message,
                    onConfirm: () => executeAction()
                });
            }
        }
    </script>
</body>

</html>