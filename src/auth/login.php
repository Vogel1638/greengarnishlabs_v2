<?php
session_start();
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usernameOrEmail = $_POST['username_or_email'];
    $password = $_POST['password'];

    // Prüfe zuerst, ob der Benutzer existiert
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = 'Benutzername oder E-Mail ist falsch.';
    } elseif ($user['status'] != 1) {
        $error = 'Ihr Konto ist deaktiviert. Bitte kontaktieren Sie den Administrator.';
    } elseif (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header('Location: ../admin/');
            exit;
        } else {
            header('Location: ../user/profile.php');
            exit;
        }
    } else {
        $error = 'Das eingegebene Passwort ist falsch.';
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
  <link rel="stylesheet" href="../../public/css/login.css">
  <script src="https://kit.fontawsome.com/28c75585b3.js" crossorigin="anonymous"></script>
</head>
<body>
  <div class="login-container">
    <div class="left-side">
      <img src="../../public/images/hero-img.png">
      <h1 class="logo" style="color: var(--primary-background)">GreenGarnishLabs</h1>
    </div>

    <div class="right-side">
      <div class="close-button">
        <a href="../../public">&times;</a>
      </div>
      <div class="login-form">
        <h2 class="login-title">Willkommen zurück</h2>
        <p>Bitte melde dich an, um fortzufahren</p>
        <hr>

        <?php if (isset($error)): ?>
          <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
          <label for="username_or_email">Benutzername oder E-Mail:</label>
          <input type="text" id="username_or_email" name="username_or_email" required>

          <label for="password">Passwort:</label>
          <input type="password" id="password" name="password" required>

          <button type="submit" class="btn">Einloggen</button>
        </form>

        <p>Noch kein Account? <a href="register.php">Jetzt registrieren</a></p>
        <p><a href="password_reset.php" style="font-size: 0.8rem;">Passwort vergessen?</a></p>
      </div>
    </div>
  </div>
</body>
</html>
