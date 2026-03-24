<?php
require_once 'includes/db.php';

$success = '';
$error   = '';

// --- Handle quick-add new customer via AJAX ---
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

// --- Handle new sale submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $customer_id = $_POST['customer_id'];
    $product_ids = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];

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

                // Double check stock on server side
                $stock_stmt = $pdo->prepare("SELECT quantity FROM stock WHERE product_id = ? FOR UPDATE");
                $stock_stmt->execute([$pid]);
                $available = $stock_stmt->fetchColumn();

                if ($qty > $available) {
                    throw new Exception("Not enough stock for one of the items. Available: $available");
                }

                $price_stmt = $pdo->prepare("SELECT price FROM product WHERE product_id = ?");
                $price_stmt->execute([$pid]);
                $unit_price = $price_stmt->fetchColumn();

                $item_stmt = $pdo->prepare("
                    INSERT INTO sale_items (sale_id, product_id, quantity, unit_price)
                    VALUES (?, ?, ?, ?)
                ");
                $item_stmt->execute([$sale_id, $pid, $qty, $unit_price]);
            }

            $pdo->commit();
            $success = "Sale #$sale_id created successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please select a customer and at least one product.";
    }
}

// --- Load data ---
$customers = $pdo->query("SELECT * FROM customer ORDER BY customer_name")->fetchAll();
$products_raw = $pdo->query("
    SELECT p.product_id, p.product_name, p.price, st.quantity AS stock
    FROM product p
    JOIN stock st ON p.product_id = st.product_id
    WHERE st.quantity > 0
    ORDER BY p.product_name
")->fetchAll();

// Prepare JSON for JS
$products_json = json_encode($products_raw);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Sale Optimized — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }
        nav { background: #1e293b; padding: 14px 30px; display: flex; align-items: center; gap: 30px; }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 20px; grid-column: 1 / -1; }

        .main-panel, .side-panel { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        
        .form-group { margin-bottom: 20px; }
        .customer-row { display: flex; gap: 10px; align-items: flex-end; }
        .customer-row .select-group { flex: 1; }
        .btn-new-customer { background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; border-radius: 8px; padding: 10px 14px; font-size: 13px; font-weight: 600; cursor: pointer; height: 38px; }
        .btn-new-customer:hover { background: #dcfce7; }
        
        .new-customer-panel { display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-top: 15px; margin-bottom: 20px; }
        .new-customer-panel h4 { font-size: 14px; color: #1e293b; margin-bottom: 14px; }
        .panel-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        .panel-grid input { font-size: 13px; padding: 8px 12px; }
        .panel-actions { display: flex; gap: 10px; margin-top: 15px; align-items: center; }
        .btn-save-customer { background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: transparent; color: #64748b; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 16px; font-size: 13px; cursor: pointer; }
        
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; }
        select, input { width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; }
        select:focus, input:focus { border-color: #2563eb; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; }

        .qty-input { width: 80px; text-align: center; }
        .btn-remove { color: #dc2626; cursor: pointer; font-weight: 600; text-decoration: none; font-size: 18px; }
        .btn-add { display: inline-block; margin-top: 15px; background: #f0f9ff; color: #0369a1; border: 1px dashed #7dd3fc; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; width: 100%; text-align: center; }
        .btn-add:hover { background: #e0f2fe; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; }
        .summary-total { border-top: 2px solid #f1f5f9; padding-top: 15px; margin-top: 15px; font-weight: 700; font-size: 20px; color: #1e293b; }
        
        .btn-submit { width: 100%; background: #2563eb; color: #fff; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 20px; }
        .btn-submit:hover { background: #1d4ed8; }

        .stock-badge { display: inline-block; font-size: 11px; padding: 2px 6px; border-radius: 4px; background: #f1f5f9; color: #64748b; margin-top: 4px; }
        .alert { grid-column: 1 / -1; padding: 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alert.error { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
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
    <a href="new_sale.php" class="active">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
</nav>

<div class="container">
    <h2>New Sale</h2>

    <?php if ($success): ?>
        <div class="alert success">✅ <?= $success ?> <a href="reports.php" style="color: inherit; font-weight: 700;">View Reports</a></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error">❌ <?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="sale-form" style="display: contents;">
        <div class="main-panel">
            <div class="form-group">
                <div class="customer-row">
                    <div class="select-group">
                        <label>Customer Name *</label>
                        <select name="customer_id" id="customer-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['customer_id'] ?>">
                                    <?= htmlspecialchars($c['customer_name']) ?> (<?= $c['phone'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn-new-customer" onclick="toggleNewCustomer()">+ New Customer</button>
                </div>
            </div>

            <!-- Inline Add Customer Panel -->
            <div class="new-customer-panel" id="new-customer-panel">
                <h4>➕ Add New Customer</h4>
                <div class="panel-grid">
                    <input type="text" id="nc-name" placeholder="Full Name *">
                    <input type="text"  id="nc-phone" placeholder="Phone Number">
                    <input type="email" id="nc-email" placeholder="Email Address">
                </div>
                <div class="panel-actions">
                    <button type="button" class="btn-save-customer" onclick="saveNewCustomer()">Save & Select</button>
                    <button type="button" class="btn-cancel" onclick="toggleNewCustomer()">Cancel</button>
                    <span id="saving-msg" style="display:none; color: #16a34a; font-size: 13px;">Saving...</span>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <label>Items</label>
                <table id="items-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="width: 150px;">Price</th>
                            <th style="width: 120px;">Quantity</th>
                            <th style="width: 150px;">Subtotal</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <!-- Rows will be added here -->
                    </tbody>
                </table>
                <div class="btn-add" onclick="addRow()">+ Add Product</div>
            </div>
        </div>

        <div class="side-panel">
            <h3 style="font-size: 16px; margin-bottom: 20px; color: #1e293b;">Order Summary</h3>
            
            <div class="summary-row">
                <span>Items Count</span>
                <span id="summary-count">0</span>
            </div>
            
            <div class="summary-total">
                <div style="font-size: 12px; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Grand Total</div>
                Rs. <span id="summary-total">0.00</span>
            </div>

            <button type="submit" class="btn-submit">Confirm Sale</button>
            <p style="font-size: 11px; color: #94a3b8; margin-top: 15px; text-align: center;">
                Stock will be deducted automatically upon confirmation.
            </p>
        </div>
    </form>
</div>

<script>
const products = <?= $products_json ?>;

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

    const msg = document.getElementById('saving-msg');
    msg.style.display = 'inline';

    fetch('new_sale.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add_customer&customer_name=' + encodeURIComponent(name) +
              '&phone=' + encodeURIComponent(phone) +
              '&email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        msg.style.display = 'none';
        if (data.success) {
            const select = document.getElementById('customer-select');
            const option = document.createElement('option');
            option.value = data.id;
            option.textContent = data.name + (data.phone ? ' (' + data.phone + ')' : '');
            option.selected = true;
            select.appendChild(option);
            
            // Clear inputs and close panel
            document.getElementById('nc-name').value  = '';
            document.getElementById('nc-phone').value = '';
            document.getElementById('nc-email').value = '';
            document.getElementById('new-customer-panel').style.display = 'none';
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function addRow() {
    const tbody = document.getElementById('items-body');
    const row = document.createElement('tr');
    
    let options = '<option value="">-- Select Product --</option>';
    products.forEach(p => {
        options += `<option value="${p.product_id}">${p.product_name}</option>`;
    });

    row.innerHTML = `
        <td>
            <select name="product_id[]" onchange="updateRow(this)" required>
                ${options}
            </select>
            <div class="stock-badge" style="display:none">Stock: <span class="stock-val">0</span></div>
        </td>
        <td>
            <input type="text" class="price-display" value="0.00" disabled>
        </td>
        <td>
            <input type="number" name="quantity[]" class="qty-input" value="1" min="1" oninput="calculateSubtotal(this)" required>
        </td>
        <td>
            <input type="text" class="subtotal-display" value="0.00" disabled>
        </td>
        <td>
            <span class="btn-remove" onclick="removeRow(this)">&times;</span>
        </td>
    `;
    tbody.appendChild(row);
    updateSummary();
}

function updateRow(select) {
    const row = select.closest('tr');
    const productId = select.value;
    const product = products.find(p => p.product_id == productId);
    
    const priceInput = row.querySelector('.price-display');
    const qtyInput   = row.querySelector('.qty-input');
    const stockBadge = row.querySelector('.stock-badge');
    const stockVal   = row.querySelector('.stock-val');

    if (product) {
        priceInput.value = parseFloat(product.price).toFixed(2);
        stockBadge.style.display = 'inline-block';
        stockVal.textContent = product.stock;
        qtyInput.max = product.stock;
        if (parseInt(qtyInput.value) > product.stock) {
            qtyInput.value = product.stock;
        }
    } else {
        priceInput.value = '0.00';
        stockBadge.style.display = 'none';
        qtyInput.max = "";
    }
    
    calculateSubtotal(qtyInput);
}

function calculateSubtotal(input) {
    const row = input.closest('tr');
    const price = parseFloat(row.querySelector('.price-display').value) || 0;
    const qty = parseInt(input.value) || 0;
    const max = parseInt(input.max);

    if (max && qty > max) {
        alert("Quantity exceeds available stock!");
        input.value = max;
    }

    const subtotal = price * (max && qty > max ? max : qty);
    row.querySelector('.subtotal-display').value = subtotal.toFixed(2);
    updateSummary();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    updateSummary();
}

function updateSummary() {
    const subtotals = document.querySelectorAll('.subtotal-display');
    let total = 0;
    let count = 0;
    
    subtotals.forEach(s => {
        total += parseFloat(s.value) || 0;
        count++;
    });

    document.getElementById('summary-total').textContent = total.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('summary-count').textContent = count;
}

// Start with one row
window.onload = addRow;
</script>

</body>
</html>
