<?php
$host = 'localhost';
$dbname = 'ecommerce_project'; // Your specific database name
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password (usually blank)

try {
    // Create a new PDO instance to connect to MySQL
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set PDO error mode to exception so we can see detailed errors if something fails
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array for easier data handling
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // If the connection fails, stop running the page and show the error
    die("Database connection failed: " . $e->getMessage());
}
?>
