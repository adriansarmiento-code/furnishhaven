<?php
// Start the session
session_start();

// Unset all session variables
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>