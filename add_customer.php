<?php
// ============================================================
//  add_customer.php — Add a New Customer
//  When form is submitted (POST), insert into customer table
// ============================================================
require_once 'includes/db.php';

$success = '';
$error   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if ($name) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO customer (customer_name, phone, email)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$name, $phone, $email]);
            $success = "✅ Customer '$name' added successfully!";
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Customer name is required.";
    }
}

// Fetch all existing customers to display below the form
$customers = $pdo->query("
    SELECT customer_id, customer_name, phone, email
    FROM customer
    ORDER BY customer_id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Customer — Inventory Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; color: #333; }

        nav {
            background: #1e293b;
            padding: 14px 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        nav .brand { color: #fff; font-size: 20px; font-weight: 700; margin-right: auto; }
        nav a { color: #94a3b8; text-decoration: none; font-size: 14px; padding: 6px 12px; border-radius: 6px; }
        nav a:hover, nav a.active { background: #334155; color: #fff; }

        .container { max-width: 750px; margin: 40px auto; padding: 0 20px; }
        h2 { font-size: 22px; color: #1e293b; margin-bottom: 24px; }

        .form-box {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
            margin-bottom: 30px;
        }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        input {
            width: 100%; padding: 10px 14px;
            border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; color: #333; outline: none; transition: border 0.2s;
        }
        input:focus { border-color: #2563eb; }

        .btn {
            width: 100%; background: #2563eb; color: #fff;
            padding: 12px; border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer;
        }
        .btn:hover { background: #1d4ed8; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert.success { background: #dcfce7; color: #15803d; }
        .alert.error   { background: #fee2e2; color: #dc2626; }

        /* Customer list table */
        .table-box { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .table-box h3 { font-size: 16px; color: #1e293b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: #f8fafc; text-align: left; padding: 10px 14px; color: #64748b; font-weight: 600; border-bottom: 1px solid #e2e8f0; }
        td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        .empty { text-align: center; color: #94a3b8; padding: 24px; font-size: 14px; }
    </style>
</head>
<body>

<nav>
    <span class="brand"> Inventory Manager</span>
    <a href="index.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="new_sale.php">New Sale</a>
    <a href="reports.php">Reports</a>
    <a href="low_stock.php">Low Stock</a>
    <a href="add_customer.php" class="active">Customers</a>
</nav>

<div class="container">
    <h2>Add New Customer</h2>

    <?php if ($success): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <!-- Add Customer Form -->
    <div class="form-box">
        <form method="POST">
            <div class="form-group">
                <label>Customer Name *</label>
                <input type="text" name="customer_name" placeholder="e.g. Ramesh Sharma" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 9800000000">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="e.g. ramesh@gmail.com">
            </div>
            <button type="submit" class="btn">Add Customer</button>
        </form>
    </div>

    <!-- Existing Customers List -->
    <div class="table-box">
        <h3>All Customers (<?= count($customers) ?>)</h3>
        <?php if (empty($customers)): ?>
            <div class="empty">No customers added yet.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= $c['customer_id'] ?></td>
                    <td><?= htmlspecialchars($c['customer_name']) ?></td>
                    <td><?= $c['phone'] ?: '—' ?></td>
                    <td><?= $c['email'] ?: '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
