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

$tip = [
    'id' => null,
    'title' => '',
    'subtitle' => '',
    'content' => '',
    'image' => '',
    'category' => '',
    'status' => 0
];

// Wenn eine ID übergeben wurde, lade den bestehenden Tipp
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM tips WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $existing_tip = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing_tip) {
        $tip = $existing_tip;
    }
}

// Formular wurde gesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validierung und Sanitization der Eingaben
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $status = isset($_POST['status']) ? 1 : 0;

    // Bild-Upload verarbeiten
    $image = $tip['image']; // Behalte das bestehende Bild bei
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $max_size = 5 * 1024 * 1024; // 5MB in Bytes
        
        if ($_FILES['image']['size'] > $max_size) {
            $error = "Das Bild darf nicht größer als 5MB sein.";
        } else if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../../public/images/tips/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Lösche das alte Bild, falls vorhanden
                if ($tip['image'] && file_exists($upload_dir . $tip['image'])) {
                    unlink($upload_dir . $tip['image']);
                }
                $image = $new_filename; // Speichere nur den Dateinamen
            }
        } else {
            $error = "Nur Bilder im Format JPG, PNG oder GIF sind erlaubt.";
        }
    }

    try {
        if ($tip['id']) {
            // Update bestehenden Tipp
            $stmt = $pdo->prepare("UPDATE tips SET 
                title = ?, 
                subtitle = ?, 
                content = ?,
                image = ?,
                category = ?,
                status = ?
                WHERE id = ?");
            
            $stmt->execute([
                $title,
                $subtitle,
                $content,
                $image,
                $category,
                $status,
                $tip['id']
            ]);
        } else {
            // Neuen Tipp erstellen
            $stmt = $pdo->prepare("INSERT INTO tips 
                (title, content, image, status, created_at) 
                VALUES (?, ?, ?, ?, NOW())");
            
            $stmt->execute([
                $title,
                $content,
                $image,
                $status
            ]);
        }

        // Weiterleitung zur Tippliste
        header('Location: tips.php');
        exit;
    } catch (PDOException $e) {
        $error = "Fehler beim Speichern des Tipps: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tip['id'] ? 'Tipp bearbeiten' : 'Neuer Tipp'; ?> - GreenGarnishLabs</title>
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
                        <a href="tips.php" class="active">
                            <i class="bi bi-lightbulb me-2"></i> Tipps
                        </a>
                        <a href="users.php">
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
                    <h2><?php echo $tip['id'] ? 'Tipp bearbeiten' : 'Neuer Tipp'; ?></h2>
                    <a href="tips.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Zurück zur Liste
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titel</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($tip['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label">Inhalt</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($tip['content']); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image" class="form-label">Bild</label>
                                <?php if ($tip['image']): ?>
                                    <div class="mb-2">
                                        <img src="/greengarnishlabs/public/images/<?php echo htmlspecialchars($tip['image']); ?>.png" 
                                             alt="Aktuelles Tippbild" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" max="5242880">
                                <small class="text-muted">Maximale Dateigröße: 5MB. Erlaubte Formate: JPG, PNG, GIF</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="status" name="status" 
                                           <?php echo $tip['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Veröffentlicht</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Tipp speichern
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Formularvalidierung
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

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