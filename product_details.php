
<?php
session_start();
require_once 'config.php';

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid product ID");
}

$product_id = intval($_GET['id']);

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, 
           (SELECT pc.image_path 
            FROM product_colorways pc 
            WHERE pc.product_id = p.id AND pc.is_default = 1) as default_image
    FROM products p 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found");
}

// Fetch product colorways
$colorway_stmt = $pdo->prepare("
    SELECT id, color_name, image_path 
    FROM product_colorways 
    WHERE product_id = ?
");
$colorway_stmt->execute([$product_id]);
$colorways = $colorway_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details | Furnish Haven</title>
    <link rel="stylesheet" href="css/bedroom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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

    <div class="container">
        <div class="product-detail-container">
            <div class="product-gallery">
                <img id="main-product-image" 
                     src="uploads/products/<?php echo htmlspecialchars($product['default_image'] ?? $product['main_image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="main-image">
            </div>
            
            <div class="product-info">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="price">₱<?php echo number_format($product['price'], 2); ?></div>
                <div class="description"><?php echo htmlspecialchars($product['description']); ?></div>
                
                <form id="add-to-cart-form">
                    <!-- Add hidden input for product_id -->
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="customization-option">
                        <h3>Color</h3>
                        <select id="color-select" name="colorway_id" required>
                            <?php foreach ($colorways as $colorway): ?>
                                <option 
                                    value="<?php echo $colorway['id']; ?>" 
                                    data-image="uploads/products/<?php echo htmlspecialchars($colorway['image_path']); ?>">
                                    <?php echo htmlspecialchars($colorway['color_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="customization-option">
                        <h3>Quantity</h3>
                        <input type="number" 
                               name="quantity" 
                               min="1" 
                               value="1" 
                               style="padding: 8px; width: 60px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                        <button type="button" class="btn btn-secondary" onclick="addToWishlist()">Save to Wishlist</button>
                    </div>
                </form>
                
                <div class="shipping-info">
                    <p><strong>Shipping:</strong> Free standard shipping (3-5 business days). Express shipping available at checkout.</p>
                    <p><strong>Returns:</strong> 30-day easy returns. <a href="#">Learn more</a></p>
                </div>
            </div>
        </div>
        
        <div class="specs-section">
            <h2>Product Specifications</h2>
            <div class="specs-grid">
                <div class="spec-item">
                    <h3>Dimensions</h3>
                    <p><?php echo isset($product['dimensions']) ? htmlspecialchars($product['dimensions']) : 'Not specified'; ?></p>
                </div>
                <div class="spec-item">
                    <h3>Materials</h3>
                    <p><?php echo isset($product['materials']) ? htmlspecialchars($product['materials']) : 'Not specified'; ?></p>
                </div>
            </div>
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
                    <li><a href="mailto:support@furnish-haven.com">support@furnish-haven.com</a></li>
                    <li><a href="mailto:business@furnish-haven.com">business@furnish-haven.com</a></li>
                    <li><a href="tel:04599999999">(045) 9999-9999</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="product_detail.js"></script>
    <script>
        function addToWishlist() {
            alert('Added to wishlist!');
        }
    </script>
</body>
</html>