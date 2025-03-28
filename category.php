<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
include 'config.php';

// Determine the current page category
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$categoryMapping = [
    'living_room' => 'Living Room',
    'bedroom' => 'Bedroom',
    'dining_room' => 'Dining Room'
];

// Validate category
if (!isset($categoryMapping[$currentPage])) {
    die("Invalid category");
}

$category = $categoryMapping[$currentPage];

// Fetch products with their default color variant
try {
    $stmt = $conn->prepare("
        SELECT p.*, 
               COALESCE(pc.image_path, p.main_image) AS display_image
        FROM products p
        LEFT JOIN product_colorways pc ON p.id = pc.product_id AND pc.is_default = 1
        WHERE p.category = ?
    ");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch color variants for each product
    foreach ($products as &$product) {
        $colorStmt = $conn->prepare("
            SELECT color_name, image_path 
            FROM product_colorways 
            WHERE product_id = ?
        ");
        $colorStmt->bind_param("i", $product['id']);
        $colorStmt->execute();
        $colorResult = $colorStmt->get_result();
        $product['colorways'] = $colorResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Furnish Haven - <?php echo $category; ?></title>
    <link rel="stylesheet" href="css/category.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
    <!-- Navigation (Similar to home page) -->
    <header class="header">
        <!-- ... (similar to home page navigation) ... -->
    </header>

    <!-- Category Header -->
    <section class="category-header">
        <div class="container">
            <h1><?php echo $category; ?> Collection</h1>
            <p>Explore our curated selection of <?php echo strtolower($category); ?> furniture</p>
        </div>
    </section>

    <!-- Product Grid -->
    <section class="section products">
        <div class="container">
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
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
                                        data-image="<?php echo htmlspecialchars($colorway['image_path'] ?: $product['main_image']); ?>"
                                        style="background-color: <?php echo htmlspecialchars($colorway['color_name']); ?>"
                                        title="<?php echo htmlspecialchars($colorway['color_name']); ?>"
                                    ></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                            <p class="stock">
                                <?php 
                                if ($product['stock'] > 0) {
                                    echo "In Stock: " . $product['stock'];
                                } else {
                                    echo "<span class='out-of-stock'>Out of Stock</span>";
                                }
                                ?>
                            </p>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer (Similar to home page) -->
    <footer class="footer">
        <!-- ... (similar to home page footer) ... -->
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