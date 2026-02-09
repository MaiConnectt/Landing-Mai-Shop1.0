<?php
require_once 'seller_auth.php';

// Filtros
$status_filter = isset($_GET['status']) ? (int) $_GET['status'] : -1;
$records_per_page = 15;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Construir consulta
$where_clause = "WHERE o.id_member = ?";
$params = [$_SESSION['seller_id']];

if ($status_filter >= 0) {
    $where_clause .= " AND o.status = ?";
    $params[] = $status_filter;
}

// Total de pedidos
try {
    $count_query = "SELECT COUNT(*) as total FROM tbl_order o $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
}

// Obtener pedidos
try {
    $query = "
        SELECT 
            o.id_order,
            o.created_at,
            o.status,
            ot.total,
            CONCAT(u.first_name, ' ', u.last_name) as client_name,
            c.phone as client_phone,
            (ot.total * ? / 100) as commission
        FROM tbl_order o
        INNER JOIN vw_order_totals ot ON o.id_order = ot.id_order
        LEFT JOIN tbl_client c ON o.id_client = c.id_client
        LEFT JOIN tbl_user u ON c.id_user = u.id_user
        $where_clause
        ORDER BY o.created_at DESC
        LIMIT $records_per_page OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge([$_SESSION['commission_percentage']], $params));
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

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
    <title>Mis Pedidos - Mai Shop</title>
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
                <h1>Mis Pedidos</h1>
                <p>Historial de ventas realizadas</p>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Filtrar por Estado</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="?status=-1"
                            class="btn <?php echo $status_filter === -1 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">Todos</a>
                        <a href="?status=0"
                            class="btn <?php echo $status_filter === 0 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">Pendiente</a>
                        <a href="?status=1"
                            class="btn <?php echo $status_filter === 1 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">En Proceso</a>
                        <a href="?status=2"
                            class="btn <?php echo $status_filter === 2 ? 'btn-primary' : 'btn-secondary'; ?>"
                            style="padding: 0.5rem 1rem; font-size: 0.875rem;">Completado</a>
                    </div>
                </div>

                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray-500);">
                        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                        <p>No se encontraron pedidos</p>
                        <a href="nuevo_pedido.php" class="btn btn-primary" style="margin-top: 1rem;"><i
                                class="fas fa-plus"></i> Crear Pedido</a>
                    </div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Pedido #</th>
                                <th>Teléfono</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Comisión</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td style="font-weight: 600;">#
                                        <?php echo str_pad($order['id_order'], 4, '0', STR_PAD_LEFT); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($order['client_phone'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </td>
                                    <td style="font-weight: 600;">$
                                        <?php echo number_format($order['total'], 0, ',', '.'); ?>
                                    </td>
                                    <td style="color: var(--success); font-weight: 600;">$
                                        <?php echo number_format($order['commission'], 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php echo getStatusBadge($order['status']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($total_pages > 1): ?>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>&status=<?php echo $status_filter; ?>"
                                    class="btn btn-secondary"><i class="fas fa-chevron-left"></i></a>
                            <?php endif; ?>
                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"
                                    class="btn <?php echo $i === $current_page ? 'btn-primary' : 'btn-secondary'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>&status=<?php echo $status_filter; ?>"
                                    class="btn btn-secondary"><i class="fas fa-chevron-right"></i></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="seller.js"></script>
</body>

</html>