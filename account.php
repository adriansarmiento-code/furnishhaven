<?php
session_start();
require_once 'config.php'; // Assuming you have a database connection file

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile information
$user_query = "
    SELECT 
        username, 
        email, 
        created_at
    FROM 
        users
    WHERE 
        id = ?
";

// Fetch user's saved addresses
$addresses_query = "
    SELECT 
        id,
        full_name,
        street_address,
        city,
        state,
        postal_code,
        country,
        phone_number,
        is_default
    FROM 
        addresses
    WHERE 
        user_id = ?
    ORDER BY 
        is_default DESC, 
        created_at DESC
";

// Fetch user's orders
$orders_query = "
    SELECT 
        o.id AS order_id, 
        o.total_amount, 
        o.status, 
        o.order_date
    FROM 
        orders o
    WHERE 
        o.user_id = ?
    ORDER BY 
        o.order_date DESC
    LIMIT 5
";

try {
    // Fetch user profile
    $stmt = $pdo->prepare($user_query);
    $stmt->execute([$user_id]);
    $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch addresses
    $stmt = $pdo->prepare($addresses_query);
    $stmt->execute([$user_id]);
    $saved_addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch orders
    $stmt = $pdo->prepare($orders_query);
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Database fetch error: ' . $e->getMessage());
    $error_message = "Unable to retrieve user information.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account | Furnish Haven</title>
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
        
        .account-dashboard {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .account-dashboard h2 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .account-dashboard .join-date {
            color: var(--text);
            font-size: 14px;
            margin-top: 10px;
        }
        
        .account-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .account-card {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center;
        }
        
        .account-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .account-card h3 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .account-card p {
            margin-bottom: 20px;
            color: var(--text);
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

        /* New styles for order list */
        .orders-list {
            margin-top: 20px;
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

        .order-details {
            margin-bottom: 15px;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .address-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
        }

        .address-card.default::before {
            content: 'Default';
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .profile-details {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .profile-details h3 {
            color: var(--primary);
            margin-bottom: 15px;
        }

        .profile-details p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header class="header">
        <i class="fas fa-user-circle"></i> User Account
    </header>
    
    <nav class="nav">
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    
    <div class="container">
        <section class="account-dashboard">
            <h2 id="userGreeting">Welcome, <?php echo htmlspecialchars($user_profile['username']); ?></h2>
            <p>Manage your orders, addresses, and account details all in one place.</p>
            <div class="join-date" id="joinDate">
                <i class="fas fa-calendar-alt"></i> Member since: 
                <?php echo date('F j, Y', strtotime($user_profile['created_at'])); ?>
            </div>
        </section>
        
        <div class="account-sections">
            <div class="account-card" data-section="orders">
                <i class="fas fa-clipboard-list fa-3x" style="color: var(--primary); margin-bottom: 15px;"></i>
                <h3>Order History</h3>
                <p>Track your orders and view details.</p>
                <a href="#" class="btn section-toggle">View Orders</a>
            </div>
            
            <div class="account-card" data-section="addresses">
                <i class="fas fa-map-marker-alt fa-3x" style="color: var(--primary); margin-bottom: 15px;"></i>
                <h3>Saved Addresses</h3>
                <p>Manage your shipping addresses.</p>
                <a href="#" class="btn section-toggle">Manage Addresses</a>
            </div>
            
        </div>

        <!-- Profile Section -->
        <section id="profile" class="section">
            <h2>Profile Details</h2>
            <div class="profile-details">
                <h3>Account Information</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user_profile['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_profile['email']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user_profile['created_at'])); ?></p>
            </div>
        </section>

        <!-- Addresses Section -->
        <section id="addresses" class="section">
            <h2>Your Addresses</h2>
            <?php if (empty($saved_addresses)): ?>
                <p>You have not saved any addresses yet.</p>
            <?php else: ?>
                <div class="addresses-list">
                    <?php foreach ($saved_addresses as $address): ?>
                        <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                            <h3><?php echo htmlspecialchars($address['full_name']); ?></h3>
                            <p>
                                <?php echo htmlspecialchars($address['street_address']); ?><br>
                                <?php echo htmlspecialchars($address['city']); ?>, 
                                <?php echo htmlspecialchars($address['state']); ?> 
                                <?php echo htmlspecialchars($address['postal_code']); ?><br>
                                <?php echo htmlspecialchars($address['country']); ?>
                            </p>
                            <?php if (!empty($address['phone_number'])): ?>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($address['phone_number']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center mt-3">
                        <a href="add_address.php" class="btn">Add New Address</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Orders Section -->
        <section id="orders" class="section">
            <h2>Your Orders</h2>
            <?php if (isset($orders_error)): ?>
                <p class="error"><?php echo htmlspecialchars($orders_error); ?></p>
            <?php elseif (empty($recent_orders)): ?>
                <p>You have not placed any orders yet.</p>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($recent_orders as $order): ?>
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
                            </div>

                            <div class="order-actions">
                                <a href="order_details.php?order_id=<?php echo $order['order_id']; ?>" class="btn">
                                    View Order Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mt-3">
                        <a href="my_orders.php" class="btn">View All Orders</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Addresses Section -->
        <section id="addresses" class="section">
            <h2>Your Addresses</h2>
            <p>Manage your saved addresses here.</p>
            <!-- Add address management functionality here -->
        </section>

        
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Section toggle functionality
            const sectionToggles = document.querySelectorAll('.section-toggle, .account-card');
            const sections = document.querySelectorAll('.section');

            sectionToggles.forEach(toggle => {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    
                    // Remove active class from all sections
                    sections.forEach(section => {
                        section.classList.remove('active');
                    });

                    // Get the target section
                    const targetSectionId = this.getAttribute('data-section') || 
                        this.getAttribute('href')?.substring(1);
                    
                    if (targetSectionId) {
                        const targetSection = document.getElementById(targetSectionId);
                        if (targetSection) {
                            targetSection.classList.add('active');
                        }
                    }
                });
            });

            // Add a new account card for Profile
            const profileCard = document.createElement('div');
            profileCard.className = 'account-card';
            profileCard.setAttribute('data-section', 'profile');
            profileCard.innerHTML = `
                <i class="fas fa-user fa-3x" style="color: var(--primary); margin-bottom: 15px;"></i>
                <h3>Profile</h3>
                <p>View and manage your account details.</p>
                <a href="#" class="btn section-toggle">View Profile</a>
            `;

            // Insert the profile card before the first account card
            const accountSections = document.querySelector('.account-sections');
            const firstCard = accountSections.firstElementChild;
            accountSections.insertBefore(profileCard, firstCard);

            // Default to showing orders section
            document.getElementById('orders').classList.add('active');
        });
    </script>
</body>
</html>