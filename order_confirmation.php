<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Validate order_id from GET parameter
if (!isset($_GET['order_id']) || !filter_var($_GET['order_id'], FILTER_VALIDATE_INT)) {
    // Redirect to a safe page or show an error
    header('Location: account.php');
    exit;
}

$order_id = intval($_GET['order_id']);

// Fetch order details with error handling
try {
    $order_stmt = $pdo->prepare("
        SELECT 
            o.*,
            ba.full_name as billing_name,
            ba.street_address as billing_street,
            ba.city as billing_city,
            ba.state as billing_state,
            ba.postal_code as billing_postal_code,
            ba.country as billing_country,
            sa.full_name as shipping_name,
            sa.street_address as shipping_street,
            sa.city as shipping_city,
            sa.state as shipping_state,
            sa.postal_code as shipping_postal_code,
            sa.country as shipping_country
        FROM orders o
        JOIN addresses ba ON o.billing_address_id = ba.id
        JOIN addresses sa ON o.shipping_address_id = sa.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $order_stmt->execute([$order_id, $user_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    // Check if order exists
    if (!$order) {
        header('Location: account.php');
        exit;
    }

    // Fetch order items
    $items_stmt = $pdo->prepare("
        SELECT 
            oi.*,
            p.name as product_name,
            pc.color_name,
            pc.image_path
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN product_colorways pc ON oi.product_colorway_id = pc.id
        WHERE oi.order_id = ?
    ");
    $items_stmt->execute([$order_id]);
    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate subtotal
    $subtotal = array_sum(array_map(function($item) {
        return $item['quantity'] * $item['price_at_time_of_order'];
    }, $order_items));

    // Estimated shipping and tax (you might want to adjust these calculations)
    $shipping = 500; // 500 pesos (fixed shipping cost)
    $tax = $subtotal * 0.05; // 5% tax

    // Calculate total (ensure it's not null or zero)
    $total_amount = max($order['total_amount'] ?? ($subtotal + $shipping + $tax), 0);

} catch (PDOException $e) {
    // Log the error and redirect
    error_log('Order Confirmation Error: ' . $e->getMessage());
    header('Location: account.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #566d4b;
            --secondary-color: #697a5a;
            --light-accent: #8ca371;
            --white: #ffffff;
            --background: #f4f4f4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--background);
            padding-top: 120px; /* Accommodate fixed header */
        }

        /* Header Styles (Same as checkout.php) */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: var(--secondary-color);
            color: var(--white);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--white);
            text-decoration: none;
        }

        .account-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .account-right a {
            color: var(--white);
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }

        .account-right a:hover {
            color: var(--light-accent);
        }

        .account-right i {
            font-size: 18px;
        }

        .nav {
            background: var(--primary-color);
            padding: 10px 0;
            width: 100%;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .nav a {
            color: var(--white);
            text-decoration: none;
            padding: 8px 15px;
            font-size: 16px;
            transition: background-color 0.3s;
            border-radius: 4px;
        }

        .nav a:hover {
            background: var(--light-accent);
        }

        /* Order Confirmation Container */
        .order-confirmation-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .confirmation-message {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .confirmation-message h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .order-details {
            display: flex;
            gap: 20px;
        }

        .order-summary, .shipping-details, .billing-details, .payment-details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .order-summary h3, 
        .shipping-details h3, 
        .billing-details h3, 
        .payment-details h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .order-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 15px;
        }

        .order-total {
            text-align: right;
            padding: 10px 0;
            font-weight: bold;
            color: var(--primary-color);
        }

        .order-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .continue-shopping-btn, .view-orders-btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .continue-shopping-btn:hover, .view-orders-btn:hover {
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo-container">
                <img src="img/logo.png" alt="Furnish Haven Logo" class="logo">
                <a href="home.php" class="logo-text">Furnish Haven</a>
            </div>
            
            <div class="account-right">
                <a href="account.php"><i class="fas fa-user"></i> Account</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="nav">
            <div class="nav-container">
                <a href="living_room.php">Living Room</a>
                <a href="bedroom.php">Bedroom</a>
                <a href="dining_room.php">Dining Room</a>
            </div>
        </div>
    </header>

    <div class="order-confirmation-container">
        <div class="confirmation-message">
            <h2>Thank You for Your Order!</h2>
            <p>Your order #<?php echo htmlspecialchars($order_id); ?> has been successfully placed.</p>
            <p>Order Status: <?php echo htmlspecialchars($order['status'] ?? 'Processing'); ?></p>
        </div>
        
        <div class="order-details">
            <div class="order-summary" style="flex: 2;">
                <h3>Order Summary</h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <img src="uploads/products/<?php echo htmlspecialchars($item['image_path'] ?? 'placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        <div class="item-details">
                            <p><?php echo htmlspecialchars($item['product_name']); ?></p>
                            <p>Color: <?php echo htmlspecialchars($item['color_name'] ?? 'N/A'); ?></p>
                            <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                            <p>Price: ₱<?php echo number_format($item['price_at_time_of_order'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="order-total">
                    <p>Subtotal: ₱<?php echo number_format($subtotal, 2); ?></p>
                    <p>Shipping: ₱<?php echo number_format($shipping, 2); ?></p>
                    <p>Tax: ₱<?php echo number_format($tax, 2); ?></p>
                    <h3>Total: ₱<?php echo number_format($total_amount, 2); ?></h3>
                </div>
            </div>
            
            <div class="shipping-details" style="flex: 1;">
                <h3>Shipping Information</h3>
                <p><?php echo htmlspecialchars($order['shipping_name'] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars($order['shipping_street'] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars(
                    ($order['shipping_city'] ?? 'N/A') . ', ' . 
                    ($order['shipping_state'] ?? '') . ' ' . 
                    ($order['shipping_postal_code'] ?? '')
                ); ?></p>
                <p><?php echo htmlspecialchars($order['shipping_country'] ?? 'N/A'); ?></p>
            </div>
        </div>
        
        <div class="order-details">
            <div class="billing-details" style="flex: 1;">
                <h3>Billing Information</h3>
                <p><?php echo htmlspecialchars($order['billing_name'] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars($order['billing_street'] ?? 'N/A'); ?></p>
                <p><?php echo htmlspecialchars(
                    ($order['billing_city'] ?? 'N/A') . ', ' . 
                    ($order['billing_state'] ?? '') . ' ' . 
                    ($order['billing_postal_code'] ?? '')
                ); ?></p>
                <p><?php echo htmlspecialchars($order['billing_country'] ?? 'N/A'); ?></p>
            </div>
            
            <div class="payment-details" style="flex: 1;">
                <h3>Payment Details</h3>
                <p>Payment Method: <?php 
                    echo htmlspecialchars(
                        isset($order['payment_method']) 
                        ? ucwords(str_replace('_', ' ', $order['payment_method'])) 
                        : 'N/A'
                    ); 
                ?></p>
            </div>
        </div>
        
        <div class="order-actions">
            <a href="index.php" class="continue-shopping-btn">Continue Shopping</a>
            <a href="account.php" class="view-orders-btn">View My Orders</a>
        </div>
    </div>
</body>
</html>