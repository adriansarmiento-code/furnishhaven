<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$stmt = $pdo->prepare("
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
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate subtotal, shipping, and tax
$subtotal = array_sum(array_column($cart_items, 'total_price'));
$shipping = count($cart_items) > 0 ? 500 : 0; // Updated shipping logic
$tax = $subtotal * 0.05;
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Shopping Cart</title>
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

        /* Header Styles */
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
        /* ====== Footer ====== */
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

.copyright {
    text-align: center;
    padding-top: 30px;
    margin-top: 30px;
    border-top: 1px solid rgba(255,255,255,0.1);
    color: var(--text-light);
    font-size: 0.9rem;
}

/* Updated Cart Styles */
.cart-container {
    display: flex;
    max-width: 1200px;
    margin: 20px auto;
    gap: 40px;
    padding: 0 20px;
    align-items: flex-start;
}

.cart-items {
    flex: 2;
    min-width: 60%;
}

.order-summary {
    flex: 1;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    position: sticky;
    top: 140px;
}

.cart-item {
    display: flex;
    background: white;
    margin-bottom: 20px;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s ease;
}

.cart-item:hover {
    transform: translateY(-2px);
}

.cart-item img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 6px;
    margin-right: 25px;
}

.cart-item-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.quantity-control {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 15px 0;
}

.qty-input {
    width: 60px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    text-align: center;
}

.remove-item {
    align-self: flex-start;
    padding: 8px 16px;
    background: #ff4444;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.remove-item:hover {
    opacity: 0.9;
}

/* Updated Order Summary */
.order-summary h2 {
    font-size: 1.4rem;
    color: var(--primary-color);
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.order-summary-line {
    display: flex;
    justify-content: space-between;
    margin: 12px 0;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
}

.order-summary-total {
    display: flex;
    justify-content: space-between;
    margin: 25px 0;
    padding-top: 15px;
    font-size: 1.1rem;
    font-weight: bold;
    color: var(--primary-color);
    border-top: 1px solid #eee;
}

.promo-section {
    margin: 25px 0;
    display: flex;
    gap: 10px;
}

.promo-section input {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.promo-section button {
    padding: 12px 20px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.promo-section button:hover {
    background: var(--secondary-color);
}

.checkout-btn {
    width: 100%;
    padding: 16px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s ease;
    cursor: pointer;
    font-family: inherit;
}

.checkout-btn:hover {
    background: var(--secondary-color);
}
.checkout-btn:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

a.checkout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .cart-container {
        flex-direction: column;
    }
    
    .order-summary {
        position: static;
        width: 100%;
    }
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

    <div class="cart-container">
        <div class="cart-items">
            <h2>Your Cart (<?php echo count($cart_items); ?> items)</h2>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
    <img src="uploads/products/<?php echo htmlspecialchars($item['image_path'] ?? 'placeholder.jpg'); ?>" 
         alt="<?php echo htmlspecialchars($item['name']); ?>">
    
    <div class="cart-item-details">
        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
        <p>₱<?php echo number_format($item['price'], 2); ?></p>
        
        <div class="quantity-control">
    <button class="qty-decrement"></button>
    <input type="number" 
           class="qty-input" 
           value="<?php echo $item['quantity']; ?>" 
           min="1" 
           data-cart-id="<?php echo $item['cart_id']; ?>">
    <button class="qty-increment"></button>
</div>
        
        <button class="remove-item">Remove</button>
    </div>
</div>
            <?php endforeach; ?>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <p>Subtotal (<?php echo count($cart_items); ?> items): ₱<?php echo number_format($subtotal, 2); ?></p>
            <p>Shipping: ₱<?php echo number_format($shipping, 2); ?></p>
            <p>Tax: ₱<?php echo number_format($tax, 2); ?></p>
            <h3>Total: ₱<?php echo number_format($total, 2); ?></h3>
            
            <div class="promo-section">
                <input type="text" placeholder="Promo Code">
                <button>Apply</button>
            </div>

            <?php if(count($cart_items) > 0): ?>
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    <?php else: ?>
        <button class="checkout-btn" disabled>Proceed to Checkout</button>
    <?php endif; ?>
        </div>
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
                    <li><a href="mailto:support@homify.com">support@furnish_haven.com</a></li>
                    <li><a href="mailto:business@homify.com">business@furnish_haven.com</a></li>
                    <li><a href="tel:04599999999">(045) 9999-9999</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="cart.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity increment/decrement functionality
    document.querySelectorAll('.quantity-control').forEach(control => {
        const input = control.querySelector('.qty-input');
        const decrement = control.querySelector('.qty-decrement');
        const increment = control.querySelector('.qty-increment');
        const cartId = input.dataset.cartId;
        
        decrement.addEventListener('click', () => {
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateCartQuantity(cartId, input.value);
            }
        });
        
        increment.addEventListener('click', () => {
            input.value = parseInt(input.value) + 1;
            updateCartQuantity(cartId, input.value);
        });
        
        input.addEventListener('change', () => {
            if (parseInt(input.value) < 1) input.value = 1;
            updateCartQuantity(cartId, input.value);
        });
    });
    
    // Remove item functionality
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            const cartId = cartItem.dataset.cartId;
            
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeCartItem(cartId, cartItem);
            }
        });
    });
    
    // Function to update cart quantity via AJAX
    function updateCartQuantity(cartId, quantity) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optional: Update the displayed totals if needed
                location.reload(); // Simple way to refresh the cart totals
            } else {
                alert('Error updating quantity: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating quantity');
        });
    }
    
    // Function to remove cart item via AJAX
    function removeCartItem(cartId, cartElement) {
        fetch('remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cart_id=${cartId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartElement.remove();
                // Optional: Update the cart count and totals
                location.reload(); // Simple way to refresh the cart
            } else {
                alert('Error removing item: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing item');
        });
    }
});
</script>
</body>
</html>