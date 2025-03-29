<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            
            // Insert new address
            $stmt = $pdo->prepare("
            INSERT INTO addresses 
                (user_id, full_name, street_address, city, state, postal_code, 
                 country, phone_number, created_at) 
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
            
        $stmt->execute([    
            $user_id, $full_name, $street_address, $city, $state, 
            $postal_code, $country, $phone_number
        ]);
            
            $success_message = "Address added successfully!";
            
            // Redirect after successful submission
            header("Location: account.php#addresses");
            exit();
            
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $error_message = "Failed to save address. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Address | Furnish Haven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8b4513;
            --secondary: #f5f5dc;
            --text: #333;
            --light-bg: #f8f8f8;
            --border: #ddd;
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
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .btn-secondary {
            background-color: #6c757d;
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
    </style>
</head>
<body>
    <header class="header">
        <i class="fas fa-map-marker-alt"></i> Add New Address
    </header>
    
    <nav class="nav">
        <a href="account.php"><i class="fas fa-user-circle"></i> Back to Account</a>
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
            <h2>Add New Address</h2>
            <p>Please fill in the details for your new address.</p>
            
            <form action="add_address.php" method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name <span class="required">*</span></label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="street_address">Street Address <span class="required">*</span></label>
                    <input type="text" id="street_address" name="street_address" required>
                </div>
                
                <div class="form-group">
                    <label for="city">City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State/Province <span class="required">*</span></label>
                    <input type="text" id="state" name="state" required>
                </div>
                
                <div class="form-group">
                    <label for="postal_code">Postal Code <span class="required">*</span></label>
                    <input type="text" id="postal_code" name="postal_code" required>
                </div>
                
                <div class="form-group">
                    <label for="country">Country <span class="required">*</span></label>
                    <input type="text" id="country" name="country" required>
                </div>
                
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="tel" id="phone_number" name="phone_number">
                </div>
                
                <div class="form-actions">
                    <a href="account.php#addresses" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>