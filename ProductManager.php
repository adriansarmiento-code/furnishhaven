<?php
class ProductManager {
    private $pdo;
    private $upload_dir = 'uploads/products/';

    public function __construct(PDO $database_connection) {
        if (!$database_connection) {
            throw new Exception("Invalid database connection");
        }
        $this->pdo = $database_connection;
        
        // Ensure upload directory exists
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    public function uploadProductImage($file) {
        // Check if file was uploaded successfully
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("No file uploaded or upload error occurred.");
        }

        // Generate a unique filename
        $filename = uniqid() . '_' . basename($file['name']);
        $target_path = $this->upload_dir . $filename;

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Invalid file type. Only JPG, PNG, and WebP are allowed.");
        }

        // Check file size (optional - example limit of 5MB)
        $max_file_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_file_size) {
            throw new Exception("File is too large. Maximum size is 5MB.");
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return $filename;
        } else {
            throw new Exception("Failed to move uploaded file.");
        }
    }

    public function createProduct($data, $main_image, $colorways = []) {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();
            
            // Prepare insert statement to include description
            $stmt = $this->pdo->prepare(
                "INSERT INTO products (name, price, stock, category, description, main_image)
                 VALUES (:name, :price, :stock, :category, :description, :main_image)"
            );
            
            // Bind parameters
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
            $stmt->bindValue(':stock', $data['stock'], PDO::PARAM_INT);
            $stmt->bindValue(':category', $data['category'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindValue(':main_image', $main_image, PDO::PARAM_STR);
            
            // Execute statement
            $stmt->execute();
            
            $product_id = $this->pdo->lastInsertId();
            
            // Insert colorways
            $this->saveColorways($product_id, $colorways);
            
            // Commit transaction
            $this->pdo->commit();
            
            return $product_id;
        } catch (Exception $e) {
            // Rollback in case of error
            $this->pdo->rollBack();
           
            // Log the full error for debugging
            error_log("Product Creation Error: " . $e->getMessage());
           
            throw $e;
        }
    }

    private function saveColorways($product_id, $colorways) {
        if (empty($colorways)) return;

        // Prepare colorway insert statement
        $colorway_stmt = $this->pdo->prepare(
            "INSERT INTO product_colorways (product_id, color_name, image_path, is_default) 
             VALUES (:product_id, :color_name, :image_path, :is_default)"
        );

        foreach ($colorways as $index => $colorway) {
            // Skip if no color name or image
            if (empty($colorway['color_name']) || empty($colorway['image_path'])) continue;

            $colorway_stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $colorway_stmt->bindValue(':color_name', $colorway['color_name'], PDO::PARAM_STR);
            $colorway_stmt->bindValue(':image_path', $colorway['image_path'], PDO::PARAM_STR);
            $colorway_stmt->bindValue(':is_default', ($index === 0) ? 1 : 0, PDO::PARAM_INT);
            $colorway_stmt->execute();
        }
    }

    public function getProductWithColorways($product_id) {
        // Fetch product with its colorways
        $stmt = $this->pdo->prepare(
            "SELECT p.*, 
                    c.id as colorway_id, 
                    c.color_name, 
                    c.image_path, 
                    c.is_default
             FROM products p
             LEFT JOIN product_colorways c ON p.id = c.product_id
             WHERE p.id = :product_id"
        );
        $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();

        $product = null;
        $colorways = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!$product) {
                $product = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'price' => $row['price'],
                    'stock' => $row['stock'],
                    'category' => $row['category'],
                    'main_image' => $row['main_image']
                ];
            }

            if ($row['colorway_id']) {
                $colorways[] = [
                    'id' => $row['colorway_id'],
                    'color_name' => $row['color_name'],
                    'image_path' => $row['image_path'],
                    'is_default' => $row['is_default']
                ];
            }
        }

        return [
            'product' => $product,
            'colorways' => $colorways
        ];
    }

    public function listProducts($limit = 10, $offset = 0, $search = '', $category = '') {
        // Prepare base query
        $query = "SELECT * FROM products WHERE 1=1";
        $params = [];

        // Add search filter
        if (!empty($search)) {
            $query .= " AND (name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Add category filter
        if (!empty($category)) {
            $query .= " AND category = :category";
            $params[':category'] = $category;
        }

        // Add limit and offset
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        // Prepare and execute
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $param_type);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countProducts($search = '', $category = '') {
        // Prepare base query
        $query = "SELECT COUNT(*) as total FROM products WHERE 1=1";
        $params = [];

        // Add search filter
        if (!empty($search)) {
            $query .= " AND (name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // Add category filter
        if (!empty($category)) {
            $query .= " AND category = :category";
            $params[':category'] = $category;
        }

        // Prepare and execute
        $stmt = $this->pdo->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['total'];
    }

    public function deleteProduct($product_id) {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();

            // Delete product
            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
            $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
            $stmt->execute();

            // Commit transaction
            $this->pdo->commit();
        } catch (Exception $e) {
            // Rollback in case of error
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateProduct($product_id, $data, $main_image, $colorways = []) {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();
    
            // Prepare update statement
            $stmt = $this->pdo->prepare(
                "UPDATE products 
                 SET name = :name, price = :price, stock = :stock, 
                     category = :category, description = :description, main_image = :main_image
                 WHERE id = :id"
            );
    
            // Bind parameters
            $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindValue(':price', $data['price'], PDO::PARAM_STR);
            $stmt->bindValue(':stock', $data['stock'], PDO::PARAM_INT);
            $stmt->bindValue(':category', $data['category'], PDO::PARAM_STR);
            $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindValue(':main_image', $main_image, PDO::PARAM_STR);
            $stmt->bindValue(':id', $product_id, PDO::PARAM_INT);
    
            // Execute statement
            $stmt->execute();
    
            // Delete existing colorways
            $delete_stmt = $this->pdo->prepare("DELETE FROM product_colorways WHERE product_id = :product_id");
            $delete_stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
            $delete_stmt->execute();
    
            // Reinsert colorways
            $this->saveColorways($product_id, $colorways);
    
            // Commit transaction
            $this->pdo->commit();
    
            return true;
        } catch (Exception $e) {
            // Rollback in case of error
            $this->pdo->rollBack();
           
            // Log the full error for debugging
            error_log("Product Update Error: " . $e->getMessage());
           
            throw $e;
        }
    }
}