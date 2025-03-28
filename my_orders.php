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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
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
                                <strong>Billing Name:</strong> 
                                <?php echo htmlspecialchars($order['billing_name']); ?>
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
</body>
</html>