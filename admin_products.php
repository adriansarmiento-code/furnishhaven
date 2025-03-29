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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary: #4B5945;
        --secondary: #66785F;
        --accent: #B2C9AD;
        --light: #f8f8f8;
        --white: #ffffff;
        --medium-gray: #dddddd;
        --dark-gray: #666666;
        --black: #222222;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        color: var(--black);
        background-color: var(--light);
    }
    
    .admin-container {
        display: flex;
        min-height: 100vh;
    }
    
    .sidebar {
        width: 250px;
        background-color: var(--primary);
        color: white;
        padding: 20px 0;
    }
    
    .sidebar-header {
        padding: 0 20px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .sidebar-nav li {
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-nav a {
        display: block;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .sidebar-nav a:hover {
        background-color: var(--secondary);
    }
    
    .sidebar-nav a.active {
        background-color: var(--accent);
        color: var(--black);
    }
    
    .main-content {
        flex: 1;
        padding: 30px;
        background-color: var(--white);
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--medium-gray);
    }
    
    .header h1 {
        color: var(--primary);
    }
    
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background-color: var(--primary);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: var(--secondary);
    }
    
    .btn-warning {
        background-color: #ffc107;
        color: black;
    }
    
    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
    
    .btn:hover {
        opacity: 0.9;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .table th, 
    .table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--medium-gray);
    }
    
    .table th {
        background-color: var(--primary);
        color: white;
    }
    
    .table tr:nth-child(even) {
        background-color: var(--light);
    }
    
    .table tr:hover {
        background-color: rgba(178, 201, 173, 0.2);
    }
    
    .product-image {
        max-width: 80px;
        max-height: 80px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .alert {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 30px;
        gap: 5px;
    }
    
    .page-item {
        list-style: none;
    }
    
    .page-link {
        padding: 8px 12px;
        text-decoration: none;
        border: 1px solid var(--medium-gray);
        border-radius: 4px;
        color: var(--black);
        transition: all 0.3s;
    }
    
    .page-item.active .page-link {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    
    .page-link:hover:not(.active) {
        background-color: var(--light);
    }
    
    @media (max-width: 768px) {
        .admin-container {
            flex-direction: column;
        }
        
        .sidebar {
            width: 100%;
            position: static;
            height: auto;
        }
        
        .table {
            display: block;
            overflow-x: auto;
        }
    }
</style>
</head>
<body>
<div class="admin-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
    <div class="sidebar-header">
        <h2>Furnish Haven</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="sidebar-nav">
        <li><a href="admin_emails.php"><i class="fas fa-envelope"></i> Messages</a></li>
        <li><a href="admin_products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
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
                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
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