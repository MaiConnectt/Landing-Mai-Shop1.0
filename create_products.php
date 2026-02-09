<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== CREANDO TABLAS Y PRODUCTOS ===\n\n";

try {
    // 1. Crear categorías
    echo "1. Creando categorías...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_category (
            id_category SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            icon VARCHAR(50),
            color VARCHAR(7),
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT NOW()
        )
    ");

    $pdo->exec("
        INSERT INTO tbl_category (name, description, icon, color, display_order) VALUES
        ('Tortas', 'Tortas personalizadas para toda ocasión', 'fa-birthday-cake', '#c97c89', 1),
        ('Cupcakes', 'Deliciosos cupcakes decorados', 'fa-cupcake', '#e6c86e', 2),
        ('Galletas', 'Galletas artesanales', 'fa-cookie', '#a8d5ba', 3),
        ('Brownies', 'Brownies de chocolate', 'fa-square', '#8b7355', 4),
        ('Cheesecakes', 'Cheesecakes cremosos', 'fa-cheese', '#f7e6e9', 5),
        ('Postres', 'Variedad de postres', 'fa-ice-cream', '#ffa8c5', 6)
        ON CONFLICT (name) DO NOTHING
    ");
    echo "   ✓ Categorías creadas\n\n";

    // 2. Crear productos
    echo "2. Creando tabla de productos...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_product (
            id_product SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            short_description VARCHAR(500),
            price DECIMAL(10,2) NOT NULL,
            category_id INT REFERENCES tbl_category(id_category) ON DELETE SET NULL,
            status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
            stock_status VARCHAR(20) DEFAULT 'available' CHECK (stock_status IN ('available', 'out_of_stock')),
            preparation_time VARCHAR(50),
            ingredients TEXT,
            allergens TEXT,
            is_featured BOOLEAN DEFAULT FALSE,
            is_new BOOLEAN DEFAULT FALSE,
            display_order INT DEFAULT 0,
            main_image VARCHAR(500),
            created_by INT REFERENCES tbl_user(id_user) ON DELETE SET NULL,
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        )
    ");
    echo "   ✓ Tabla tbl_product creada\n\n";

    // 3. Insertar productos
    echo "3. Insertando productos...\n";
    $pdo->exec("
        INSERT INTO tbl_product (name, short_description, description, price, category_id, preparation_time, ingredients, allergens, is_featured, is_new, status, stock_status, created_by) VALUES
        ('Torta de Chocolate Premium', 'Deliciosa torta de chocolate con cobertura de ganache', 'Torta de tres capas de chocolate húmedo con relleno de ganache de chocolate belga y cobertura de chocolate negro. Perfecta para celebraciones especiales.', 85000, 1, '2 días', 'Harina, huevos, chocolate, mantequilla, azúcar, leche', 'Gluten, huevo, lácteos', TRUE, FALSE, 'active', 'available', 1),
        ('Cupcakes de Vainilla (x12)', 'Docena de cupcakes de vainilla con buttercream', 'Set de 12 cupcakes esponjosos de vainilla decorados con buttercream de colores y sprinkles. Ideales para fiestas infantiles.', 45000, 2, '1 día', 'Harina, huevos, mantequilla, azúcar, vainilla, leche', 'Gluten, huevo, lácteos', FALSE, TRUE, 'active', 'available', 1),
        ('Cheesecake de Frutos Rojos', 'Cheesecake cremoso con salsa de frutos rojos', 'Cheesecake suave y cremoso sobre base de galleta, cubierto con una deliciosa salsa de frutos rojos naturales.', 55000, 5, '1 día', 'Queso crema, galletas, mantequilla, azúcar, frutos rojos', 'Gluten, lácteos', TRUE, FALSE, 'active', 'available', 1),
        ('Brownies Clásicos (x6)', 'Media docena de brownies de chocolate', 'Brownies de chocolate intenso, húmedos por dentro y crujientes por fuera. Perfectos para acompañar con café.', 28000, 4, '24 horas', 'Chocolate, harina, huevos, mantequilla, azúcar, nueces', 'Gluten, huevo, lácteos, frutos secos', FALSE, FALSE, 'active', 'available', 1),
        ('Galletas Decoradas (x20)', 'Set de 20 galletas decoradas personalizadas', 'Galletas de mantequilla decoradas con glaseado real. Diseños personalizables según la ocasión.', 50000, 3, '3 días', 'Harina, mantequilla, azúcar, huevos, vainilla', 'Gluten, huevo, lácteos', FALSE, TRUE, 'active', 'available', 1)
    ");
    echo "   ✓ 5 productos insertados\n\n";

    // 4. Crear tablas adicionales
    echo "4. Creando tablas adicionales...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_product_image (
            id_image SERIAL PRIMARY KEY,
            product_id INT NOT NULL REFERENCES tbl_product(id_product) ON DELETE CASCADE,
            image_path VARCHAR(500) NOT NULL,
            is_main BOOLEAN DEFAULT FALSE,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT NOW()
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_product_variant (
            id_variant SERIAL PRIMARY KEY,
            product_id INT NOT NULL REFERENCES tbl_product(id_product) ON DELETE CASCADE,
            variant_name VARCHAR(100) NOT NULL,
            variant_type VARCHAR(50) DEFAULT 'size',
            price DECIMAL(10,2) NOT NULL,
            is_available BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT NOW()
        )
    ");
    echo "   ✓ Tablas adicionales creadas\n\n";

    echo "✅ PRODUCTOS CREADOS EXITOSAMENTE!\n\n";

    $products = $pdo->query("SELECT name, price FROM tbl_product ORDER BY id_product")->fetchAll(PDO::FETCH_ASSOC);
    echo "Productos disponibles:\n";
    foreach ($products as $p) {
        echo "  - {$p['name']} - \$" . number_format($p['price'], 0, ',', '.') . "\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
