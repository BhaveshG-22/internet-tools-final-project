# Computer Store - Online Shopping Platform

A full-stack web application for an online computer store built with PHP, MySQL, HTML5, CSS (Bootstrap), and JavaScript.

## Project Information

### Project Group Members

- **Erin Viju**  
  Student ID: 229586570  
  Email: eviju@algomau.ca

- **Bhavesh Gavali**  
  Student ID: 229541340  
  Email: bgavali@algomau.ca

- **Spandan Narayan Devagirkar**  
  Student ID: 249424300

- **Kirandeep Kaur Mehta**  
  Student ID: 5144806

**Course**: COSC2956001 - Internet Tool  
**Institution**: Algoma University

## Features

### User Features
- **User Registration & Authentication**: Secure user registration and login system
- **Product Browsing**: Browse products by category (Laptops, Desktops, Graphics Cards, Memory, Accessories)
- **Product Search**: Search products by name or description
- **Product Details**: View detailed product information
- **Shopping Cart**: Add/remove products, update quantities
- **Checkout**: Secure checkout process with order confirmation
- **Order History**: View past orders and their status

### Admin Features
- **Admin Dashboard**: Overview of sales, users, and products
- **Product Management**: Add, edit, delete products and manage inventory
- **Order Management**: View and update order status
- **User Management**: Manage user accounts and admin privileges
- **Low Stock Alerts**: Monitor products with low inventory

### Security Features
- **Password Hashing**: Secure password storage using PHP's password_hash()
- **SQL Injection Protection**: All database queries use prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Cross-site request forgery protection
- **Rate Limiting**: Login attempt rate limiting
- **Security Headers**: Various HTTP security headers
- **Session Security**: Secure session management

## Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Server**: Apache/Nginx (XAMPP, LAMP, or similar)

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- XAMPP/LAMP/WAMP (recommended for local development)

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   cd webtermproject
   ```

2. **Database Setup**
   - Start your MySQL server
   - Create a new database named `computer_store`
   - Import the database schema:
     ```bash
     mysql -u root -p computer_store < database.sql
     ```
   - Or execute the SQL commands in `database.sql` through phpMyAdmin

3. **Configuration**
   - Edit `includes/config.php` to match your database settings:
     ```php
     $host = 'localhost';
     $dbname = 'computer_store';
     $username = 'root';  // Your MySQL username
     $password = '';      // Your MySQL password
     ```

4. **Web Server Setup**
   - Place the project folder in your web server's document root
   - For XAMPP: `C:/xampp/htdocs/webtermproject/`
   - For LAMP: `/var/www/html/webtermproject/`

5. **Access the Application**
   - Open your browser and navigate to: `http://localhost/webtermproject/`

### Default Admin Account
- **Email**: admin@computerstore.com
- **Password**: admin123

## Project Structure

```
webtermproject/
├── admin/                  # Admin panel files
│   ├── dashboard.php      # Admin dashboard
│   ├── products.php       # Product management
│   ├── orders.php         # Order management
│   └── users.php          # User management
├── css/
│   └── style.css          # Custom styles
├── js/
│   └── main.js            # JavaScript functionality
├── includes/
│   ├── config.php         # Database configuration
│   ├── functions.php      # Common functions
│   └── security.php       # Security functions
├── images/                # Product images (create this folder)
├── logs/                  # Security logs (create this folder)
├── database.sql           # Database schema and sample data
├── index.php              # Homepage
├── products.php           # Product listing
├── product.php            # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout process
├── orders.php             # User order history
├── login.php              # User login
├── register.php           # User registration
├── logout.php             # User logout
├── add_to_cart.php        # Add product to cart
├── update_cart.php        # Update cart quantities
├── remove_from_cart.php   # Remove from cart
└── README.md              # This file
```

## Database Schema

### Users Table
- `id` (Primary Key)
- `name` (User's full name)
- `email` (Unique email address)
- `password` (Hashed password)
- `is_admin` (Boolean for admin privileges)
- `created_at` (Registration timestamp)

### Products Table
- `id` (Primary Key)
- `name` (Product name)
- `description` (Product description)
- `price` (Product price)
- `image_url` (Product image URL)
- `category` (Product category)
- `stock` (Available quantity)
- `created_at` (Creation timestamp)

### Cart Table
- `id` (Primary Key)
- `user_id` (Foreign Key to users)
- `product_id` (Foreign Key to products)
- `quantity` (Number of items)
- `added_at` (Timestamp)

### Orders Table
- `id` (Primary Key)
- `user_id` (Foreign Key to users)
- `total_price` (Order total)
- `order_date` (Order timestamp)
- `status` (Order status)

### Order Items Table
- `id` (Primary Key)
- `order_id` (Foreign Key to orders)
- `product_id` (Foreign Key to products)
- `quantity` (Ordered quantity)
- `price` (Price at time of order)

## Features Implementation

### Security Measures
1. **Password Security**: All passwords are hashed using PHP's `password_hash()` function
2. **SQL Injection Prevention**: All database queries use prepared statements with parameter binding
3. **XSS Protection**: User input is sanitized using `htmlspecialchars()` and other filtering functions
4. **CSRF Protection**: Forms include CSRF tokens to prevent cross-site request forgery
5. **Session Security**: Secure session handling with regeneration and proper cleanup
6. **Input Validation**: Both client-side and server-side validation for all user inputs
7. **Rate Limiting**: Login attempts are rate-limited to prevent brute force attacks

### Responsive Design
- Bootstrap 5 framework for responsive layout
- Mobile-friendly navigation with collapsible menu
- Responsive product cards and tables
- Optimized for desktop, tablet, and mobile devices

### User Experience
- Clean, modern interface design
- Intuitive navigation and product browsing
- Real-time cart updates
- Form validation with user feedback
- Success/error message notifications
- Loading states for better user feedback

## Development Milestones

### Week 8: Frontend Design ✅
- Homepage layout with featured products
- Product listing and detail pages
- User authentication forms (login/register)
- Responsive design implementation

### Week 9: User Authentication ✅
- User registration and login functionality
- Password hashing and security
- Session management
- User logout functionality

### Week 10: Product Display & Cart ✅
- Product database integration
- Category-based browsing
- Search functionality
- Shopping cart implementation

### Week 11: Backend Integration ✅
- Add to cart functionality
- Checkout process
- Order management
- Admin panel implementation

### Week 12: Testing & Final Polish ✅
- Security implementation
- Bug fixes and optimizations
- Final testing and validation
- Documentation completion



## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is created for educational purposes as part of a web development course.

  

## Live Demo

🌐 **Live Site**: [Add your deployed site URL here when available]

*Note: If you have deployed this project to a web hosting service, replace the placeholder above with the actual URL.*


---

*This project demonstrates a complete e-commerce web application built with modern web technologies and security best practices.*