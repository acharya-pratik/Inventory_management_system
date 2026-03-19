-- ============================================================
--  INVENTORY / SHOP MANAGEMENT SYSTEM
--  File 02: Insert Sample Data
--  Purpose: Populate all tables with realistic test data
-- ============================================================

USE inventory_db;

-- ============================================================
-- Insert Categories (5 rows)
-- ============================================================
INSERT INTO category (category_name, description) VALUES
('Electronics',     'Gadgets, devices and electronic accessories'),
('Groceries',       'Daily use food and beverage items'),
('Stationery',      'Office and school supplies'),
('Clothing',        'Apparel and fashion items'),
('Home Appliances', 'Kitchen and home use appliances');

-- ============================================================
-- Insert Suppliers (5 rows)
-- ============================================================
INSERT INTO supplier (supplier_name, phone, email, address) VALUES
('TechWorld Pvt Ltd',   '9800000001', 'techworld@email.com',   'Kathmandu, Nepal'),
('FreshMart Supplies',  '9800000002', 'freshmart@email.com',   'Biratnagar, Nepal'),
('OfficeHub Co.',       '9800000003', 'officehub@email.com',   'Pokhara, Nepal'),
('FashionLine Nepal',   '9800000004', 'fashionline@email.com', 'Lalitpur, Nepal'),
('HomeEssentials Ltd',  '9800000005', 'homeessentials@email.com', 'Butwal, Nepal');

-- ============================================================
-- Insert Products (10 rows)
-- ============================================================
INSERT INTO product (product_name, category_id, supplier_id, price, reorder_level) VALUES
('Smartphone X10',      1, 1, 25000.00, 3),
('Wireless Earbuds',    1, 1,  3500.00, 5),
('Rice 5kg',            2, 2,   600.00, 10),
('Cooking Oil 1L',      2, 2,   280.00, 10),
('Notebook A4 (Pack)',  3, 3,   150.00, 15),
('Ball Pen Box',        3, 3,    80.00, 20),
('Men\'s T-Shirt',      4, 4,   850.00, 8),
('Women\'s Kurti',      4, 4,  1200.00, 8),
('Electric Kettle',     5, 5,  1800.00, 4),
('Ceiling Fan',         5, 5,  4500.00, 3);

-- ============================================================
-- Insert Stock (10 rows — one per product)
-- ============================================================
INSERT INTO stock (product_id, quantity) VALUES
(1,  15),   -- Smartphone X10
(2,  40),   -- Wireless Earbuds
(3, 100),   -- Rice 5kg
(4,  80),   -- Cooking Oil 1L
(5,  60),   -- Notebook A4
(6,  90),   -- Ball Pen Box
(7,  35),   -- Men's T-Shirt
(8,  30),   -- Women's Kurti
(9,  20),   -- Electric Kettle
(10, 12);   -- Ceiling Fan

-- ============================================================
-- Insert Customers (5 rows)
-- ============================================================
INSERT INTO customer (customer_name, phone, email) VALUES
('Aarav Sharma',    '9811111111', 'aarav@gmail.com'),
('Priya Thapa',     '9822222222', 'priya@gmail.com'),
('Rajan Karki',     '9833333333', 'rajan@gmail.com'),
('Sunita Rai',      '9844444444', 'sunita@gmail.com'),
('Bikash Limbu',    '9855555555', 'bikash@gmail.com');

-- ============================================================
-- Insert Sales (5 bills)
-- ============================================================
INSERT INTO sale (customer_id, sale_date) VALUES
(1, '2024-11-01 10:30:00'),
(2, '2024-11-02 11:00:00'),
(3, '2024-11-03 14:15:00'),
(4, '2024-11-04 09:45:00'),
(5, '2024-11-05 16:00:00');

-- ============================================================
-- Insert Sale Items
-- NOTE: Inserting into sale_items will automatically:
--   1. Reduce stock (via trigger from file 03)
--   2. Update sale total (via trigger from file 03)
-- So make sure you run file 03 BEFORE inserting here!
-- ============================================================
INSERT INTO sale_items (sale_id, product_id, quantity, unit_price) VALUES
-- Sale 1: Aarav buys a Smartphone and Earbuds
(1, 1, 1, 25000.00),
(1, 2, 1,  3500.00),

-- Sale 2: Priya buys Groceries
(2, 3, 2,   600.00),
(2, 4, 3,   280.00),

-- Sale 3: Rajan buys Stationery
(3, 5, 4,   150.00),
(3, 6, 2,    80.00),

-- Sale 4: Sunita buys Clothing
(4, 7, 2,   850.00),
(4, 8, 1,  1200.00),

-- Sale 5: Bikash buys Appliances
(5, 9,  1, 1800.00),
(5, 10, 1, 4500.00);

-- ============================================================
-- Verification Queries
-- ============================================================
SELECT * FROM category;
SELECT * FROM supplier;
SELECT * FROM product;
SELECT * FROM stock;
SELECT * FROM customer;
SELECT * FROM sale;
SELECT * FROM sale_items;
