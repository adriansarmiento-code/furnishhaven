<?php
require_once 'config.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Fetch order items
    $items_stmt = $pdo->prepare("
        SELECT oi.*, p.name, pc.color_name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        LEFT JOIN product_colorways pc ON oi.product_colorway_id = pc.id 
        WHERE oi.order_id = :order_id
    ");
    $items_stmt->execute([':order_id' => $order_id]);
    $order_items = $items_stmt->fetchAll();

    // Fetch order details
    $order_stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :order_id");
    $order_stmt->execute([':order_id' => $order_id]);
    $order = $order_stmt->fetch();

    // Output HTML
    echo '<table>';
    echo '<thead><tr><th>Product</th><th>Color</th><th>Quantity</th><th>Price</th></tr></thead>';
    echo '<tbody>';
    foreach ($order_items as $item) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($item['name']) . '</td>';
        echo '<td>' . htmlspecialchars($item['color_name'] ?? 'N/A') . '</td>';
        echo '<td>' . htmlspecialchars($item['quantity']) . '</td>';
        echo '<td>$' . number_format($item['price_at_time_of_order'], 2) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
} else {
    echo 'No order ID provided';
}
?>