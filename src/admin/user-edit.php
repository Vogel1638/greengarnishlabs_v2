<?php
// Admin-Login-Überprüfung
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist und ob er ein Admin ist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Datenbankverbindung
require_once '../includes/db.php';

// Benutzer-ID aus der URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Wenn keine ID angegeben wurde, zur Benutzerliste zurückkehren
if (!$user_id) {
    header('Location: users.php');
    exit;
}

// Benutzerdaten abrufen
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Wenn Benutzer nicht gefunden wurde, zur Benutzerliste zurückkehren
if (!$user) {
    header('Location: users.php');
    exit;
}

// Formular wurde gesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Überprüfen, ob der Benutzername bereits existiert (außer bei dem aktuellen Benutzer)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        $error = "Der Benutzername existiert bereits.";
    } else {
        try {
            // Profilbild-Upload verarbeiten
            $profile_img = $user['profile_img']; // Behalte das bestehende Bild bei
            if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $file_type = $_FILES['profile_img']['type'];
                $max_size = 5 * 1024 * 1024; // 5MB in Bytes
                
                if ($_FILES['profile_img']['size'] > $max_size) {
                    $error = "Das Profilbild darf nicht größer als 5MB sein.";
                } else if (in_array($file_type, $allowed_types)) {
                    $upload_dir = '../../public/images/profiles/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $upload_path)) {
                        // Lösche das alte Bild, falls vorhanden
                        if ($user['profile_img'] && file_exists($upload_dir . $user['profile_img'])) {
                            unlink($upload_dir . $user['profile_img']);
                        }
                        $profile_img = $new_filename; // Speichere nur den Dateinamen
                    }
                } else {
                    $error = "Nur Bilder im Format JPG, PNG oder GIF sind erlaubt.";
                }
            }
            
            // Passwort aktualisieren, wenn ein neues eingegeben wurde
            $password_update = "";
            $params = [$username, $email, $role, $status, $profile_img, $user_id];
            
            if (!empty($_POST['password'])) {
                $password_update = ", password = ?";
                $params = [$username, $email, $role, $status, $profile_img, password_hash($_POST['password'], PASSWORD_DEFAULT), $user_id];
            }
            
            // Benutzer aktualisieren
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, status = ?, profile_img = ?" . $password_update . " WHERE id = ?");
            $stmt->execute($params);
            
            // Erfolgsmeldung
            $_SESSION['success_message'] = "Benutzer wurde erfolgreich aktualisiert.";
            header('Location: users.php');
            exit;
        } catch (PDOException $e) {
            $error = "Fehler beim Aktualisieren des Benutzers.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzer bearbeiten - GreenGarnishLabs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../public/css/admin.css">

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Hamburger Menu Button -->
            <button class="hamburger-menu">
                <i class="bi bi-list"></i>
            </button>

            <!-- Overlay -->
            <div class="sidebar-overlay"></div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-content">
                    <h3 class="text-white text-center mb-4">Admin Panel</h3>
                    <nav>
                        <a href="index.php">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                        <a href="recipes.php">
                            <i class="bi bi-book me-2"></i> Rezepte
                        </a>
                        <a href="tips.php">
                            <i class="bi bi-lightbulb me-2"></i> Tipps
                        </a>
                        <a href="users.php" class="active">
                            <i class="bi bi-people me-2"></i> Benutzer
                        </a>
                    </nav>
                    <div class="mt-auto">
                        <a href="../auth/logout.php" class="btn btn-danger w-100">
                            <i class="bi bi-box-arrow-right me-2"></i> Abmelden
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hauptinhalt -->
            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Benutzer bearbeiten</h2>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Zurück zur Liste
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?php echo !empty($user['profile_img']) ? '../../public/images/profiles/' . basename($user['profile_img']) : '../../public/images/user-solid.svg'; ?>"
                                     alt="Profilbild" 
                                     class="profile-image">
                                <label class="btn btn-primary profile-image-upload">
                                    <i class="bi bi-camera me-2"></i>Profilbild ändern
                                    <input type="file" name="profile_img" accept="image/*" max="5242880">
                                </label>
                                <small class="text-muted">Maximale Dateigröße: 5MB. Erlaubte Formate: JPG, PNG, GIF</small>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Benutzername</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-Mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Neues Passwort</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="6">
                                <div class="form-text">Lassen Sie das Feld leer, um das aktuelle Passwort zu behalten.</div>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Rolle</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Benutzer</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="status" name="status" 
                                           <?php echo $user['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Aktiv</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Änderungen speichern
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Vorschau des Profilbilds
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-image').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Hamburger Menu Toggle
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        hamburgerMenu.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Schließe Sidebar bei Klick auf einen Link
        document.querySelectorAll('.sidebar nav a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    toggleSidebar();
                }
            });
        });
    </script>
</body>
</html> 