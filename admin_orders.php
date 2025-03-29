<?php
session_start();
require_once 'config.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];

        // Validate status
        $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception("Invalid status");
        }

        // Prepare update query
        $stmt = $pdo->prepare("UPDATE orders SET status = :status, 
            shipped_at = CASE WHEN :status = 'Shipped' THEN NOW() ELSE shipped_at END,
            delivered_at = CASE WHEN :status = 'Delivered' THEN NOW() ELSE delivered_at END
            WHERE id = :order_id");
        
        $stmt->execute([
            ':status' => $new_status,
            ':order_id' => $order_id
        ]);

        $_SESSION['message'] = "Order status updated successfully.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating order status: " . $e->getMessage();
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Filter options
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$query = "SELECT o.*, u.username, 
         COUNT(oi.id) as total_items, 
         SUM(oi.quantity) as total_quantity,
         a.street_address, a.city, a.state, a.postal_code, a.country
         FROM orders o
         JOIN users u ON o.user_id = u.id
         JOIN order_items oi ON o.id = oi.order_id
         JOIN addresses a ON o.shipping_address_id = a.id";
$where_conditions = [];
$params = [];

if (!empty($status_filter)) {
    $where_conditions[] = "o.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($search_query)) {
    $where_conditions[] = "(u.username LIKE :search OR o.id LIKE :search)";
    $params[':search'] = "%{$search_query}%";
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY o.id ORDER BY o.order_date DESC LIMIT :limit OFFSET :offset";

// Prepare and execute query
$stmt = $pdo->prepare($query);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM orders o JOIN users u ON o.user_id = u.id";
if (!empty($where_conditions)) {
    $count_query .= " WHERE " . implode(" AND ", array_keys($params));
}
$count_stmt = $pdo->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_orders = $count_stmt->fetch()['total'];
$total_pages = ceil($total_orders / $records_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #4B5945;
        --secondary: #66785F;
        --accent: #B2C9AD;
        --light: #f8f8f8;
        --white: #ffffff;
        --medium-gray: #dddddd;
        --dark-gray: #666666;
        --black: #222222;
        --status-pending: #ffc107;
        --status-processing: #17a2b8;
        --status-shipped: #28a745;
        --status-delivered: #007bff;
        --status-cancelled: #dc3545;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        color: var(--black);
        background-color: var(--light);
    }
    
    .admin-container {
        display: flex;
        min-height: 100vh;
    }
    
    .sidebar {
        width: 250px;
        background-color: var(--primary);
        color: white;
        padding: 20px 0;
    }
    
    .sidebar-header {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-nav li {
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-nav a {
        display: block;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .sidebar-nav a:hover {
        background-color: var(--secondary);
    }
    
    .sidebar-nav a.active {
        background-color: var(--accent);
        color: var(--black);
    }
    
    .sidebar-nav i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }
    
    .main-content {
        flex: 1;
        padding: 30px;
        background-color: var(--white);
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--medium-gray);
    }
    
    .header h1 {
        color: var(--primary);
    }
    
    .logout-btn {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .logout-btn:hover {
        background-color: var(--secondary);
    }
    
    .filter-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .filter-form select, 
    .filter-form input, 
    .filter-form button {
        padding: 10px 15px;
        border: 1px solid var(--medium-gray);
        border-radius: 4px;
        font-size: 14px;
    }
    
    .filter-form button {
        background-color: var(--primary);
        color: white;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .filter-form button:hover {
        background-color: var(--secondary);
    }
    
    .orders-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .orders-table th, 
    .orders-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--medium-gray);
    }
    
    .orders-table th {
        background-color: var(--primary);
        color: white;
    }
    
    .orders-table tr:nth-child(even) {
        background-color: var(--light);
    }
    
    .orders-table tr:hover {
        background-color: rgba(178, 201, 173, 0.2);
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 600;
    }
    
    .status-Pending { background-color: var(--status-pending); color: black; }
    .status-Processing { background-color: var(--status-processing); color: white; }
    .status-Shipped { background-color: var(--status-shipped); color: white; }
    .status-Delivered { background-color: var(--status-delivered); color: white; }
    .status-Cancelled { background-color: var(--status-cancelled); color: white; }
    
    .action-btns {
        display: flex;
        gap: 8px;
    }
    
    .action-btns button {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .action-btns button:first-child {
        background-color: var(--primary);
        color: white;
    }
    
    .action-btns button:last-child {
        background-color: #17a2b8;
        color: white;
    }
    
    .action-btns button:hover {
        opacity: 0.8;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 30px;
        gap: 5px;
    }
    
    .pagination a {
        padding: 8px 12px;
        text-decoration: none;
        border: 1px solid var(--medium-gray);
        border-radius: 4px;
        color: var(--black);
        transition: all 0.3s;
    }
    
    .pagination a.active {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .pagination a:hover:not(.active) {
        background-color: var(--light);
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--medium-gray);
    }
    
    .close-modal {
        cursor: pointer;
        font-size: 24px;
        color: var(--dark-gray);
        transition: color 0.3s;
    }
    
    .close-modal:hover {
        color: var(--black);
    }
    
    /* Order details modal specific styles */
    #orderDetailsContent .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
        font-weight: 600;
    }
    
    #orderDetailsContent .status-Pending { background-color: var(--status-pending); color: black; }
    #orderDetailsContent .status-Processing { background-color: var(--status-processing); color: white; }
    #orderDetailsContent .status-Shipped { background-color: var(--status-shipped); color: white; }
    #orderDetailsContent .status-Delivered { background-color: var(--status-delivered); color: white; }
    #orderDetailsContent .status-Cancelled { background-color: var(--status-cancelled); color: white; }
    
    @media (max-width: 768px) {
        .admin-container {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
            position: static;
            height: auto;
        }
        
        .filter-form {
            flex-direction: column;
        }
        
        .orders-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</head>
<body>
<div class="admin-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
    <div class="sidebar-header">
        <h2>Furnish Haven</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-nav">
        <li><a href="admin_emails.php"><i class="fas fa-envelope"></i> Messages</a></li>
        <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="admin_orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
    <div class="container">
        <div class="page-header">
            <h1>Order Management</h1>
        </div>

        <form method="GET" class="filter-form">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $status_filter === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $status_filter === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <input type="text" name="search" placeholder="Search Order ID/Username" 
                   value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit">Filter</button>
        </form>

        <table class="orders-table">
<!-- In the table header -->
<!-- In the table header -->
<thead>
    <tr>
        <th>Order ID</th>
        <th>Username</th>
        <th>Total Amount</th>
        <th>Items</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Shipping Address</th>
        <th>Actions</th>
    </tr>
</thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    <td><?= htmlspecialchars($order['total_quantity']) ?> item(s)</td>
                    <td><?= date('Y-m-d H:i', strtotime($order['order_date'])) ?></td>
<!-- In the table row -->
                    <td>
                        <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td class="action-btns">
                        <button onclick="showOrderDetails(<?= $order['id'] ?>)">View Details</button>
                        <button onclick="showStatusModal(<?= $order['id'] ?>)">Update Status</button>
                    </td>
                    <td>
    <?php 
    $address = htmlspecialchars($order['street_address']) . ', ' . 
               htmlspecialchars($order['city']) . ', ' . 
               htmlspecialchars($order['state']) . ' ' . 
               htmlspecialchars($order['postal_code']) . ', ' . 
               htmlspecialchars($order['country']);
    echo $address;
    ?>
</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_query) ?>" 
                   class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>

        <!-- Order Details Modal -->
        <div id="orderDetailsModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Order Details</h2>
                    <span class="close-modal" onclick="closeModal()">&times;</span>
                </div>
                <div id="orderDetailsContent"></div>
            </div>
        </div>

        <!-- Update Status Modal -->
        <div id="updateStatusModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Update Order Status</h2>
                    <span class="close-modal" onclick="closeModal()">&times;</span>
                </div>
                <form method="POST" id="updateStatusForm">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    <select name="status" required>
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                    <button type="submit" name="update_status">Update Status</button>
                </form>
            </div>
        </div>
    </div>

    <script>
// In the modal JavaScript function
function showOrderDetails(orderId) {
    fetch('get_order_details.php?order_id=' + orderId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('orderDetailsContent').innerHTML = html;
            document.getElementById('orderDetailsModal').style.display = 'block';
        });
}

        function showStatusModal(orderId) {
            document.getElementById('statusOrderId').value = orderId;
            document.getElementById('updateStatusModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('orderDetailsModal').style.display = 'none';
            document.getElementById('updateStatusModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const detailsModal = document.getElementById('orderDetailsModal');
            const statusModal = document.getElementById('updateStatusModal');
            if (event.target == detailsModal) detailsModal.style.display = 'none';
            if (event.target == statusModal) statusModal.style.display = 'none';
        }
    </script>
</body>
</html>