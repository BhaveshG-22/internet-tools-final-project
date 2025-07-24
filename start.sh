#!/bin/bash

echo "🚀 Starting Computer Store Application..."
echo "=================================="

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

echo "✅ PHP found: $(php --version | head -n 1)"

# Start PHP development server
echo "🌐 Starting PHP development server on http://localhost:8000"
echo ""
echo "📋 IMPORTANT SETUP STEPS:"
echo "1. You need MySQL/MariaDB for the database"
echo "2. Create database 'computer_store'"
echo "3. Import database.sql file"
echo "4. Update includes/config.php with your database credentials"
echo ""
echo "🔑 Default Admin Login:"
echo "   Email: admin@computerstore.com"
echo "   Password: admin123"
echo ""
echo "🌍 Open your browser and go to: http://localhost:8000"
echo ""
echo "Press Ctrl+C to stop the server"
echo "=================================="

# Start the server
php -S localhost:8000