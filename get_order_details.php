<?php
require_once 'config.php';

if (!isset($_GET['order_id'])) {
    die('No order ID provided');
}

$order_id = $_GET['order_id'];

try {
    // First fetch the order details including shipping_fee
    $order_stmt = $pdo->prepare("
        SELECT o.*, u.username, 
               a.street_address, a.city, 
               a.state, a.postal_code, a.country, a.phone_number
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN addresses a ON o.shipping_address_id = a.id
        WHERE o.id = :order_id
    ");
    $order_stmt->execute([':order_id' => $order_id]);
    $order = $order_stmt->fetch();

    if (!$order) {
        die('Order not found');
    }

    // Then fetch order items
    $items_stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.price, pc.color_name, pc.image_path
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        LEFT JOIN product_colorways pc ON oi.product_colorway_id = pc.id 
        WHERE oi.order_id = :order_id
    ");
    $items_stmt->execute([':order_id' => $order_id]);
    $order_items = $items_stmt->fetchAll();

    // Calculate totals
    $subtotal = array_reduce($order_items, function($carry, $item) {
        return $carry + ($item['price_at_time_of_order'] * $item['quantity']);
    }, 0);

    $shipping_fee = 500; // Fixed shipping fee of ₱500
    $tax = $subtotal * 0.05; // Assuming 5% tax
    $total = $subtotal + $shipping_fee + $tax;

    // Format address
    $address = htmlspecialchars($order['street_address']) . '<br>' .
               htmlspecialchars($order['city']) . ', ' . 
               htmlspecialchars($order['state']) . ' ' . 
               htmlspecialchars($order['postal_code']) . '<br>' . 
               htmlspecialchars($order['country']) . '<br>' .
               'Phone: ' . htmlspecialchars($order['phone_number'] ?? 'N/A');

    // Output HTML
    echo '<div class="order-details-container">';
    
    // Order header
    echo '<div class="order-header">';
    echo '<h2>Order #' . htmlspecialchars($order['id']) . '</h2>';
    echo '<p><strong>Customer:</strong> ' . htmlspecialchars($order['username']) . '</p>';
    echo '<p><strong>Date:</strong> ' . date('F j, Y', strtotime($order['order_date'])) . '</p>';
    echo '<p><strong>Status:</strong> <span class="status-badge">' . htmlspecialchars($order['status']) . '</span></p>';
    echo '</div>';

    // Shipping address
    echo '<div class="shipping-address">';
    echo '<h3>Shipping Address</h3>';
    echo '<div class="address-content">' . $address . '</div>';
    echo '</div>';

    // Order items table
    echo '<div class="order-items">';
    echo '<h3>Order Items</h3>';
    echo '<table class="items-table">';
    echo '<thead><tr><th>Product</th><th>Color</th><th>Quantity</th><th>Price</th><th>Total</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($order_items as $item) {
        $item_total = $item['price_at_time_of_order'] * $item['quantity'];
        echo '<tr>';
        echo '<td>';
        echo '<div class="product-info">';
        if (!empty($item['image_path'])) {
            echo '<img src="uploads/products/' . htmlspecialchars($item['image_path']) . '" class="product-thumbnail">';
        }
        echo htmlspecialchars($item['name']) . '</div></td>';
        echo '<td>' . htmlspecialchars($item['color_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
        echo '<td>₱' . number_format($item['price_at_time_of_order'], 2) . '</td>';
        echo '<td>₱' . number_format($item_total, 2) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // Order summary
    echo '<div class="order-summary">';
    echo '<h3>Order Summary</h3>';
    echo '<div class="summary-row"><span>Subtotal:</span><span>₱' . number_format($subtotal, 2) . '</span></div>';
    echo '<div class="summary-row"><span>Shipping:</span><span>₱' . number_format($shipping_fee, 2) . '</span></div>';
    echo '<div class="summary-row"><span>Tax:</span><span>₱' . number_format($tax, 2) . '</span></div>';
    echo '<div class="summary-row total"><span>Total:</span><span>₱' . number_format($total, 2) . '</span></div>';
    echo '</div>';

    echo '</div>'; // Close container

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<style>
.order-details-container {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.order-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.shipping-address, .order-items, .order-summary {
    margin-bottom: 25px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 5px;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.items-table th, .items-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

.items-table th {
    background-color: #f2f2f2;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.product-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 3px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin: 8px 0;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.summary-row.total {
    font-weight: bold;
    font-size: 1.1em;
    border-bottom: none;
}

.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.9em;
    background: #eee;
}
</style>