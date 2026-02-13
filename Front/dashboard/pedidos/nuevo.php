<?php
require_once '../auth.php';
require_once '../../conexion.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get form data
        $id_cliente = $_POST['customer_id'] ?? null;
        $nombre_cliente = trim($_POST['customer_name'] ?? '');
        $telefono_cliente = trim($_POST['customer_phone'] ?? '');
        $email_cliente = trim($_POST['customer_email'] ?? '');
        $estado_str = $_POST['status'] ?? 'pending';
        $notas = trim($_POST['notes'] ?? '');
        $productos = $_POST['products'] ?? [];

        // Map status
        $estado_map = ['pending' => 0, 'completed' => 2, 'cancelled' => 3];
        $estado = $estado_map[$estado_str] ?? 0;

        // Validate
        if (empty($nombre_cliente)) {
            throw new Exception('El nombre del cliente es obligatorio');
        }

        if (empty($productos)) {
            throw new Exception('Debe agregar al menos un producto');
        }

        // Create or get customer
        if (empty($id_cliente)) {
            // Create new customer
            $stmt = $pdo->prepare("
                INSERT INTO tbl_cliente (nombre, telefono, email, fecha_creacion)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$nombre_cliente, $telefono_cliente, $email_cliente]);
            $id_cliente = $pdo->lastInsertId();
        }

        // Calculate total and commission
        $total_amount = 0;
        foreach ($productos as $product) {
            if (!empty($product['name']) && !empty($product['quantity']) && !empty($product['price'])) {
                $total_amount += $product['quantity'] * $product['price'];
            }
        }

        // Get user as optional member
        $id_miembro = null; // Admin-created orders might not have a member unless assigned
        $monto_comision = 0;

        // Create order
        $stmt = $pdo->prepare("
            INSERT INTO tbl_pedido (id_cliente, estado, notas, fecha_creacion, monto_comision, telefono_contacto)
            VALUES (?, ?, ?, NOW(), ?, ?)
            RETURNING id_pedido
        ");
        $stmt->execute([$id_cliente, $estado, $notas, $monto_comision, $telefono_cliente]);
        $id_pedido = $stmt->fetchColumn();

        // Create order items (detail)
        $stmt = $pdo->prepare("
            INSERT INTO tbl_detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($productos as $product) {
            // Note: Simplification - using product name as is or finding ID
            // Here the UI uses arbitrary names, but tbl_detalle_pedido expects id_producto
            // For now, if it's a name, we might need a dummy product or update schema
            // BUT wait, tbl_producto exists. We should probably find by name or create.
            // Simplified for now assuming product 0 or creating if missing
            $stmt_prod = $pdo->prepare("SELECT id_producto FROM tbl_producto WHERE nombre_producto = ? LIMIT 1");
            $stmt_prod->execute([$product['name']]);
            $id_producto = $stmt_prod->fetchColumn();

            if (!$id_producto) {
                $stmt_new_prod = $pdo->prepare("INSERT INTO tbl_producto (nombre_producto, precio, stock) VALUES (?, ?, 0) RETURNING id_producto");
                $stmt_new_prod->execute([$product['name'], $product['price']]);
                $id_producto = $stmt_new_prod->fetchColumn();
            }

            if ($id_producto && !empty($product['quantity']) && !empty($product['price'])) {
                $stmt->execute([
                    $id_pedido,
                    $id_producto,
                    $product['quantity'],
                    $product['price']
                ]);
            }
        }

        // Create order history entry
        $log = $pdo->prepare("INSERT INTO tbl_historial_pedido (id_pedido, cambiado_por, accion, estado_anterior, estado_nuevo, pago_anterior, pago_nuevo, motivo) VALUES (?, ?, ?, NULL, ?, NULL, 0, ?)");
        $log->execute([
            $id_pedido,
            $_SESSION['user_id'],
            'CREAR_PEDIDO_ADMIN',
            $estado,
            'Pedido creado desde el panel de administración'
        ]);

        // Commit transaction
        $pdo->commit();

        // Redirect to order details
        header("Location: ver.php?id=$id_pedido&success=1");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get existing customers for autocomplete
try {
    $stmt = $pdo->query("SELECT id_cliente as id_customer, nombre as name, telefono as phone, email FROM tbl_cliente ORDER BY nombre");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    $customers = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Pedido - Mai Shop</title>

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
        .form-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-md);
        }

        .form-section {
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--accent-color);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-family: var(--font-heading);
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: var(--gray-dark);
            margin-bottom: 0.5rem;
        }

        .form-label.required::after {
            content: ' *';
            color: #ff6b9d;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-size: 0.95rem;
            transition: all var(--transition-fast);
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(201, 124, 137, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--spacing-sm);
        }

        .products-table th {
            background: var(--accent-color);
            padding: 0.8rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
        }

        .products-table td {
            padding: 0.8rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .products-table input {
            width: 100%;
            padding: 0.6rem;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius-sm);
        }

        .btn-add-product,
        .btn-remove-product {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-family: var(--font-body);
            font-weight: 500;
            transition: all var(--transition-fast);
        }

        .btn-add-product {
            background: var(--gradient-primary);
            color: var(--white);
            margin-top: var(--spacing-sm);
        }

        .btn-add-product:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-remove-product {
            background: rgba(255, 107, 157, 0.1);
            color: #ff6b9d;
        }

        .btn-remove-product:hover {
            background: #ff6b9d;
            color: var(--white);
        }

        .total-display {
            text-align: right;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-top: var(--spacing-md);
        }

        .form-actions {
            display: flex;
            gap: var(--spacing-md);
            justify-content: flex-end;
            margin-top: var(--spacing-lg);
        }

        .btn-submit,
        .btn-cancel {
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-family: var(--font-body);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-normal);
            text-decoration: none;
            display: inline-block;
        }

        .btn-submit {
            background: var(--gradient-primary);
            color: var(--white);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-cancel {
            background: var(--gray-light);
            color: var(--gray-dark);
        }

        .btn-cancel:hover {
            background: var(--gray);
            color: var(--white);
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
        }

        .alert-error {
            background: rgba(255, 107, 157, 0.1);
            color: #ff6b9d;
            border: 2px solid #ff6b9d;
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
            <div class="form-container">
                <h1 class="orders-title" style="margin-bottom: var(--spacing-md);">
                    <i class="fas fa-plus-circle"></i> Nuevo Pedido
                </h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="nuevo.php" id="orderForm">
                    <!-- Customer Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i> Información del Cliente
                        </h2>

                        <div class="form-grid">
                            <div class="form-group-full">
                                <label class="form-label">Cliente Existente</label>
                                <select class="form-select" id="existingCustomer">
                                    <option value="">-- Seleccionar cliente existente --</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id_customer']; ?>"
                                            data-name="<?php echo htmlspecialchars($customer['name']); ?>"
                                            data-phone="<?php echo htmlspecialchars($customer['phone']); ?>"
                                            data-email="<?php echo htmlspecialchars($customer['email']); ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?> -
                                            <?php echo htmlspecialchars($customer['phone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <input type="hidden" name="customer_id" id="customerId">

                            <div>
                                <label class="form-label required">Nombre</label>
                                <input type="text" name="customer_name" id="customerName" class="form-input" required>
                            </div>

                            <div>
                                <label class="form-label">Teléfono</label>
                                <input type="tel" name="customer_phone" id="customerPhone" class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" id="customerEmail" class="form-input">
                            </div>
                        </div>
                    </div>

                    <!-- Products Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-cookie-bite"></i> Productos
                        </h2>

                        <table class="products-table" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 100px;">Cantidad</th>
                                    <th style="width: 120px;">Precio Unit.</th>
                                    <th style="width: 120px;">Subtotal</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody id="productsBody">
                                <tr class="product-row">
                                    <td><input type="text" name="products[0][name]" class="product-name"
                                            placeholder="Nombre del producto" required></td>
                                    <td><input type="number" name="products[0][quantity]" class="product-quantity"
                                            min="1" value="1" required></td>
                                    <td><input type="number" name="products[0][price]" class="product-price" min="0"
                                            step="1000" placeholder="0" required></td>
                                    <td><input type="text" class="product-subtotal" readonly value="$0"></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>

                        <button type="button" class="btn-add-product" id="addProductBtn">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>

                        <div class="total-display">
                            Total: <span id="totalAmount">$0</span>
                        </div>
                    </div>

                    <!-- Order Details Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-info-circle"></i> Detalles del Pedido
                        </h2>

                        <div class="form-grid">
                            <div>
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="pending">Pendiente</option>
                                    <option value="completed">Completado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>

                            <div class="form-group-full">
                                <label class="form-label">Notas</label>
                                <textarea name="notes" class="form-textarea"
                                    placeholder="Notas adicionales sobre el pedido..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="pedidos.php" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Crear Pedido
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../dashboard.js"></script>
    <script>
        // Customer selection
        document.getElementById('existingCustomer').addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            if (this.value) {
                document.getElementById('customerId').value = this.value;
                document.getElementById('customerName').value = option.dataset.name;
                document.getElementById('customerPhone').value = option.dataset.phone;
                document.getElementById('customerEmail').value = option.dataset.email;
            } else {
                document.getElementById('customerId').value = '';
                document.getElementById('customerName').value = '';
                document.getElementById('customerPhone').value = '';
                document.getElementById('customerEmail').value = '';
            }
        });

        // Product management
        let productIndex = 1;

        document.getElementById('addProductBtn').addEventListener('click', function () {
            const tbody = document.getElementById('productsBody');
            const row = document.createElement('tr');
            row.className = 'product-row';
            row.innerHTML = `
                <td><input type="text" name="products[${productIndex}][name]" class="product-name" placeholder="Nombre del producto" required></td>
                <td><input type="number" name="products[${productIndex}][quantity]" class="product-quantity" min="1" value="1" required></td>
                <td><input type="number" name="products[${productIndex}][price]" class="product-price" min="0" step="1000" placeholder="0" required></td>
                <td><input type="text" class="product-subtotal" readonly value="$0"></td>
                <td><button type="button" class="btn-remove-product"><i class="fas fa-trash"></i></button></td>
            `;
            tbody.appendChild(row);
            productIndex++;
            attachProductListeners(row);
        });

        function attachProductListeners(row) {
            const quantityInput = row.querySelector('.product-quantity');
            const priceInput = row.querySelector('.product-price');
            const subtotalInput = row.querySelector('.product-subtotal');
            const removeBtn = row.querySelector('.btn-remove-product');

            function updateSubtotal() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const subtotal = quantity * price;
                subtotalInput.value = '$' + subtotal.toLocaleString('es-CO');
                updateTotal();
            }

            quantityInput.addEventListener('input', updateSubtotal);
            priceInput.addEventListener('input', updateSubtotal);

            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    row.remove();
                    updateTotal();
                });
            }
        }

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.product-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.product-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.product-price').value) || 0;
                total += quantity * price;
            });
            document.getElementById('totalAmount').textContent = '$' + total.toLocaleString('es-CO');
        }

        // Attach listeners to initial row
        document.querySelectorAll('.product-row').forEach(attachProductListeners);
    </script>
</body>

</html>