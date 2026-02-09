<?php
require_once 'seller_auth.php';

// Obtener productos
try {
    $products_query = "SELECT id_product, product_name, price, stock FROM tbl_product WHERE stock > 0 ORDER BY product_name";
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
        $payment_proof_path = null;
        $total_order_amount = 0;

        // Procesar comprobante de pago
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/orders/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $new_filename = 'proof_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target_file)) {
                $payment_proof_path = 'uploads/orders/' . $new_filename;
            } else {
                throw new Exception("Error al subir el comprobante de pago");
            }
        }

        // Validaciones
        if (empty($client_phone)) {
            throw new Exception("El teléfono de entrega es obligatorio");
        }

        if (empty($products_data)) {
            throw new Exception("Debes agregar al menos un producto");
        }

        // Generar ID para usuario
        $next_user_id_stmt = $pdo->query("SELECT COALESCE(MAX(id_user), 0) + 1 as next_id FROM tbl_user");
        $next_user_id = $next_user_id_stmt->fetch()['next_id'];

        // Crear usuario para el cliente
        $user_stmt = $pdo->prepare("
            INSERT INTO tbl_user (id_user, first_name, last_name, email, password, role_id)
            VALUES (?, ?, ?, ?, ?, 3)
        ");

        $final_email = 'cliente_' . time() . '@temp.com';
        $temp_password = password_hash('temp123', PASSWORD_DEFAULT);

        $user_stmt->execute([
            $next_user_id,
            'Cliente',
            'Anónimo',
            $final_email,
            $temp_password
        ]);

        $user_id = $next_user_id;

        // Generar ID para cliente
        $next_client_id_stmt = $pdo->query("SELECT COALESCE(MAX(id_client), 0) + 1 as next_id FROM tbl_client");
        $next_client_id = $next_client_id_stmt->fetch()['next_id'];

        // Crear cliente
        $client_stmt = $pdo->prepare("
            INSERT INTO tbl_client (id_client, id_user, phone, address)
            VALUES (?, ?, ?, ?)
        ");

        $client_stmt->execute([
            $next_client_id,
            $user_id,
            $client_phone,
            $client_address
        ]);

        $client_id = $next_client_id;

        // Generar ID para pedido
        $next_order_id_stmt = $pdo->query("SELECT COALESCE(MAX(id_order), 0) + 1 as next_id FROM tbl_order");
        $next_order_id = $next_order_id_stmt->fetch()['next_id'];

        // Crear pedido (usar id_member en lugar de seller_id según MaiConnect.sql)
        $order_stmt = $pdo->prepare("
            INSERT INTO tbl_order (id_order, id_client, id_member, status)
            VALUES (?, ?, ?, 0)
        ");

        $order_stmt->execute([
            $next_order_id,
            $client_id,
            $_SESSION['seller_id']
        ]);

        $order_id = $next_order_id;

        // Agregar productos al pedido
        foreach ($products_data as $product_id => $quantity) {
            if ($quantity > 0) {
                // Generar ID para detalle
                $next_detail_id_stmt = $pdo->query("SELECT COALESCE(MAX(id_order_detail), 0) + 1 as next_id FROM tbl_order_detail");
                $next_detail_id = $next_detail_id_stmt->fetch()['next_id'];

                // Obtener precio del producto
                $price_stmt = $pdo->prepare("SELECT price FROM tbl_product WHERE id_product = ?");
                $price_stmt->execute([$product_id]);
                $price = $price_stmt->fetch()['price'];

                $detail_stmt = $pdo->prepare("
                    INSERT INTO tbl_order_detail (id_order_detail, id_order, id_product, quantity, unit_price)
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

        // Actualizar pedido con la comisión (ya que se insertó antes sin ella, o podríamos haber calculado antes)
        // Como ya insertamos el pedido arriba, hacemos un UPDATE
        $update_order_stmt = $pdo->prepare("UPDATE tbl_order SET commission_amount = ? WHERE id_order = ?");
        $update_order_stmt->execute([$commission_amount, $order_id]);

        $pdo->commit();
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

                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Comprobante de Pago (Opcional)</label>
                            <div style="border: 2px dashed var(--gray-300); padding: 2rem; text-align: center; border-radius: 12px; transition: all 0.3s ease; background: var(--gray-50); cursor: pointer;"
                                onclick="document.getElementById('payment_proof').click()">
                                <i class="fas fa-cloud-upload-alt"
                                    style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                                <p style="margin-bottom: 0.5rem; font-weight: 500;">Haz clic o arrastra la imagen aquí
                                </p>
                                <p style="font-size: 0.8rem; color: var(--gray-500);">Formatos: JPG, PNG</p>
                                <input type="file" name="payment_proof" id="payment_proof" accept="image/*"
                                    style="display: none;"
                                    onchange="document.getElementById('fileName').textContent = this.files[0]?.name || ''">
                                <p id="fileName" style="margin-top: 0.5rem; font-weight: 600; color: var(--primary);">
                                </p>
                            </div>
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
                                        <?php echo htmlspecialchars($product['product_name']); ?>
                                        <span style="font-size: 0.75rem; color: var(--gray-500);">
                                            (Stock:
                                            <?php echo $product['stock']; ?>)
                                        </span>
                                    </div>
                                    <div class="product-item-price">
                                        $
                                        <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                    </div>
                                    <input type="number" name="products[<?php echo $product['id_product']; ?>]"
                                        class="form-input quantity-input product-quantity" min="0"
                                        max="<?php echo $product['stock']; ?>" value="0"
                                        data-price="<?php echo $product['price']; ?>">
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
                alert('Debes agregar al menos un producto al pedido');
            }
        });
    </script>
</body>

</html>