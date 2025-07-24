-- Add reviews table for product ratings and reviews
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

-- Add sample reviews (skip if already exists)
INSERT IGNORE INTO reviews (product_id, user_id, rating, title, comment) VALUES
(1, 1, 5, 'Excellent Gaming Laptop!', 'Amazing performance for gaming. The RTX 4060 handles all my games perfectly at high settings.'),
(2, 1, 4, 'Solid Desktop PC', 'Good value for money. Perfect for office work and light gaming.'),
(3, 1, 5, 'Best GPU upgrade ever!', 'Upgraded from my old card and the difference is night and day. Highly recommended!'),
(4, 1, 5, 'Fast and reliable RAM', 'No issues with compatibility. System feels much snappier now.'),
(5, 1, 4, 'Great gaming mouse', 'Very responsive and comfortable for long gaming sessions. RGB is a nice touch.'),
(6, 1, 4, 'Good mechanical keyboard', 'Nice tactile feedback and the RGB lighting is customizable. Great for both gaming and typing.');