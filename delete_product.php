<?php
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;
if ($id) {
    try {
        $pdo->beginTransaction();
        
        // Stock will be deleted automatically if ON DELETE CASCADE is set, 
        // but let's be explicit if we're not sure about the schema.
        $stmt1 = $pdo->prepare("DELETE FROM stock WHERE product_id = ?");
        $stmt1->execute([$id]);

        $stmt2 = $pdo->prepare("DELETE FROM product WHERE product_id = ?");
        $stmt2->execute([$id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        // You might want to pass an error message via session or GET
    }
}

header('Location: products.php');
exit;
