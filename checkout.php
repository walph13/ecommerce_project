<?php
session_start();
require 'config/database.php';

// 1. SECURITY: User must be logged in to checkout
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. LOGIC: Cart cannot be empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$error = '';
$total_price = 0;
$cart_items = [];

// 3. FETCH CART DATA: Calculate final totals
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

// 4. HANDLE CHECKOUT SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $shipping_address = trim($_POST['shipping_address']);

    if (empty($shipping_address)) {
        $error = "Please provide a shipping address.";
    } else {
        try {
            // Start the transaction
            $pdo->beginTransaction();

            // Insert into Orders table
            $order_stmt = $pdo->prepare("INSERT INTO Orders (user_id, shipping_address, total_amount) VALUES (?, ?, ?)");
            $order_stmt->execute([$_SESSION['user_id'], $shipping_address, $total_price]);
            $order_id = $pdo->lastInsertId();

            // Insert into Order_Items and update stock
            $item_stmt = $pdo->prepare("INSERT INTO Order_Items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $update_stock = $pdo->prepare("UPDATE Products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            foreach ($cart_items as $item) {
                $item_stmt->execute([$order_id, $item['product_id'], $item['cart_quantity'], $item['price']]);
                $update_stock->execute([$item['cart_quantity'], $item['product_id']]);
            }

            // --- START XENDIT INTEGRATION ---
            $xendit_api_key = 'xnd_development_fld7gobBqahDyLNfLs7aVY3jlmjAQ9FLUUREq260n3t0EyaZuvgExlpZlXia'; 
            $encoded_key = base64_encode($xendit_api_key . ':');

            $invoice_data = [
                'external_id' => 'BABYLLOYD_ORDER_' . time() . '_' . $order_id, // Ensure unique ID for testing
                'amount' => $total_price,
                'description' => 'Payment for Baby Lloyd Apparels Order #' . $order_id,
                'success_redirect_url' => 'http://localhost/ecommerce-project/index.php?payment=success',
                'failure_redirect_url' => 'http://localhost/ecommerce-project/cart.php?payment=failed'
            ];

            $ch = curl_init('https://api.xendit.co/v2/invoices');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invoice_data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . $encoded_key
            ]);
            // FIX FOR XAMPP LOCALHOST SSL ERRORS:
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

            $response = curl_exec($ch);
            
            if(curl_errno($ch)){
                throw new Exception("cURL Error: " . curl_error($ch));
            }
            curl_close($ch);

            $invoice = json_decode($response, true);

            // Check if Xendit successfully created the link
            if (isset($invoice['invoice_url'])) {
                $update_order = $pdo->prepare("UPDATE Orders SET xendit_invoice_id = ? WHERE order_id = ?");
                $update_order->execute([$invoice['id'], $order_id]);

                // SUCCESS! Now we permanently commit the database
                $pdo->commit();
                unset($_SESSION['cart']);

                header("Location: " . $invoice['invoice_url']);
                exit;
            } else {
                // Grab exact error from Xendit
                $xendit_error = isset($invoice['message']) ? $invoice['message'] : 'Unknown API Error';
                throw new Exception("Xendit Error: " . $xendit_error);
            }
            // --- END XENDIT INTEGRATION ---

        } catch (Exception $e) {
            // Safely undo database changes if anything above fails
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-7 mb-4">
            <h2 class="mb-4 fw-bold" style="color: var(--brand-black);">Checkout</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Shipping Information</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['username']); ?>" readonly>
                            <small class="text-muted">Name is pulled from your account.</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Complete Delivery Address</label>
                            <textarea name="shipping_address" class="form-control" rows="3" placeholder="House/Unit No., Street, Barangay, City, Province" required></textarea>
                        </div>
                        
                        <div class="alert alert-info border-0">
                            <strong>Payment Method:</strong> You will be redirected to our secure payment gateway (Xendit) in the next step.
                        </div>

                        <button type="submit" name="place_order" class="btn btn-lg w-100 text-white mt-2" style="background-color: var(--brand-brown);">
                            Confirm Order & Pay
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm border-0" style="background-color: var(--brand-light-grey, #F4F4F4);">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 border-bottom pb-3">Order Summary</h4>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span>
                                <?= htmlspecialchars($item['name']); ?> 
                                <strong class="text-muted">x<?= $item['cart_quantity']; ?></strong>
                            </span>
                            <span>₱<?= number_format($item['subtotal'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between mt-4 border-top pt-3">
                        <span class="fs-5 fw-bold">Total to Pay</span>
                        <span class="fs-5 fw-bold text-success">₱<?= number_format($total_price, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
