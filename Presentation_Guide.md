# Presentation: Modern Inventory Management System
**Topic:** Database Management System (DBMS) Project
**Duration:** 10-15 Minutes
**Pages:** 14

---

## Slide 1: Title Page
- **Project Title:** Modern Inventory Management System
- **Subtitle:** A Full-Stack Approach to Retail Stock and Sales Management
- **Key Focus:** DBMS Architecture, Normalization, and Automation
- **Presenter Name:** [Your Name]

## Slide 2: Introduction
- **Problem Statement:** Small businesses struggle with manual tracking, leading to stockouts or overstocking.
- **Solution:** A centralized DBMS to manage products, suppliers, customers, and sales in real-time.
- **Goal:** To create a robust, scalable, and user-friendly inventory solution.

## Slide 3: Objectives
- Centralize all inventory and sales data.
- Automate stock updates using Database Triggers.
- Ensure Data Integrity through Foreign Key constraints.
- Provide actionable insights via SQL-driven reporting.

## Slide 4: Technology Stack
- **DBMS:** MySQL 8.0 (Relational Database)
- **Backend:** PHP 8.2 (Using PDO for SQL Injection Protection)
- **Frontend:** HTML5, Modern CSS, JavaScript (ES6)
- **Deployment:** Docker & Docker Compose (Containerization)

## Slide 5: Database Schema Overview
- **Type:** Relational Database (RDBMS)
- **Total Tables:** 7
- **Key Entities:** Categories, Suppliers, Products, Stock, Customers, Sales, Sale Items.
- **Primary Keys:** Used in every table for unique identification.

## Slide 6: Entity-Relationship Diagram (ERD)
- **Relationships:**
  - One-to-Many (Supplier -> Product)
  - One-to-Many (Category -> Product)
  - One-to-One (Product -> Stock)
  - One-to-Many (Customer -> Sale)
  - One-to-Many (Sale -> Sale Items)

## Slide 7: Database Normalization
- **1NF, 2NF, 3NF:** How we avoided redundancy.
- **Example:** Moving Supplier and Category into separate tables instead of storing strings in the Product table.
- **Benefit:** Reduces data duplication and storage space.

## Slide 8: Data Integrity & Constraints
- **Foreign Keys:** Ensuring every sale belongs to a real customer and every product to a real supplier.
- **Check Constraints:** Preventing negative quantities in sales.
- **Atomic Operations:** Using SQL Transactions to ensure a sale is only saved if stock is available.

## Slide 9: Automation with Triggers
- **Stock Deduction:** `AFTER INSERT` on `sale_items` automatically reduces `stock` quantity.
- **Total Calculation:** `AFTER INSERT` on `sale_items` updates the `total_amount` in the `sale` table.
- **Stock Restoration:** `AFTER DELETE` on `sale_items` restores stock if a bill is modified.

## Slide 10: Advanced SQL Queries (Reports)
- **Daily Revenue:** Grouping sales by date.
- **Top Sellers:** Joining `sale_items` and `product` to find the highest-volume products.
- **Category Insights:** Aggregating revenue by category to see what's most profitable.

## Slide 11: Real-time Stock Validation
- How the system prevents "Ghost Sales."
- **Process:** Before a sale is finalized, the system queries the `stock` table to verify availability.
- **UI Feedback:** Instant stock badges shown on the sales page.

## Slide 12: System Security
- **SQL Injection Prevention:** Use of Prepared Statements in PHP.
- **Data Safety:** Using Docker Volumes to persist database data across container restarts.

## Slide 13: Project Demonstration (Screenshots)
- **Dashboard:** At a glance summary.
- **Optimized Sales Page:** The heart of the system.
- **Reports:** Data-driven decision making.
- **Responsive Design:** Working on all devices.

## Slide 14: Conclusion & Future Scope
- **Conclusion:** Successfully built a normalized, automated, and secure DBMS-driven application.
- **Future Scope:**
  - AI-based demand forecasting.
  - Integration with Barcode Scanners.
  - Multi-warehouse support.

---
**Thank You! Questions?**
