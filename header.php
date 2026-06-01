<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baby Lloyd Apparels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-black: #1A1A1A;
            --brand-grey: #808080;
            --brand-brown: #8B5A2B;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--brand-black);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/ecommerce-project/index.php" style="color: #d4af37;">BABY LLOYD.</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/ecommerce-project/index.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ecommerce-project/cart.php">Cart</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/ecommerce-project/my-orders.php">My Orders</a></li>
                        <li class="nav-item"><a class="nav-link text-success" href="#">Hello, <?= htmlspecialchars($_SESSION['username']); ?></a></li>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Administrator'): ?>
                            <li class="nav-item"><a class="nav-link text-warning" href="/ecommerce-project/admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        
                        <li class="nav-item"><a class="nav-link" href="/ecommerce-project/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/ecommerce-project/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
