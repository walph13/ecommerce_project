<?php
session_start();
require '../config/database.php';

// SECURITY: Kick out anyone who isn't an Administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../index.php");
    exit;
}

$message = '';

// HANDLE STATUS UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    
    $update_stmt = $pdo->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
    if ($update_stmt->execute([$new_status, $order_id])) {
        $message = "<div class='alert alert-success'>Order #$order_id status updated to $new_status!</div>";
    }
}

// FETCH ALL ORDERS (Joining with Users table to get the customer's name)
$stmt = $pdo->query("
    SELECT o.*, u.username, u.email 
    FROM Orders o 
    LEFT JOIN Users u ON o.user_id = u.user_id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="products.php" class="list-group-item list-group-item-action">Manage Products</a>
            <a href="orders.php" class="list-group-item list-group-item-action active" style="background-color: var(--brand-brown); border-color: var(--brand-brown);">View Orders</a>
        </div>
    </div>

    <div class="col-md-9">
        <h2 class="mb-4 fw-bold">Order Management</h2>
        <?= $message ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total Amount</th>
                                <th>Date</th>
                                <th>Current Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['order_id']; ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($order['username']); ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($order['email']); ?></small>
                                </td>
                                <td class="fw-bold text-success">₱<?= number_format($order['total_amount'], 2); ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'bg-secondary';
                                        if ($order['status'] == 'Paid') $badgeClass = 'bg-info text-dark';
                                        if ($order['status'] == 'Shipped') $badgeClass = 'bg-primary';
                                        if ($order['status'] == 'Completed') $badgeClass = 'bg-success';
                                        if ($order['status'] == 'Cancelled') $badgeClass = 'bg-danger';
                                    ?>
                                    <span class="badge <?= $badgeClass; ?>"><?= $order['status']; ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="" class="d-flex gap-2">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                        <select name="status" class="form-select form-select-sm" style="width: 130px;">
                                            <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Paid" <?= $order['status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-dark">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No orders have been placed yet.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>s
