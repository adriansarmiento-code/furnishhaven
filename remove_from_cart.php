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
if (!isset($_POST['cart_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid input'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);

try {
    // Remove cart item
    $stmt = $pdo->prepare("
        DELETE FROM cart 
        WHERE id = ? AND user_id = ?
    ");
    $result = $stmt->execute([$cart_id, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Item removed from cart'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to remove item'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}