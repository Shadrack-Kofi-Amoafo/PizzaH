<?php
// Database connection configuration
$host = 'localhost';
$dbname = 'pizzahut_db';
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password (empty)

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
