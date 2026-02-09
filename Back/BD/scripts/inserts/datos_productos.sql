-- =====================================================
-- Inserts de Productos - Mai Shop
-- =====================================================
-- Archivo: datos_productos.sql
-- Descripción: Datos iniciales de productos para el catálogo
-- Ejecutar después de MaiConnect.sql
-- =====================================================

-- Insertar productos de ejemplo
INSERT INTO tbl_product (id_product, product_name, price, description, stock) VALUES
(1, 'Torta de Chocolate Premium', 85000, 'Torta de tres capas de chocolate húmedo con relleno de ganache de chocolate belga y cobertura de chocolate negro. Perfecta para celebraciones especiales.', 10),
(2, 'Cupcakes de Vainilla (x12)', 45000, 'Set de 12 cupcakes esponjosos de vainilla decorados con buttercream de colores y sprinkles. Ideales para fiestas infantiles.', 20),
(3, 'Cheesecake de Frutos Rojos', 55000, 'Cheesecake suave y cremoso sobre base de galleta, cubierto con una deliciosa salsa de frutos rojos naturales.', 15),
(4, 'Brownies Clásicos (x6)', 28000, 'Brownies de chocolate intenso, húmedos por dentro y crujientes por fuera. Perfectos para acompañar con café.', 25),
(5, 'Galletas Decoradas (x20)', 50000, 'Galletas de mantequilla decoradas con glaseado real. Diseños personalizables según la ocasión.', 30),
(6, 'Torta Red Velvet', 75000, 'Clásica torta red velvet con capas suaves y húmedas, rellena y cubierta con frosting de queso crema. Un clásico irresistible.', 8),
(7, 'Mini Cheesecakes (x6)', 38000, 'Set de 6 mini cheesecakes individuales. Disponibles en varios sabores: natural, frutos rojos, chocolate.', 12),
(8, 'Torta de Zanahoria', 68000, 'Torta húmeda de zanahoria con nueces, canela y especias, cubierta con frosting de queso crema.', 10),
(9, 'Macarons Franceses (x12)', 42000, 'Docena de macarons franceses en variedad de sabores: vainilla, chocolate, frambuesa, limón, pistacho.', 15),
(10, 'Pie de Limón', 48000, 'Pie de limón con base crujiente de galleta, relleno cremoso de limón y merengue italiano tostado.', 10);

-- Comentarios
COMMENT ON TABLE tbl_product IS 'Catálogo de productos disponibles para venta';

-- Verificación
SELECT COUNT(*) as total_productos FROM tbl_product;
