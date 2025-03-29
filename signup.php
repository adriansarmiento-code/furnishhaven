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
        :root {
            --primary-color: #4B5945;
            --secondary-color: #66785F;
            --accent-color: #A3B899;
            --text-color: #333;
            --light-color: #f8f9fa;
            --error-color: #e74c3c;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--primary-color);
            background-image: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 20px;
            perspective: 1000px;
        }

        .login-card {
            width: 100%;
            padding: 40px;
            background: white;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border-radius: 16px;
            text-align: center;
            transform-style: preserve-3d;
            transition: transform 0.6s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            display: block;
            border-radius: 50%;
            object-fit: contain;
            background-color: white;
            padding: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .logo:hover {
            transform: scale(1.05) rotate(5deg);
        }

        h2 {
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
        }

        h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            margin: 10px auto 0;
            border-radius: 3px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 14px 20px;
            margin: 8px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s;
            background-color: var(--light-color);
        }

        input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(163, 184, 153, 0.2);
        }

        input::placeholder {
            color: #aaa;
        }

        button {
            width: 100%;
            padding: 14px;
            margin: 20px 0 15px;
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        button:hover {
            background: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(75, 89, 69, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #aaa;
            font-size: 14px;
        }

        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider::before {
            margin-right: 10px;
        }

        .divider::after {
            margin-left: 10px;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 14px;
            color: #666;
        }

        .footer-text a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .footer-text a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .error-message {
            color: var(--error-color);
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 500;
            padding: 10px;
            background-color: rgba(231, 76, 60, 0.1);
            border-radius: 5px;
            display: none;
        }

        .error-message.show {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <img src="img/logo2.png" alt="Company Logo" class="logo">

            <h2>Create a Account</h2>

        
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
            <div class="divider">or</div>
            <p class="footer-text">
            Already have an account? <a href="login.php">Login</a>
        </p>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>