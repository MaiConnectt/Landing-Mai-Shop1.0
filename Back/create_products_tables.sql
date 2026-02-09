-- =====================================================
-- Mai Shop - Products Module Database Schema
-- =====================================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS tbl_product_variant CASCADE;
DROP TABLE IF EXISTS tbl_product_image CASCADE;
DROP TABLE IF EXISTS tbl_product CASCADE;
DROP TABLE IF EXISTS tbl_category CASCADE;

-- =====================================================
-- Table: tbl_category
-- Description: Product categories (Tortas, Cupcakes, etc.)
-- =====================================================
CREATE TABLE tbl_category (
    id_category SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50), -- Font Awesome icon class (e.g., 'fa-birthday-cake')
    color VARCHAR(7), -- Hex color for UI (e.g., '#c97c89')
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Insert initial categories
INSERT INTO tbl_category (name, description, icon, color, display_order) VALUES
('Tortas', 'Tortas personalizadas para toda ocasión', 'fa-birthday-cake', '#c97c89', 1),
('Cupcakes', 'Deliciosos cupcakes decorados', 'fa-cupcake', '#e6c86e', 2),
('Galletas', 'Galletas artesanales', 'fa-cookie', '#a8d5ba', 3),
('Brownies', 'Brownies de chocolate', 'fa-square', '#8b7355', 4),
('Cheesecakes', 'Cheesecakes cremosos', 'fa-cheese', '#f7e6e9', 5),
('Postres', 'Variedad de postres', 'fa-ice-cream', '#ffa8c5', 6);

-- =====================================================
-- Table: tbl_product
-- Description: Main products table
-- =====================================================
CREATE TABLE tbl_product (
    id_product SERIAL PRIMARY KEY,
    
    -- Basic Information
    name VARCHAR(255) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    
    -- Pricing
    price DECIMAL(10,2) NOT NULL,
    
    -- Category
    category_id INT REFERENCES tbl_category(id_category) ON DELETE SET NULL,
    
    -- Availability
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    stock_status VARCHAR(20) DEFAULT 'available' CHECK (stock_status IN ('available', 'out_of_stock')),
    
    -- Additional Information
    preparation_time VARCHAR(50), -- e.g., '1 día', '2 días', '24 horas'
    ingredients TEXT,
    allergens TEXT,
    
    -- Features
    is_featured BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    
    -- Main Image
    main_image VARCHAR(500),
    
    -- Metadata
    created_by INT REFERENCES tbl_user(id_user) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- =====================================================
-- Table: tbl_product_image
-- Description: Additional product images (gallery)
-- =====================================================
CREATE TABLE tbl_product_image (
    id_image SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES tbl_product(id_product) ON DELETE CASCADE,
    image_path VARCHAR(500) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

-- =====================================================
-- Table: tbl_product_variant
-- Description: Product variants (sizes, flavors, etc.)
-- =====================================================
CREATE TABLE tbl_product_variant (
    id_variant SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES tbl_product(id_product) ON DELETE CASCADE,
    variant_name VARCHAR(100) NOT NULL, -- e.g., 'Pequeño', 'Mediano', 'Grande'
    variant_type VARCHAR(50) DEFAULT 'size', -- 'size', 'flavor', 'custom'
    price DECIMAL(10,2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- =====================================================
-- Indexes for Performance
-- =====================================================
CREATE INDEX idx_product_category ON tbl_product(category_id);
CREATE INDEX idx_product_status ON tbl_product(status);
CREATE INDEX idx_product_featured ON tbl_product(is_featured);
CREATE INDEX idx_product_name ON tbl_product(name);
CREATE INDEX idx_product_image_product ON tbl_product_image(product_id);
CREATE INDEX idx_product_variant_product ON tbl_product_variant(product_id);

-- =====================================================
-- Sample Data (Optional - for testing)
-- =====================================================
INSERT INTO tbl_product (
    name, 
    short_description, 
    description, 
    price, 
    category_id, 
    preparation_time,
    ingredients,
    allergens,
    is_featured,
    is_new,
    status,
    stock_status,
    created_by
) VALUES
(
    'Torta de Chocolate Premium',
    'Deliciosa torta de chocolate con cobertura de ganache',
    'Torta de tres capas de chocolate húmedo con relleno de ganache de chocolate belga y cobertura de chocolate negro. Perfecta para celebraciones especiales.',
    85000,
    1, -- Tortas
    '2 días',
    'Harina, huevos, chocolate, mantequilla, azúcar, leche',
    'Gluten, huevo, lácteos',
    TRUE, -- Featured
    FALSE,
    'active',
    'available',
    1 -- Admin user
),
(
    'Cupcakes de Vainilla (x12)',
    'Docena de cupcakes de vainilla con buttercream',
    'Set de 12 cupcakes esponjosos de vainilla decorados con buttercream de colores y sprinkles. Ideales para fiestas infantiles.',
    45000,
    2, -- Cupcakes
    '1 día',
    'Harina, huevos, mantequilla, azúcar, vainilla, leche',
    'Gluten, huevo, lácteos',
    FALSE,
    TRUE, -- New
    'active',
    'available',
    1
),
(
    'Cheesecake de Frutos Rojos',
    'Cheesecake cremoso con salsa de frutos rojos',
    'Cheesecake suave y cremoso sobre base de galleta, cubierto con una deliciosa salsa de frutos rojos naturales.',
    55000,
    5, -- Cheesecakes
    '1 día',
    'Queso crema, galletas, mantequilla, azúcar, frutos rojos',
    'Gluten, lácteos',
    TRUE,
    FALSE,
    'active',
    'available',
    1
),
(
    'Brownies Clásicos (x6)',
    'Media docena de brownies de chocolate',
    'Brownies de chocolate intenso, húmedos por dentro y crujientes por fuera. Perfectos para acompañar con café.',
    28000,
    4, -- Brownies
    '24 horas',
    'Chocolate, harina, huevos, mantequilla, azúcar, nueces',
    'Gluten, huevo, lácteos, frutos secos',
    FALSE,
    FALSE,
    'active',
    'available',
    1
),
(
    'Galletas Decoradas (x20)',
    'Set de 20 galletas decoradas personalizadas',
    'Galletas de mantequilla decoradas con glaseado real. Diseños personalizables según la ocasión.',
    50000,
    3, -- Galletas
    '3 días',
    'Harina, mantequilla, azúcar, huevos, vainilla',
    'Gluten, huevo, lácteos',
    FALSE,
    TRUE,
    'active',
    'available',
    1
);

-- =====================================================
-- Comments
-- =====================================================
COMMENT ON TABLE tbl_category IS 'Product categories for Mai Shop';
COMMENT ON TABLE tbl_product IS 'Main products catalog';
COMMENT ON TABLE tbl_product_image IS 'Additional product images (gallery)';
COMMENT ON TABLE tbl_product_variant IS 'Product variants (sizes, flavors, etc.)';

COMMENT ON COLUMN tbl_product.status IS 'Product status: active or inactive';
COMMENT ON COLUMN tbl_product.stock_status IS 'Stock availability: available or out_of_stock';
COMMENT ON COLUMN tbl_product.is_featured IS 'Featured products shown prominently';
COMMENT ON COLUMN tbl_product.is_new IS 'New products with NEW badge';
