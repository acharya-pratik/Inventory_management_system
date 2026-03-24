<?php
// ============================================================
//  reports.php — Sales Reports
// ============================================================
require_once 'includes/db.php';

// Daily revenue
$daily = $pdo->query("
    SELECT DATE(sale_date) AS day, COUNT(*) AS total_sales, SUM(total_amount) AS revenue
    FROM sale GROUP BY DATE(sale_date) ORDER BY day DESC
")->fetchAll();

// Best selling products
$best = $pdo->query("
    SELECT p.product_name, c.category_name,
           SUM(si.quantity) AS units_sold,
           SUM(si.subtotal) AS revenue
    FROM sale_items si
    JOIN product p  ON si.product_id = p.product_id
    JOIN category c ON p.category_id = c.category_id
    GROUP BY p.product_id ORDER BY units_sold DESC
")->fetchAll();

// Revenue by category
$by_cat = $pdo->query("
    SELECT c.category_name, SUM(si.subtotal) AS revenue
    FROM sale_items si
    JOIN product p  ON si.product_id = p.product_id
    JOIN category c ON p.category_id = c.category_id
    GROUP BY c.category_id ORDER BY revenue DESC
")->fetchAll();

// Recent Sales for the new section
$recent_sales = $pdo->query("
    SELECT sa.sale_id, c.customer_name, sa.sale_date, sa.total_amount
    FROM sale sa
    JOIN customer c ON sa.customer_id = c.customer_id
    ORDER BY sa.sale_date DESC
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 24px; }
        h3 { font-size: 16px; color: #1e293b; margin-bottom: 14px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f8fafc; text-align: left; padding: 10px 14px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
    </style>
</head>
<body>
<nav>
    <span class="brand">🏪 Inventory Manager</span>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="categories.php">Categories</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="new_sale_v2.php">New Sale</a>
    <a href="reports.php" class="active">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <h2>Sales Reports</h2>

    <div class="grid">
        <!-- Daily Revenue -->
        <div class="table-box">
            <h3>Daily Revenue</h3>
            <table>
                <thead><tr><th>Date</th><th>Sales</th><th>Revenue (Rs.)</th></tr></thead>
                <tbody>
                    <?php foreach ($daily as $d): ?>
                    <tr>
                        <td><?= $d['day'] ?></td>
                        <td><?= $d['total_sales'] ?></td>
                        <td><?= number_format($d['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Revenue by Category -->
        <div class="table-box">
            <h3>Revenue by Category</h3>
            <table>
                <thead><tr><th>Category</th><th>Revenue (Rs.)</th></tr></thead>
                <tbody>
                    <?php foreach ($by_cat as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['category_name']) ?></td>
                        <td><?= number_format($cat['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Best Selling Products -->
    <div class="table-box">
        <h3>Best Selling Products</h3>
        <table>
            <thead><tr><th>Product</th><th>Category</th><th>Units Sold</th><th>Revenue (Rs.)</th></tr></thead>
            <tbody>
                <?php foreach ($best as $b): ?>
                <tr>
                    <td><?= htmlspecialchars($b['product_name']) ?></td>
                    <td><?= htmlspecialchars($b['category_name']) ?></td>
                    <td><?= $b['units_sold'] ?></td>
                    <td><?= number_format($b['revenue'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_sales as $sale): ?>
                <tr>
                    <td>#<?= $sale['sale_id'] ?></td>
                    <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                    <td><?= date('d M Y, h:i A', strtotime($sale['sale_date'])) ?></td>
                    <td>Rs. <?= number_format($sale['total_amount'], 2) ?></td>
                    <td><a href="view_sale.php?id=<?= $sale['sale_id'] ?>" style="color: #2563eb; text-decoration: none; font-weight: 600;">View Details</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
