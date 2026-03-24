<?php
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: reports.php');
    exit;
}

// Fetch sale details
$stmt = $pdo->prepare("
    SELECT sa.sale_id, sa.sale_date, sa.total_amount, c.customer_name, c.phone, c.email
    FROM sale sa
    JOIN customer c ON sa.customer_id = c.customer_id
    WHERE sa.sale_id = ?
");
$stmt->execute([$id]);
$sale = $stmt->fetch();

if (!$sale) {
    header('Location: reports.php');
    exit;
}

// Fetch items in the sale
$stmt_items = $pdo->prepare("
    SELECT si.*, p.product_name
    FROM sale_items si
    JOIN product p ON si.product_id = p.product_id
    WHERE si.sale_id = ?
");
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Details #<?= $sale['sale_id'] ?> — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover { background: #334155; color: #fff; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .invoice-box { background: #fff; border-radius: 12px; padding: 40px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; }
        .header h2 { font-size: 24px; color: #1e293b; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .info-label { font-size: 13px; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 8px; }
        .info-value { font-size: 15px; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid #f1f5f9; color: #64748b; font-size: 13px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .total-row { text-align: right; font-size: 18px; font-weight: 700; color: #1e293b; }
        .btn-print { display: inline-block; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 20px; cursor: pointer; border: none; }
        .back-link { display: inline-block; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 14px; }
        @media print {
            nav, .btn-print, .back-link { display: none; }
            body { background: #fff; }
            .invoice-box { box-shadow: none; padding: 0; }
        }
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
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <div class="invoice-box">
        <div class="header">
            <div>
                <h2>INVOICE</h2>
                <p style="color: #64748b; font-size: 14px;">Bill #<?= $sale['sale_id'] ?></p>
            </div>
            <div style="text-align: right;">
                <p style="font-weight: 600;"><?= date('d M Y, h:i A', strtotime($sale['sale_date'])) ?></p>
            </div>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-label">Customer Details</div>
                <div class="info-value">
                    <strong><?= htmlspecialchars($sale['customer_name']) ?></strong><br>
                    <?= htmlspecialchars($sale['phone']) ?><br>
                    <?= htmlspecialchars($sale['email']) ?>
                </div>
            </div>
            <div style="text-align: right;">
                <div class="info-label">Store Details</div>
                <div class="info-value">
                    <strong>Inventory Manager</strong><br>
                    123 Business Street<br>
                    City, State 12345
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align: right;">Price</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="text-align: right;">Rs. <?= number_format($item['unit_price'], 2) ?></td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: right;">Rs. <?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="total-row">Grand Total</td>
                    <td class="total-row">Rs. <?= number_format($sale['total_amount'], 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <button onclick="window.print()" class="btn-print">Print Invoice</button>
    </div>
    
    <a href="reports.php" class="back-link">← Back to Reports</a>
</div>

</body>
</html>
