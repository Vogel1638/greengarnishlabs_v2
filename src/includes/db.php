<!--/**
 * Database Connection Handler
 * 
 * This file establishes the database connection using PDO with:
 * - UTF-8 character encoding
 * - Error mode set to exception
 * - Secure connection parameters
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */-->

<?php
// Database connection parameters
$host = "localhost"; 
$dbname = "greengarnishlabs";
$username = "web"; 
$password = "Gg987412365.00";

try {
  // Create PDO instance with error handling
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Verbindung fehlgeschlagen: " . $e->getMessage());
}
?>
