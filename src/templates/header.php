
<?php
// Define base URL for all assets and links
define("BASE_URL", "/greengarnishlabs/");

// Fetch user profile image if logged in
if (isset($_SESSION['user_id'])) {
    // Query user's profile image from database
    $stmt = $pdo->prepare("SELECT profile_img FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Set profile image path with fallback to default image
$profileImage = !empty($user['profile_img']) ? BASE_URL . "uploads/profile_pics/" . $user['profile_img'] : BASE_URL . "public/images/user-solid.svg";
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenGarnishLabs</title>

  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/general.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/header.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/home.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/recipes.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/recipes_detail.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/tips.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/tips_detail.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/dse.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/profile.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/footer.css">

  <!-- JavaScript Files -->
  <script src="<?php echo BASE_URL; ?>public/js/script.js" defer></script>
  <script src="<?php echo BASE_URL; ?>public/js/account.js" defer></script>

</head>
<body>
  <header>
    <div class="desktop-header">
      <!-- Company Logo -->
      <a href="<?php echo BASE_URL; ?>public/" class="logo">GreenGarnishLabs</a>
      
      <!-- Main Navigation Menu -->
      <nav class="desktop-navbar">
        <menu>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>public/" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>src/recipes/" class="nav-link">Rezepte</a></li>
          <li class="nav-item"><a href="<?php echo BASE_URL; ?>src/tips/" class="nav-link">Tipps & Tricks</a></li>
        </menu>
      </nav>

      <!-- User Authentication Section -->
      <?php if (isset($_SESSION['user_id'])) { ?>
        <!-- Logged In User Profile Section -->
        <div class="profile">
          <div class="profile-img" style="overlay: hidden;">
            <img src="<?php echo $profileImage; ?>" alt="Profilbild">
          </div>
          <!-- Profile Dropdown Menu -->
          <nav class="profile-menu" style="display: none;">
            <menu>
              <li class="profile-menu-item"><a href="<?php echo BASE_URL; ?>src/user/profile.php">Mein Profil</a></li>
              <li class="profile-menu-item"><a href="<?php echo BASE_URL; ?>src/user/profile.php">Meine Favoriten</a></li>
              <li class="profile-menu-item"><a href="<?php echo BASE_URL; ?>src/auth/logout.php">Ausloggen</a></li>
            </menu>
          </nav>
        </div>
      <?php } else { ?>
        <!-- Guest User Authentication Buttons -->
        <div class="login">
          <a href="<?php echo BASE_URL; ?>src/auth/login.php" class="cta-btn">Einloggen</a>
          <a href="<?php echo BASE_URL; ?>src/auth/register.php" class="cta-btn">Registrieren</a>
        </div>
      <?php } ?>
    
  </header>

