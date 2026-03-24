<?php
// ============================================================
//  new_sale.php — Create a New Sale / Bill
//  Features: Select existing customer OR add new one inline
// ============================================================
require_once 'includes/db.php';

$success = '';
$error   = '';

// ── Handle quick-add new customer via AJAX ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_customer') {
    $name  = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    if ($name) {
        try {
            $stmt = $pdo->prepare("INSERT INTO customer (customer_name, phone, email) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $email]);
            $new_id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'id' => $new_id, 'name' => $name, 'phone' => $phone]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Name is required']);
    }
    exit;
}

// ── Handle new sale submission ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $customer_id = $_POST['customer_id'];
    $product_ids = $_POST['product_id'];
    $quantities  = $_POST['quantity'];

    if ($customer_id && !empty($product_ids)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO sale (customer_id) VALUES (?)");
            $stmt->execute([$customer_id]);
            $sale_id = $pdo->lastInsertId();

            for ($i = 0; $i < count($product_ids); $i++) {
                $pid = $product_ids[$i];
                $qty = $quantities[$i];
                if (!$pid || !$qty || $qty <= 0) continue;

                $price_stmt = $pdo->prepare("SELECT price FROM product WHERE product_id = ?");
                $price_stmt->execute([$pid]);
                $unit_price = $price_stmt->fetchColumn();

                $stock_stmt = $pdo->prepare("SELECT quantity FROM stock WHERE product_id = ?");
                $stock_stmt->execute([$pid]);
                $available = $stock_stmt->fetchColumn();

                if ($qty > $available) {
                    throw new Exception("Not enough stock. Available: $available");
                }

                $item_stmt = $pdo->prepare("
                    INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                $item_stmt->execute([$sale_id, $pid, $qty, $unit_price]);
            }

            $pdo->commit();
            $success = "Sale #$sale_id created successfully! Stock updated automatically.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please select a customer and at least one product.";
    }
}

// ── Load data for dropdowns ─────────────────────────────────
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
        input, select { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; color: #333; outline: none; transition: border 0.2s; }
        input:focus, select:focus { border-color: #2563eb; }
        .customer-row { display: flex; gap: 10px; align-items: center; }
        .customer-row select { flex: 1; }
        .btn-new-customer { white-space: nowrap; background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; border-radius: 8px; padding: 10px 14px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-new-customer:hover { background: #dcfce7; }
        .new-customer-panel { display: none; background: #f0fdf4; border: 1px solid #86efac; border-radius: 10px; padding: 20px; margin-top: 12px; }
        .new-customer-panel h4 { font-size: 14px; color: #15803d; margin-bottom: 14px; }
        .panel-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .panel-grid .full { grid-column: 1 / -1; }
        .panel-grid input { font-size: 13px; padding: 8px 12px; }
        .panel-actions { display: flex; gap: 10px; margin-top: 12px; align-items: center; }
        .btn-save-customer { background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 9px 18px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-save-customer:hover { background: #15803d; }
        .btn-cancel { background: transparent; color: #64748b; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 18px; font-size: 13px; cursor: pointer; }
        .btn-cancel:hover { background: #f8fafc; }
        .saving-msg { font-size: 13px; color: #16a34a; display: none; }
        .items-section { margin: 24px 0 16px; }
        .items-section h3 { font-size: 15px; color: #1e293b; margin-bottom: 14px; font-weight: 600; }
        .item-row { display: grid; grid-template-columns: 1fr 110px; gap: 12px; margin-bottom: 10px; }
        .btn-add-product { background: #f0f9ff; color: #0369a1; border: 1px dashed #7dd3fc; border-radius: 8px; padding: 10px; width: 100%; cursor: pointer; font-size: 14px; margin-bottom: 20px; }
        .btn-add-product:hover { background: #e0f2fe; }
        .btn-submit { width: 100%; background: #2563eb; color: #fff; padding: 12px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #1d4ed8; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert.success { background: #dcfce7; color: #15803d; }
        .alert.error   { background: #fee2e2; color: #dc2626; }
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
    <h2>Create New Sale</h2>

    <?php if ($success): ?>
        <div class="alert success">✅ <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error">❌ <?= $error ?></div>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST" id="sale-form">

            <!-- Customer Selection -->
            <div class="form-group">
                <label>Select Customer *</label>
                <div class="customer-row">
                    <select name="customer_id" id="customer-select" required>
                        <option value="">-- Select Existing Customer --</option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['customer_id'] ?>">
                                <?= htmlspecialchars($c['customer_name']) ?>
                                <?= $c['phone'] ? ' — ' . $c['phone'] : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn-new-customer" onclick="toggleNewCustomer()">
                        + New Customer
                    </button>
                </div>
            </div>

            <!-- Inline Add Customer Panel -->
            <div class="new-customer-panel" id="new-customer-panel">
                <h4>➕ Add New Customer</h4>
                <div class="panel-grid">
                    <div class="full">
                        <input type="text" id="nc-name" placeholder="Full Name *">
                    </div>
                    <input type="text"  id="nc-phone" placeholder="Phone Number">
                    <input type="email" id="nc-email" placeholder="Email Address">
                </div>
                <div class="panel-actions">
                    <button type="button" class="btn-save-customer" onclick="saveNewCustomer()">Save & Select</button>
                    <button type="button" class="btn-cancel" onclick="toggleNewCustomer()">Cancel</button>
                    <span class="saving-msg" id="saving-msg">Saving...</span>
                </div>
            </div>

            <!-- Products -->
            <div class="items-section">
                <h3>Products in this Sale</h3>
                <div id="items-container">
                    <div class="item-row">
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
                        <input type="number" name="quantity[]" min="1" value="1" placeholder="Qty">
                    </div>
                </div>
                <button type="button" class="btn-add-product" onclick="addProductRow()">+ Add Another Product</button>
            </div>

            <button type="submit" class="btn-submit">Create Sale</button>
        </form>
    </div>
</div>

<script>
function toggleNewCustomer() {
    const panel = document.getElementById('new-customer-panel');
    const isOpen = panel.style.display === 'block';
    panel.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) document.getElementById('nc-name').focus();
}

function saveNewCustomer() {
    const name  = document.getElementById('nc-name').value.trim();
    const phone = document.getElementById('nc-phone').value.trim();
    const email = document.getElementById('nc-email').value.trim();
    if (!name) { alert('Please enter the customer name.'); return; }

    document.getElementById('saving-msg').style.display = 'inline';

    fetch('new_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add_customer&customer_name=' + encodeURIComponent(name) +
              '&phone=' + encodeURIComponent(phone) +
              '&email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('saving-msg').style.display = 'none';
        if (data.success) {
            const select = document.getElementById('customer-select');
            const option = document.createElement('option');
            option.value = data.id;
            option.textContent = data.name + (data.phone ? ' — ' + data.phone : '');
            option.selected = true;
            select.appendChild(option);
            document.getElementById('nc-name').value  = '';
            document.getElementById('nc-phone').value = '';
            document.getElementById('nc-email').value = '';
            document.getElementById('new-customer-panel').style.display = 'none';
        } else {
            alert('Error: ' + data.error);
        }
    });
}

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

function addProductRow() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row';
    row.innerHTML = '<select name="product_id[]"><option value="">-- Select Product --</option>' + productOptions + '</select><input type="number" name="quantity[]" min="1" value="1" placeholder="Qty">';
    container.appendChild(row);
}
</script>
</body>
</html>
