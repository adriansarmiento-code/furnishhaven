<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's addresses
$address_stmt = $pdo->prepare("
    SELECT * FROM addresses 
    WHERE user_id = ?
");
$address_stmt->execute([$user_id]);
$user_addresses = $address_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cart items
$cart_stmt = $pdo->prepare("
    SELECT 
        c.id as cart_id,
        p.id as product_id,
        p.name,
        p.price,
        pc.color_name,
        pc.image_path,
        c.quantity,
        (p.price * c.quantity) as total_price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    LEFT JOIN product_colorways pc ON c.product_colorway_id = pc.id
    WHERE c.user_id = ?
");
$cart_stmt->execute([$user_id]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total cart value
$total_cart_value = array_sum(array_column($cart_items, 'total_price'));
$shipping = 500; // 500 pesos
$tax = $total_cart_value * 0.05; // 5% tax
$total = $total_cart_value + $shipping + $tax;

// Check if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO orders 
    (user_id, total_amount, shipping_fee, shipping_address_id, status, order_date)
    VALUES 
    (?, ?, 500, ?, 'Pending', NOW())
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --primary-color: #566d4b;
            --secondary-color: #697a5a;
            --light-accent: #8ca371;
            --white: #ffffff;
            --background: #f4f4f4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--background);
            padding-top: 120px; /* Accommodate fixed header */
        }

        /* Header Styles (Same as cart.php) */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: var(--secondary-color);
            color: var(--white);
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--white);
            text-decoration: none;
        }

        .account-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .account-right a {
            color: var(--white);
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s;
        }

        .account-right a:hover {
            color: var(--light-accent);
        }

        .account-right i {
            font-size: 18px;
        }

        .nav {
            background: var(--primary-color);
            padding: 10px 0;
            width: 100%;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .nav a {
            color: var(--white);
            text-decoration: none;
            padding: 8px 15px;
            font-size: 16px;
            transition: background-color 0.3s;
            border-radius: 4px;
        }

        .nav a:hover {
            background: var(--light-accent);
        }

        /* Checkout Container Styles */
        .checkout-container {
            max-width: 1200px;
            margin: 20px auto;
            display: flex;
            gap: 20px;
        }

        .checkout-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .checkout-section h2 {
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        /* Address Option Styles */
        .address-option {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .address-option input[type="radio"] {
            margin-right: 15px;
        }

        .address-details p {
            margin: 5px 0;
        }

        /* New Address Form Styles */
        .new-address-form .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .new-address-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Payment Method Styles */
        .payment-methods label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .payment-methods input[type="radio"] {
            margin-right: 10px;
        }

        /* Order Summary Styles */
        .order-items {
            margin-bottom: 15px;
        }

        .order-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .order-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 15px;
        }

        .order-total {
            text-align: right;
            padding: 10px 0;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Place Order Button */
        .place-order-btn {
            display: block;
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .place-order-btn:hover {
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo-container">
                <img src="img/logo.png" alt="Furnish Haven Logo" class="logo">
                <a href="home.php" class="logo-text">Furnish Haven</a>
            </div>
            
            <div class="account-right">
                <a href="account.php"><i class="fas fa-user"></i> Account</a>
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="nav">
            <div class="nav-container">
                <a href="living_room.php">Living Room</a>
                <a href="bedroom.php">Bedroom</a>
                <a href="dining_room.php">Dining Room</a>
            </div>
        </div>
    </header>

    <div class="checkout-container">
        <div class="checkout-sections" style="flex: 2;">
        <form id="checkout-form" action="process_order.php" method="POST">
                <div class="checkout-section">
                    <h2>Shipping Address</h2>
                    
                    <?php if (!empty($user_addresses)): ?>
                        <div class="existing-addresses">
                            <?php foreach ($user_addresses as $address): ?>
                                <label class="address-option">
                                    <input type="radio" 
                                           name="shipping_address_id" 
                                           value="<?php echo $address['id']; ?>" 
                                           required>
                                    <div class="address-details">
                                        <p><?php echo htmlspecialchars($address['full_name']); ?></p>
                                        <p><?php echo htmlspecialchars($address['street_address']); ?></p>
                                        <p><?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' ' . $address['postal_code']); ?></p>
                                        <p><?php echo htmlspecialchars($address['country']); ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="new-address-toggle">
                        <label>
                            <input type="checkbox" id="new-address-checkbox"> 
                            Add New Address
                        </label>
                    </div>
                    
                    <div id="new-address-form" class="new-address-form" style="display:none;">
                        <div class="form-row">
                            <input type="text" name="new_full_name" placeholder="Full Name">
                            <input type="text" name="new_street_address" placeholder="Street Address">
                        </div>
                        <div class="form-row">
                            <input type="text" name="new_city" placeholder="City">
                            <input type="text" name="new_state" placeholder="State/Province">
                        </div>
                        <div class="form-row">
                            <input type="text" name="new_postal_code" placeholder="Postal Code">
                            <input type="text" name="new_country" placeholder="Country">
                        </div>
                        <input type="tel" name="new_phone" placeholder="Phone Number">
                    </div>
                </div>
                
                <div class="checkout-section">
                    <h2>Payment Method</h2>
                    <div class="payment-methods">
                        <label>
                            <input type="radio" name="payment_method" value="credit_card">
                            Credit Card
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="paypal">
                            PayPal
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="bank_transfer">
                            Bank Transfer
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="place-order-btn" disabled>Place Order</button>
            </form>
        </div>

        <div class="checkout-section" style="flex: 1;">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <img src="uploads/products/<?php echo htmlspecialchars($item['image_path'] ?? 'placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <p><?php echo htmlspecialchars($item['name']); ?></p>
                            <p>Color: <?php echo htmlspecialchars($item['color_name']); ?></p>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p>Price: ₱<?php echo number_format($item['total_price'], 2); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-total">
                <p>Subtotal: ₱<?php echo number_format($total_cart_value, 2); ?></p>
                <p>Shipping: ₱<?php echo number_format($shipping, 2); ?></p>
                <p>Tax: ₱<?php echo number_format($tax, 2); ?></p>
                <h3>Total: ₱<?php echo number_format($total, 2); ?></h3>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkoutForm = document.getElementById('checkout-form');
            const newAddressCheckbox = document.getElementById('new-address-checkbox');
            const placeOrderBtn = document.querySelector('.place-order-btn');
            const existingAddressRadios = document.querySelectorAll('input[name="shipping_address_id"]');
            const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
            const newAddressForm = document.getElementById('new-address-form');
            
            // Function to validate the form
            // Replace the validateForm function with this improved version
            function validateForm() {
                // Check if an existing address is selected or new address is valid
                const existingAddressSelected = Array.from(existingAddressRadios).some(radio => radio.checked);
                const newAddressChecked = newAddressCheckbox.checked;
                const paymentMethodSelected = Array.from(paymentMethodRadios).some(radio => radio.checked);
                
                let newAddressValid = true;
                if (newAddressChecked) {
                    const requiredNewAddressFields = [
                        'new_full_name', 
                        'new_street_address', 
                        'new_city', 
                        'new_state', 
                        'new_postal_code', 
                        'new_country'
                    ];
                    
                    newAddressValid = requiredNewAddressFields.every(fieldName => {
                        const field = document.querySelector(`[name="${fieldName}"]`);
                        return field && field.value.trim() !== '';
                    });
                    
                    // Disable existing address selection if adding a new address
                    existingAddressRadios.forEach(radio => {
                        radio.disabled = newAddressChecked;
                        if (newAddressChecked) {
                            radio.checked = false;
                        }
                    });
                } else {
                    // Enable existing address selection
                    existingAddressRadios.forEach(radio => {
                        radio.disabled = false;
                    });
                }
                
                // Enable/disable place order button based on validation
                placeOrderBtn.disabled = !(
                    (existingAddressSelected || (newAddressChecked && newAddressValid)) && 
                    paymentMethodSelected
                );
            }
            
            // Add event listeners for validation
            existingAddressRadios.forEach(radio => {
                radio.addEventListener('change', validateForm);
            });
            
            paymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', validateForm);
            });
            
            newAddressCheckbox.addEventListener('change', () => {
                newAddressForm.style.display = newAddressCheckbox.checked ? 'block' : 'none';
                
                // Add validation to new address fields
                const newAddressFields = newAddressForm.querySelectorAll('input');
                newAddressFields.forEach(field => {
                    field.addEventListener('input', validateForm);
                });
                
                validateForm();
            });
            
            // Initial form validation
            validateForm();
            
            // Form submission handler
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Add a hidden field to indicate whether we're using a new address
                if (newAddressCheckbox.checked) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'use_new_address';
                    hiddenField.value = '1';
                    checkoutForm.appendChild(hiddenField);
                }
                
                // Final validation before submission
                if (!placeOrderBtn.disabled) {
                    checkoutForm.submit();
                }
            });
        });
    </script>

    <script src="checkout.js"></script>
</body>
</html>