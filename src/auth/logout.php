<!--/**
 * User Logout Handler
 * 
 * This file handles the user logout process by:
 * - Starting the session
 * - Clearing all session variables
 * - Destroying the session
 * - Redirecting to login page
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */-->

<?php
// Initialize session
session_start();

// Clear all session variables
session_unset();

// Destroy the session completely
session_destroy();

// Redirect user to login page
header('Location: login.php');
exit; 
?>
