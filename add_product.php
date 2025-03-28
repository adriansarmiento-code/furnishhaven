<?php
session_start();
// Database connection
$host = 'localhost';
$dbname = 'furn_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database connection failed: ' . $e->getMessage();
    header('Location: admin_products.php');
    exit();
}

require_once 'ProductManager.php';

// Initialize variables to prevent undefined variable warnings
$edit_mode = false;
$product = null;
$colorways = [];

$productManager = new ProductManager($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Prepare product data
        $product_data = [
            'name' => $_POST['name'],
            'price' => (float)$_POST['price'],
            'stock' => (int)$_POST['stock'],
            'category' => $_POST['category'],
            'description' => $_POST['description']
        ];

        // Handle main image upload
        $main_image = null;
        if (!empty($_FILES['main_image']['name'])) {
            $main_image = $productManager->uploadProductImage($_FILES['main_image']);
        }

        // Prepare colorways
        $colorway_images = [];
        if (isset($_FILES['colorway_images']['tmp_name'])) {
            foreach ($_FILES['colorway_images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $colorway_file = [
                        'name' => $_FILES['colorway_images']['name'][$key],
                        'type' => $_FILES['colorway_images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['colorway_images']['error'][$key],
                        'size' => $_FILES['colorway_images']['size'][$key]
                    ];
                    $colorway_images[] = [
                        'color_name' => $_POST['colorway_names'][$key],
                        'image_path' => $productManager->uploadProductImage($colorway_file)
                    ];
                }
            }
        }

        // Create product
        $product_id = $productManager->createProduct($product_data, $main_image, $colorway_images);
        
        $_SESSION['message'] = 'Product created successfully.';
        header('Location: admin_products.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4"><?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?></h1>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" 
                       value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Price</label>
                <input type="number" name="price" step="0.01" class="form-control" 
                       value="<?php echo $product ? number_format($product['price'], 2) : ''; ?>" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" 
                       value="<?php echo $product ? $product['stock'] : ''; ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <option value="Living Room" 
                        <?php echo ($product && isset($product['category']) && $product['category'] === 'Living Room') ? 'selected' : ''; ?>>
                        Living Room
                    </option>
                    <option value="Bedroom" 
                        <?php echo ($product && isset($product['category']) && $product['category'] === 'Bedroom') ? 'selected' : ''; ?>>
                        Bedroom
                    </option>
                    <option value="Dining Room" 
                        <?php echo ($product && isset($product['category']) && $product['category'] === 'Dining Room') ? 'selected' : ''; ?>>
                        Dining Room
                    </option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Product Description</label>
            <textarea name="description" class="form-control" rows="4" 
                      placeholder="Enter a detailed description of the product"><?php 
                      echo $product ? htmlspecialchars($product['description'] ?? '') : ''; 
                      ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Main Product Image</label>
            <input type="file" name="main_image" class="form-control" 
                   <?php echo $edit_mode ? '' : 'required'; ?> accept="image/*">
            <?php if ($edit_mode && $product && !empty($product['main_image'])): ?>
                <div class="mt-2">
                    <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                         alt="Current Image" style="max-width: 200px; max-height: 200px;">
                </div>
            <?php endif; ?>
        </div>

        <div id="colorways-container">
            <h3>Colorways</h3>
            <?php 
            // Use empty array if $colorways is null, and ensure at least one colorway field
            $colorway_count = $colorways ? count($colorways) : 1;
            for ($i = 0; $i < $colorway_count; $i++): 
            ?>
                <div class="colorway-group mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Color Name</label>
                            <input type="text" name="colorway_names[]" class="form-control" 
                                   value="<?php echo isset($colorways[$i]) ? htmlspecialchars($colorways[$i]['color_name']) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Colorway Image</label>
                            <input type="file" name="colorway_images[]" class="form-control" accept="image/*">
                            <?php if (isset($colorways[$i]) && !empty($colorways[$i]['image_path'])): ?>
                                <div class="mt-2">
                                    <img src="uploads/products/<?php echo htmlspecialchars($colorways[$i]['image_path']); ?>" 
                                         alt="Colorway Image" style="max-width: 150px; max-height: 150px;">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="mb-3">
            <button type="button" id="add-colorway" class="btn btn-secondary">Add Another Colorway</button>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">
                <?php echo $edit_mode ? 'Update Product' : 'Create Product'; ?>
            </button>
            <a href="admin_products.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('add-colorway').addEventListener('click', function() {
    const container = document.getElementById('colorways-container');
    const newColorway = document.createElement('div');
    newColorway.classList.add('colorway-group', 'mb-3');
    newColorway.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label">Color Name</label>
                <input type="text" name="colorway_names[]" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Colorway Image</label>
                <input type="file" name="colorway_images[]" class="form-control" accept="image/*">
            </div>
        </div>
    `;
    container.appendChild(newColorway);
});
</script>
</body>
</html>