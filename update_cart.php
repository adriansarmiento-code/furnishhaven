<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please log in'
    ]);
    exit;
}

// Validate input
if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid input'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);
$quantity = intval($_POST['quantity']);

try {
    // First, verify the cart item belongs to the user
    $stmt = $pdo->prepare("
        SELECT c.id, p.stock 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $user_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        echo json_encode([
            'success' => false, 
            'message' => 'Cart item not found'
        ]);
        exit;
    }
    
    // Check stock availability
    if ($quantity > $cart_item['stock']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Insufficient stock',
            'currentQuantity' => $cart_item['stock']
        ]);
        exit;
    }
    
    // Update cart quantity
    $stmt = $pdo->prepare("
        UPDATE cart 
        SET quantity = ? 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$quantity, $cart_id, $user_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Cart updated successfully'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}