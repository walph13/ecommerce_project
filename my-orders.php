<?php
session_start();
require 'config/database.php';

// 1. SECURITY: User must be logged in to view their orders
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Fetch all orders for this specific customer
$stmt = $pdo->prepare("
    SELECT * FROM Orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold" style="color: var(--brand-black);">My Order History</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Order ID</th>
                            <th>Date Placed</th>
                            <th>Total Amount</th>
                            <th>Shipping Address</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="ps-4 fw-bold">#<?= $order['order_id']; ?></td>
                            <td><?= date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="fw-bold text-success">₱<?= number_format($order['total_amount'], 2); ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($order['shipping_address']); ?></small></td>
                            <td class="pe-4">
                                <?php 
                                    $badgeClass = 'bg-secondary';
                                    if ($order['status'] == 'Paid') $badgeClass = 'bg-info text-dark';
                                    if ($order['status'] == 'Shipped') $badgeClass = 'bg-primary';
                                    if ($order['status'] == 'Completed') $badgeClass = 'bg-success';
                                    if ($order['status'] == 'Cancelled') $badgeClass = 'bg-danger';
                                ?>
                                <span class="badge <?= $badgeClass; ?>"><?= $order['status']; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                You haven't placed any orders yet. <br>
                                <a href="index.php" class="btn btn-sm mt-3 text-white" style="background-color: var(--brand-brown);">Start Shopping</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
