-- Mai Shop - Orders Management System
-- Database Schema Creation Script
-- Execute this script in your PostgreSQL database

-- =====================================================
-- 1. CUSTOMER TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_customer (
    id_customer SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Index for faster customer searches
CREATE INDEX idx_customer_name ON tbl_customer(name);
CREATE INDEX idx_customer_phone ON tbl_customer(phone);

-- =====================================================
-- 2. ORDER TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_order (
    id_order SERIAL PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT REFERENCES tbl_customer(id_customer),
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_by INT REFERENCES tbl_user(id_user),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT chk_status CHECK (status IN ('pending', 'completed', 'cancelled')),
    CONSTRAINT chk_total_amount CHECK (total_amount >= 0)
);

-- Indexes for faster queries
CREATE INDEX idx_order_number ON tbl_order(order_number);
CREATE INDEX idx_order_customer ON tbl_order(customer_id);
CREATE INDEX idx_order_status ON tbl_order(status);
CREATE INDEX idx_order_created_at ON tbl_order(created_at);

-- =====================================================
-- 3. ORDER ITEMS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_order_item (
    id_order_item SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES tbl_order(id_order) ON DELETE CASCADE,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    
    -- Constraints
    CONSTRAINT chk_quantity CHECK (quantity > 0),
    CONSTRAINT chk_unit_price CHECK (unit_price >= 0),
    CONSTRAINT chk_subtotal CHECK (subtotal >= 0)
);

-- Index for faster order item queries
CREATE INDEX idx_order_item_order ON tbl_order_item(order_id);

-- =====================================================
-- 4. ORDER HISTORY TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS tbl_order_history (
    id_history SERIAL PRIMARY KEY,
    order_id INT NOT NULL REFERENCES tbl_order(id_order) ON DELETE CASCADE,
    old_status VARCHAR(20),
    new_status VARCHAR(20) NOT NULL,
    changed_by INT REFERENCES tbl_user(id_user),
    changed_at TIMESTAMP DEFAULT NOW(),
    notes TEXT,
    
    -- Constraints
    CONSTRAINT chk_old_status CHECK (old_status IN ('pending', 'completed', 'cancelled') OR old_status IS NULL),
    CONSTRAINT chk_new_status CHECK (new_status IN ('pending', 'completed', 'cancelled'))
);

-- Index for faster history queries
CREATE INDEX idx_order_history_order ON tbl_order_history(order_id);
CREATE INDEX idx_order_history_changed_at ON tbl_order_history(changed_at);

-- =====================================================
-- 5. SAMPLE DATA (OPTIONAL)
-- =====================================================

-- Insert sample customers
INSERT INTO tbl_customer (name, phone, email, address) VALUES
('María García', '3001234567', 'maria.garcia@email.com', 'Calle 123 #45-67, Bogotá'),
('Juan Pérez', '3009876543', 'juan.perez@email.com', 'Carrera 45 #12-34, Medellín'),
('Ana Rodríguez', '3005551234', 'ana.rodriguez@email.com', 'Avenida 68 #23-45, Cali')
ON CONFLICT DO NOTHING;

-- Note: Sample orders should be created through the web interface
-- to ensure proper order number generation and history tracking

-- =====================================================
-- 6. USEFUL QUERIES
-- =====================================================

-- View all orders with customer information
-- SELECT 
--     o.order_number,
--     c.name as customer_name,
--     o.total_amount,
--     o.status,
--     o.created_at
-- FROM tbl_order o
-- LEFT JOIN tbl_customer c ON o.customer_id = c.id_customer
-- ORDER BY o.created_at DESC;

-- View order details with items
-- SELECT 
--     o.order_number,
--     oi.product_name,
--     oi.quantity,
--     oi.unit_price,
--     oi.subtotal
-- FROM tbl_order o
-- JOIN tbl_order_item oi ON o.id_order = oi.order_id
-- WHERE o.id_order = 1;

-- View order history
-- SELECT 
--     h.changed_at,
--     u.email as changed_by,
--     h.old_status,
--     h.new_status,
--     h.notes
-- FROM tbl_order_history h
-- LEFT JOIN tbl_user u ON h.changed_by = u.id_user
-- WHERE h.order_id = 1
-- ORDER BY h.changed_at DESC;

-- =====================================================
-- SCRIPT COMPLETE
-- =====================================================
-- All tables created successfully!
-- You can now use the Orders Management System.
