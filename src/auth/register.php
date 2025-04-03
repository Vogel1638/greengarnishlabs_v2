<!--/**
 * User Registration Handler
 * 
 * This file handles the user registration process including:
 * - Form validation
 * - Password security checks
 * - User data storage
 * - Error handling
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */-->

<?php
// Initialize session and database connection
session_start();
require '../includes/db.php';

// Initialize error array for storing validation messages
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and trim user inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validate required fields
    if (empty($username)) {
        $errors[] = 'Benutzername ist erforderlich.';
    }

    if (empty($email)) {
        $errors[] = 'E-Mail-Adresse ist erforderlich.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ungültige E-Mail-Adresse.';
    }

    // Check for existing user
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $errors[] = 'Benutzername oder E-Mail-Adresse ist bereits vergeben.';
        }
    }

    // Validate password requirements
    if (empty($password)) {
        $errors[] = 'Passwort ist erforderlich.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($password !== $password_confirm) {
        $errors[] = 'Passwörter stimmen nicht überein.';
    }

    // If no errors, proceed with user registration
    if (empty($errors)) {
        // Hash password for secure storage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Insert new user into database
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            // Set success message and redirect to login
            $_SESSION['success'] = 'Registrierung erfolgreich! Bitte melden Sie sich an.';
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Registrierung fehlgeschlagen. Bitte versuchen Sie es später erneut.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | GreenGarnishLabs</title>

  <link rel="stylesheet" href="../../public/css/general.css">
  <link rel="stylesheet" href="../../public/css/header.css">
  <link rel="stylesheet" href="../../public/css/home.css">
  <link rel="stylesheet" href="../../public/css/footer.css">
  <link rel="stylesheet" href="../../public/css/register.css">

  <script src="https://kit.fontawsome.com/28c75585b3.js" crossorigin="anonymous"></script>

</head>
<body>
  <div class="register-container">
    <div class="left-side">
      <img src="../../public/images/hero-img.png">
      <h1 class="logo" style="color: var(--primary-background)">GreenGarnishLabs</h1>
    </div>

    <div class="right-side">
      <div class="close-button">
        <a href="../../public">&times;</a>
      </div>
      <div class="register-form">
        <h2 class="register-title">Registriere dich</h2>
        <p>Erstelle ein Konto, um die vollen Funktionen zu nutzen!</p>
        <hr>

        <form method="POST" action="register.php">
          <?php
            if (!empty($errors)) {
                echo "<div class='error-messages'>";
                foreach ($errors as $error) {
                    echo "<p class='error-message'>$error</p>";
                }
                echo "</div>";
            }
          ?>
          <label for="username">Benutzername:</label>
          <input type="text" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>

          <label for="email">E-Mail-Adresse:</label>
          <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>

          <label for="password">Passwort:
            <span class="help-icon">?
              <div class="tooltip-content">
                <p>das Passwort wird anhand der folgenden Kriterien bewertet:</p>
                <ul>
                  <li>Mindestens 8 Zeichem</li>
                  <li>Mindestens ein Grosbuchtaben</li>
                  <li>Mindestens ein Kleinbuchstabe</li>
                  <li>Mindestens eine Zahl</li>
                  <li>Mindestens ein Sonderzeichen</li>
                  <li>Leerzeichen sind nicht erlaubt</li>
                </ul>
              </div>
            </span>
          </label>
          <input type="password" id="password" name="password" required>

          <label for="password_confirm">Passwort bestätigen:</label>
          <input type="password" id="password_confirm" name="password_confirm" required>

          <label>
            <input type="checkbox" name="accept_agbs" required> Ich akzeptiere die <a href="agb.php" target="_blank">AGB</a>
          </label>

          <button type="submit" class="btn">Registrieren</button>
      </form>

        <p>Schon ein Konto? <a href="login.php">Jetzt anmelden</a></p>
      </div>
    </div>
  </div>
  
</body>
</html>
