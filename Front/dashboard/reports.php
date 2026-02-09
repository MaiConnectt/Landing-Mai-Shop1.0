<?php
require_once 'auth.php';
require_once '../conexion.php';

// --- FUNCIONES DE AYUDA ---

function getSalesStats($pdo)
{
    // Ventas totales (Solo completadas: status = 2)
    $sql_sales = "SELECT COALESCE(SUM(ot.total), 0) as total_sales 
                  FROM tbl_order o 
                  JOIN vw_order_totals ot ON o.id_order = ot.id_order 
                  WHERE o.status = 2";
    $total_sales = $pdo->query($sql_sales)->fetchColumn();

    // Total Pedidos (Todos)
    $sql_orders = "SELECT COUNT(*) FROM tbl_order";
    $total_orders = $pdo->query($sql_orders)->fetchColumn();

    // Ticket Promedio (Ventas Totales / Pedidos Completados)
    $sql_completed = "SELECT COUNT(*) FROM tbl_order WHERE status = 2";
    $completed_orders = $pdo->query($sql_completed)->fetchColumn();
    $avg_ticket = $completed_orders > 0 ? $total_sales / $completed_orders : 0;

    // Productos Vendidos
    $sql_items = "SELECT COALESCE(SUM(quantity), 0) 
                  FROM tbl_order_detail od 
                  JOIN tbl_order o ON od.id_order = o.id_order 
                  WHERE o.status = 2";
    $total_items = $pdo->query($sql_items)->fetchColumn();

    return [
        'total_sales' => $total_sales,
        'total_orders' => $total_orders,
        'avg_ticket' => $avg_ticket,
        'total_items' => $total_items
    ];
}

function getMonthlySales($pdo)
{
    // Últimos 6 meses
    $sql = "
        SELECT 
            TO_CHAR(o.created_at, 'YYYY-MM') as month,
            TO_CHAR(o.created_at, 'Month') as month_name,
            COALESCE(SUM(ot.total), 0) as total
        FROM tbl_order o
        JOIN vw_order_totals ot ON o.id_order = ot.id_order
        WHERE o.status = 2 AND o.created_at >= NOW() - INTERVAL '6 months'
        GROUP BY 1, 2
        ORDER BY 1 ASC
    ";
    return $pdo->query($sql)->fetchAll();
}

function getOrdersByStatus($pdo)
{
    $sql = "SELECT status, COUNT(*) as count FROM tbl_order GROUP BY status";
    return $pdo->query($sql)->fetchAll();
}

function getTopProducts($pdo)
{
    // Top 5 productos más vendidos
    $sql = "
        SELECT p.name, SUM(od.quantity) as total_sold
        FROM tbl_order_detail od
        JOIN tbl_product p ON od.id_product = p.id_product
        JOIN tbl_order o ON od.id_order = o.id_order
        WHERE o.status = 2
        GROUP BY p.id_product, p.name
        ORDER BY total_sold DESC
        LIMIT 5
    ";
    return $pdo->query($sql)->fetchAll();
}

try {
    $stats = getSalesStats($pdo);
    $monthly_sales = getMonthlySales($pdo);
    $orders_status = getOrdersByStatus($pdo);
    $top_products = getTopProducts($pdo);
} catch (PDOException $e) {
    // En caso de error, arrays vacíos para no romper la UI
    $error = $e->getMessage();
    $stats = ['total_sales' => 0, 'total_orders' => 0, 'avg_ticket' => 0, 'total_items' => 0];
    $monthly_sales = [];
    $orders_status = [];
    $top_products = [];
}

// Preparar datos para JS
$months = array_column($monthly_sales, 'month_name');
$sales_data = array_column($monthly_sales, 'total');
// Trim month names placeholders
$months = array_map('trim', $months);

// Status labels
$status_map = [0 => 'Pendiente', 1 => 'En Proceso', 2 => 'Completado'];
$status_labels = [];
$status_data = [];
$status_colors = [];
foreach ($orders_status as $row) {
    $status_labels[] = $status_map[$row['status']] ?? 'Desconocido';
    $status_data[] = $row['count'];
    // Colores: Pendiente (Amarillo), Proceso (Azul), Completado (Verde)
    if ($row['status'] == 0)
        $status_colors[] = '#e6c86e';
    elseif ($row['status'] == 1)
        $status_colors[] = '#74ebd5';
    elseif ($row['status'] == 2)
        $status_colors[] = '#20ba5a';
    else
        $status_colors[] = '#cbd5e0';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Mai Shop</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .chart-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .chart-header {
            margin-bottom: 1rem;
            border-bottom: 1px solid var(--gray-light);
            padding-bottom: 0.5rem;
        }

        .chart-title {
            font-family: var(--font-heading);
            font-size: 1.2rem;
            color: var(--dark);
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php $base = '.';
        include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="dashboard-header">
                <div class="header-left">
                    <h1>Reportes y Estadísticas</h1>
                    <p>Visión general del rendimiento de tu negocio</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($stats['total_sales'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Ventas Totales</div>
                        </div>
                        <div class="stat-icon" style="background: var(--gradient-primary);"><i
                                class="fas fa-dollar-sign"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">
                                <?php echo number_format($stats['total_orders']); ?>
                            </div>
                            <div class="stat-label">Pedidos Totales</div>
                        </div>
                        <div class="stat-icon" style="background: var(--gradient-secondary);"><i
                                class="fas fa-shopping-bag"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$
                                <?php echo number_format($stats['avg_ticket'], 0, ',', '.'); ?>
                            </div>
                            <div class="stat-label">Ticket Promedio</div>
                        </div>
                        <div class="stat-icon" style="background: #20ba5a;"><i class="fas fa-receipt"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">
                                <?php echo number_format($stats['total_items']); ?>
                            </div>
                            <div class="stat-label">Productos Vendidos</div>
                        </div>
                        <div class="stat-icon" style="background: #a65c68;"><i class="fas fa-box"></i></div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="charts-grid">
                <!-- Sales Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Ventas Mensuales (Últimos 6 Meses)</h3>
                    </div>
                    <canvas id="salesChart"></canvas>
                </div>

                <!-- Status Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Estado de Pedidos</h3>
                    </div>
                    <canvas id="statusChart"></canvas>
                </div>

                <!-- Top Products -->
                <div class="chart-card" style="grid-column: 1 / -1;">
                    <div class="chart-header">
                        <h3 class="chart-title">Top 5 Productos Más Vendidos</h3>
                    </div>
                    <canvas id="productsChart" height="100"></canvas>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="dashboard.js"></script>
    <script>
        // Configuración común
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#6e5c5f';

        // 1. Ventas Mensuales
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Ventas ($)',
                    data: <?php echo json_encode($sales_data); ?>,
                    borderColor: '#c97c89',
                    backgroundColor: 'rgba(201, 124, 137, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // 2. Estado de Pedidos
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_data); ?>,
                    backgroundColor: <?php echo json_encode($status_colors); ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // 3. Top Productos
        const productsData = <?php echo json_encode($top_products); ?>;
        new Chart(document.getElementById('productsChart'), {
            type: 'bar',
            data: {
                labels: productsData.map(p => p.name),
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: productsData.map(p => p.total_sold),
                    backgroundColor: '#e6c86e',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>

</html>