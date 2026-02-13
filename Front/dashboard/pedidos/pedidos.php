<?php
require_once '../auth.php';
require_once '../../conexion.php';

// Pagination settings
$records_per_page = 20;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$seller_filter = isset($_GET['seller']) ? $_GET['seller'] : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if ($status_filter !== '') {
    $where_conditions[] = "o.estado = ?";
    $params[] = (int)$status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "o.telefono_contacto LIKE ?";
    $params[] = "%$search%";
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(o.fecha_creacion) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(o.fecha_creacion) <= ?";
    $params[] = $date_to;
}

if (!empty($seller_filter)) {
    $where_conditions[] = "o.id_member = ?";
    $params[] = $seller_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM tbl_pedido o
    $where_clause
";

try {
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
}

// Get orders with pagination
$query = "
    SELECT 
        o.id_pedido,
        o.fecha_creacion,
        o.estado,
        o.estado_pago,
        ot.total,
        o.telefono_contacto,
        o.direccion_entrega,
        CONCAT(u.nombre, ' ', u.apellido) as seller_name,
        m.id_miembro as seller_id
    FROM tbl_pedido o
    INNER JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido
    LEFT JOIN tbl_miembro m ON o.id_member = m.id_miembro
    LEFT JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
    $where_clause
    ORDER BY o.fecha_creacion DESC
    LIMIT $records_per_page OFFSET $offset
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error_message = "Error al cargar pedidos: " . $e->getMessage();
}

// Get sellers for filter
try {
    $sellers_query = "
        SELECT m.id_miembro as id_member, CONCAT(u.nombre, ' ', u.apellido) as seller_name
        FROM tbl_miembro m
        INNER JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
        WHERE u.id_rol = 2
        ORDER BY u.nombre
    ";
    $sellers_stmt = $pdo->query($sellers_query);
    $sellers = $sellers_stmt->fetchAll();
} catch (PDOException $e) {
    $sellers = [];
}

// Function to get status badge
function getStatusBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge pending">Pendiente</span>';
        case 1:
            return '<span class="badge processing">En Proceso</span>';
        case 2:
            return '<span class="badge completed">Completado</span>';
        case 3:
            return '<span class="badge error">Cancelado</span>';
        default:
            return '<span class="badge">Desconocido</span>';
    }
}

function getPaymentBadge($status)
{
    switch ($status) {
        case 0:
            return '<span class="badge" style="background: #eee; color: #666;">Sin Pago</span>';
        case 1:
            return '<span class="badge processing">Validar</span>';
        case 2:
            return '<span class="badge completed">Aprobado</span>';
        case 3:
            return '<span class="badge error">Rechazado</span>';
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
    <title>Gestión de Pedidos - Mai Shop</title>

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
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php $base = '..'; include '../includes/sidebar.php'; ?>
        <!-- Main Content -->
        <main class="main-content">
            <!-- Filter Bar -->
            <form method="GET" action="pedidos.php" class="filter-bar" id="filterForm">
                <div class="filter-group">
                    <label for="searchInput">Buscar</label>
                    <input type="text" id="searchInput" name="search" class="filter-input"
                        placeholder="Buscar por cliente o teléfono..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="filter-group">
                    <label for="statusFilter">Estado</label>
                    <select id="statusFilter" name="status" class="filter-select">
                        <option value="">Todos los estados</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="2" <?php echo $status_filter === '2' ? 'selected' : ''; ?>>Completado</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="sellerFilter">Vendedor</label>
                    <select id="sellerFilter" name="seller" class="filter-select">
                        <option value="">Todos los vendedores</option>
                        <?php foreach ($sellers as $seller): ?>
                            <option value="<?php echo $seller['id_member']; ?>" 
                                <?php echo $seller_filter == $seller['id_member'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($seller['seller_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <button type="button" class="btn-filter secondary" id="clearFilters">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </form>

            <!-- Orders Container -->
            <div class="orders-container">
                <div class="orders-header">
                    <h1 class="orders-title">Gestión de Pedidos</h1>
                </div>

                <?php if (isset($error_message)): ?>
                    <div style="padding: 1rem; background: #ffe6e6; color: #ff6b9d; border-radius: 8px; margin: 1rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No hay pedidos</h3>
                        <p>No se encontraron pedidos con los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-table-wrapper">
                        <table class="orders-table-full">
                            <thead>
                                <tr>
                                    <th>Pedido #</th>
                                    <th>Contacto</th>
                                    <th>Vendedor</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Pago</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="order-number">
                                                #<?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($order['telefono_contacto'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <?php if ($order['seller_name']): ?>
                                                <span style="color: var(--primary); font-weight: 500;">
                                                    <i class="fas fa-user-tie"></i>
                                                    <?php echo htmlspecialchars($order['seller_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--gray-400);">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>$<?php echo number_format($order['total'], 0, ',', '.'); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo getStatusBadge($order['estado']); ?>
                                        </td>
                                        <td>
                                            <?php echo getPaymentBadge($order['estado_pago']); ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($order['fecha_creacion'])); ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="ver.php?id=<?php echo $order['id_pedido']; ?>" class="btn-action view"
                                                    title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <select class="status-select" data-order-id="<?php echo $order['id_pedido']; ?>" title="Cambiar estado">
                                                    <option value="0" <?php echo $order['estado'] == 0 ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="1" <?php echo $order['estado'] == 1 ? 'selected' : ''; ?>>En Proceso</option>
                                                    <option value="2" <?php echo $order['estado'] == 2 ? 'selected' : ''; ?>>Completado</option>
                                                    <option value="3" <?php echo $order['estado'] == 3 ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                                <button class="btn-action delete btn-delete"
                                                    data-order-id="<?php echo $order['id_pedido']; ?>"
                                                    data-order-number="#<?php echo str_pad($order['id_pedido'], 4, '0', STR_PAD_LEFT); ?>"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <div class="pagination-info">
                                Mostrando
                                <?php echo min($offset + 1, $total_records); ?> -
                                <?php echo min($offset + $records_per_page, $total_records); ?>
                                de
                                <?php echo $total_records; ?> pedidos
                            </div>
                            <div class="pagination-buttons">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                        class="btn-page">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                        class="btn-page <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($status_filter) ? '&status=' . $status_filter : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                        class="btn-page">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Confirmar Acción</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalMessage">¿Estás seguro de realizar esta acción?</p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal cancel" id="modalCancel">Cancelar</button>
                <button class="btn-modal confirm" id="modalConfirm">Confirmar</button>
            </div>
        </div>
    </div>

    <script src="../dashboard.js"></script>
    <script src="pedidos.js"></script>
</body>

</html>