
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
