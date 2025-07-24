<?php
session_start();
require_once 'security.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function getCartItemCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function addToCart($pdo, $user_id, $product_id, $quantity = 1) {
    try {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE quantity = quantity + ?");
        $stmt->execute([$user_id, $product_id, $quantity, $quantity]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}

function removeFromCart($pdo, $user_id, $product_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        return true;
    } catch(PDOException $e) {
        return false;
    }
}
?>