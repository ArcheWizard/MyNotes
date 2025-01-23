<?php
// Start the session to access session variables
session_start();

// Completely destroy the session
session_unset();     // Remove all session variables
session_destroy();   // Destroy the session

// Redirect to home page with a clear message
$_SESSION['logout_message'] = "You have been logged out successfully.";
header("Location: ../HTML/index.php");
exit;
?>