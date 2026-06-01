<?php
session_start();
require '../config/database.php';

// SECURITY: Kick out anyone who isn't an Administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../index.php");
    exit;
}

// Fetch live statistics for the dashboard
$productCount = $pdo->query("SELECT COUNT(*) FROM Products")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$orderCount = $pdo->query("SELECT COUNT(*) FROM Orders")->fetchColumn();

// Calculate total revenue (excluding cancelled orders)
$revenueQuery = $pdo->query("SELECT SUM(total_amount) FROM Orders WHERE status != 'Cancelled'");
$totalRevenue = $revenueQuery->fetchColumn();
$totalRevenue = $totalRevenue ? $totalRevenue : 0; // Fallback to 0 if no sales yet

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action active" style="background-color: var(--brand-brown); border-color: var(--brand-brown);">Dashboard</a>
            <a href="products.php" class="list-group-item list-group-item-action">Manage Products</a>
            <a href="orders.php" class="list-group-item list-group-item-action">View Orders</a>
        </div>
    </div>

    <div class="col-md-9">
        <h2 class="mb-4 fw-bold">Admin Dashboard</h2>
        
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 text-center py-4" style="background-color: #28a745; color: white;">
                    <h5 class="mb-2">Total Revenue</h5>
                    <h2 class="fw-bold">₱<?= number_format($totalRevenue, 2); ?></h2>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 text-center py-4" style="background-color: var(--brand-black); color: white;">
                    <h5 class="mb-2" style="color: #d4af37;">Total Orders</h5>
                    <h2 class="fw-bold"><?= $orderCount; ?></h2>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 text-center py-4 bg-light">
                    <h5 class="mb-2 text-muted">Products in Inventory</h5>
                    <h2 class="fw-bold text-dark"><?= $productCount; ?></h2>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 text-center py-4 bg-light">
                    <h5 class="mb-2 text-muted">Registered Users</h5>
                    <h2 class="fw-bold text-dark"><?= $userCount; ?></h2>
                </div>
            </div>
        </div>
        
        <div class="alert alert-success mt-4 border-0 shadow-sm">
            <strong>System Status:</strong> All core e-commerce systems are fully operational. Great job!
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
