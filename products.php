<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'name';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($min_price > 0) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
}

if ($max_price > 0) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
}

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY price DESC";
        break;
    case 'name':
    default:
        $sql .= " ORDER BY name ASC";
        break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories_stmt = $pdo->prepare("SELECT DISTINCT category FROM products ORDER BY category");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Computer Store</title>
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
                    <a class="nav-link active" href="products.php">Products</a>
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
        <div class="row">
            <div class="col-md-3">
                <h5>Categories</h5>
                <div class="list-group mb-4">
                    <a href="products.php" class="list-group-item <?php echo empty($category) ? 'active' : ''; ?>">All Products</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="products.php?category=<?php echo urlencode($cat); ?>" 
                           class="list-group-item <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo ucwords(str_replace('_', ' ', $cat)); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <h5>Search & Filter</h5>
                <form method="GET" class="mb-4">
                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                    <?php endif; ?>
                    
                    <!-- Search Input -->
                    <div class="input-group mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." 
                               value="<?php echo $search; ?>">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                    
                    <!-- Price Range Filter -->
                    <div class="mb-3">
                        <label class="form-label">Price Range</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control" placeholder="Min" 
                                       value="<?php echo $min_price > 0 ? $min_price : ''; ?>" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control" placeholder="Max" 
                                       value="<?php echo $max_price > 0 ? $max_price : ''; ?>" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sort Options -->
                    <div class="mb-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" type="submit">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline-secondary">Clear All</a>
                    </div>
                </form>
            </div>

            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Products
                        <?php if (!empty($category)): ?>
                            <small class="text-muted">in <?php echo ucwords(str_replace('_', ' ', $category)); ?></small>
                        <?php endif; ?>
                        <?php if (!empty($search)): ?>
                            <small class="text-muted">for "<?php echo htmlspecialchars($search); ?>"</small>
                        <?php endif; ?>
                    </h2>
                    <span class="text-muted"><?php echo count($products); ?> products found</span>
                </div>
                
                <!-- Active Filters Display -->
                <?php if (!empty($search) || !empty($category) || $min_price > 0 || $max_price > 0): ?>
                <div class="mb-3">
                    <small class="text-muted">Active filters:</small>
                    <?php if (!empty($search)): ?>
                        <span class="badge bg-info">Search: <?php echo htmlspecialchars($search); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($category)): ?>
                        <span class="badge bg-secondary">Category: <?php echo ucwords(str_replace('_', ' ', $category)); ?></span>
                    <?php endif; ?>
                    <?php if ($min_price > 0): ?>
                        <span class="badge bg-success">Min: $<?php echo number_format($min_price, 2); ?></span>
                    <?php endif; ?>
                    <?php if ($max_price > 0): ?>
                        <span class="badge bg-success">Max: $<?php echo number_format($max_price, 2); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="row">
                    <?php if (empty($products)): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No products found.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo $product['image_url']; ?>" class="card-img-top" 
                                         alt="<?php echo $product['name']; ?>" style="height: 200px; object-fit: cover;">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                        <p class="card-text"><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                                        <span class="badge bg-secondary mb-2"><?php echo ucwords(str_replace('_', ' ', $product['category'])); ?></span>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h5 text-primary"><?php echo formatPrice($product['price']); ?></span>
                                                <small class="text-muted">Stock: <?php echo $product['stock']; ?></small>
                                            </div>
                                            <div class="mt-2">
                                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                                <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                                                    <form method="POST" action="add_to_cart.php" class="d-inline">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm">Add to Cart</button>
                                                    </form>
                                                <?php elseif ($product['stock'] == 0): ?>
                                                    <button class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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