<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn();

$recent_orders = $pdo->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC 
    LIMIT 5
")->fetchAll();

$low_stock = $pdo->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Computer Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Computer Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav me-auto">
                    <a class="nav-link" href="../index.php">Home</a>
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="products.php">Manage Products</a>
                    <a class="nav-link" href="orders.php">Manage Orders</a>
                    <a class="nav-link" href="users.php">Manage Users</a>
                </div>
                <div class="navbar-nav">
                    <span class="nav-link">Hello, <?php echo $_SESSION['user_name']; ?></span>
                    <a class="nav-link" href="../logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        
        <!-- Stats Cards - Responsive -->
        <div class="row mb-4 g-3">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h5 class="card-title">Total Users</h5>
                        <h3><?php echo $total_users; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-success text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-box fa-2x mb-2"></i>
                        <h5 class="card-title">Total Products</h5>
                        <h3><?php echo $total_products; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-info text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                        <h5 class="card-title">Total Orders</h5>
                        <h3><?php echo $total_orders; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                        <h5 class="card-title">Total Revenue</h5>
                        <h3><?php echo formatPrice($total_revenue ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content - Responsive Layout -->
        <div class="row g-3">
            <div class="col-xl-8 col-lg-7">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Orders</h5>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No orders yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th class="d-none d-md-table-cell">Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                                <td>
                                                    <div class="fw-bold"><?php echo $order['customer_name']; ?></div>
                                                    <small class="text-muted d-md-none"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></small>
                                                </td>
                                                <td class="d-none d-md-table-cell"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                                <td><strong><?php echo formatPrice($order['total_price']); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $order['status'] == 'pending' ? 'warning' : 'success'; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5>Low Stock Alert</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock)): ?>
                            <p class="text-muted">All products are well stocked.</p>
                        <?php else: ?>
                            <?php foreach ($low_stock as $product): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong><?php echo $product['name']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $product['category']; ?></small>
                                    </div>
                                    <span class="badge bg-danger"><?php echo $product['stock']; ?> left</span>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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