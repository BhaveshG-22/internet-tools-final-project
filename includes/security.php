<?php
// Additional security functions

// CSRF Token generation and validation
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting for login attempts
function checkLoginAttempts($email) {
    $key = 'login_attempts_' . $email;
    $attempts = $_SESSION[$key] ?? 0;
    $last_attempt = $_SESSION[$key . '_time'] ?? 0;
    
    // Reset attempts after 15 minutes
    if (time() - $last_attempt > 900) {
        unset($_SESSION[$key]);
        unset($_SESSION[$key . '_time']);
        return true;
    }
    
    return $attempts < 5;
}

function recordLoginAttempt($email, $success = false) {
    $key = 'login_attempts_' . $email;
    
    if ($success) {
        unset($_SESSION[$key]);
        unset($_SESSION[$key . '_time']);
    } else {
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
        $_SESSION[$key . '_time'] = time();
    }
}

// Input validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword($password) {
    // At least 6 characters, at least one letter and one number
    return strlen($password) >= 6 && preg_match('/[A-Za-z]/', $password) && preg_match('/[0-9]/', $password);
}

function validateProductId($id) {
    return is_numeric($id) && $id > 0;
}

function validateQuantity($quantity) {
    return is_numeric($quantity) && $quantity > 0 && $quantity <= 100;
}

function validatePrice($price) {
    return is_numeric($price) && $price >= 0 && $price <= 999999.99;
}

// XSS Protection
function escapeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// File upload validation (for future image uploads)
function validateImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    if ($file['size'] > $max_size) {
        return false;
    }
    
    // Check if it's actually an image
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return false;
    }
    
    return true;
}

// Session security
function regenerateSessionId() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// Secure headers
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // XSS Protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (basic)
    header("Content-Security-Policy: default-src 'self' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net; script-src 'self' cdn.jsdelivr.net; img-src 'self' data: https:;");
}

// SQL Injection prevention helper
function preparePlaceholders($count) {
    return str_repeat('?,', $count - 1) . '?';
}

// Log security events
function logSecurityEvent($event, $details = '') {
    $log_entry = date('Y-m-d H:i:s') . " - {$event} - IP: " . $_SERVER['REMOTE_ADDR'] . " - {$details}" . PHP_EOL;
    file_put_contents('logs/security.log', $log_entry, FILE_APPEND | LOCK_EX);
}

// Check if user agent is suspicious
function checkUserAgent() {
    $suspicious_patterns = [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget'
    ];
    
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    
    foreach ($suspicious_patterns as $pattern) {
        if (strpos($user_agent, $pattern) !== false) {
            return false;
        }
    }
    
    return true;
}

// Initialize security headers on every page
setSecurityHeaders();
?>