
<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Location: my_orders.php');
    exit();
}

// Fetch order details with product information
$query = "
    SELECT 
        oi.id AS order_item_id,
        p.name AS product_name,
        pc.color_name,
        oi.quantity,
        oi.price_at_time_of_order,
        p.main_image
    FROM 
        order_items oi
    JOIN 
        products p ON oi.product_id = p.id
    LEFT JOIN 
        product_colorways pc ON oi.product_colorway_id = pc.id
    JOIN 
        orders o ON oi.order_id = o.id
    WHERE 
        o.id = ? AND o.user_id = ?
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$order_id, $user_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order summary
    $order_query = "
        SELECT 
            total_amount, 
            status, 
            order_date, 
            payment_method,
            ba.full_name AS billing_name,
            ba.street_address AS billing_address,
            sa.full_name AS shipping_name,
            sa.street_address AS shipping_address
        FROM 
            orders o
        JOIN 
            addresses ba ON o.billing_address_id = ba.id
        JOIN 
            addresses sa ON o.shipping_address_id = sa.id
        WHERE 
            o.id = ? AND o.user_id = ?
    ";
    $order_stmt = $pdo->prepare($order_query);
    $order_stmt->execute([$order_id, $user_id]);
    $order_summary = $order_stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log('Order details fetch error: ' . $e->getMessage());
    $error = "Unable to retrieve order details. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details #<?php echo htmlspecialchars($order_id); ?> | Furnish Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4B5945;
            --secondary: #66785F;
            --accent: #B2C9AD;
            --light: #f8f8f8;
            --dark: #333;
            --text: #555;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: var(--text);
            background-color: var(--light);
        }
        
        .header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 24px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .nav {
            background-color: var(--secondary);
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            padding: 15px 0;
        }
        
        .nav a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            transition: all 0.3s;
            border-radius: 4px;
        }
        
        .nav a:hover {
            background-color: var(--accent);
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .footer {
            background-color: #f5f5f5;
            padding: 40px 0;
            font-family: Arial, sans-serif;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }
        
        .footer-copyright {
            width: 40%;
            padding-right: 30px;
        }
        
        .footer-copyright p {
            color: #666;
            font-size: 14px;
            margin: 0;
            line-height: 1.6;
        }
        
        .footer-column {
            width: 25%;
        }
        
        .footer h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer li {
            margin-bottom: 8px;
        }
        
        .footer a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: #4B5945;
            text-decoration: underline;
        }
        
        /* Order Details Specific Styles */
        .order-details-section {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .order-summary {
            border-bottom: 1px solid var(--accent);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .order-summary h2, 
        .shipping-billing-info h3, 
        .order-items h2 {
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .shipping-billing-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .billing-info, 
        .shipping-info {
            width: 48%;
            background-color: var(--light);
            padding: 20px;
            border-radius: 8px;
        }
        
        .order-items .order-item {
            display: flex;
            align-items: center;
            background-color: white;
            border: 1px solid var(--accent);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .order-items .order-item img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 8px;
        }
        
        .order-items .item-details {
            flex-grow: 1;
        }
        
        .order-items .item-details h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            text-transform: uppercase;
        }
        
        .order-status.processing { background-color: #f0ad4e; color: white; }
        .order-status.shipped { background-color: #5bc0de; color: white; }
        .order-status.delivered { background-color: #5cb85c; color: white; }
        .order-status.cancelled { background-color: #d9534f; color: white; }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        
        .btn:hover {
            background-color: var(--secondary);
        }
        
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        </style>
</head>
<body>
    <header class="header">
        <i class="fas fa-clipboard-list"></i> Order Details
    </header>
    
    <nav class="nav">
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="account.php"><i class="fas fa-user-circle"></i> Account</a>
        <a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    
    <div class="container">
        <div class="order-details-section">
            <h1>Order #<?php echo htmlspecialchars($order_id); ?></h1>
            
            <?php if (isset($error)): ?>
                <div class="alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <div class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="row">
                        <div class="col">
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order_summary['order_date'])); ?></p>
                            <p><strong>Total Amount:</strong> $<?php echo number_format($order_summary['total_amount'], 2); ?></p>
                        </div>
                        <div class="col">
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_summary['payment_method']); ?></p>
                            <p>
                                <strong>Status:</strong> 
                                <span class="order-status <?php echo strtolower($order_summary['status']); ?>">
                                    <?php echo htmlspecialchars($order_summary['status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="shipping-billing-info">
                    <div class="billing-info">
                        <h3>Billing Information</h3>
                        <p><?php echo htmlspecialchars($order_summary['billing_name']); ?></p>
                        <p><?php echo htmlspecialchars($order_summary['billing_address']); ?></p>
                    </div>
                    <div class="shipping-info">
                        <h3>Shipping Information</h3>
                        <p><?php echo htmlspecialchars($order_summary['shipping_name']); ?></p>
                        <p><?php echo htmlspecialchars($order_summary['shipping_address']); ?></p>
                    </div>
                </div>

                <div class="order-items">
                    <h2>Order Items</h2>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <img src="uploads/products/<?php echo htmlspecialchars(basename($item['main_image'])); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                                <?php if ($item['color_name']): ?>
                                    <p>Color: <?php echo htmlspecialchars($item['color_name']); ?></p>
                                <?php endif; ?>
                                <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                <p>Price: $<?php echo number_format($item['price_at_time_of_order'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-actions">
                    <a href="account.php" class="btn">Back to Account</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="footer">
        <!-- ... (previous footer remains the same) ... -->
    </footer>
</body>
</html>