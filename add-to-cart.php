<?php
session_start();

// Check if a product_id and quantity were sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // Initialize the cart array in the session if it doesn't exist yet
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add the quantity to the cart, or update it if the item is already there
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Redirect the user straight to the cart page
    header("Location: ../cart.php");
    exit;
} else {
    // If someone tries to access this file directly, send them to the homepage
    header("Location: ../index.php");
    exit;
}
?>
