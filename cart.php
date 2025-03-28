<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT 
        c.id as cart_id,
        p.id as product_id,
        p.name,
        p.price,
        pc.color_name,
        pc.image_path,
        c.quantity,
        (p.price * c.quantity) as total_price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN product_colorways pc ON c.product_colorway_id = pc.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal, shipping, and tax
$subtotal = array_sum(array_column($cart_items, 'total_price'));
$shipping = 500; // 500 pesos
$tax = $subtotal * 0.05; // 5% tax
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
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

        /* Header Styles */
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

        /* Cart Styles */
        .cart-container {
            display: flex;
            max-width: 1200px;
            margin: 20px auto;
            gap: 20px;
        }

        .cart-items {
            flex: 2;
        }

        .cart-item {
            display: flex;
            background-color: white;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .cart-item img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-right: 20px;
        }

        .cart-item-details {
            flex: 1;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }

        .qty-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            margin: 0 10px;
        }

        .remove-item {
            background-color: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .order-summary {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .promo-section {
            display: flex;
            margin-bottom: 15px;
        }

        .promo-section input {
            flex-grow: 1;
            padding: 8px;
            margin-right: 10px;
        }

        .promo-section button {
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
        }

        .checkout-btn {
            display: block;
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
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

    <div class="cart-container">
        <div class="cart-items">
            <h2>Your Cart (<?php echo count($cart_items); ?> items)</h2>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
    <img src="uploads/products/<?php echo htmlspecialchars($item['image_path'] ?? 'placeholder.jpg'); ?>" 
         alt="<?php echo htmlspecialchars($item['name']); ?>">
    
    <div class="cart-item-details">
        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
        <p>₱<?php echo number_format($item['price'], 2); ?></p>
        
        <div class="quantity-control">
            <input type="number" 
                   class="qty-input" 
                   value="<?php echo $item['quantity']; ?>" 
                   min="1" 
                   data-cart-id="<?php echo $item['cart_id']; ?>">
        </div>
        
        <button class="remove-item">Remove</button>
    </div>
</div>
            <?php endforeach; ?>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <p>Subtotal (<?php echo count($cart_items); ?> items): ₱<?php echo number_format($subtotal, 2); ?></p>
            <p>Shipping: ₱<?php echo number_format($shipping, 2); ?></p>
            <p>Tax: ₱<?php echo number_format($tax, 2); ?></p>
            <h3>Total: ₱<?php echo number_format($total, 2); ?></h3>
            
            <div class="promo-section">
                <input type="text" placeholder="Promo Code">
                <button>Apply</button>
            </div>

            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    </div>

    <script src="cart.js"></script>
</body>
</html>