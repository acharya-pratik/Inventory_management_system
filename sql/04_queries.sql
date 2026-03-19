-- ============================================================
--  INVENTORY / SHOP MANAGEMENT SYSTEM
--  File 04: Queries — Reports & Lookups
--  Purpose: Demonstrate real-world use of the database
-- ============================================================

USE inventory_db;

-- ============================================================
-- QUERY 1: View all products with category and supplier name
-- Concept: JOIN across 3 tables
-- Use: Full product catalogue view
-- ============================================================
SELECT
    p.product_id,
    p.product_name,
    c.category_name,
    s.supplier_name,
    p.price,
    p.reorder_level
FROM product p
JOIN category c ON p.category_id = c.category_id
JOIN supplier s ON p.supplier_id = s.supplier_id
ORDER BY c.category_name, p.product_name;

-- ============================================================
-- QUERY 2: Current stock levels for all products
-- Concept: JOIN product + stock
-- Use: View what's in the warehouse right now
-- ============================================================
SELECT
    p.product_id,
    p.product_name,
    c.category_name,
    st.quantity AS stock_quantity,
    p.reorder_level,
    CASE
        WHEN st.quantity = 0            THEN 'OUT OF STOCK'
        WHEN st.quantity <= p.reorder_level THEN 'LOW STOCK'
        ELSE                                 'OK'
    END AS stock_status,
    st.last_updated
FROM product p
JOIN category c ON p.category_id = c.category_id
JOIN stock st   ON p.product_id  = st.product_id
ORDER BY stock_status, st.quantity;

-- ============================================================
-- QUERY 3: Low stock alert — products needing reorder
-- Concept: JOIN + WHERE condition
-- Use: Daily alert for shop owner to restock items
-- ============================================================
SELECT
    p.product_name,
    s.supplier_name,
    s.phone AS supplier_contact,
    st.quantity AS current_stock,
    p.reorder_level
FROM product p
JOIN stock    st ON p.product_id  = st.product_id
JOIN supplier s  ON p.supplier_id = s.supplier_id
WHERE st.quantity <= p.reorder_level
ORDER BY st.quantity ASC;

-- ============================================================
-- QUERY 4: Complete sales report — all bills with customer info
-- Concept: JOIN sale + customer
-- Use: View all transactions
-- ============================================================
SELECT
    sa.sale_id,
    c.customer_name,
    c.phone,
    sa.sale_date,
    sa.total_amount
FROM sale sa
JOIN customer c ON sa.customer_id = c.customer_id
ORDER BY sa.sale_date DESC;

-- ============================================================
-- QUERY 5: Detailed bill — show all items in each sale
-- Concept: JOIN sale_items + product + customer + sale
-- Use: Print/view a full invoice
-- ============================================================
SELECT
    sa.sale_id,
    c.customer_name,
    p.product_name,
    si.quantity,
    si.unit_price,
    si.subtotal,
    sa.sale_date,
    sa.total_amount AS bill_total
FROM sale_items si
JOIN sale     sa ON si.sale_id    = sa.sale_id
JOIN product   p ON si.product_id = p.product_id
JOIN customer  c ON sa.customer_id = c.customer_id
ORDER BY sa.sale_id, si.sale_item_id;

-- ============================================================
-- QUERY 6: Total sales per day
-- Concept: GROUP BY + SUM + DATE()
-- Use: Daily revenue report
-- ============================================================
SELECT
    DATE(sale_date)       AS sale_day,
    COUNT(sale_id)        AS total_transactions,
    SUM(total_amount)     AS daily_revenue
FROM sale
GROUP BY DATE(sale_date)
ORDER BY sale_day DESC;

-- ============================================================
-- QUERY 7: Best-selling products (by quantity sold)
-- Concept: GROUP BY + SUM + ORDER BY
-- Use: Find most popular products
-- ============================================================
SELECT
    p.product_name,
    c.category_name,
    SUM(si.quantity)   AS total_units_sold,
    SUM(si.subtotal)   AS total_revenue
FROM sale_items si
JOIN product  p ON si.product_id  = p.product_id
JOIN category c ON p.category_id  = c.category_id
GROUP BY p.product_id, p.product_name, c.category_name
ORDER BY total_units_sold DESC;

-- ============================================================
-- QUERY 8: Revenue by category
-- Concept: JOIN + GROUP BY + SUM
-- Use: See which product category earns the most
-- ============================================================
SELECT
    c.category_name,
    COUNT(DISTINCT si.sale_id)  AS number_of_sales,
    SUM(si.quantity)            AS units_sold,
    SUM(si.subtotal)            AS total_revenue
FROM sale_items si
JOIN product  p ON si.product_id = p.product_id
JOIN category c ON p.category_id = c.category_id
GROUP BY c.category_id, c.category_name
ORDER BY total_revenue DESC;

-- ============================================================
-- QUERY 9: Customer purchase history
-- Concept: JOIN + GROUP BY per customer
-- Use: View how much each customer has spent
-- ============================================================
SELECT
    c.customer_id,
    c.customer_name,
    c.phone,
    COUNT(sa.sale_id)    AS total_purchases,
    SUM(sa.total_amount) AS total_spent
FROM customer c
LEFT JOIN sale sa ON c.customer_id = sa.customer_id
GROUP BY c.customer_id, c.customer_name, c.phone
ORDER BY total_spent DESC;

-- ============================================================
-- QUERY 10: Revenue by supplier (which supplier's products sell most)
-- Concept: Multi-table JOIN + GROUP BY
-- Use: Evaluate supplier performance
-- ============================================================
SELECT
    s.supplier_name,
    COUNT(DISTINCT p.product_id) AS products_supplied,
    SUM(si.quantity)             AS total_units_sold,
    SUM(si.subtotal)             AS total_revenue
FROM supplier s
JOIN product    p  ON s.supplier_id  = p.supplier_id
JOIN sale_items si ON p.product_id   = si.product_id
GROUP BY s.supplier_id, s.supplier_name
ORDER BY total_revenue DESC;
