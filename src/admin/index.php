<?php
// Admin login check
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Database connection
require_once '../includes/db.php';

// Retrieve statistics
$stats = [
    'recipes' => [
        'published' => 0,
        'drafts' => 0
    ],
    'tips' => [
        'published' => 0,
        'drafts' => 0
    ],
    'users' => 0
];

// Count recipes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM recipes WHERE status = 1");
$stats['recipes']['published'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM recipes WHERE status = 0");
$stats['recipes']['drafts'] = $stmt->fetch()['total'];

// Count tips
$stmt = $pdo->query("SELECT COUNT(*) as total FROM tips WHERE status = 1");
$stats['tips']['published'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM tips WHERE status = 0");
$stats['tips']['drafts'] = $stmt->fetch()['total'];

// Count users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GreenGarnishLabs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../public/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Overlay -->
            <div class="sidebar-overlay"></div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-content">
                    <h3 class="text-white text-center mb-4">Admin Panel</h3>
                    <nav>
                        <a href="index.php" class="active">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                        <a href="recipes.php">
                            <i class="bi bi-book me-2"></i> Rezepte
                        </a>
                        <a href="tips.php">
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
                    <button class="hamburger-menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <h2 class="mb-0">Dashboard</h2>
                    <div></div> <!-- Platzhalter für die rechte Seite -->
                </div>
                
                <!-- Statistiken -->
                <div class="row g-4 mb-4">
                    <!-- Rezepte -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Rezepte</h5>
                                <h2><?php echo $stats['recipes']['published']; ?></h2>
                                <p class="mb-0">Online</p>
                                <small><?php echo $stats['recipes']['drafts']; ?> Entwürfe</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tipps -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Tipps</h5>
                                <h2><?php echo $stats['tips']['published']; ?></h2>
                                <p class="mb-0">Online</p>
                                <small><?php echo $stats['tips']['drafts']; ?> Entwürfe</small>
                            </div>
                        </div>
                    </div>

                    <!-- Benutzer -->
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Benutzer</h5>
                                <h2><?php echo $stats['users']; ?></h2>
                                <p class="mb-0">Registriert</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weitere Dashboard-Inhalte können hier hinzugefügt werden -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
