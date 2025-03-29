<?php
session_start();
require_once 'config.php'; // Assuming you have a database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders with detailed information
$query = "
    SELECT 
        o.id AS order_id, 
        o.total_amount, 
        o.status, 
        o.order_date, 
        o.shipped_at, 
        o.delivered_at,
        ba.full_name AS billing_name,
        sa.full_name AS shipping_name
    FROM 
        orders o
    JOIN 
        addresses ba ON o.billing_address_id = ba.id
    JOIN 
        addresses sa ON o.shipping_address_id = sa.id
    WHERE 
        o.user_id = ?
    ORDER BY 
        o.order_date DESC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log('Order fetch error: ' . $e->getMessage());
    $error = "Unable to retrieve orders. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
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
            min-height: calc(100vh - 300px); /* Adjust based on your header/footer height */
        }

        h1 {
            color: var(--primary);
            margin-bottom: 20px;
            text-align: center;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .orders-list {
            margin-top: 30px;
        }

        .order-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid var(--accent);
            padding-bottom: 10px;
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

        .order-details p,
        .order-tracking p {
            margin-bottom: 8px;
        }

        .order-actions {
            margin-top: 15px;
            text-align: right;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: var(--secondary);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
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
    </style>
</head>
<body>
    <header class="header">
        <i class="fas fa-clipboard-list"></i> My Orders
    </header>
    
    <nav class="nav">
        <a href="account.php"><i class="fas fa-arrow-left"></i> Back to Account</a>
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    
    <div class="container">
        <h1>My Orders</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p>You have not placed any orders yet.</p>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h3>Order #<?php echo htmlspecialchars($order['order_id']); ?></h3>
                            <span class="order-status <?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                        
                        <div class="order-details">
                            <p>
                                <strong>Order Date:</strong> 
                                <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?>
                            </p>
                            <p>
                                <strong>Total Amount:</strong> 
                                $<?php echo number_format($order['total_amount'], 2); ?>
                            </p>
                            <p>
                                <strong>Shipping Name:</strong> 
                                <?php echo htmlspecialchars($order['shipping_name']); ?>
                            </p>
                        </div>

                        <div class="order-tracking">
                            <?php if ($order['shipped_at']): ?>
                                <p>
                                    <strong>Shipped:</strong> 
                                    <?php echo date('F j, Y, g:i a', strtotime($order['shipped_at'])); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if ($order['delivered_at']): ?>
                                <p>
                                    <strong>Delivered:</strong> 
                                    <?php echo date('F j, Y, g:i a', strtotime($order['delivered_at'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="order-actions">
                            <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary">
                                View Order Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-copyright">
                <p>
                    © Created by Cassandra Arcilla<br>
                    Ryna David<br>
                    Robyn Gonzales<br>
                    Adrian Sarmiento<br>
                    All photos used in this website<br>
                    are intended for placeholders. Copyright<br>
                    reserved to their respective owners.
                </p>
            </div>
            
            <div class="footer-column">
                <h3>Products</h3>
                <ul>
                    <li><a href="living_room.php">Living Room</a></li>
                    <li><a href="bedroom.php">Bedroom</a></li>
                    <li><a href="dining_room.php">Dining Room</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contact Us</h3>
                <ul>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="mailto:support@furnish_haven.com">support@furnish_haven.com</a></li>
                    <li><a href="mailto:business@furnish_haven.com">business@furnish_haven.com</a></li>
                    <li><a href="tel:04599999999">(045) 9999-9999</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>