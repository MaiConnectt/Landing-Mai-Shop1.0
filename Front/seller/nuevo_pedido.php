<?php
require_once 'seller_auth.php';

// Obtener productos
try {
    $products_query = "SELECT id_producto, nombre_producto, precio, stock FROM tbl_producto WHERE stock > 0 ORDER BY nombre_producto";
    $products_stmt = $pdo->query($products_query);
    $products = $products_stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

// Procesar formulario
$success_message = null;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Datos del cliente
        $client_phone = trim($_POST['client_phone'] ?? '');
        $client_name = trim($_POST['client_name'] ?? '');
        $client_email = trim($_POST['client_email'] ?? '');
        $client_address = trim($_POST['client_address'] ?? '');

        // Split name
        $name_parts = explode(' ', $client_name, 2);
        $client_first_name = $name_parts[0] ?? 'Cliente';
        $client_last_name = $name_parts[1] ?? '';

        // Datos del pedido
        $delivery_date = $_POST['delivery_date'] ?? null;
        $notes = trim($_POST['notes'] ?? '');
        $products_data = $_POST['products'] ?? [];
        $total_order_amount = 0;


        // Validaciones
        if (empty($client_phone)) {
            throw new Exception("El teléfono de contacto es obligatorio");
        }

        if (empty($delivery_date)) {
            throw new Exception("La fecha de entrega es obligatoria");
        }

        if (empty($client_address)) {
            throw new Exception("La dirección de entrega es obligatoria");
        }

        if (empty($products_data)) {
            throw new Exception("Debes agregar al menos un producto");
        }

        // Generar ID para pedido
        $next_order_id_stmt = $pdo->query("SELECT COALESCE(MAX(id_pedido), 0) + 1 as next_id FROM tbl_pedido");
        $next_order_id = $next_order_id_stmt->fetch()['next_id'];
        $order_id = $next_order_id;


        // Crear pedido
        $order_stmt = $pdo->prepare("
            INSERT INTO tbl_pedido (
                id_pedido, id_vendedor, telefono_contacto, fecha_entrega, 
                direccion_entrega, notas, estado, estado_pago, monto_comision
            )
            VALUES (?, ?, ?, ?, ?, ?, 0, ?, 0)
        ");

        $order_stmt->execute([
            $order_id,
            $_SESSION['member_id'],
            $client_phone,
            $delivery_date,
            $client_address,
            $notes,
            0 // estado_pago inicial: Sin comprobante
        ]);


        // Agregar productos al pedido
        foreach ($products_data as $product_id => $quantity) {
            if ($quantity > 0) {
                // Generar ID para detalle
                $next_detail_id_stmt = $pdo->query("SELECT COALESCE(MAX(id_detalle_pedido), 0) + 1 as next_id FROM tbl_detalle_pedido");
                $next_detail_id = $next_detail_id_stmt->fetch()['next_id'];

                // Obtener precio del producto (Spanish table)
                $price_stmt = $pdo->prepare("SELECT precio FROM tbl_producto WHERE id_producto = ?");
                $price_stmt->execute([$product_id]);
                $product = $price_stmt->fetch();
                $price = $product['precio'];

                $detail_stmt = $pdo->prepare("
                    INSERT INTO tbl_detalle_pedido (id_detalle_pedido, id_pedido, id_producto, cantidad, precio_unitario)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $detail_stmt->execute([
                    $next_detail_id,
                    $order_id,
                    $product_id,
                    $quantity,
                    $price
                ]);

                $total_order_amount += ($quantity * $price);
            }
        }

        // Calcular comisión
        $commission_percentage = $_SESSION['commission_percentage'] ?? 5.00;
        $commission_amount = $total_order_amount * ($commission_percentage / 100);

        // Actualizar pedido con el monto final de comisión
        $update_order_stmt = $pdo->prepare("UPDATE tbl_pedido SET monto_comision = ? WHERE id_pedido = ?");
        $update_order_stmt->execute([$commission_amount, $order_id]);


        $pdo->commit();

        // Log History (Initial Creation)
        try {
            $log_stmt = $pdo->prepare("INSERT INTO tbl_historial_pedido (id_pedido, cambiado_por, accion, estado_anterior, estado_nuevo, pago_anterior, pago_nuevo, motivo) VALUES (?, ?, 'CREAR_PEDIDO', NULL, 0, NULL, 0, 'Pedido creado por el vendedor')");
            $log_stmt->execute([$order_id, $_SESSION['user_id']]);
        } catch (Exception $e) {
            // Non-blocking history error
        }

        $success_message = "¡Pedido #" . str_pad($order_id, 4, '0', STR_PAD_LEFT) . " creado exitosamente!";

        // Limpiar formulario
        $_POST = [];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Pedido - Mai Shop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="seller.css">
    <style>
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

        .form-input,
        .form-select,
        .form-textarea {
            padding: 0.75rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.9375rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .product-selector {
            background: var(--gray-50);
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .product-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            margin-bottom: 0.75rem;
        }

        .product-item-name {
            flex: 1;
            font-weight: 500;
        }

        .product-item-price {
            color: var(--primary);
            font-weight: 600;
        }

        .quantity-input {
            width: 80px;
            text-align: center;
        }

        .commission-preview {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
        }

        .commission-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
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
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Crear Nuevo Pedido</h1>
                <p>Registra una nueva venta y gana comisiones</p>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                        <a href="mis_pedidos.php" style="margin-left: auto; color: inherit; font-weight: 600;">
                            Ver Pedidos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="orderForm" enctype="multipart/form-data">
                    <!-- Client Information -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">Datos de Entrega</h3>
                        </div>

                        <div class="form-grid">


                            <div class="form-group">
                                <label class="form-label">Teléfono / Contacto *</label>
                                <input type="tel" name="client_phone" class="form-input" required
                                    placeholder="Para contactar al recibir">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Fecha de Entrega</label>
                                <input type="date" name="delivery_date" class="form-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Dirección de Entrega</label>
                            <input type="text" name="client_address" class="form-input">
                        </div>

                    </div>

                    <!-- Products Selection -->
                    <div class="content-card">
                        <div class="card-header">
                            <h3 class="card-title">Seleccionar Productos</h3>
                        </div>

                        <div class="product-selector" id="productSelector">
                            <?php foreach ($products as $product): ?>
                                <div class="product-item">
                                    <div class="product-item-name">
                                        <i class="fas fa-cookie-bite" style="color: var(--primary);"></i>
                                        <?php echo htmlspecialchars($product['nombre_producto']); ?>
                                        <span style="font-size: 0.75rem; color: var(--gray-500);">
                                            (Stock:
                                            <?php echo $product['stock']; ?>)
                                        </span>
                                    </div>
                                    <div class="product-item-price">
                                        $
                                        <?php echo number_format($product['precio'], 0, ',', '.'); ?>
                                    </div>
                                    <input type="number" name="products[<?php echo $product['id_producto']; ?>]"
                                        class="form-input quantity-input product-quantity" min="0"
                                        max="<?php echo $product['stock']; ?>" value="0"
                                        data-price="<?php echo $product['precio']; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Commission Preview -->
                    <div class="commission-preview">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">
                                    Total del Pedido
                                </div>
                                <div class="commission-value" id="orderTotal">$0</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.25rem;">
                                    Tu Comisión (
                                    <?php echo number_format($_SESSION['commission_percentage'], 1); ?>%)
                                </div>
                                <div class="commission-value" id="commissionAmount">$0</div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="content-card">
                        <div class="form-group">
                            <label class="form-label">Notas Adicionales</label>
                            <textarea name="notes" class="form-textarea"
                                placeholder="Instrucciones especiales, detalles del pedido, etc."></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Crear Pedido
                        </button>
                        <a href="seller_dash.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
        </main>
    </div>

    <script src="seller.js"></script>
    <script>
        // Calculate totals in real-time
        const quantityInputs = document.querySelectorAll('.product-quantity');
        const orderTotalEl = document.getElementById('orderTotal');
        const commissionEl = document.getElementById('commissionAmount');
        const commissionPercentage = <?php echo $_SESSION['commission_percentage']; ?>;

        function calculateTotals() {
            let total = 0;
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const price = parseFloat(input.dataset.price) || 0;
                total += quantity * price;
            });

            const commission = total * (commissionPercentage / 100);

            orderTotalEl.textContent = '$' + total.toLocaleString('es-CO');
            commissionEl.textContent = '$' + Math.round(commission).toLocaleString('es-CO');
        }

        quantityInputs.forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        // Form validation
        document.getElementById('orderForm').addEventListener('submit', function (e) {
            let hasProducts = false;
            quantityInputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    hasProducts = true;
                }
            });

            if (!hasProducts) {
                e.preventDefault();
                MaiModal.alert({
                    title: 'Pedido Incompleto',
                    message: 'Debes agregar al menos un producto al pedido para continuar.',
                    type: 'danger'
                });
            }
        });
    </script>
</body>

</html>