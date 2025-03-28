<?php
// Database connection
$host = 'localhost';
$db   = 'furn_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize variables
$username = $email = $password = $confirm_password = '';
$errors = [];
$registration_successful = false;

// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be 3-50 characters long";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 5) {
        $errors[] = "Password must be at least 5 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->execute(['username' => $username, 'email' => $email]);
            
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username or email already exists";
            }
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }

    // If no errors, insert user
    if (empty($errors)) {
        try {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute insert statement
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
            $result = $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => $password_hash
            ]);

            if ($result) {
                $registration_successful = true;
                // Clear inputs after successful registration
                $username = $email = $password = $confirm_password = '';
            }
        } catch(PDOException $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 400px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .error { 
            color: red; 
            background-color: #ffeeee;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .success { 
            color: green; 
            background-color: #eeffee;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        input { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .button { 
            width: 100%; 
            padding: 10px; 
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .button:hover {
            background-color: #4cae4c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        
        <?php
        // Display errors
        if (!empty($errors)) {
            echo '<div class="error">';
            foreach ($errors as $error) {
                echo "<p>$error</p>";
            }
            echo '</div>';
        }

        // Display success message and login button
        if ($registration_successful) {
            echo '<div class="success">Account created successfully!</div>';
            echo '<a href="login.php" class="button">Go to Login</a>';
        }
        ?>

        <?php if (!$registration_successful): ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" 
                   value="<?php echo htmlspecialchars($username); ?>" required>
            
            <input type="email" name="email" placeholder="Email" 
                   value="<?php echo htmlspecialchars($email); ?>" required>
            
            <input type="password" name="password" placeholder="Password" required>
            
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <button type="submit" class="button">Sign Up</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>