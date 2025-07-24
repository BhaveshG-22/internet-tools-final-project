<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get reviews for this product
$reviews_stmt = $pdo->prepare("
    SELECT r.*, u.name as reviewer_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->execute([$product_id]);
$reviews = $reviews_stmt->fetchAll();

// Calculate average rating
$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = round($total_rating / count($reviews), 1);
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    $rating = (int)$_POST['rating'];
    $title = sanitizeInput($_POST['title']);
    $comment = sanitizeInput($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($title) && !empty($comment)) {
        $insert_stmt = $pdo->prepare("
            INSERT INTO reviews (product_id, user_id, rating, title, comment) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            rating = VALUES(rating), title = VALUES(title), comment = VALUES(comment)
        ");
        
        if ($insert_stmt->execute([$product_id, $_SESSION['user_id'], $rating, $title, $comment])) {
            header('Location: product.php?id=' . $product_id);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Computer Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
    .rating-input {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }
    .rating-input input {
        display: none;
    }
    .rating-input label {
        cursor: pointer;
        color: #ddd;
        font-size: 1.5rem;
        margin: 0 2px;
    }
    .rating-input label:hover,
    .rating-input label:hover ~ label,
    .rating-input input:checked ~ label {
        color: #ffc107;
    }
    </style>
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

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo $product['image_url']; ?>" class="img-fluid" alt="<?php echo $product['name']; ?>">
            </div>
            <div class="col-md-6">
                <h1><?php echo $product['name']; ?></h1>
                <span class="badge bg-secondary mb-3"><?php echo ucwords(str_replace('_', ' ', $product['category'])); ?></span>
                
                <h3 class="text-primary"><?php echo formatPrice($product['price']); ?></h3>
                
                <p class="lead"><?php echo $product['description']; ?></p>
                
                <div class="mb-3">
                    <strong>Stock: </strong>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="text-success"><?php echo $product['stock']; ?> available</span>
                    <?php else: ?>
                        <span class="text-danger">Out of stock</span>
                    <?php endif; ?>
                </div>

                <?php if (isLoggedIn()): ?>
                    <?php if ($product['stock'] > 0): ?>
                        <form method="POST" action="add_to_cart.php" class="mb-3">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="quantity" class="form-label">Quantity:</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           value="1" min="1" max="<?php echo $product['stock']; ?>">
                                </div>
                                <div class="col-md-8 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>Out of Stock</button>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="login.php">Login</a> to purchase this product.
                    </div>
                <?php endif; ?>

                <a href="products.php?category=<?php echo urlencode($product['category']); ?>" class="btn btn-outline-secondary">
                    View Similar Products
                </a>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Customer Reviews</h3>
                
                <!-- Ratings Display -->
                <?php if (!empty($reviews)): ?>
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="me-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $avg_rating ? 'text-warning' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="fw-bold"><?php echo $avg_rating; ?>/5</span>
                        <span class="text-muted ms-2">(<?php echo count($reviews); ?> reviews)</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                <!-- Add Review Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Write a Review</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="rating-input">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                        <label for="star<?php echo $i; ?>" class="star-label">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="form-label">Review Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Your Review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <a href="login.php">Login</a> to write a review.
                </div>
                <?php endif; ?>

                <!-- Display Reviews -->
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($review['title']); ?></h6>
                                    <div class="mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                            </div>
                            <p class="mb-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <small class="text-muted">by <?php echo htmlspecialchars($review['reviewer_name']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                    </div>
                <?php endif; ?>
            </div>
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