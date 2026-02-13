<?php
// Function to determine if a link is active based on path
function isActive($path_segment)
{
    return strpos($_SERVER['PHP_SELF'], $path_segment) !== false ? 'active' : '';
}

// Ensure $base is set
if (!isset($base))
    $base = '.';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?php echo $base; ?>/../img/mai.png" alt="Mai Shop" class="sidebar-logo">
        <h2 class="sidebar-title">Mai Center</h2>
    </div>

    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="<?php echo $base; ?>/dash.php" class="nav-item <?php echo isActive('/dash.php'); ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <!-- Pedidos -->
        <a href="<?php echo $base; ?>/pedidos/pedidos.php" class="nav-item <?php echo isActive('/pedidos/'); ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Pedidos</span>
        </a>



        <!-- Comisiones -->
        <a href="<?php echo $base; ?>/comisiones/index.php" class="nav-item <?php echo isActive('/comisiones/'); ?>">
            <i class="fas fa-dollar-sign"></i>
            <span>Comisiones</span>
        </a>

        <!-- Reportes -->
        <a href="<?php echo $base; ?>/reports.php" class="nav-item <?php echo isActive('/reports.php'); ?>">
            <i class="fas fa-chart-line"></i>
            <span>Reportes</span>
        </a>

        <!-- Configuración -->
        <a href="<?php echo $base; ?>/settings.php" class="nav-item <?php echo isActive('settings.php'); ?>">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
    </nav>

    <a href="<?php echo $base; ?>/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
    </a>
</aside>

<?php include_once __DIR__ . '/../../includes/modals.php'; ?>
<link rel="stylesheet" href="<?php echo $base; ?>/../css/mai-modal.css">
<script src="<?php echo $base; ?>/../js/mai-modal.js"></script>