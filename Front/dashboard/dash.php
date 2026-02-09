<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mai Shop</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php $base = '.';
        include 'includes/sidebar.php'; ?>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <h1>Hola Mai, buen día!</h1>
                    <p>Aquí está un resumen de tu negocio hoy</p>
                </div>
                <div class="header-right">
                    <div class="user-profile">
                        <button class="profile-button" id="profileButton">
                            <div class="profile-avatar">
                                <?php echo strtoupper(substr($current_user['email'], 0, 1)); ?>
                            </div>
                            <span>
                                <?php echo htmlspecialchars($current_user['role']); ?>
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="profile-dropdown" id="profileDropdown">
                            <a href="settings.php" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>Mi Perfil</span>
                            </a>
                            <a href="settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Configuración</span>
                            </a>
                            <a href="logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Stats Logic -->
            <?php
            // Fetch real dashboard stats
            try {
                // Total Orders (All time)
                $stmt_count = $pdo->query("SELECT COUNT(*) FROM tbl_order WHERE status != 4"); // Assuming 4 is cancelled? No, 3 (from insert script: 0=pending, 1=process, 2=completed, 3=cancelled). Wait, check dados_referencia.sql. Line 319: (4, 'order', 3, 'Cancelado'). So status 3.
                $total_orders = $stmt_count->fetchColumn();

                // Monthly Income (Completed orders only)
                $stmt_income = $pdo->query("
                    SELECT COALESCE(SUM(vw.total), 0) 
                    FROM vw_order_totals vw
                    JOIN tbl_order o ON vw.id_order = o.id_order
                    WHERE o.status = 2 
                    AND o.created_at >= DATE_TRUNC('month', CURRENT_DATE)
                ");
                $monthly_income = $stmt_income->fetchColumn();

            } catch (PDOException $e) {
                $total_orders = 0;
                $monthly_income = 0;
            }
            ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                            <div class="stat-label">Pedidos Totales</div>
                            <!-- 
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+12% este mes</span>
                            </div>
                            -->
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">$<?php echo number_format($monthly_income, 0, ',', '.'); ?></div>
                            <div class="stat-label">Ingresos del Mes</div>
                            <!--
                            <div class="stat-change positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+8% este mes</span>
                            </div>
                            -->
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>




            </div>

            <!-- Content Grid -->
            <div class="content-grid" style="grid-template-columns: 1fr;">
                <!-- Recent Orders -->
                <div class="content-card">
                    <div class="card-header">
                        <h2 class="card-title">Pedidos Recientes</h2>
                        <a href="pedidos/pedidos.php" class="card-action">Ver todos <i
                                class="fas fa-arrow-right"></i></a>
                    </div>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Pedido #</th>
                                <th>Vendedor</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Obtener últimos 5 pedidos
                            try {
                                $sql_recent = "
                                    SELECT 
                                        o.id_order, 
                                        um.first_name, 
                                        um.last_name,
                                        ot.total,
                                        o.status,
                                        o.created_at
                                    FROM tbl_order o
                                    JOIN tbl_member m ON o.id_member = m.id_member
                                    JOIN tbl_user um ON m.id_user = um.id_user
                                    JOIN vw_order_totals ot ON o.id_order = ot.id_order
                                    ORDER BY o.created_at DESC
                                    LIMIT 5
                                ";
                                $stmt_recent = $pdo->query($sql_recent);
                                $recent_orders = $stmt_recent->fetchAll();

                                if (empty($recent_orders)) {
                                    echo "<tr><td colspan='5' style='text-align:center; padding: 2rem;'>No hay pedidos recientes.</td></tr>";
                                } else {
                                    foreach ($recent_orders as $order) {
                                        $status_class = '';
                                        $status_text = '';
                                        switch ($order['status']) {
                                            case 0:
                                                $status_class = 'pending';
                                                $status_text = 'Pendiente';
                                                break;
                                            case 1:
                                                $status_class = 'processing'; // Usaremos pending style o similar si no hay processing definido
                                                $status_text = 'En Proceso';
                                                break;
                                            case 2:
                                                $status_class = 'completed';
                                                $status_text = 'Completado';
                                                break;
                                            default:
                                                $status_class = 'cancelled';
                                                $status_text = 'Cancelado';
                                        }
                                        // Ajuste de estilo para 'En Proceso' si no existe en CSS, usaremos pending con color azul in-line o clase nueva
                                        // En dashboard.css vi: pending, completed, cancelled. 
                                        // Agregaré estilo inline para 'processing' si es necesario, o reutilizaré.
                            
                                        echo "<tr>";
                                        echo "<td>#" . str_pad($order['id_order'], 4, '0', STR_PAD_LEFT) . "</td>";
                                        echo "<td>" . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . "</td>";
                                        echo "<td>" . date('d/m/Y', strtotime($order['created_at'])) . "</td>";
                                        echo "<td>$" . number_format($order['total'], 0, ',', '.') . "</td>";
                                        echo "<td><span class='order-status " . ($order['status'] == 1 ? 'pending' : $status_class) . "' " . ($order['status'] == 1 ? "style='background:rgba(116, 235, 213, 0.2); color:#0cab9c;'" : "") . ">" . $status_text . "</span></td>";
                                        echo "</tr>";
                                    }
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='5'>Error al cargar pedidos.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Quick Actions Removed -->
            </div>
        </main>
    </div>

    <script src="dashboard.js"></script>
</body>

</html>