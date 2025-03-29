<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'furn_db';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all messages
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Customer Messages | Furnish Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4B5945;
            --secondary: #66785F;
            --accent: #B2C9AD;
            --light: #f8f8f8;
            --dark: #333;
            --text: #555;
            --white: #ffffff;
            --medium-gray: #dddddd;
            --dark-gray: #666666;
            --black: #222222;
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
            color: var(--dark);
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
        
        .messages-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .messages-table th, 
        .messages-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--medium-gray);
        }
        
        .messages-table th {
            background-color: var(--primary);
            color: white;
        }
        
        .messages-table tr:nth-child(even) {
            background-color: var(--light);
        }
        
        .messages-table tr:hover {
            background-color: rgba(178, 201, 173, 0.2);
        }
        
        .message-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s;
        }
        
        .view-btn {
            background-color: var(--primary);
            color: white;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
        }
        
        .no-messages {
            text-align: center;
            padding: 30px;
            color: var(--dark-gray);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .messages-table {
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
    <li><a href="admin_emails.php" class="active"><i class="fas fa-envelope"></i> Messages</a></li>
        <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-envelope"></i> Customer Messages</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
            
            <?php if (empty($messages)): ?>
                <div class="no-messages">
                    <i class="fas fa-inbox" style="font-size: 50px; margin-bottom: 15px;"></i>
                    <h3>No messages found</h3>
                    <p>There are no customer messages in the database.</p>
                </div>
            <?php else: ?>
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($message['id']); ?></td>
                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                            <td><?php echo htmlspecialchars($message['subject']); ?></td>
                            <td class="message-content" title="<?php echo htmlspecialchars($message['message']); ?>">
                                <?php echo htmlspecialchars($message['message']); ?>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></td>
                            <td>
                                <button class="action-btn view-btn" onclick="viewMessage(<?php echo $message['id']; ?>)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="action-btn delete-btn" onclick="deleteMessage(<?php echo $message['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>   
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <!-- Message View Modal -->
<div id="messageModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 10% auto; padding: 20px; width: 60%; max-width: 800px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <span class="close-btn" style="float: right; font-size: 28px; cursor: pointer;">&times;</span>
        <h2 id="modalSubject" style="color: var(--primary); margin-bottom: 20px;"></h2>
        <div class="modal-meta" style="margin-bottom: 20px;">
            <p><strong>From:</strong> <span id="modalName"></span> &lt;<span id="modalEmail"></span>&gt;</p>
            <p><strong>Date:</strong> <span id="modalDate"></span></p>
        </div>
        <div class="modal-body" style="padding: 15px; border: 1px solid var(--medium-gray); border-radius: 4px;">
            <p id="modalMessage" style="white-space: pre-wrap;"></p>
        </div>
    </div>
</div>
<script>
    // Get the modal element
    const modal = document.getElementById('messageModal');
    const closeBtn = document.querySelector('.close-btn');
    
    // Close modal when clicking X or outside modal
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    function viewMessage(id) {
        // Fetch message details via AJAX
        fetch('get_message.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Populate modal with message data
                    document.getElementById('modalSubject').textContent = data.subject;
                    document.getElementById('modalName').textContent = data.name;
                    document.getElementById('modalEmail').textContent = data.email;
                    document.getElementById('modalDate').textContent = 
                        new Date(data.created_at).toLocaleString();
                    document.getElementById('modalMessage').textContent = data.message;
                    
                    // Show the modal
                    modal.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error fetching message:', error);
                alert('Error loading message details. Please try again.');
            });
    }
    
    function deleteMessage(id) {
        if (confirm('Are you sure you want to delete this message?')) {
            window.location.href = 'delete_message.php?id=' + id;
        }
    }
</script>
</body>
</html>