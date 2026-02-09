<?php
require_once __DIR__ . '/Front/conexion.php';

echo "=== CREANDO PRODUCTOS ===\n\n";

try {
    // Verificar si ya existen
    $exists = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'tbl_product')")->fetchColumn();

    if ($exists) {
        $count = $pdo->query("SELECT COUNT(*) FROM tbl_product")->fetchColumn();
        echo "✓ tbl_product ya existe ($count productos)\n";

        if ($count > 0) {
            echo "\n✅ PRODUCTOS YA CREADOS!\n";
            exit(0);
        }
    }

    // Crear categorías
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
        );
        
        INSERT INTO tbl_category (name, description, icon, color, display_order) VALUES
        ('Tortas', 'Tortas personalizadas', 'fa-birthday-cake', '#c97c89', 1),
        ('Cupcakes', 'Deliciosos cupcakes', 'fa-cupcake', '#e6c86e', 2),
        ('Galletas', 'Galletas artesanales', 'fa-cookie', '#a8d5ba', 3),
        ('Brownies', 'Brownies de chocolate', 'fa-square', '#8b7355', 4),
        ('Cheesecakes', 'Cheesecakes cremosos', 'fa-cheese', '#f7e6e9', 5)
        ON CONFLICT (name) DO NOTHING;
    ");
    echo "   ✓ Categorías creadas\n\n";

    // Crear tabla de productos
    echo "2. Creando tabla tbl_product...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tbl_product (
            id_product SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            short_description VARCHAR(500),
            price DECIMAL(10,2) NOT NULL,
            category_id INT REFERENCES tbl_category(id_category),
            status VARCHAR(20) DEFAULT 'active',
            stock_status VARCHAR(20) DEFAULT 'available',
            preparation_time VARCHAR(50),
            ingredients TEXT,
            allergens TEXT,
            is_featured BOOLEAN DEFAULT FALSE,
            is_new BOOLEAN DEFAULT FALSE,
            display_order INT DEFAULT 0,
            main_image VARCHAR(500),
            created_by INT REFERENCES tbl_user(id_user),
            created_at TIMESTAMP DEFAULT NOW(),
            updated_at TIMESTAMP DEFAULT NOW()
        );
    ");
    echo "   ✓ Tabla creada\n\n";

    // Insertar productos
    echo "3. Insertando productos...\n";
    $pdo->exec("
        INSERT INTO tbl_product (name, short_description, description, price, category_id, preparation_time, ingredients, allergens, is_featured, status, stock_status, created_by) VALUES
        ('Torta de Chocolate Premium', 'Deliciosa torta de chocolate', 'Torta de tres capas de chocolate húmedo con ganache.', 85000, 1, '2 días', 'Harina, huevos, chocolate', 'Gluten, huevo, lácteos', TRUE, 'active', 'available', 1),
        ('Cupcakes de Vainilla (x12)', 'Docena de cupcakes', 'Set de 12 cupcakes esponjosos de vainilla.', 45000, 2, '1 día', 'Harina, huevos, vainilla', 'Gluten, huevo, lácteos', FALSE, 'active', 'available', 1),
        ('Cheesecake de Frutos Rojos', 'Cheesecake cremoso', 'Cheesecake suave y cremoso con frutos rojos.', 55000, 5, '1 día', 'Queso crema, galletas', 'Gluten, lácteos', TRUE, 'active', 'available', 1),
        ('Brownies Clásicos (x6)', 'Media docena de brownies', 'Brownies de chocolate intenso.', 28000, 4, '24 horas', 'Chocolate, harina', 'Gluten, huevo, lácteos', FALSE, 'active', 'available', 1),
        ('Galletas Decoradas (x20)', 'Set de 20 galletas', 'Galletas decoradas personalizables.', 50000, 3, '3 días', 'Harina, mantequilla', 'Gluten, huevo, lácteos', FALSE, 'active', 'available', 1);
    ");
    echo "   ✓ 5 productos creados\n\n";

    echo "✅ PRODUCTOS LISTOS!\n";

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
