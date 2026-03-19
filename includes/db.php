<?php
// ============================================================
//  db.php — Database Connection File
//  Purpose: Connect to MySQL database
//  Usage:   Include this file at the top of every PHP page
//           using: require_once '../includes/db.php';
// ============================================================

$host     = 'localhost';   // XAMPP MySQL runs here
$dbname   = 'inventory_db'; // The database we created
$username = 'root';         // Default XAMPP username
$password = '';             // Default XAMPP password is empty

try {
    // PDO = PHP Data Objects — a safe, modern way to connect to MySQL
    // It protects against SQL injection attacks
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Show errors clearly during development
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Return results as associative arrays (e.g. $row['product_name'])
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, stop everything and show the error
    die(" Database connection failed: " . $e->getMessage());
}
?>
