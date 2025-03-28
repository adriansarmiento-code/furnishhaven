<?php
session_start();

// Database connection
$host = 'localhost';
$db   = 'furn_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Error message variable
$error_message = '';

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error_message = "Please fill in all fields";
    } else {
        try {
            // Prepare SQL statement to prevent SQL injection
            $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password
            if ($user && password_verify($password, $user['password_hash'])) {
                // Password is correct, start a new session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Update last login timestamp
                $update_stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :user_id");
                $update_stmt->execute(['user_id' => $user['id']]);

                // Redirect to dashboard
                header("Location: home.php");
                exit();
            } else {
                // Invalid username or password
                $error_message = "Invalid username or password";
            }
        } catch(PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .logo {
            display: block;
            max-width: 150px;
            margin: 0 auto 20px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            background-color: #ffeeee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .footer-text {
            text-align: center;
            margin-top: 15px;
        }
        .divider {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            border-top: 1px solid #ddd;
        }
        .divider span {
            background: white;
            padding: 0 10px;
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        <img src="img/logo2.png" alt="Company Logo" class="logo">
       
        <h2 style="text-align: center;">Welcome Back</h2>
       
        <!-- Error message placeholder -->
        <?php
        if (!empty($error_message)) {
            echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>
       
        <!-- Login Form -->
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
           
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
           
            <button type="submit">Sign In</button>
           
            <div class="divider"><span>or</span></div>
           
            <p class="footer-text">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </p>
        </form>
    </div>
</body>
</html>
<?php
// Connection is automatically closed by PDO when script ends
?>