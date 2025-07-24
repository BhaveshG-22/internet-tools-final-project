<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

requireLogin();

$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $pdo->lastInsertId();
        
        foreach ($cart_items as $item) {
            if ($item['stock'] < $item['quantity']) {
                throw new Exception("Insufficient stock for " . $item['name']);
            }
            
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $pdo->commit();
        $success = true;
        
    } catch (Exception $e) {
        $pdo->rollback();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Computer Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Computer Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="index.php">Home</a>
                    <a class="nav-link" href="products.php">Products</a>
                </div>
                <div class="navbar-nav">
                    <a class="nav-link" href="cart.php">
                        Cart (<?php echo getCartItemCount($pdo, $_SESSION['user_id']); ?>)
                    </a>
                    <a class="nav-link" href="orders.php">Orders</a>
                    <?php if (isAdmin()): ?>
                        <a class="nav-link" href="admin/dashboard.php">Admin</a>
                    <?php endif; ?>
                    <span class="nav-link">Hello, <?php echo $_SESSION['user_name']; ?></span>
                    <a class="nav-link" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($success): ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-success mb-3">
                                <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                            </div>
                            <h3 class="text-success">Order Placed Successfully!</h3>
                            <p>Thank you for your purchase. Your order has been confirmed.</p>
                            <div class="mt-3">
                                <a href="orders.php" class="btn btn-primary">View Orders</a>
                                <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <h2>Checkout</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong><?php echo $item['name']; ?></strong>
                                        <br>
                                        <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <div>
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </div>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                            
                            <div class="d-flex justify-content-between">
                                <strong>Total: <?php echo formatPrice($total); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="expiry" class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry" placeholder="MM/YY" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label for="cardholder_name" class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" id="cardholder_name" required>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="alert alert-info">
                                        <small>
                                            <strong>Demo Mode:</strong> This is a demonstration. No real payment will be processed.
                                        </small>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Place Order</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; 2025 Computer Store. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>