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
        // Check admin credentials
        if ($username === 'furnishhaven' && $password === 'dwebfinals') {
            $_SESSION['admin'] = true;
            $_SESSION['username'] = 'Administrator';
            header("Location: admin_products.php");
            exit();
        } else {
            $error_message = "Invalid admin credentials";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
            text-align: center;
        }

        .error-message.show {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .shake {
            animation: shake 0.4s ease-in-out;
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
    <div class="login-card <?php echo !empty($error_message) ? 'shake' : ''; ?>">
        <!-- Logo -->
        <img src="img/logo2.png" alt="Company Logo" class="logo">
        
        <h2>Admin Login</h2>
   
        <!-- Error message -->
        <div id="errorDisplay" class="error-message <?php echo !empty($error_message) ? 'show' : ''; ?>">
            <?php echo !empty($error_message) ? htmlspecialchars($error_message) : ''; ?>
        </div>
       
       <form method="POST" action="">
        <div class="input-group">
            <input type="text" name="username" placeholder="Admin Username" required 
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        
        <div class="input-group">
            <input type="password" name="password" placeholder="Admin Password" required>
        </div>                
        <button type="submit">Sign In</button>
        
        <div class="divider">or</div>
        
        <p class="footer-text">
            <a href="login.php">Back to User Login</a>
        </p>
    </form>
    </div>
</div>

<script>
    // Clear error when user starts typing
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', function() {
            document.getElementById('errorDisplay').classList.remove('show');
            document.querySelector('.login-card').classList.remove('shake');
        });
    });
</script>
</body>
</html>