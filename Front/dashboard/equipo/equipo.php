<?php
require_once '../auth.php';
require_once '../../conexion.php';

// Pagination settings
$records_per_page = 12;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "m.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(u.nombre LIKE ? OR u.apellido LIKE ? OR m.universidad LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM tbl_member m
    INNER JOIN tbl_user u ON m.id_user = u.id_user
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

// Get sellers with statistics
$query = "
    SELECT 
        m.id_miembro,
        u.nombre,
        u.apellido,
        u.email,
        m.universidad,
        m.telefono,
        m.porcentaje_comision,
        m.status,
        m.fecha_contratacion,
        (SELECT COUNT(*) FROM tbl_pedido o WHERE o.id_miembro = m.id_miembro AND o.estado = 2) as total_orders,
        (SELECT COALESCE(SUM(ot.total),0) FROM tbl_pedido o JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido WHERE o.id_miembro = m.id_miembro AND o.estado = 2) as total_sales,
        (SELECT COALESCE(SUM(monto_comision),0) FROM tbl_pedido o WHERE o.id_miembro = m.id_miembro AND o.estado = 2) as total_commissions_earned,
        (SELECT COALESCE(SUM(monto_comision),0) FROM tbl_pedido o WHERE o.id_miembro = m.id_miembro AND o.estado = 2 AND o.id_pago_comision IS NOT NULL) as total_paid,
        (SELECT COALESCE(SUM(monto_comision),0) FROM tbl_pedido o WHERE o.id_miembro = m.id_miembro AND o.estado = 2 AND o.id_pago_comision IS NULL) as balance_pending
    FROM tbl_miembro m
    INNER JOIN tbl_usuario u ON m.id_usuario = u.id_usuario
    $where_clause
    ORDER BY total_sales DESC
    LIMIT $records_per_page OFFSET $offset
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $sellers = $stmt->fetchAll();
} catch (PDOException $e) {
    $sellers = [];
    $error_message = "Error al cargar vendedores: " . $e->getMessage();
}

// Get summary statistics
try {
    $stats_query = "
        SELECT 
            COUNT(*) as total_sellers,
            COUNT(CASE WHEN status = 'active' THEN 1 END) as active_sellers,
            COALESCE((SELECT SUM(ot.total) FROM tbl_pedido o JOIN vw_totales_pedido ot ON o.id_pedido = ot.id_pedido WHERE o.estado = 2), 0) as total_sales_all,
            COALESCE((SELECT SUM(monto_comision) FROM tbl_pedido WHERE estado = 2), 0) as total_commissions_all,
            COALESCE((SELECT SUM(monto_comision) FROM tbl_pedido WHERE estado = 2 AND id_pago_comision IS NULL), 0) as total_pending_all
        FROM tbl_miembro
    ";
    $stats = $pdo->query($stats_query)->fetch();
} catch (PDOException $e) {
    $stats = [
        'total_sellers' => 0,
        'active_sellers' => 0,
        'total_sales_all' => 0,
        'total_commissions_all' => 0,
        'total_pending_all' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipo de Vendedores - Mai Shop</title>

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
    <link rel="stylesheet" href="equipo.css">
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
            <!-- Header -->
            <div class="team-header">
                <div class="header-left">
                    <h1>Equipo de Vendedores</h1>
                    <p>Gestiona tu equipo universitario de ventas</p>
                </div>
                <!-- Add Button Removed -->
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">
                            <?php echo $stats['active_sellers']; ?>
                        </div>
                        <div class="stat-label">Vendedores Activos</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">$
                            <?php echo number_format($stats['total_sales_all'], 0, ',', '.'); ?>
                        </div>
                        <div class="stat-label">Ventas Totales</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">$
                            <?php echo number_format($stats['total_commissions_all'], 0, ',', '.'); ?>
                        </div>
                        <div class="stat-label">Comisiones Generadas</div>
                    </div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">$
                            <?php echo number_format($stats['total_pending_all'], 0, ',', '.'); ?>
                        </div>
                        <div class="stat-label">Pendiente por Pagar</div>
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="equipo.php" class="filter-bar">
                <div class="search-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="searchInput" placeholder="Buscar vendedores..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <select name="status" class="status-select" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Activos</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactivos
                    </option>
                </select>

                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="equipo.php" class="btn-clear-filters">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </form>

            <!-- Sellers Grid -->
            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($sellers)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-friends"></i>
                    <h3>No hay vendedores</h3>
                    <p>No se encontraron vendedores con los filtros seleccionados.</p>
                    <!-- Add Button Removed -->
                </div>
            <?php else: ?>
                <div class="sellers-grid">
                    <?php foreach ($sellers as $seller): ?>
                        <div class="seller-card <?php echo $seller['status'] === 'inactive' ? 'inactive' : ''; ?>">
                            <div class="seller-header">
                                <div class="seller-avatar">
                                    <?php echo strtoupper(substr($seller['nombre'], 0, 1) . substr($seller['apellido'], 0, 1)); ?>
                                </div>
                                <div class="seller-status-badge <?php echo $seller['status']; ?>">
                                    <?php echo $seller['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                </div>
                            </div>

                            <div class="seller-info">
                                <h3 class="seller-name">
                                    <?php echo htmlspecialchars($seller['nombre'] . ' ' . $seller['apellido']); ?>
                                </h3>

                                <?php if (!empty($seller['universidad'])): ?>
                                    <p class="seller-university">
                                        <i class="fas fa-graduation-cap"></i>
                                        <?php echo htmlspecialchars($seller['universidad']); ?>
                                    </p>
                                <?php endif; ?>

                                <p class="seller-commission">
                                    <i class="fas fa-percentage"></i>
                                    Comisión:
                                    <?php echo number_format($seller['porcentaje_comision'], 1); ?>%
                                </p>
                            </div>

                            <div class="seller-stats">
                                <div class="stat-item">
                                    <span class="stat-label">Ventas</span>
                                    <span class="stat-value">$
                                        <?php echo number_format($seller['total_sales'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Comisiones</span>
                                    <span class="stat-value">$
                                        <?php echo number_format($seller['total_commissions_earned'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                                <div class="stat-item pending">
                                    <span class="stat-label">Pendiente</span>
                                    <span class="stat-value">$
                                        <?php echo number_format($seller['balance_pending'], 0, ',', '.'); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="seller-actions">
                                <a href="ver.php?id=<?php echo $seller['id_miembro']; ?>" class="btn-action view"
                                    title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editar.php?id=<?php echo $seller['id_miembro']; ?>" class="btn-action edit"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if (!empty($seller['telefono'])): ?>
                                    <a href="https://wa.me/57<?php echo preg_replace('/[^0-9]/', '', $seller['telefono']); ?>"
                                        target="_blank" class="btn-action whatsapp" title="WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                <?php endif; ?>
                                <button class="btn-action delete btn-delete"
                                    data-seller-id="<?php echo $seller['id_miembro']; ?>"
                                    data-seller-name="<?php echo htmlspecialchars($seller['nombre'] . ' ' . $seller['apellido']); ?>"
                                    title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <div class="pagination-info">
                            Mostrando
                            <?php echo min($offset + 1, $total_records); ?> -
                            <?php echo min($offset + $records_per_page, $total_records); ?> de
                            <?php echo $total_records; ?> vendedores
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
        </main>
    </div>

    <script src="../dashboard.js"></script>
    <script src="equipo.js"></script>
</body>

</html>