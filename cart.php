<?php
session_start();
require 'config/database.php';

// Handle removing an item from the cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    // Refresh the page to update the cart display
    header("Location: cart.php");
    exit;
}

$cart_items = [];
$total_price = 0;

// If the cart isn't empty, fetch the product details from the database
if (!empty($_SESSION['cart'])) {
    // Create SQL placeholders for the IN clause based on how many items are in the cart
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $product_ids = array_keys($_SESSION['cart']);
    
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE product_id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['product_id']];
        $subtotal = $product['price'] * $quantity;
        $total_price += $subtotal;
        
        $product['cart_quantity'] = $quantity;
        $product['subtotal'] = $subtotal;
        $cart_items[] = $product;
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold" style="color: var(--brand-black);">Your Shopping Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info border-0 shadow-sm">
            Your cart is currently empty. <a href="index.php" class="alert-link text-decoration-none">Browse our collection</a>.
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Subtotal</th>
                                        <th class="pe-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td class="ps-4 py-3 d-flex align-items-center gap-3">
                                                <img src="public/uploads/<?= htmlspecialchars($item['image_url']); ?>" alt="Product" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                <div>
                                                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($item['name']); ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($item['category']); ?></small>
                                                </div>
                                            </td>
                                            <td>₱<?= number_format($item['price'], 2); ?></td>
                                            <td><?= $item['cart_quantity']; ?></td>
                                            <td class="fw-bold">₱<?= number_format($item['subtotal'], 2); ?></td>
                                            <td class="text-end pe-4">
                                                <a href="cart.php?remove=<?= $item['product_id']; ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0" style="background-color: var(--brand-light-grey, #F4F4F4);">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-4 border-bottom pb-3">Order Summary</h4>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Subtotal</span>
                            <span class="fw-bold">₱<?= number_format($total_price, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Shipping</span>
                            <span>Calculated next</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-4 border-top pt-3">
                            <span class="fs-5 fw-bold">Total</span>
                            <span class="fs-5 fw-bold text-success">₱<?= number_format($total_price, 2); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn w-100 text-white py-2 fw-bold" style="background-color: var(--brand-brown);">Proceed to Checkout</a>
                        <a href="index.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
