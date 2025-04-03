<?php
/**
 * Admin Users Management
 * 
 * This file handles the administration of users in the GreenGarnishLabs platform.
 * It provides functionality for viewing, searching, sorting, and managing user accounts.
 * 
 * @package GreenGarnishLabs
 * @subpackage Admin
 * @version 1.0.0
 */

// Start session and check admin authentication
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Database connection
require_once '../includes/db.php';

// Search and sort parameters with default values
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Pagination configuration
$items_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of users for pagination
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_pages = ceil($total_users / $items_per_page);

// Base SQL query with search and sort conditions
$sql = "SELECT * FROM users";
$params = [];

// Add search condition if search term exists
if (!empty($search)) {
    $sql .= " WHERE username LIKE ? OR email LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add sorting with validation
$allowed_sort_columns = ['username', 'email', 'created_at', 'role'];
if (in_array($sort_by, $allowed_sort_columns)) {
    $sql .= " ORDER BY $sort_by $sort_order";
} else {
    $sql .= " ORDER BY created_at DESC";
}

// Execute query and fetch results
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Status-Änderung verarbeiten
if (isset($_POST['toggle_status']) && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    
    // Prüfen ob es sich um ein Admin-Konto handelt
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userRole = $stmt->fetch()['role'];
    
    if ($userRole !== 'admin') {
        $stmt = $pdo->prepare("UPDATE users SET status = NOT status WHERE id = ?");
        $stmt->execute([$userId]);
    }
    header('Location: users.php');
    exit;
}

// Rolle ändern
if (isset($_POST['change_role']) && isset($_POST['user_id']) && isset($_POST['new_role']) && isset($_POST['confirm_role_change'])) {
    $userId = $_POST['user_id'];
    $newRole = $_POST['new_role'];
    
    // Prüfen ob es sich um das eigene Konto handelt
    if ($userId != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $userId]);
    }
    header('Location: users.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzer verwalten - GreenGarnishLabs</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- Custom Admin CSS -->
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
                    <button class="hamburger-menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <h2 class="mb-0">Benutzer verwalten</h2>
                    <a href="user-create.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </div>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['success_message']);
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['error_message']);
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Such- und Sortierfunktionen -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Benutzer suchen..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="sort_by">
                                    <option value="username" <?php echo $sort_by === 'username' ? 'selected' : ''; ?>>Benutzername</option>
                                    <option value="email" <?php echo $sort_by === 'email' ? 'selected' : ''; ?>>E-Mail</option>
                                    <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Erstellungsdatum</option>
                                    <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                                    <option value="role" <?php echo $sort_by === 'role' ? 'selected' : ''; ?>>Rolle</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="sort_order">
                                    <option value="ASC" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Aufsteigend</option>
                                    <option value="DESC" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Absteigend</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Anwenden</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Benutzerliste -->
                <div class="user-list">
                    <?php foreach ($users as $user): ?>
                        <div class="user-item">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h5>
                                    <small class="text-muted">
                                        ID: <?php echo $user['id']; ?> | 
                                        E-Mail: <?php echo htmlspecialchars($user['email']); ?>
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <span class="status-badge <?php echo $user['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $user['status'] == 1 ? 'Aktiv' : 'Inaktiv'; ?>
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <span class="role-badge <?php echo $user['role'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar me-1"></i> Registriert am: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Aktionen
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <?php if ($user['role'] !== 'admin'): ?>
                                            <li>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="toggle_status" value="1">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bi bi-power me-2"></i>
                                                        <?php echo $user['status'] == 1 ? 'Deaktivieren' : 'Aktivieren'; ?>
                                                    </button>
                                                </form>
                                            </li>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="confirmRoleChange(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">
                                                    <i class="bi bi-person-badge me-2"></i>
                                                    Rolle zu <?php echo $user['role'] == 'admin' ? 'Benutzer' : 'Admin'; ?> ändern
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                          
                                            <li><a class="dropdown-item" href="user-edit.php?id=<?php echo $user['id']; ?>">
                                                <i class="bi bi-pencil me-2"></i>Bearbeiten
                                            </a></li>
                                            
                                            <?php if ($user['role'] !== 'admin'): ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="confirmDelete(<?php echo $user['id']; ?>)">
                                                    <i class="bi bi-trash me-2"></i>Löschen
                                                </a>
                                            </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Seitennavigation">
                    <ul class="pagination">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Vorherige">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Nächste">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal für Rollenwechsel -->
    <div class="modal fade" id="roleChangeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rollenwechsel bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Möchten Sie die Rolle dieses Benutzers wirklich ändern?</p>
                    <p class="text-danger">Diese Aktion kann erhebliche Auswirkungen auf die Berechtigungen des Benutzers haben.</p>
                </div>
                <div class="modal-footer">
                    <form method="post" id="roleChangeForm">
                        <input type="hidden" name="user_id" id="roleChangeUserId">
                        <input type="hidden" name="new_role" id="roleChangeNewRole">
                        <input type="hidden" name="change_role" value="1">
                        <input type="hidden" name="confirm_role_change" value="1">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-primary">Bestätigen</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm('Möchten Sie diesen Benutzer wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden!')) {
                window.location.href = 'user-delete.php?id=' + id;
            }
        }

        function confirmRoleChange(userId, currentRole) {
            const newRole = currentRole === 'admin' ? 'user' : 'admin';
            document.getElementById('roleChangeUserId').value = userId;
            document.getElementById('roleChangeNewRole').value = newRole;
            new bootstrap.Modal(document.getElementById('roleChangeModal')).show();
        }

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
