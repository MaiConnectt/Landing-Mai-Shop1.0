<?php
function isActive($link)
{
    return basename($_SERVER['PHP_SELF']) === $link ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/mai.png" alt="Mai Shop" class="sidebar-logo">
        <h2 class="sidebar-title">Mai Shop</h2>
        <p class="sidebar-subtitle">Panel de Vendedor</p>
    </div>

    <nav class="sidebar-nav">
        <a href="seller_dash.php" class="nav-item <?php echo isActive('seller_dash.php'); ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <!-- Productos link REMOVED as per request -->

        <a href="nuevo_pedido.php" class="nav-item <?php echo isActive('nuevo_pedido.php'); ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Nuevo Pedido</span>
        </a>
        <a href="mis_pedidos.php" class="nav-item <?php echo isActive('mis_pedidos.php'); ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Mis Pedidos</span>
        </a>
        <a href="comisiones.php" class="nav-item <?php echo isActive('comisiones.php'); ?>">
            <i class="fas fa-dollar-sign"></i>
            <span>Comisiones</span>
        </a>
        <a href="perfil.php" class="nav-item <?php echo isActive('perfil.php'); ?>">
            <i class="fas fa-user"></i>
            <span>Mi Perfil</span>
        </a>
    </nav>

    <a href="../login/logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span>Cerrar Sesión</span>
    </a>
</aside>