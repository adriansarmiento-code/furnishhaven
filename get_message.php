<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'furn_db';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get message ID from request
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if (!$id) {
        header("HTTP/1.1 400 Bad Request");
        exit();
    }
    
    // Fetch the message
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
    
    // Return as JSON
    header('Content-Type: application/json');
    echo json_encode($message);
    
} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    exit();
}
?>