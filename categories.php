<?php
require_once 'includes/db.php';

$success = '';
$error   = '';

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name']);
    $desc = trim($_POST['description']);
    
    if ($name) {
        try {
            $stmt = $pdo->prepare("INSERT INTO category (category_name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            $success = "Category '$name' added!";
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle delete category
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM category WHERE category_id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Category deleted!";
    } catch (Exception $e) {
        $error = "Could not delete category. It might be linked to products.";
    }
}

$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover { background: #334155; color: #fff; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 24px; grid-column: 1 / -1; }
        .form-box, .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        input, textarea { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
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
    <a href="categories.php" class="active">Categories</a>
    <a href="suppliers.php">Suppliers</a>
    <a href="customers.php">Customers</a>
    <a href="new_sale_v2.php">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <h2>Manage Categories</h2>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3>Add Category</h3>
        <form method="POST">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="category_name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <button type="submit" name="add_category" class="btn">Add Category</button>
        </form>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td><?= htmlspecialchars($cat['description']) ?></td>
                    <td>
                        <a href="?delete=<?= $cat['category_id'] ?>" style="color: #dc2626; text-decoration: none;" onclick="return confirm('Delete this category?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
