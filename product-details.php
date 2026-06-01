<?php
session_start();
require 'config/database.php';

// 1. Get the product ID from the URL securely
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Fetch the specific product from the database
$stmt = $pdo->prepare("SELECT * FROM Products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// 3. If someone types an invalid ID, redirect them back to the shop
if (!$product) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: var(--brand-grey);">Shop</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['category']); ?></li>
        </ol>
    </nav>

    <div class="row gx-5 mt-4">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <img src="public/uploads/<?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" class="img-fluid w-100" style="object-fit: cover; max-height: 500px;">
            </div>
        </div>

        <div class="col-md-6">
            <h1 class="display-5 fw-bold mb-2" style="color: var(--brand-black);"><?= htmlspecialchars($product['name']); ?></h1>
            <h3 class="fw-bold mb-4" style="color: #28a745;">₱<?= number_format($product['price'], 2); ?></h3>
            
            <div class="mb-4">
                <h5 class="fw-bold" style="color: var(--brand-black);">The Story</h5>
                <p style="color: var(--brand-grey); line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($product['description'])); ?>
                </p>
            </div>

            <div class="mb-4">
                <span class="badge bg-secondary mb-2"><?= htmlspecialchars($product['category']); ?></span>
                <p class="small text-muted">Availability: 
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="text-success fw-bold"><?= $product['stock_quantity']; ?> in stock</span>
                    <?php else: ?>
                        <span class="text-danger fw-bold">Out of stock</span>
                    <?php endif; ?>
                </p>
            </div>

            <form action="api/add-to-cart.php" method="POST" class="d-flex align-items-center gap-3">
                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                
                <div style="width: 100px;">
                    <input type="number" name="quantity" class="form-control form-control-lg text-center" value="1" min="1" max="<?= $product['stock_quantity']; ?>" <?= $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                </div>
                
                <button type="submit" class="btn btn-lg text-white px-5" style="background-color: var(--brand-brown);" <?= $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                    Add to Cart
                </button>
            </form>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-warning mt-4 small">
                    Please <a href="login.php" class="alert-link">log in</a> or <a href="register.php" class="alert-link">register</a> to add items to your cart.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
