-- ============================================================
--  INVENTORY / SHOP MANAGEMENT SYSTEM
--  File 03: Triggers
--  Purpose: Automate stock deduction and bill total updates
--  IMPORTANT: Run this file BEFORE inserting into sale_items
-- ============================================================

USE inventory_db;

-- Change delimiter so MySQL doesn't confuse semicolons inside the trigger
DELIMITER $$

-- ============================================================
-- TRIGGER 1: Reduce stock after a sale item is inserted
--
-- When?  After every INSERT into sale_items
-- What?  Automatically reduces the stock quantity of that product
-- Why?   Without this, we'd have to manually update stock every time
--        a sale is made — error-prone and easy to forget
--
-- How it works:
--   NEW.product_id → the product that was just sold
--   NEW.quantity   → how many units were sold
--   We subtract NEW.quantity from stock.quantity for that product
-- ============================================================
CREATE TRIGGER reduce_stock_after_sale
AFTER INSERT ON sale_items
FOR EACH ROW
BEGIN
    UPDATE stock
    SET quantity = quantity - NEW.quantity
    WHERE product_id = NEW.product_id;
END$$

-- ============================================================
-- TRIGGER 2: Update sale total after a sale item is inserted
--
-- When?  After every INSERT into sale_items
-- What?  Adds the subtotal of the new item to sale.total_amount
-- Why?   The bill total should always be accurate automatically.
--        No need to calculate it manually.
--
-- How it works:
--   NEW.subtotal → quantity * unit_price (computed column)
--   We ADD it to the sale's total_amount
-- ============================================================
CREATE TRIGGER update_sale_total
AFTER INSERT ON sale_items
FOR EACH ROW
BEGIN
    UPDATE sale
    SET total_amount = total_amount + NEW.subtotal
    WHERE sale_id = NEW.sale_id;
END$$

-- ============================================================
-- TRIGGER 3: Restore stock if a sale item is deleted
--
-- When?  After a DELETE on sale_items (e.g. cancelling a line item)
-- What?  Adds quantity back to stock
-- Why?   If an item is removed from a bill, the stock must be restored
-- ============================================================
CREATE TRIGGER restore_stock_on_delete
AFTER DELETE ON sale_items
FOR EACH ROW
BEGIN
    UPDATE stock
    SET quantity = quantity + OLD.quantity
    WHERE product_id = OLD.product_id;
END$$

-- ============================================================
-- TRIGGER 4: Reverse total if a sale item is deleted
--
-- When?  After a DELETE on sale_items
-- What?  Subtracts the deleted item's subtotal from sale total
-- ============================================================
CREATE TRIGGER reverse_sale_total_on_delete
AFTER DELETE ON sale_items
FOR EACH ROW
BEGIN
    UPDATE sale
    SET total_amount = total_amount - OLD.subtotal
    WHERE sale_id = OLD.sale_id;
END$$

-- Reset delimiter back to normal
DELIMITER ;

-- ============================================================
-- Verify triggers were created
-- ============================================================
SHOW TRIGGERS;
