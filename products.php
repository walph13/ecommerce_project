<?php
session_start();
require '../config/database.php';

// 1. SECURITY: Kick out anyone who isn't an Administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: ../index.php");
    exit;
}

$message = '';

// 2. HANDLE FORM SUBMISSION (Adding a product)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $category = $_POST['category'];
    $stock = $_POST['stock'];
    
    // Handle Image Upload
    $image_url = 'placeholder.jpg'; // Default if no image uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../public/uploads/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]); // Add timestamp to prevent overwriting
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $file_name;
        } else {
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
        }
    }

    // Insert into Database
    $stmt = $pdo->prepare("INSERT INTO Products (name, description, price, category, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $price, $category, $stock, $image_url])) {
        $message = "<div class='alert alert-success'>Product added successfully!</div>";
    }
}

// 3. FETCH EXISTING PRODUCTS
$stmt = $pdo->query("SELECT * FROM Products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="products.php" class="list-group-item list-group-item-action active" style="background-color: var(--brand-brown); border-color: var(--brand-brown);">Manage Products</a>
            <a href="orders.php" class="list-group-item list-group-item-action">View Orders</a>
        </div>
    </div>

    <div class="col-md-9">
        <h2 class="mb-4">Product Inventory</h2>
        <?= $message ?>

        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-dark text-white">Add New Accessory</div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Price (PHP)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="Necklaces">Necklaces</option>
                                <option value="Bracelets">Bracelets</option>
                                <option value="Earrings">Earrings</option>
                                <option value="Agimat/Charms">Agimat/Charms</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Story & Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="add_product" class="btn text-white" style="background-color: var(--brand-brown);">Save Product</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="card-title mb-3">Current Inventory</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="../public/uploads/<?= htmlspecialchars($product['image_url']) ?>" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                                <td>₱<?= number_format($product['price'], 2) ?></td>
                                <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($products)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No products found. Start adding some above!</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
