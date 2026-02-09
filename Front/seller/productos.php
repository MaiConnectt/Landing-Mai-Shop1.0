<?php
require_once 'seller_auth.php';

// Parámetros de búsqueda y filtros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$records_per_page = 12;
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Construir consulta
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "product_name LIKE ?";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener total de productos
try {
    $count_query = "SELECT COUNT(*) as total FROM tbl_product $where_clause";
    $count_stmt = $pdo->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_records / $records_per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
}

// Obtener productos
try {
    $query = "
        SELECT 
            id_product,
            product_name,
            price,
            description,
            stock
        FROM tbl_product
        $where_clause
        ORDER BY product_name ASC
        LIMIT $records_per_page OFFSET $offset
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $error_message = "Error al cargar productos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Mai Shop</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="seller.css">
    <style>
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.125rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .product-description {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-100);
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .product-stock {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .product-stock.low {
            color: var(--danger);
            font-weight: 600;
        }

        .search-bar {
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }

        .search-group {
            position: relative;
        }

        .search-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
        }
    </style>
</head>

<body>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../img/mai.png" alt="Mai Shop" class="sidebar-logo">
                <h2 class="sidebar-title">Mai Shop</h2>
                <p class="sidebar-subtitle">Panel de Vendedor</p>
            </div>

            <nav class="sidebar-nav">
                <a href="seller_dash.php" class="nav-item">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="productos.php" class="nav-item active">
                    <i class="fas fa-cookie-bite"></i>
                    <span>Productos</span>
                </a>
                <a href="nuevo_pedido.php" class="nav-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Pedido</span>
                </a>
                <a href="mis_pedidos.php" class="nav-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Mis Pedidos</span>
                </a>
                <a href="comisiones.php" class="nav-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Comisiones</span>
                </a>
                <a href="perfil.php" class="nav-item">
                    <i class="fas fa-user"></i>
                    <span>Mi Perfil</span>
                </a>
            </nav>

            <a href="../login/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h1>Catálogo de Productos</h1>
                <p>Explora nuestros productos disponibles</p>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <form method="GET" action="productos.php">
                    <div class="search-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" class="search-input" placeholder="Buscar productos..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </form>
            </div>

            <?php if (isset($error_message)): ?>
                <div
                    style="background: #ffe6e6; color: #c53030; padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <div class="content-card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-cookie-bite" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
                    <h3>No se encontraron productos</h3>
                    <p style="color: var(--gray-500);">Intenta con otra búsqueda</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-cookie-bite"></i>
                            </div>
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>

                                <?php if (!empty($product['description'])): ?>
                                    <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <?php endif; ?>

                                <div class="product-footer">
                                    <div>
                                        <div class="product-price">$<?php echo number_format($product['price'], 0, ',', '.'); ?>
                                        </div>
                                        <div class="product-stock <?php echo $product['stock'] < 10 ? 'low' : ''; ?>">
                                            <?php if ($product['stock'] > 0): ?>
                                                <i class="fas fa-box"></i> Stock: <?php echo $product['stock']; ?>
                                            <?php else: ?>
                                                <i class="fas fa-exclamation-circle"></i> Sin stock
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <a href="nuevo_pedido.php?product=<?php echo $product['id_product']; ?>"
                                        class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                class="btn btn-secondary">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                class="btn <?php echo $i === $current_page ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                class="btn btn-secondary">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <script src="seller.js"></script>
</body>

</html>