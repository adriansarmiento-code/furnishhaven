<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in (you'd typically have a login system)
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please log in to add items to cart'
    ]);
    exit;
}

// Validate input
if (!isset($_POST['product_id']) || !isset($_POST['colorway_id']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid input'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id']);
$colorway_id = intval($_POST['colorway_id']);
$quantity = intval($_POST['quantity']);

try {
    // Check product exists and has stock
    $stmt = $pdo->prepare("
        SELECT id, stock 
        FROM products 
        WHERE id = ? AND stock >= ?
    ");
    $stmt->execute([$product_id, $quantity]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode([
            'success' => false, 
            'message' => 'Product not available or insufficient stock'
        ]);
        exit;
    }

    // Check colorway exists
    $stmt = $pdo->prepare("
        SELECT id 
        FROM product_colorways 
        WHERE id = ? AND product_id = ?
    ");
    $stmt->execute([$colorway_id, $product_id]);
    $colorway = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$colorway) {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid color selection'
        ]);
        exit;
    }

    // Try to update existing cart item or insert new
    $stmt = $pdo->prepare("
        INSERT INTO cart (user_id, product_id, product_colorway_id, quantity) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + ?
    ");
    $stmt->execute([
        $user_id, 
        $product_id, 
        $colorway_id, 
        $quantity,
        $quantity
    ]);

    echo json_encode([
        'success' => true, 
        'message' => 'Product added to cart'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}