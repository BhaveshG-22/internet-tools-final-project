<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = sanitizeInput($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $message = '<div class="alert alert-success">Order status updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error updating order status.</div>';
    }
}

$stmt = $pdo->prepare("
    SELECT o.*, u.name as customer_name, u.email as customer_email,
           GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.order_date DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Computer Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="products.php">Manage Products</a>
                    <a class="nav-link active" href="orders.php">Manage Orders</a>
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
        <h2>Manage Orders</h2>

        <?php echo $message; ?>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">No orders found.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td>
                                    <?php echo $order['customer_name']; ?>
                                    <br>
                                    <small class="text-muted"><?php echo $order['customer_email']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <small><?php echo $order['items']; ?></small>
                                </td>
                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $order['status'] == 'pending' ? 'warning' : ($order['status'] == 'completed' ? 'success' : 'info'); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" class="form-select form-select-sm me-2" style="width: auto;">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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