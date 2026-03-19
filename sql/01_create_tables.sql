-- ============================================================
--  INVENTORY / SHOP MANAGEMENT SYSTEM
--  File 01: Create Database & Tables
--  Author: Your Name
--  Date: 2024
-- ============================================================

-- Step 1: Create and select the database
CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

-- ============================================================
-- TABLE 1: category
-- Purpose: Groups products into categories (e.g. Electronics)
-- Why separate? Avoids repeating category name in every product row (normalization)
-- ============================================================
CREATE TABLE category (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description   TEXT
);

-- ============================================================
-- TABLE 2: supplier
-- Purpose: Stores supplier/vendor details
-- Why separate? One supplier can provide many products — storing
--              supplier info here avoids duplication in product table
-- ============================================================
CREATE TABLE supplier (
    supplier_id   INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    phone         VARCHAR(15),
    email         VARCHAR(100),
    address       TEXT
);

-- ============================================================
-- TABLE 3: product
-- Purpose: Core table — every item available in the shop
-- Links to: category (what type), supplier (who provides it)
-- ============================================================
CREATE TABLE product (
    product_id   INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category_id  INT NOT NULL,
    supplier_id  INT NOT NULL,
    price        DECIMAL(10, 2) NOT NULL,
    reorder_level INT DEFAULT 5,        -- alert when stock falls below this
    FOREIGN KEY (category_id) REFERENCES category(category_id),
    FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id)
);

-- ============================================================
-- TABLE 4: stock
-- Purpose: Tracks current quantity of each product
-- Why separate from product? Stock changes frequently (every sale).
--   Keeping it separate avoids modifying the product table constantly.
-- Relationship: One product has exactly ONE stock record (1-to-1)
-- ============================================================
CREATE TABLE stock (
    stock_id     INT AUTO_INCREMENT PRIMARY KEY,
    product_id   INT NOT NULL UNIQUE,   -- UNIQUE ensures 1-to-1 with product
    quantity     INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);

-- ============================================================
-- TABLE 5: customer
-- Purpose: Stores customer information
-- Why store customers? Enables purchase history, reports, loyalty tracking
-- ============================================================
CREATE TABLE customer (
    customer_id   INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone         VARCHAR(15),
    email         VARCHAR(100)
);

-- ============================================================
-- TABLE 6: sale
-- Purpose: Represents one complete transaction / bill
-- Links to: customer (who bought)
-- total_amount is updated automatically via trigger (see file 03)
-- ============================================================
CREATE TABLE sale (
    sale_id      INT AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT NOT NULL,
    sale_date    DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
);

-- ============================================================
-- TABLE 7: sale_items
-- Purpose: Each row = one product line on a bill
-- Why separate from sale? One sale has MANY products.
--   This is the bridge/junction table between sale and product.
-- Both quantity and unit_price are stored here because:
--   - unit_price may change later; we preserve the price AT TIME OF SALE
--   - subtotal = quantity * unit_price (computed automatically)
-- ============================================================
CREATE TABLE sale_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id      INT NOT NULL,
    product_id   INT NOT NULL,
    quantity     INT NOT NULL CHECK (quantity > 0),
    unit_price   DECIMAL(10, 2) NOT NULL,
    subtotal     DECIMAL(10, 2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (sale_id)    REFERENCES sale(sale_id),
    FOREIGN KEY (product_id) REFERENCES product(product_id)
);

-- ============================================================
-- Verification: List all created tables
-- ============================================================
SHOW TABLES;
