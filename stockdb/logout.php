<?php
session_start();  // Start the session

// Destroy the session to log out the user
session_unset();  // Remove all session variables
session_destroy();  // Destroy the session

// Redirect to login page
echo "<script>alert('You have been logged out.'); window.location.href='login.php';</script>";
exit();
?>
