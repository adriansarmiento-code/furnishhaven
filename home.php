<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection parameters
$host = 'localhost';
$dbname = 'furn_db';
$username = 'root';
$password = '';

// Establish PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to fetch products with their default color variant
function getProductsByCategory(PDO $pdo, string $category, int $limit = 3): array {
    try {
        // Prepare SQL to fetch products with default image
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   COALESCE(
                       CONCAT('uploads/products/', pc.image_path), 
                       CONCAT('uploads/products/', p.main_image)
                   ) AS display_image
            FROM products p
            LEFT JOIN product_colorways pc ON p.id = pc.product_id AND pc.is_default = 1
            WHERE p.category = :category
            LIMIT :limit
        ");
        
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        // Fetch color variants for each product
        foreach ($products as &$product) {
            $colorStmt = $pdo->prepare("
                SELECT color_name, 
                       CONCAT('uploads/products/', image_path) AS image_path
                FROM product_colorways 
                WHERE product_id = :product_id
            ");
            $colorStmt->bindValue(':product_id', $product['id'], PDO::PARAM_INT);
            $colorStmt->execute();
            $product['colorways'] = $colorStmt->fetchAll();
        }

        return $products;
    } catch (PDOException $e) {
        // Log the error and return an empty array
        error_log("Database error in getProductsByCategory: " . $e->getMessage());
        return [];
    }
}

// Fetch new arrivals and best sellers
try {
    $newArrivals = [
        'Living Room' => getProductsByCategory($pdo, 'Living Room'),
        'Bedroom' => getProductsByCategory($pdo, 'Bedroom'),
        'Dining Room' => getProductsByCategory($pdo, 'Dining Room')
    ];

    // You might want to implement a way to track best sellers 
    // For now, we'll use the same approach as new arrivals
    $bestSellers = [
        'Living Room' => getProductsByCategory($pdo, 'Living Room'),
        'Bedroom' => getProductsByCategory($pdo, 'Bedroom'),
        'Dining Room' => getProductsByCategory($pdo, 'Dining Room')
    ];
} catch (Exception $e) {
    // Log the error and show a generic error message
    error_log("Database error: " . $e->getMessage());
    $newArrivals = [];
    $bestSellers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="home.css">
    <title>Furnish Haven - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
    --primary-color: #566d4b;
    --secondary-color: #697a5a;
    --light-accent: #8ca371;
    --white: #ffffff;
    --background: #f4f4f4;
}

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

/* Adjust body padding to account for fixed header */
body {
    padding-top: 120px;
}
        .color-selector {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        .color-option {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header class="header">
    <div class="header-container">
        <div class="logo-container">
            <img src="img/logo.png" alt="Furnish Haven Logo" class="logo">
            <a href="home.php" class="logo-text">Furnish Haven</a>
        </div>
        
        <div class="account-right">
            <span class="welcome-user">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
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

    <!-- Hero Banner -->
    <section class="hero">
        <div class="container">
            <h2>Elevate Your Living Space</h2>
            <br />
            <br />
            <br />
            <div class="hero-btns">
                <a href="#new-arrivals" class="btn">Shop Now</a>
                <a href="living_room.php" class="btn btn-outline">Browse Collections</a>
            </div>
        </div>
    </section>

    <section class="section" id="new-arrivals">
        <div class="container">
            <h2 class="section-title">New Arrivals</h2>
            <div class="product-grid">
                <?php 
                // Combine new arrivals from different categories
                $allNewArrivals = array_merge(
                    $newArrivals['Living Room'], 
                    $newArrivals['Bedroom'], 
                    $newArrivals['Dining Room']
                );
                
                // Limit to 3 items
                $allNewArrivals = array_slice($allNewArrivals, 0, 3);
                
                foreach ($allNewArrivals as $product): 
                ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['display_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-img"
                             id="product-<?php echo $product['id']; ?>">
                        
                        <?php if (!empty($product['colorways'])): ?>
                            <div class="color-selector">
                                <?php foreach ($product['colorways'] as $colorway): ?>
                                    <div 
                                        class="color-option" 
                                        data-product-id="<?php echo $product['id']; ?>"
                                        data-image="<?php echo htmlspecialchars($colorway['image_path'] ?: $product['display_image']); ?>"
                                        style="background-color: <?php echo htmlspecialchars($colorway['color_name']); ?>"
                                        title="<?php echo htmlspecialchars($colorway['color_name']); ?>"
                                    ></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Best Sellers (Dynamic with Color Variants) -->
    <section class="section" id="best-sellers">
        <div class="container">
            <h2 class="section-title">Best Sellers</h2>
            <div class="product-grid">
                <?php 
                // Combine best sellers from different categories
                $allBestSellers = array_merge(
                    $bestSellers['Living Room'], 
                    $bestSellers['Bedroom'], 
                    $bestSellers['Dining Room']
                );
                
                // Limit to 3 items
                $allBestSellers = array_slice($allBestSellers, 0, 3);
                
                foreach ($allBestSellers as $product): 
                ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['display_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-img"
                             id="product-<?php echo $product['id']; ?>">
                        
                        <?php if (!empty($product['colorways'])): ?>
                            <div class="color-selector">
                                <?php foreach ($product['colorways'] as $colorway): ?>
                                    <div 
                                        class="color-option" 
                                        data-product-id="<?php echo $product['id']; ?>"
                                        data-image="<?php echo htmlspecialchars($colorway['image_path'] ?: $product['display_image']); ?>"
                                        style="background-color: <?php echo htmlspecialchars($colorway['color_name']); ?>"
                                        title="<?php echo htmlspecialchars($colorway['color_name']); ?>"
                                    ></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Categories (Partially Dynamic) -->
    <section class="section" id="categories">
        <div class="container">
            <h2 class="section-title">Shop by Categories</h2>
            <div class="category-grid">
                <?php 
                $categories = [
                    ['name' => 'Living Room', 'image' => 'img/livingroom.avif'],
                    ['name' => 'Bedroom', 'image' => 'img/bedroom.avif'],
                    ['name' => 'Dining Room', 'image' => 'img/diningroom.avif']
                ];
                
                foreach ($categories as $category): 
                ?>
                    <div class="category-card">
                        <a href="<?php echo strtolower(str_replace(' ', '_', $category['name'])); ?>.php">
                            <img src="<?php echo $category['image']; ?>" alt="<?php echo $category['name']; ?>" class="category-img">
                            <div class="category-info">
                                <h3><?php echo $category['name']; ?></h3>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="account.php">My Account</a></li>
                    <li><a href="cart.php">Shopping Cart</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Categories</h3>
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
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="mailto:support@furnish-haven.com">support@furnish-haven.com</a></li>
                    <li><a href="tel:+04599999999">(045) 9999-9999</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Legal</h3>
                <ul>
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Furnish Haven. All Rights Reserved.</p>
            <p>Developed by Cassandra Arcilla, Ryna David, Robyn Gonzales, Adrian Sarmiento</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const colorOptions = document.querySelectorAll('.color-option');

        colorOptions.forEach(option => {
            option.addEventListener('click', () => {
                const productId = option.dataset.productId;
                const newImage = option.dataset.image;
                
                // Update the corresponding product image
                const productImage = document.getElementById(`product-${productId}`);
                if (productImage) {
                    productImage.src = newImage;
                }
            });
        });
    });
    </script>
</body>
</html>