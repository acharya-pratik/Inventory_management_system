<?php
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: products.php');
    exit;
}

$success = '';
$error   = '';

// Fetch existing product data
$stmt = $pdo->prepare("
    SELECT p.*, st.quantity 
    FROM product p 
    JOIN stock st ON p.product_id = st.product_id 
    WHERE p.product_id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['product_name']);
    $category_id   = $_POST['category_id'];
    $supplier_id   = $_POST['supplier_id'];
    $price         = $_POST['price'];
    $reorder_level = $_POST['reorder_level'];
    $quantity      = $_POST['quantity'];

    if ($name && $category_id && $supplier_id && $price) {
        try {
            $pdo->beginTransaction();
            
            // Update product table
            $stmt = $pdo->prepare("
                UPDATE product 
                SET product_name = ?, category_id = ?, supplier_id = ?, price = ?, reorder_level = ?
                WHERE product_id = ?
            ");
            $stmt->execute([$name, $category_id, $supplier_id, $price, $reorder_level, $id]);

            // Update stock table
            $stmt2 = $pdo->prepare("UPDATE stock SET quantity = ? WHERE product_id = ?");
            $stmt2->execute([$quantity, $id]);

            $pdo->commit();
            $success = "Product updated successfully!";
            
            // Refresh product data
            $product['product_name'] = $name;
            $product['category_id'] = $category_id;
            $product['supplier_id'] = $supplier_id;
            $product['price'] = $price;
            $product['reorder_level'] = $reorder_level;
            $product['quantity'] = $quantity;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
$suppliers  = $pdo->query("SELECT * FROM supplier ORDER BY supplier_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover { background: #334155; color: #fff; }
        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 24px; }
        .form-box { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        input, select {
            width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0;
            border-radius: 8px; font-size: 14px; color: #333;
            outline: none; transition: border 0.2s;
        }
        input:focus, select:focus { border-color: #2563eb; }
        .btn { width: 100%; background: #2563eb; color: #fff; padding: 12px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert.success { background: #dcfce7; color: #15803d; }
        .alert.error   { background: #fee2e2; color: #dc2626; }
        .back-link { display: inline-block; margin-top: 16px; color: #2563eb; text-decoration: none; font-size: 14px; }
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
    <h2>Edit Product</h2>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <select name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Supplier *</label>
                <select name="supplier_id" required>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['supplier_id'] ?>" <?= $sup['supplier_id'] == $product['supplier_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sup['supplier_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Selling Price (Rs.) *</label>
                <input type="number" name="price" step="0.01" min="0" value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="quantity" min="0" value="<?= $product['quantity'] ?>">
            </div>

            <div class="form-group">
                <label>Reorder Level</label>
                <input type="number" name="reorder_level" min="0" value="<?= $product['reorder_level'] ?>">
            </div>

            <button type="submit" class="btn">Update Product</button>
        </form>
    </div>

    <a href="products.php" class="back-link">← Back to Products</a>
</div>

</body>
</html>
