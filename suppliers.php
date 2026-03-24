<?php
require_once 'includes/db.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    $name    = trim($_POST['supplier_name']);
    $contact = trim($_POST['contact_name']);
    $phone   = trim($_POST['phone']);
    $email   = trim($_POST['email']);
    
    if ($name) {
        try {
            $stmt = $pdo->prepare("INSERT INTO supplier (supplier_name, contact_name, phone, email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $contact, $phone, $email]);
            $success = "Supplier '$name' added!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM supplier WHERE supplier_id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Supplier deleted!";
    } catch (Exception $e) {
        $error = "Could not delete supplier. It might be linked to products.";
    }
}

$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY supplier_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Suppliers — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover { background: #334155; color: #fff; }
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 24px; grid-column: 1 / -1; }
        .form-box, .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        input { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
        .btn { background: #2563eb; color: #fff; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .alert { grid-column: 1 / -1; padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; font-size: 14px; }
        .alert.success { background: #dcfce7; color: #15803d; }
        .alert.error   { background: #fee2e2; color: #dc2626; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f8fafc; text-align: left; padding: 10px 14px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
<body>

<nav>
    <span class="brand">🏪 Inventory Manager</span>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="categories.php">Categories</a>
    <a href="suppliers.php" class="active">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="new_sale_v2.php">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <h2>Manage Suppliers</h2>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3>Add Supplier</h3>
        <form method="POST">
            <div class="form-group">
                <label>Supplier Name</label>
                <input type="text" name="supplier_name" required>
            </div>
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact_name">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <button type="submit" name="add_supplier" class="btn">Add Supplier</button>
        </form>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $sup): ?>
                <tr>
                    <td><?= htmlspecialchars($sup['supplier_name']) ?></td>
                    <td><?= htmlspecialchars($sup['contact_name']) ?></td>
                    <td><?= htmlspecialchars($sup['phone']) ?></td>
                    <td><?= htmlspecialchars($sup['email']) ?></td>
                    <td>
                        <a href="?delete=<?= $sup['supplier_id'] ?>" style="color: #dc2626; text-decoration: none;" onclick="return confirm('Delete this supplier?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
