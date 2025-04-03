<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] != 'user') {
    header('Location: ../admin/index.php');
    exit;
}

// Get user data
try {
    // Debug output before query
    error_log("Session User ID: " . $_SESSION['user_id']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        error_log("No user found for ID: " . $_SESSION['user_id']);
        echo "Kein Benutzer gefunden!";
        exit;
    }

    // Check if all required fields exist
    if (!isset($user['username']) || !isset($user['email'])) {
        error_log("Missing fields in user array: " . print_r($user, true));
        echo "Fehler: Unvollständige Benutzerdaten!";
        exit;
    }

    $userJson = json_encode($user);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    echo "Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.";
    exit;
}

// Default profile image if none exists
$profileImage = !empty($user['profile_img']) ? "../uploads/profile_pics/" . $user['profile_img'] : "../public/images/user-solid.svg";

// Get all favorited recipes for the user
try {
    $stmt = $pdo->prepare("SELECT r.* 
                          FROM recipes r
                          INNER JOIN favorites f ON f.recipe_id = r.id 
                          WHERE f.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error loading favorites: " . $e->getMessage());
    $favorites = []; // Show no favorites on error
}

?>

<?php include('../templates/header.php'); ?>

<div class="account-settings-container">
    <h1 class="account-title">Mein Profil</h1>
    <main class="account-settings">
        <div class="account-settings-content">
            <section id="profile-tab">
                <h2 class="account-section-title">Profilbild</h2>
                <div class="profile-pictures-content">
                    <img src="<?php echo $profileImage; ?>" alt="Profilbild" id="profile-img">
                    <form id="profile-pic-form" enctype="multipart/form-data">
                        <input type="file" name="profile_image" id="profile-image-upload" hidden>
                        <button type="button" class="cta-btn" id="change-profile-pic">Profilbild ändern</button>
                        <button type="button" class="btn" id="delete-profile-pic">Profilbild löschen</button>
                    </form>
                </div>

                <hr>

                <div class="personal-infos-content">
                    <h2 class="account-section-title">Persönliche Informationen</h2>
                    <div class="info-row">
                        <label>Benutzername:</label>
                        <span id="username"><?php echo $user['username']; ?></span>
                        <button class="edit-btn btn" data-field="username">Bearbeiten</button>
                    </div>

                    <div class="info-row">
                        <label>Email:</label>
                        <span id="email"><?php echo $user['email']; ?></span>
                        <button class="edit-btn btn" data-field="email">Bearbeiten</button>
                    </div>

                    <div class="info-row">
                        <label>Passwort:</label>
                        <span id="password">********</span>
                        <button class="edit-btn btn" data-field="password">Ändern</button>
                    </div>
                </div>
            </section>

            <hr>

            <!-- TAB: FAVORITES -->
            <section id="favorites-tab" class="tab-content">
                <h2 class="account-section-title">Meine Favoriten</h2>
                <div id="favorites-content">
                    <?php if (empty($favorites)): ?>
                        <p>Du hast noch keine Favoriten hinzugefügt.</p>
                    <?php else: ?>
                        <div class="favorites-list">
                            <?php foreach ($favorites as $favorite): ?>
                            <article class="favorite-card">
                                <!-- Display Image -->
                                <img src="<?php echo BASE_URL; ?>public/images/<?php echo $favorite['image']; ?>.png" alt="<?php echo $favorite['title']; ?>">
                                
                                <!-- Title and vegan Symbol -->
                                <h3><?php echo $favorite['title']; ?></h3>
                                                                
                                <!-- Button for the detail page -->
                                <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/recipes/view.php?id=<?php echo $favorite['id']; ?>'">Zum Rezept</button>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
    // User object passed from PHP
    var user = <?php echo $userJson; ?>;

    // Store user data in SessionStorage
    sessionStorage.setItem('user', JSON.stringify(user));
    sessionStorage.setItem('userId', '<?php echo $_SESSION['user_id']; ?>');
    sessionStorage.setItem('userRole', '<?php echo $_SESSION['role']; ?>');

    // Output user object to console
    console.log('User data:', user);
    console.log('SessionStorage:', {
        user: JSON.parse(sessionStorage.getItem('user')),
        userId: sessionStorage.getItem('userId'),
        userRole: sessionStorage.getItem('userRole')
    });
</script>


<?php include('../templates/footer.php'); ?>
