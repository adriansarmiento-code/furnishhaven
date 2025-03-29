<?php
session_start();
require_once 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to log errors to a file
function logError($message) {
    file_put_contents('order_error_log.txt', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    logError('No user ID in session');
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Log all POST data for debugging
logError('POST Data: ' . print_r($_POST, true));

// Validate required fields
if (!isset($_POST['shipping_address_id']) && 
    (!isset($_POST['new_full_name']) || empty($_POST['new_full_name']))) {
    logError('No shipping address provided');
    header('Location: checkout.php?error=no_address');
    exit;
}

if (!isset($_POST['payment_method'])) {
    logError('No payment method selected');
    header('Location: checkout.php?error=no_payment_method');
    exit;
}

try {
    // Start a database transaction
    $pdo->beginTransaction();

    // Use the existing address or the new address
    $shipping_address_id = isset($_POST['shipping_address_id']) 
        ? intval($_POST['shipping_address_id']) 
        : null;

    // Identify or create billing address (using same address for billing and shipping)
    $billing_address_id = $shipping_address_id;

    // If new address, insert it
    if (!$shipping_address_id && isset($_POST['new_full_name'])) {
        $address_stmt = $pdo->prepare("
            INSERT INTO addresses 
            (user_id, full_name, street_address, city, state, postal_code, country, phone_number) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $address_stmt->execute([
            $user_id,
            $_POST['new_full_name'],
            $_POST['new_street_address'],
            $_POST['new_city'],
            $_POST['new_state'],
            $_POST['new_postal_code'],
            $_POST['new_country'],
            $_POST['new_phone']
        ]);
        $shipping_address_id = $pdo->lastInsertId();
        $billing_address_id = $shipping_address_id;
        logError('New address inserted with ID: ' . $shipping_address_id);
    }

    // Fetch cart items
    $cart_stmt = $pdo->prepare("
        SELECT 
            c.id as cart_id,
            p.id as product_id,
            p.price,
            c.quantity,
            c.product_colorway_id
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $cart_stmt->execute([$user_id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log cart items
    logError('Cart Items: ' . print_r($cart_items, true));

    // Redirect to order confirmation or show error if cart is empty
    if (empty($cart_items)) {
        logError('Cart is empty');
        header('Location: cart.php?error=empty_cart');
        exit;
    }

    // Calculate total and taxes
    $total_cart_value = array_sum(array_map(function($item) {
        return $item['price'] * $item['quantity'];
    }, $cart_items));
    $shipping = 500; // 500 pesos
    $tax = $total_cart_value * 0.05; // 5% tax
    $total_amount = $total_cart_value + $shipping + $tax;

    // Log total calculations
    logError("Subtotal: $total_cart_value, Shipping: $shipping, Tax: $tax, Total: $total_amount");

    // Create order
    $order_stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, billing_address_id, shipping_address_id, total_amount, status, payment_method) 
        VALUES (?, ?, ?, ?, 'Pending', ?)
    ");
    $order_stmt->execute([
        $user_id, 
        $billing_address_id, 
        $shipping_address_id, 
        $total_amount, 
        $_POST['payment_method']
    ]);
    $order_id = $pdo->lastInsertId();
    logError('Order created with ID: ' . $order_id);

    // Insert order items
    $order_item_stmt = $pdo->prepare("
        INSERT INTO order_items 
        (order_id, product_id, product_colorway_id, quantity, price_at_time_of_order) 
        VALUES (?, ?, ?, ?, ?)
    ");
    foreach ($cart_items as $item) {
        $order_item_stmt->execute([
            $order_id,
            $item['product_id'],
            $item['product_colorway_id'],
            $item['quantity'],
            $item['price']
        ]);
    }
    logError('Order items inserted');

    // Clear the cart
    $clear_cart_stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_cart_stmt->execute([$user_id]);
    logError('Cart cleared');

    // Commit the transaction
    $pdo->commit();

    // Store order ID in session for confirmation page
    $_SESSION['recent_order_id'] = $order_id;

    // Redirect to order confirmation page
    header('Location: order_confirmation.php');
    exit;

} catch (Exception $e) {
    // Rollback the transaction in case of error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log error details
    $error_message = $e->getMessage();
    logError('Exception occurred: ' . $error_message);
    
    // Redirect with error
    header('Location: checkout.php?error=' . urlencode($error_message));
    exit;
}