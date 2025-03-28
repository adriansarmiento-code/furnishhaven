<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "furn_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve living room products
$products = [];
$sql = "SELECT id, main_image, name, price, description FROM products WHERE category = 'Bedroom'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Update image path to uploads/products/
        $products[] = [
            "id" => $row['id'],  // Add this line to include the product ID
            "img" => "uploads/products/" . $row['main_image'],
            "name" => $row['name'],
            "price" => "₱" . number_format($row['price'], 2),
            "description" => $row['description'],
            // Add placeholders for other details
            "dimensions" => "Varies", 
            "materials" => "Varies",
            "colors" => [], // You might want to add a separate colors table
            "reviews" => [] // You might want to add a separate reviews table
        ];
    }
}

// Retrieve product details if "View Details" is clicked
$selected_product = null;
if (isset($_GET['name'])) {
    foreach ($products as $product) {
        if ($product['name'] === $_GET['name']) {
            $selected_product = $product;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selected_product ? $selected_product['name'] . ' - Product Details' : 'Bedroom - Product Listing'; ?> | Homify</title>
    <link rel="stylesheet" href="css/bedroom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        
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

    <div class="container">
        <?php if ($selected_product): ?>
            <div class="product-detail-container">
                <div class="product-gallery">
                    <img id="main-image" src="<?php echo $selected_product['img']; ?>" alt="<?php echo $selected_product['name']; ?>" class="main-image">
                </div>
                
                <div class="product-info">
                    <h1><?php echo $selected_product['name']; ?></h1>
                    <div class="price"><?php echo $selected_product['price']; ?></div>
                    <div class="description"><?php echo $selected_product['description']; ?></div>
                    
                    <div class="customization-option">
                        <h3>Quantity</h3>
                        <input type="number" min="1" value="1" style="padding: 8px; width: 60px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="addToCart()">Add to Cart</button>
                        <button class="btn btn-secondary" onclick="addToWishlist()">Save to Wishlist</button>
                    </div>
                    
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
                        <p><?php echo $selected_product['dimensions']; ?></p>
                    </div>
                    <div class="spec-item">
                        <h3>Materials</h3>
                        <p><?php echo $selected_product['materials']; ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Hero Section -->
            <div class="hero">
                <img src="img/bedroom.avif" alt="Bedroom Furniture" class="hero-image">
                <div class="hero-content">
                    <h1>Create Your Dream Bedroom</h1>
                    <p>Discover our premium collection of bedroom furniture for ultimate comfort and style</p>
                    <a href="#featured" class="hero-btn">Shop Now</a>
                </div>
            </div>
            
            <h2 id="featured">Featured Bedroom Collection</h2>
            <?php if (!$selected_product): ?>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo $product['img']; ?>" alt="<?php echo $product['name']; ?>">
                <div class="product-card-content">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>Price: <?php echo $product['price']; ?></p>
                    <a href="product_details.php?id=<?php echo $product['id']; ?>">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
        <?php endif; ?>
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
    <script>
        // JavaScript functions remain the same as in the previous version
        function addToCart() {
            alert('Added to cart!');
        }
        
        function addToWishlist() {
            alert('Added to wishlist!');
        }
    </script>
</body>
</html>