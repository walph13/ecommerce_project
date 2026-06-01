<?php
session_start();
require 'config/database.php';

// Fetch all products from the database
$stmt = $pdo->query("SELECT * FROM Products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="p-5 mb-5 rounded-3 text-center" style="background-color: #F4F4F4;">
    <h1 class="display-4 fw-bold" style="color: var(--brand-black);">Welcome to Baby Lloyd Apparels</h1>
    <p class="col-md-8 mx-auto fs-5" style="color: var(--brand-grey);">
        Handcrafted accessories that blend modern simplicity with everyday protection.
    </p>
</div>

<div class="container mb-5">
    <h2 class="mb-4 text-center fw-bold" style="color: var(--brand-black);">Our Collection</h2>
    
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <img src="public/uploads/<?= htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']); ?>" style="height: 250px; object-fit: cover;">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($product['name']); ?></h5>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($product['category']); ?></p>
                        
                        <p class="card-text text-truncate mb-3" style="color: var(--brand-grey);">
                            <?= htmlspecialchars($product['description']); ?>
                        </p>
                        
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-success">₱<?= number_format($product['price'], 2); ?></span>
                            <a href="product-details.php?id=<?= $product['product_id']; ?>" class="btn text-white" style="background-color: var(--brand-brown);">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if(empty($products)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted fs-5">No products available yet. Check back soon!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
