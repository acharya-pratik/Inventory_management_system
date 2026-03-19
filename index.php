<?php
// ============================================================
//  index.php — Dashboard / Home Page
//  Shows: total products, total sales, revenue, low stock count
// ============================================================
require_once 'includes/db.php';

// --- Fetch summary numbers for the dashboard cards ---

// Total number of products
$total_products = $pdo->query("SELECT COUNT(*) FROM product")->fetchColumn();

// Total number of customers
$total_customers = $pdo->query("SELECT COUNT(*) FROM customer")->fetchColumn();

// Total sales count
$total_sales = $pdo->query("SELECT COUNT(*) FROM sale")->fetchColumn();

// Total revenue earned
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM sale")->fetchColumn();
$total_revenue = $total_revenue ? number_format($total_revenue, 2) : '0.00';

// Low stock items (quantity <= reorder_level)
$low_stock_count = $pdo->query("
    SELECT COUNT(*) FROM stock st
    JOIN product p ON st.product_id = p.product_id
    WHERE st.quantity <= p.reorder_level
")->fetchColumn();

// Recent 5 sales
$recent_sales = $pdo->query("
    SELECT sa.sale_id, c.customer_name, sa.sale_date, sa.total_amount
    FROM sale sa
    JOIN customer c ON sa.customer_id = c.customer_id
    ORDER BY sa.sale_date DESC
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Manager — Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }

        /* Navigation */
        nav {
            background: #1e293b;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }

        /* Page layout */
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { font-size: 22px; margin-bottom: 20px; color: #1e293b; }

        /* Summary cards */
        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .card .label { font-size: 13px; color: #64748b; margin-bottom: 8px; }
        .card .value { font-size: 28px; font-weight: 700; }
        .card.blue   .value { color: #2563eb; }
        .card.green  .value { color: #16a34a; }
        .card.purple .value { color: #7c3aed; }
        .card.red    .value { color: #dc2626; }

        /* Table */
        .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .table-box h3 { font-size: 16px; margin-bottom: 16px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f8fafc; text-align: left; padding: 10px 14px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }

        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.warning { background: #fef3c7; color: #b45309; }
    </style>
</head>
<body>

<nav>
    <span class="brand">🏪 Inventory Manager</span>
    <a href="index.php" class="active">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="new_sale.php">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
    <a href="add_customer.php">Add Customer</a>
</nav>

<div class="container">
    <h2>Dashboard</h2>

    <!-- Summary Cards -->
    <div class="cards">
        <div class="card blue">
            <div class="label">Total Products</div>
            <div class="value"><?= $total_products ?></div>
        </div>
        <div class="card green">
            <div class="label">Total Revenue</div>
            <div class="value">Rs. <?= $total_revenue ?></div>
        </div>
        <div class="card purple">
            <div class="label">Total Sales</div>
            <div class="value"><?= $total_sales ?></div>
        </div>
        <div class="card red">
            <div class="label">Low Stock Items</div>
            <div class="value"><?= $low_stock_count ?></div>
        </div>
    </div>

    <!-- Recent Sales Table -->
    <div class="table-box">
        <h3>Recent Sales</h3>
        <table>
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_sales as $sale): ?>
                <tr>
                    <td>#<?= $sale['sale_id'] ?></td>
                    <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                    <td><?= date('d M Y, h:i A', strtotime($sale['sale_date'])) ?></td>
                    <td>Rs. <?= number_format($sale['total_amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
