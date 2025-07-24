<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            $price = (float)$_POST['price'];
            $category = sanitizeInput($_POST['category']);
            $stock = (int)$_POST['stock'];
            $image_url = sanitizeInput($_POST['image_url']);
            
            if (!empty($name) && $price > 0 && $stock >= 0) {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, stock, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $description, $price, $category, $stock, $image_url])) {
                    $message = '<div class="alert alert-success">Product added successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error adding product.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Please fill in all required fields correctly.</div>';
            }
        } elseif ($_POST['action'] == 'delete') {
            $product_id = (int)$_POST['product_id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$product_id])) {
                $message = '<div class="alert alert-success">Product deleted successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error deleting product.</div>';
            }
        } elseif ($_POST['action'] == 'update_stock') {
            $product_id = (int)$_POST['product_id'];
            $new_stock = (int)$_POST['new_stock'];
            
            $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
            if ($stmt->execute([$new_stock, $product_id])) {
                $message = '<div class="alert alert-success">Stock updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Error updating stock.</div>';
            }
        } elseif ($_POST['action'] == 'update_image') {
            $product_id = (int)$_POST['product_id'];
            $new_image_url = sanitizeInput($_POST['image_url']);
            
            // Validate URL format if not empty
            if (!empty($new_image_url) && !filter_var($new_image_url, FILTER_VALIDATE_URL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?");
            if ($stmt->execute([$new_image_url, $product_id])) {
                echo json_encode(['success' => true, 'message' => 'Image URL updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating image URL.']);
            }
            exit();
        } elseif ($_POST['action'] == 'edit_product') {
            $product_id = (int)$_POST['product_id'];
            $name = sanitizeInput($_POST['name']);
            $description = sanitizeInput($_POST['description']);
            $price = (float)$_POST['price'];
            $category = sanitizeInput($_POST['category']);
            $stock = (int)$_POST['stock'];
            $image_url = sanitizeInput($_POST['image_url']);
            
            if (!empty($name) && $price > 0 && $stock >= 0) {
                $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock = ?, image_url = ? WHERE id = ?");
                if ($stmt->execute([$name, $description, $price, $category, $stock, $image_url, $product_id])) {
                    $message = '<div class="alert alert-success">Product updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error updating product.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Please fill in all required fields correctly.</div>';
            }
        }
    }
}

$products = $pdo->query("SELECT * FROM products ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Computer Store</title>
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
                    <a class="nav-link active" href="products.php">Manage Products</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Products</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">Add New Product</button>
        </div>


        <?php echo $message; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Image URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <?php if (!empty($product['image_url'])): ?>
                                    <img src="<?php echo $product['image_url']; ?>" 
                                         alt="<?php echo $product['name']; ?>" 
                                         class="img-thumbnail" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.src='https://via.placeholder.com/50x50/cccccc/666666?text=No+Image'">
                                <?php else: ?>
                                    <span class="text-muted small">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['name']; ?></td>
                            <td><?php echo ucwords(str_replace('_', ' ', $product['category'])); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td>
                                <form method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="action" value="update_stock">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="number" name="new_stock" value="<?php echo $product['stock']; ?>" 
                                           class="form-control form-control-sm me-2" style="width: 80px;" min="0">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <input type="text" class="form-control form-control-sm me-2" 
                                           style="width: 200px;" 
                                           value="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                           placeholder="https://example.com/image.jpg"
                                           id="image_url_<?php echo $product['id']; ?>">
                                    <button type="button" class="btn btn-sm btn-outline-success me-1" 
                                            onclick="updateImageUrl(<?php echo $product['id']; ?>)">Update</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            onclick="previewProductImage(<?php echo $product['id']; ?>)"
                                            title="Preview Image">👁️</button>
                                </div>
                                <div id="preview_<?php echo $product['id']; ?>" class="mt-1" style="display: none;">
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 75px;">
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProductModal" 
                                        onclick="loadProductData(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', '<?php echo addslashes($product['description']); ?>', <?php echo $product['price']; ?>, '<?php echo $product['category']; ?>', <?php echo $product['stock']; ?>, '<?php echo addslashes($product['image_url']); ?>')">
                                    Edit
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="laptops">Laptops</option>
                                <option value="desktops">Desktops</option>
                                <option value="graphic_cards">Graphic Cards</option>
                                <option value="memories">Memories</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Product Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" 
                                   placeholder="https://example.com/product-image.jpg" 
                                   onchange="previewImage(this.value)">
                            <div class="form-text">
                                Enter a direct link to the product image (JPG, PNG, WebP). 
                                <br>Free image hosting: <a href="https://imgur.com" target="_blank">Imgur</a>, 
                                <a href="https://postimages.org" target="_blank">PostImages</a>, 
                                or <a href="https://via.placeholder.com/300x200" target="_blank">Placeholder Images</a>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Quick samples:</small>
                                <div class="btn-group btn-group-sm mt-1" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="useSampleImage('laptop')">Laptop</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useSampleImage('desktop')">Desktop</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useSampleImage('gpu')">GPU</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useSampleImage('memory')">Memory</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useSampleImage('accessory')">Accessory</button>
                                </div>
                            </div>
                            <div id="image-preview" class="mt-2" style="display: none;">
                                <img id="preview-img" src="" alt="Image Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editProductForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_product">
                        <input type="hidden" name="product_id" id="edit_product_id">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="edit_price" class="form-label">Price</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3 mt-3">
                            <label for="edit_category" class="form-label">Category</label>
                            <select class="form-control" id="edit_category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="laptops">Laptops</option>
                                <option value="desktops">Desktops</option>
                                <option value="graphic_cards">Graphic Cards</option>
                                <option value="memories">Memories</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label">Product Image URL</label>
                            <input type="url" class="form-control" id="edit_image_url" name="image_url" 
                                   placeholder="https://example.com/product-image.jpg" 
                                   onchange="previewEditImage(this.value)">
                            <div class="form-text">
                                Enter a direct link to the product image (JPG, PNG, WebP).
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Quick samples:</small>
                                <div class="btn-group btn-group-sm mt-1" role="group">
                                    <button type="button" class="btn btn-outline-secondary" onclick="useEditSampleImage('laptop')">Laptop</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useEditSampleImage('desktop')">Desktop</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useEditSampleImage('gpu')">GPU</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useEditSampleImage('memory')">Memory</button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="useEditSampleImage('accessory')">Accessory</button>
                                </div>
                            </div>
                            <div id="edit-image-preview" class="mt-2" style="display: none;">
                                <img id="edit-preview-img" src="" alt="Image Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; 2025 Computer Store. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(url) {
            const preview = document.getElementById('image-preview');
            const img = document.getElementById('preview-img');
            
            if (url && isValidImageUrl(url)) {
                img.src = url;
                img.onload = function() {
                    preview.style.display = 'block';
                };
                img.onerror = function() {
                    preview.style.display = 'none';
                    showImageError();
                };
            } else {
                preview.style.display = 'none';
            }
        }
        
        function isValidImageUrl(url) {
            return /\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i.test(url) || url.includes('placeholder');
        }
        
        function showImageError() {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-2';
            alertDiv.innerHTML = `
                <strong>Image Preview Failed:</strong> Please check if the URL is correct and points to a valid image.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const imageField = document.getElementById('image_url');
            imageField.parentNode.insertBefore(alertDiv, imageField.nextSibling);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Sample image URLs for quick testing
        function useSampleImage(type) {
            const sampleUrls = {
                laptop: 'https://via.placeholder.com/300x200/007bff/ffffff?text=Gaming+Laptop',
                desktop: 'https://via.placeholder.com/300x200/28a745/ffffff?text=Desktop+PC',
                gpu: 'https://via.placeholder.com/300x200/dc3545/ffffff?text=Graphics+Card',
                memory: 'https://via.placeholder.com/300x200/ffc107/000000?text=RAM+Memory',
                accessory: 'https://via.placeholder.com/300x200/6f42c1/ffffff?text=Accessory'
            };
            
            const imageInput = document.getElementById('image_url');
            imageInput.value = sampleUrls[type];
            previewImage(sampleUrls[type]);
        }
        
        // Update image URL for existing product
        function updateImageUrl(productId) {
            const imageInput = document.getElementById(`image_url_${productId}`);
            const imageUrl = imageInput.value.trim();
            
            // Show loading state
            const updateBtn = event.target;
            const originalText = updateBtn.textContent;
            updateBtn.textContent = 'Updating...';
            updateBtn.disabled = true;
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('action', 'update_image');
            formData.append('product_id', productId);
            formData.append('image_url', imageUrl);
            
            fetch('products.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    // Update the thumbnail in the table
                    const thumbnailImg = document.querySelector(`tr td img[alt*="product"]`);
                    if (thumbnailImg && imageUrl) {
                        thumbnailImg.src = imageUrl;
                    }
                    // Refresh page after 1 second to show updated image
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error updating image URL', 'danger');
                console.error('Error:', error);
            })
            .finally(() => {
                updateBtn.textContent = originalText;
                updateBtn.disabled = false;
            });
        }
        
        // Preview image for existing product
        function previewProductImage(productId) {
            const imageInput = document.getElementById(`image_url_${productId}`);
            const previewDiv = document.getElementById(`preview_${productId}`);
            const previewImg = previewDiv.querySelector('img');
            const imageUrl = imageInput.value.trim();
            
            if (imageUrl) {
                previewImg.src = imageUrl;
                previewImg.onload = function() {
                    previewDiv.style.display = 'block';
                };
                previewImg.onerror = function() {
                    previewDiv.style.display = 'none';
                    showAlert('Unable to load image preview. Please check the URL.', 'warning');
                };
            } else {
                previewDiv.style.display = 'none';
                showAlert('Please enter an image URL first.', 'info');
            }
        }
        
        // Show alert messages
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            container.insertBefore(alertDiv, container.children[1]);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // Add quick image samples for existing products
        function addSampleToProduct(productId, type) {
            const sampleUrls = {
                laptop: 'https://via.placeholder.com/300x200/007bff/ffffff?text=Gaming+Laptop',
                desktop: 'https://via.placeholder.com/300x200/28a745/ffffff?text=Desktop+PC',
                gpu: 'https://via.placeholder.com/300x200/dc3545/ffffff?text=Graphics+Card',
                memory: 'https://via.placeholder.com/300x200/ffc107/000000?text=RAM+Memory',
                accessory: 'https://via.placeholder.com/300x200/6f42c1/ffffff?text=Accessory'
            };
            
            const imageInput = document.getElementById(`image_url_${productId}`);
            imageInput.value = sampleUrls[type];
        }
        
        // Load product data into edit modal
        function loadProductData(id, name, description, price, category, stock, imageUrl) {
            document.getElementById('edit_product_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('edit_image_url').value = imageUrl;
            
            // Show current image preview
            if (imageUrl) {
                previewEditImage(imageUrl);
            } else {
                document.getElementById('edit-image-preview').style.display = 'none';
            }
        }
        
        // Preview image for edit modal
        function previewEditImage(url) {
            const preview = document.getElementById('edit-image-preview');
            const img = document.getElementById('edit-preview-img');
            
            if (url && isValidImageUrl(url)) {
                img.src = url;
                img.onload = function() {
                    preview.style.display = 'block';
                };
                img.onerror = function() {
                    preview.style.display = 'none';
                };
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Sample images for edit modal
        function useEditSampleImage(type) {
            const sampleUrls = {
                laptop: 'https://via.placeholder.com/300x200/007bff/ffffff?text=Gaming+Laptop',
                desktop: 'https://via.placeholder.com/300x200/28a745/ffffff?text=Desktop+PC',
                gpu: 'https://via.placeholder.com/300x200/dc3545/ffffff?text=Graphics+Card',
                memory: 'https://via.placeholder.com/300x200/ffc107/000000?text=RAM+Memory',
                accessory: 'https://via.placeholder.com/300x200/6f42c1/ffffff?text=Accessory'
            };
            
            const imageInput = document.getElementById('edit_image_url');
            imageInput.value = sampleUrls[type];
            previewEditImage(sampleUrls[type]);
        }
    </script>
</body>
</html>