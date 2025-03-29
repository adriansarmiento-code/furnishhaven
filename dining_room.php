<?php
// Database connection
require_once 'config.php';

try {
    // Retrieve product details if "View Details" is clicked
    $selected_product = null;
    if (isset($_GET['id'])) {
        $product_id = intval($_GET['id']);
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id AND category = 'Dining Room'");
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $selected_product = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch dining room products
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'Dining Room'");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Error handling
    error_log("Database error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selected_product ? htmlspecialchars($selected_product['name']) . ' - Product Details' : 'Dining Room - Product Listing'; ?> | Furnish Haven</title>
    <link rel="stylesheet" href="css/diningroom.css">
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
        <?php if ($selected_product): ?>
            <div class="product-detail-container">
                <div class="product-gallery">
                    <img id="main-image" src="uploads/products/<?php echo htmlspecialchars($selected_product['main_image']); ?>" alt="<?php echo htmlspecialchars($selected_product['name']); ?>" class="main-image">
                    <div class="thumbnail-container">
                        <img src="uploads/products/<?php echo htmlspecialchars($selected_product['main_image']); ?>" alt="Thumbnail 1" class="thumbnail active" onclick="changeImage(this, 'uploads/products/<?php echo htmlspecialchars($selected_product['main_image']); ?>')">
                        <!-- Additional thumbnails would go here in a real implementation -->
                    </div>
                </div>
                
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($selected_product['name']); ?></h1>
                    <div class="price">₱<?php echo number_format($selected_product['price'], 2); ?></div>
                    <div class="description"><?php echo htmlspecialchars($selected_product['description']); ?></div>
                    
                    <div class="customization-option">
                        <h3>Quantity</h3>
                        <input type="number" min="1" max="<?php echo intval($selected_product['stock']); ?>" value="1" style="padding: 8px; width: 60px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="addToCart(<?php echo intval($selected_product['id']); ?>)">Add to Cart</button>
                        <button class="btn btn-secondary" onclick="addToWishlist(<?php echo intval($selected_product['id']); ?>)">Save to Wishlist</button>
                    </div>
                    
                    <div class="shipping-info">
                        <p><strong>Shipping:</strong> Free standard shipping (3-5 business days). Express shipping available at checkout.</p>
                        <p><strong>Returns:</strong> 30-day easy returns. <a href="#" style="color: #4B5945;">Learn more</a></p>
                        <p><strong>Stock:</strong> <?php echo intval($selected_product['stock']); ?> items available</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Hero Section -->
            <div class="hero">
                <img src="img/diningroom.avif" alt="Dining Room Setup" class="hero-image">
                <div class="hero-content">
                    <h1>Elevate Your Dining Experience</h1>
                    <p>Discover our premium collection of dining room furniture for memorable meals</p>
                    <a href="#featured" class="hero-btn">Shop Now</a>
                </div>
            </div>
            
            <h2 id="featured">Featured Dining Room Collection</h2>
        <?php endif; ?>

        <!-- Product Grid -->
        <div class="product-grid">
    <?php foreach ($products as $product): ?>
        <?php if (!$selected_product || $product['id'] !== $selected_product['id']): ?>
            <div class="product-card">
                <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Price: ₱<?php echo number_format($product['price'], 2); ?></p>
                <a href="product_details.php?id=<?php echo intval($product['id']); ?>">View Details</a>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
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

    <script>
        // Image zoom functionality
        const mainImage = document.getElementById('main-image');
        if (mainImage) {
            mainImage.addEventListener('click', function() {
                this.classList.toggle('zoomed');
            });
        }
        
        // Change main image when thumbnail is clicked
        function changeImage(thumbnail, newSrc) {
            document.querySelectorAll('.thumbnail').forEach(img => img.classList.remove('active'));
            thumbnail.classList.add('active');
            document.getElementById('main-image').src = newSrc;
            document.getElementById('main-image').classList.remove('zoomed');
        }
        
        // Select color/size options
        function selectOption(element, type) {
            const container = element.parentElement;
            container.querySelectorAll('.' + type + '-option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
        }
        
        // Cart and wishlist functions
        function addToCart() {
            alert('Added to cart!');
            // In a real implementation, this would send an AJAX request to add the item to cart
        }
        
        function addToWishlist() {
            alert('Added to wishlist!');
            // In a real implementation, this would send an AJAX request to add the item to wishlist
        }
        
        function submitReview() {
            alert('Thank you for your review!');
            // In a real implementation, this would send the review data to the server
        }
    </script>
</body>
</html>