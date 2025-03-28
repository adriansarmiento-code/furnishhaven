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
         SUM(oi.quantity) as total_quantity 
         FROM orders o
         JOIN users u ON o.user_id = u.id
         JOIN order_items oi ON o.id = oi.order_id";

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
    <style>
        :root {
            --primary-bg: #f4f6f9;
            --card-bg: white;
            --text-dark: #2c3e50;
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--primary-bg);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-form select, 
        .filter-form input, 
        .filter-form button {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--card-bg);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .orders-table th, 
        .orders-table td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: left;
        }

        .orders-table th {
            background-color: #f8f9fa;
            color: var(--text-dark);
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
            gap: 5px;
        }

        .action-btns button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            text-decoration: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            color: var(--text-dark);
        }

        .pagination a.active {
            background-color: var(--status-delivered);
            color: white;
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
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .close-modal {
            cursor: pointer;
            font-size: 24px;
            color: #999;
        }
    </style>
</head>
<body>
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
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Total Amount</th>
                    <th>Items</th>
                    <th>Order Date</th>
                    <th>Status</th>
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
                    <td>
                        <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td class="action-btns">
                        <button onclick="showOrderDetails(<?= $order['id'] ?>)">View Details</button>
                        <button onclick="showStatusModal(<?= $order['id'] ?>)">Update Status</button>
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