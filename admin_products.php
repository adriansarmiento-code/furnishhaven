<?php
session_start();
require_once 'config.php'; 
require_once 'ProductManager.php'; // This should now contain the PDO connection code you shared

// Update to use $pdo instead of $conn
$productManager = new ProductManager($pdo);

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $productManager->deleteProduct((int)$_GET['delete']);
        $_SESSION['message'] = 'Product deleted successfully.';
        header('Location: admin_products.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting product: ' . $e->getMessage();
    }
}

// Fetch products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Products per page
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$products = $productManager->listProducts($limit, $offset, $search, $category);
$totalProducts = $productManager->countProducts($search, $category);
$totalPages = ceil($totalProducts / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-md-block bg-light sidebar">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Product Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add_product.php" class="btn btn-sm btn-primary">Add New Product</a>
                </div>
            </div>

            <!-- Search and Filter -->
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search products" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="Living Room" <?php echo $category === 'Living Room' ? 'selected' : ''; ?>>Living Room</option>
                            <option value="Bedroom" <?php echo $category === 'Bedroom' ? 'selected' : ''; ?>>Bedroom</option>
                            <option value="Dining Room" <?php echo $category === 'Dining Room' ? 'selected' : ''; ?>>Dining Room</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                    </div>
                </div>
            </form>

            <!-- Alerts -->
            <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['message']); 
                    unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo htmlspecialchars($_SESSION['error']); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Products Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="uploads/products/<?php echo htmlspecialchars($product['main_image']); ?>" 
                                     alt="Product Image" class="product-image">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-warning">Edit</a>
                                    <a href="?delete=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; 
                            echo $search ? '&search=' . urlencode($search) : ''; 
                            echo $category ? '&category=' . urlencode($category) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>