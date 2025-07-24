<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featured_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Computer Store - Home</title>
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
                    <a class="nav-link active" href="index.php">Home</a>
                    <a class="nav-link" href="products.php">Products</a>
                </div>
                <div class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <a class="nav-link" href="cart.php">
                            Cart (<?php echo getCartItemCount($pdo, $_SESSION['user_id']); ?>)
                        </a>
                        <a class="nav-link" href="orders.php">Orders</a>
                        <?php if (isAdmin()): ?>
                            <a class="nav-link" href="admin/dashboard.php">Admin</a>
                        <?php endif; ?>
                        <span class="nav-link">Hello, <?php echo $_SESSION['user_name']; ?></span>
                        <a class="nav-link" href="logout.php">Logout</a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">Login</a>
                        <a class="nav-link" href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-section bg-primary text-white py-5">
        <div class="container text-center">
            <h1 class="display-4">Welcome to Computer Store</h1>
            <p class="lead">Your one-stop shop for all computer needs</p>
            <a href="products.php" class="btn btn-light btn-lg">Shop Now</a>
        </div>
    </div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="row">
            <?php foreach ($featured_products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <p class="card-text"><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary"><?php echo formatPrice($product['price']); ?></span>
                                    <div>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View</a>
                                        <?php if (isLoggedIn()): ?>
                                            <form method="POST" action="add_to_cart.php" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; 2025 Computer Store. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>