<?php
// ============================================================
//  low_stock.php — Low Stock Alerts
// ============================================================
require_once 'includes/db.php';

$low_stock = $pdo->query("
    SELECT p.product_name, c.category_name,
           s.supplier_name, s.phone AS supplier_phone,
           st.quantity, p.reorder_level,
           CASE WHEN st.quantity = 0 THEN 'OUT OF STOCK' ELSE 'LOW STOCK' END AS status
    FROM product p
    JOIN stock    st ON p.product_id  = st.product_id
    JOIN category c  ON p.category_id = c.category_id
    JOIN supplier s  ON p.supplier_id = s.supplier_id
    WHERE st.quantity <= p.reorder_level
    ORDER BY st.quantity ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Low Stock — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }
        .container { max-width: 1000px; margin: 30px auto; padding: 0 20px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 6px; }
        .subtitle { color: #64748b; font-size: 14px; margin-bottom: 24px; }
        .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f8fafc; text-align: left; padding: 10px 14px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.low { background: #fef3c7; color: #b45309; }
        .badge.out { background: #fee2e2; color: #dc2626; }
        .empty { text-align: center; color: #16a34a; padding: 30px; font-size: 15px; }
    </style>
</head>
<body>
<nav>
    <span class="brand">🏪 Inventory Manager</span>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="new_sale.php">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php" class="active">Low Stock</a>
</nav>

<div class="container">
    <h2>Low Stock Alerts</h2>
    <p class="subtitle">Products that need to be reordered from suppliers.</p>

    <div class="table-box">
        <?php if (empty($low_stock)): ?>
            <div class="empty">✅ All products are sufficiently stocked!</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                    <th>Supplier</th>
                    <th>Supplier Phone</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($low_stock as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= htmlspecialchars($item['category_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= $item['reorder_level'] ?></td>
                    <td>
                        <span class="badge <?= $item['status'] === 'OUT OF STOCK' ? 'out' : 'low' ?>">
                            <?= $item['status'] ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($item['supplier_name']) ?></td>
                    <td><?= $item['supplier_phone'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
