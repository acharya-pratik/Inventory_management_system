<?php
// ============================================================
//  products.php — View All Products with Stock Status
// ============================================================
require_once 'includes/db.php';

// Fetch all products with category, supplier and stock info
$products = $pdo->query("
    SELECT
        p.product_id,
        p.product_name,
        c.category_name,
        s.supplier_name,
        p.price,
        p.reorder_level,
        st.quantity,
        CASE
            WHEN st.quantity = 0                 THEN 'OUT OF STOCK'
            WHEN st.quantity <= p.reorder_level  THEN 'LOW STOCK'
            ELSE                                      'OK'
        END AS stock_status
    FROM product p
    JOIN category c ON p.category_id = c.category_id
    JOIN supplier s ON p.supplier_id = s.supplier_id
    JOIN stock   st ON p.product_id  = st.product_id
    ORDER BY c.category_name, p.product_name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }
        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        h2 { font-size: 22px; color: #1e293b; }
        .btn { background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .btn:hover { background: #1d4ed8; }
        .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f8fafc; text-align: left; padding: 10px 14px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.ok      { background: #dcfce7; color: #15803d; }
        .badge.low     { background: #fef3c7; color: #b45309; }
        .badge.out     { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

<nav>
    <span class="brand">🏪 Inventory Manager</span>
    <a href="index.php">Dashboard</a>
    <a href="products.php" class="active">Products</a>
    <a href="categories.php">Categories</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="new_sale.php">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <div class="top-bar">
        <h2>All Products</h2>
        <a href="add_product.php" class="btn">+ Add Product</a>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Supplier</th>
                    <th>Price (Rs.)</th>
                    <th>Stock Qty</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= $p['product_id'] ?></td>
                    <td><?= htmlspecialchars($p['product_name']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td><?= htmlspecialchars($p['supplier_name']) ?></td>
                    <td><?= number_format($p['price'], 2) ?></td>
                    <td><?= $p['quantity'] ?></td>
                    <td><?= $p['reorder_level'] ?></td>
                    <td>
                        <?php if ($p['stock_status'] === 'OK'): ?>
                            <span class="badge ok">OK</span>
                        <?php elseif ($p['stock_status'] === 'LOW STOCK'): ?>
                            <span class="badge low">Low Stock</span>
                        <?php else: ?>
                            <span class="badge out">Out of Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= $p['product_id'] ?>" style="color: #2563eb; text-decoration: none; font-weight: 600; margin-right: 10px;">Edit</a>
                        <a href="delete_product.php?id=<?= $p['product_id'] ?>" style="color: #dc2626; text-decoration: none; font-weight: 600;" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
