<?php
session_start();
require_once 'config.php';

// At the top of the file after session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Check if address ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: account.php#addresses');
    exit();
}

$address_id = $_GET['id'];

// Verify the address belongs to the user
$check_query = "SELECT * FROM addresses WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($check_query);
$stmt->execute([$address_id, $user_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    header('Location: account.php#addresses');
    exit();
}

// Handle form submission for updating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Get form data
    $full_name = trim($_POST['full_name'] ?? '');
    $street_address = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate form data
    if (empty($full_name) || empty($street_address) || empty($city) || 
        empty($state) || empty($postal_code) || empty($country)) {
        $error_message = "Please fill in all required fields.";
    } else {
        try {
            
            // Update address
            $stmt = $pdo->prepare("
            UPDATE addresses 
            SET full_name = ?, street_address = ?, city = ?, state = ?, 
                postal_code = ?, country = ?, phone_number = ?
            WHERE id = ? AND user_id = ?
        ");
            
        $stmt->execute([
            $full_name, $street_address, $city, $state, $postal_code,
            $country, $phone_number, $address_id, $user_id
        ]);
            
            $success_message = "Address updated successfully!";
            
            // Refresh address data
            $stmt = $pdo->prepare($check_query);
            $stmt->execute([$address_id, $user_id]);
            $address = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Redirect after 2 seconds
            header("Refresh: 2; URL=account.php#addresses");
            
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $error_message = "Failed to update address. Please try again.";
        }
    }
}

// Handle address deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        // Check if address is used in any orders
        $order_check = $pdo->prepare("
            SELECT COUNT(*) FROM orders 
            WHERE billing_address_id = ? OR shipping_address_id = ?
        ");
        $order_check->execute([$address_id, $address_id]);
        $order_count = $order_check->fetchColumn();

        if ($order_count > 0) {
            $error_message = "This address cannot be deleted because it's associated with existing orders.";
        } else {
            // Check if this is the only address
            $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ?");
            $count_stmt->execute([$user_id]);
            $address_count = $count_stmt->fetchColumn();
            
            if ($address_count <= 1) {
                $error_message = "Cannot delete your only address. Please add another address first.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
                $stmt->execute([$address_id, $user_id]);
                
                if ($stmt->rowCount() > 0) {
                    header("Location: account.php#addresses");
                    exit();
                } else {
                    $error_message = "Address not found or already deleted.";
                }
            }
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $error_message = "Failed to delete address. Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Address | Furnish Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8b4513;
            --secondary: #f5f5dc;
            --text: #333;
            --light-bg: #f8f8f8;
            --border: #ddd;
            --danger: #dc3545;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text);
            line-height: 1.6;
        }
        
        .header {
            background-color: #697a5a;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 1.5rem;
        }
        
        .nav {
            background-color: var(--secondary);
            padding: 10px;
            display: flex;
            justify-content: space-around;
        }
        
        .nav a {
            color: var(--primary);
            text-decoration: none;
            font-weight: bold;
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .form-container {
            background-color: var(--light-bg);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
        }
        
        .checkbox-group input {
            margin-right: 10px;
        }
        
        .btn {
            background-color: var(--primary);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-right: 10px;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-danger {
            background-color: var(--danger);
        }
        
        .form-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .required {
            color: red;
        }
        
        .delete-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        #deleteConfirmation {
            display: none;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <header class="header">
        <i class="fas fa-map-marker-alt"></i> Edit Address
    </header>
    
    <nav class="nav">
        <a href="account.php#addresses"><i class="fas fa-arrow-left"></i> Back to Addresses</a>
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
    </nav>
    
    <div class="container">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2>Edit Address</h2>
            <p>Update your address information below.</p>
            
            <form action="edit_address.php?id=<?php echo $address_id; ?>" method="POST">
                <input type="hidden" name="action" value="update">
                
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($address['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="street_address">Street Address <span class="required">*</span></label>
                    <input type="text" id="street_address" name="street_address" value="<?php echo htmlspecialchars($address['street_address']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($address['city']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State/Province <span class="required">*</span></label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($address['state']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="postal_code">Postal Code <span class="required">*</span></label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($address['postal_code']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="country">Country <span class="required">*</span></label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($address['country']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($address['phone_number']); ?>">
                </div>
                
                
                <div class="form-actions">
                    <a href="account.php#addresses" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn">Update Address</button>
                </div>
            </form>
            
            <div class="delete-form">
                <h3>Delete Address</h3>
                <p>This action cannot be undone. Your address will be permanently deleted.</p>
                
                <button id="showDeleteConfirmation" class="btn btn-danger">Delete Address</button>
                
                <div id="deleteConfirmation">
                    <p>Are you sure you want to delete this address?</p>
                    <form action="edit_address.php?id=<?php echo $address_id; ?>" method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete this address?');">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        <button type="button" id="cancelDelete" class="btn btn-secondary">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showDeleteBtn = document.getElementById('showDeleteConfirmation');
            const deleteConfirmation = document.getElementById('deleteConfirmation');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            
            showDeleteBtn.addEventListener('click', function() {
                deleteConfirmation.style.display = 'block';
                showDeleteBtn.style.display = 'none';
            });
            
            cancelDeleteBtn.addEventListener('click', function() {
                deleteConfirmation.style.display = 'none';
                showDeleteBtn.style.display = 'inline-block';
            });
        });
    </script>
</body>
</html>