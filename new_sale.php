<?php
// ============================================================
//  new_sale.php — Create a New Sale / Bill
//  When submitted: inserts into sale + sale_items
//  Triggers automatically handle: stock deduction + total update
// ============================================================
require_once 'includes/db.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = $_POST['customer_id'];
    $product_ids = $_POST['product_id'];    // array
    $quantities  = $_POST['quantity'];       // array

    if ($customer_id && !empty($product_ids)) {
        try {
            // Start a transaction — all inserts succeed or all fail together
            $pdo->beginTransaction();

            // Insert the sale record
            $stmt = $pdo->prepare("INSERT INTO sale (customer_id) VALUES (?)");
            $stmt->execute([$customer_id]);
            $sale_id = $pdo->lastInsertId();

            // Insert each product line item
            for ($i = 0; $i < count($product_ids); $i++) {
                $pid = $product_ids[$i];
                $qty = $quantities[$i];

                if (!$pid || !$qty || $qty <= 0) continue;

                // Get the current price of the product
                $price_stmt = $pdo->prepare("SELECT price FROM product WHERE product_id = ?");
                $price_stmt->execute([$pid]);
                $unit_price = $price_stmt->fetchColumn();

                // Check stock availability
                $stock_stmt = $pdo->prepare("SELECT quantity FROM stock WHERE product_id = ?");
                $stock_stmt->execute([$pid]);
                $available = $stock_stmt->fetchColumn();

                if ($qty > $available) {
                    throw new Exception("Not enough stock for product ID $pid. Available: $available");
                }

                // Insert sale item (triggers will auto-deduct stock + update total)
                $item_stmt = $pdo->prepare("
                    INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                $item_stmt->execute([$sale_id, $pid, $qty, $unit_price]);
            }

            $pdo->commit();
            $success = "✅ Sale #$sale_id created successfully! Stock updated automatically.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "❌ " . $e->getMessage();
        }
    } else {
        $error = "❌ Please select a customer and at least one product.";
    }
}

// Load customers and products for the form
$customers = $pdo->query("SELECT * FROM customer ORDER BY customer_name")->fetchAll();
$products  = $pdo->query("
    SELECT p.product_id, p.product_name, p.price, st.quantity AS stock
    FROM product p
    JOIN stock st ON p.product_id = st.product_id
    WHERE st.quantity > 0
    ORDER BY p.product_name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Sale — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }
        .container { max-width: 750px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 24px; }
        .form-box { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        input, select { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
        input:focus, select:focus { border-color: #2563eb; }
        .items-section { margin: 24px 0 16px; }
        .items-section h3 { font-size: 16px; color: #1e293b; margin-bottom: 14px; }
        .item-row { display: grid; grid-template-columns: 1fr 120px; gap: 12px; margin-bottom: 10px; align-items: center; }
        .btn-add { background: #f0f9ff; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 8px; padding: 10px; width: 100%; cursor: pointer; font-size: 14px; margin-bottom: 20px; }
        .btn-add:hover { background: #e0f2fe; }
        .btn { width: 100%; background: #2563eb; color: #fff; padding: 12px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert.success { background: #dcfce7; color: #15803d; }
        .alert.error   { background: #fee2e2; color: #dc2626; }
        .stock-note { font-size: 12px; color: #94a3b8; margin-top: 4px; }
    </style>
</head>
<body>

<nav>
    <span class="brand">🏪 Inventory Manager</span>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="new_sale.php" class="active">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <h2>Create New Sale</h2>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST">

            <div class="form-group">
                <label>Select Customer *</label>
                <select name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['customer_id'] ?>">
                            <?= htmlspecialchars($c['customer_name']) ?> (<?= $c['phone'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="items-section">
                <h3>Products in this Sale</h3>
                <div id="items-container">
                    <!-- One item row by default -->
                    <div class="item-row">
                        <div>
                            <select name="product_id[]">
                                <option value="">-- Select Product --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['product_id'] ?>">
                                        <?= htmlspecialchars($p['product_name']) ?>
                                        — Rs. <?= number_format($p['price'], 2) ?>
                                        (Stock: <?= $p['stock'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <input type="number" name="quantity[]" min="1" value="1" placeholder="Qty">
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-add" onclick="addItemRow()">+ Add Another Product</button>
            </div>

            <button type="submit" class="btn">Create Sale</button>
        </form>
    </div>
</div>

<script>
// Dynamically add more product rows
function addItemRow() {
    const container = document.getElementById('items-container');
    const productOptions = `<?php
        $opts = '';
        foreach ($products as $p) {
            $opts .= '<option value="'.$p['product_id'].'">'
                   . htmlspecialchars($p['product_name'])
                   . ' — Rs. '.number_format($p['price'],2)
                   . ' (Stock: '.$p['stock'].')</option>';
        }
        echo addslashes($opts);
    ?>`;

    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = `
        <select name="product_id[]">
            <option value="">-- Select Product --</option>
            ${productOptions}
        </select>
        <input type="number" name="quantity[]" min="1" value="1" placeholder="Qty">
    `;
    container.appendChild(row);
}
</script>

</body>
</html>
