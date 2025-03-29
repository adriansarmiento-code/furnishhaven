
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
    <style>
        /* Related Products Section */
.related-products {
    padding: 40px 20px;
    background-color: #f9f9f9;
    margin-top: 40px;
    text-align: center;
}

.related-products h2 {
    font-size: 24px;
    color: #4B5945;
    margin-bottom: 30px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.related-products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.related-product-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    text-align: center;
    padding-bottom: 20px;
}

.related-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.related-product-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    margin-bottom: 15px;
}

.related-product-card h3 {
    font-size: 16px;
    color: #333;
    margin: 0 15px 10px;
    font-weight: 500;
}

.related-product-card .price {
    color: #4B5945;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 15px;
}

.related-product-card .view-details {
    display: inline-block;
    padding: 8px 20px;
    background-color: #4B5945;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s;
}

.related-product-card .view-details:hover {
    background-color: #66785F;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .related-products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .related-products-grid {
        grid-template-columns: 1fr;
    }
}
.customization-option {
    margin-bottom: 20px;
}

.customization-option h3 {
    font-size: 16px;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
}

#color-select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background-color: #fff;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
    transition: all 0.3s ease;
}

#color-select:focus {
    outline: none;
    border-color: #4B5945;
    box-shadow: 0 0 0 2px rgba(75, 89, 69, 0.2);
}

#color-select option {
    padding: 8px;
    position: relative;
}

/* Optional: If you want to show color swatches next to options */
#color-select option::before {
    content: "";
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 8px;
    vertical-align: middle;
    background-image: var(--option-image);
    background-size: cover;
    border-radius: 3px;
    border: 1px solid #ddd;
}

/* For browsers that support data attributes in CSS (limited support) */
#color-select option[data-image] {
    background-image: var(--option-image);
    padding-left: 30px;
    background-repeat: no-repeat;
    background-position: left center;
    background-size: 20px;
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
                    </div>
                </form>
                
                <div class="shipping-info">
    <p><strong>Shipping:</strong> Standard shipping fee of ₱500 for all Philippines orders. Expected delivery within 3-5 business days after processing.</p>
    <p><strong>Returns:</strong> 30-day easy returns. Customer responsible for return shipping costs.</p>
</div>
            </div>
        </div>
    </div>

    <!-- You May Also Like Section -->
<div class="related-products">
    <h2>You May Also Like</h2>
    <div class="related-products-grid">
        <?php
        // Fetch 4 random products from the same category (excluding current product)
        $related_stmt = $pdo->prepare("
            SELECT id, name, price, main_image 
            FROM products 
            WHERE category = :category AND id != :product_id
            ORDER BY RAND()
            LIMIT 4
        ");
        $related_stmt->execute([
            ':category' => $product['category'],
            ':product_id' => $product_id
        ]);
        $related_products = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If not enough in same category, get random products
        if (count($related_products) < 4) {
            $needed = 4 - count($related_products);
            $fallback_stmt = $pdo->prepare("
                SELECT id, name, price, main_image 
                FROM products 
                WHERE id != :product_id
                ORDER BY RAND()
                LIMIT :needed
            ");
            $fallback_stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $fallback_stmt->bindValue(':needed', $needed, PDO::PARAM_INT);
            $fallback_stmt->execute();
            $fallback_products = $fallback_stmt->fetchAll(PDO::FETCH_ASSOC);
            $related_products = array_merge($related_products, $fallback_products);
        }
        
        foreach ($related_products as $related): ?>
            <div class="related-product-card">
                <a href="product_details.php?id=<?php echo $related['id']; ?>">
                    <img src="uploads/products/<?php echo htmlspecialchars($related['main_image']); ?>" 
                         alt="<?php echo htmlspecialchars($related['name']); ?>">
                    <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                    <div class="price">₱<?php echo number_format($related['price'], 2); ?></div>
                    <a href="product_details.php?id=<?php echo $related['id']; ?>" class="view-details">View Details</a>
                </a>
            </div>
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
                    <li><a href="mailto:support@furnish-haven.com">support@furnish-haven.com</a></li>
                    <li><a href="mailto:business@furnish-haven.com">business@furnish-haven.com</a></li>
                    <li><a href="tel:04599999999">(045) 9999-9999</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="product_detail.js"></script>
</body>
</html>