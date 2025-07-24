CREATE DATABASE IF NOT EXISTS computer_store;
USE computer_store;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(255),
    category VARCHAR(50) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@computerstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insert sample products
INSERT INTO products (name, description, price, image_url, category, stock) VALUES 
('Gaming Laptop - ROG Strix', 'High-performance gaming laptop with RTX 4060', 1299.99, 'images/laptop1.png', 'laptops', 10),
('Desktop PC - Intel i7', 'Custom built desktop with Intel i7 processor', 899.99, 'images/desktop1.png', 'desktops', 5),
('Graphics Card - RTX 4070', 'NVIDIA GeForce RTX 4070 8GB', 599.99, 'images/gpu1.png', 'graphic_cards', 8),
('RAM - 32GB DDR4', 'Corsair Vengeance 32GB DDR4 3200MHz', 149.99, 'images/ram1.png', 'memories', 15),
('Gaming Mouse', 'RGB Gaming Mouse with 12000 DPI', 49.99, 'images/mouse1.png', 'accessories', 20),
('Mechanical Keyboard', 'RGB Mechanical Gaming Keyboard', 79.99, 'images/keyboard1.png', 'accessories', 12);