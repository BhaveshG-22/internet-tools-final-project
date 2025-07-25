-- =====================================================
-- Online Computer Store - Complete Database Setup
-- =====================================================
-- Course: COSC2956001 - Internet Tool
-- Institution: Algoma University
-- Contributors: Erin Viju, Bhavesh Gavali, Spandan Narayan Devagirkar, Kirandeep Kaur Mehta
-- =====================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS computer_store;
USE computer_store;

-- =====================================================
-- TABLE CREATION
-- =====================================================

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
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

-- Shopping Cart Table
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

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Reviews Table (Optional Feature)
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (user_id, product_id)
);

-- =====================================================
-- DATA POPULATION
-- =====================================================

-- Insert Default Admin User
-- Email: admin@computerstore.com
-- Password: admin123 (hashed)
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin User', 'admin@computerstore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- Insert Sample Products
INSERT INTO products (name, description, price, image_url, category, stock) VALUES 

-- Laptops Category
('Gaming Laptop - ROG Strix', 'High-performance gaming laptop with RTX 4060', 1299.99, 'https://ccimg.canadacomputers.com/Products/800x800/135/4187/252424/17873.jpg', 'laptops', 10),
('MacBook Pro M4 Max 14"', 'Apple M4 Max Chip 14-Core CPU, 32-Core GPU, 36GB Unified Memory, 1TB SSD Storage - Space Black', 3999.99, 'https://m.media-amazon.com/images/I/61lJqaJOhwL._AC_SL1500_.jpg', 'laptops', 3),

-- Desktops Category
('Desktop PC - Intel i7', 'Custom built desktop with Intel i7 processor', 899.99, 'https://m.media-amazon.com/images/I/51zPnDlZ3QL._UF894,1000_QL80_.jpg', 'desktops', 5),

-- Graphics Cards Category
('Graphics Card - RTX 4070', 'NVIDIA GeForce RTX 4070 8GB', 599.99, 'https://m.media-amazon.com/images/I/61u5pQ152oL._UF894,1000_QL80_.jpg', 'graphic_cards', 8),

-- Memory Category
('RAM - 32GB DDR4', 'Corsair Vengeance 32GB DDR4 3200MHz', 149.99, 'https://m.media-amazon.com/images/I/91D1GM1NYKL._UF894,1000_QL80_.jpg', 'memories', 15),

-- Accessories Category
('Gaming Mouse', 'RGB Gaming Mouse with 12000 DPI', 49.99, 'https://m.media-amazon.com/images/I/61mpMH5TzkL._AC_SL1500_.jpg', 'accessories', 20),
('Mechanical Keyboard', 'RGB Mechanical Gaming Keyboard', 79.99, 'https://m.media-amazon.com/images/I/61LcZpKWW-L._UF894,1000_QL80_.jpg', 'accessories', 12),
('Epson XP-4200 Wireless Printer', 'Expression Home XP-4200 Wireless Colour Inkjet All-in-One Printer with Scan and Copy', 129.99, 'https://m.media-amazon.com/images/I/71rKWWMXRNL._AC_SL1500_.jpg', 'accessories', 15),
('Insignia 6-Sheet Cross-Cut Shredder', 'Insignia 6-Sheet Cross-Cut Shredder - Secure document destruction for home office', 49.99, 'https://m.media-amazon.com/images/I/71QX8WKsJeL._AC_SL1500_.jpg', 'accessories', 8);

-- Insert Sample Reviews (Optional Feature)
INSERT INTO reviews (product_id, user_id, rating, title, comment) VALUES
(1, 1, 5, 'Excellent Gaming Laptop!', 'Amazing performance for gaming. The RTX 4060 handles all my games perfectly at high settings.'),
(2, 1, 5, 'Best laptop for professionals!', 'The M4 Max chip is incredibly fast. Perfect for video editing and development work.'),
(3, 1, 4, 'Solid Desktop PC', 'Good value for money. Perfect for office work and light gaming.'),
(4, 1, 5, 'Best GPU upgrade ever!', 'Upgraded from my old card and the difference is night and day. Highly recommended!'),
(5, 1, 5, 'Fast and reliable RAM', 'No issues with compatibility. System feels much snappier now.'),
(6, 1, 4, 'Great gaming mouse', 'Very responsive and comfortable for long gaming sessions. RGB is a nice touch.'),
(7, 1, 4, 'Good mechanical keyboard', 'Nice tactile feedback and the RGB lighting is customizable. Great for both gaming and typing.'),
(8, 1, 4, 'Reliable printer', 'Easy setup and good print quality. Great for home office use.'),
(9, 1, 3, 'Does the job', 'Basic shredder that works as expected. Good for home use.');