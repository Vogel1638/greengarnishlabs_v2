<?php
/**
 * Admin Tips Management
 * 
 * This file handles the administration of cooking tips in the GreenGarnishLabs platform.
 * It provides functionality for viewing, searching, sorting, and managing cooking tips.
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

// Get total number of tips for pagination
$total_tips = $pdo->query("SELECT COUNT(*) FROM tips")->fetchColumn();
$total_pages = ceil($total_tips / $items_per_page);

// Base SQL query with search and sort conditions
$sql = "SELECT * FROM tips";
$params = [];

// Add search condition if search term exists
if (!empty($search)) {
    $sql .= " WHERE title LIKE ?";
    $params[] = "%$search%";
}

// Add sorting with validation
$allowed_sort_columns = ['title', 'created_at', 'views', 'status'];
if (in_array($sort_by, $allowed_sort_columns)) {
    $sql .= " ORDER BY $sort_by $sort_order";
} else {
    $sql .= " ORDER BY created_at DESC";
}

// Execute query and fetch results
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tips = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipps verwalten - GreenGarnishLabs</title>
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
                    <button class="hamburger-menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <h2 class="mb-0">Tipps verwalten</h2>
                    <a href="tip-edit.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                    </a>
                </div>

                <!-- Such- und Sortierfunktionen -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Tipp suchen..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="sort_by">
                                    <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>Titel</option>
                                    <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Erstellungsdatum</option>
                                    <option value="views" <?php echo $sort_by === 'views' ? 'selected' : ''; ?>>Aufrufe</option>
                                    <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
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

                <!-- Tippsliste -->
                <div class="tip-list">
                    <?php foreach ($tips as $tip): ?>
                        <div class="tip-item" data-tip-id="<?php echo $tip['id']; ?>">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($tip['title']); ?></h5>
                                    <small class="text-muted">
                                        ID: <?php echo $tip['id']; ?> | 
                                        Erstellt am: <?php echo date('d.m.Y H:i', strtotime($tip['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <span class="status-badge <?php echo $tip['status'] == 1 ? 'status-published' : 'status-draft'; ?>">
                                        <?php echo $tip['status'] == 1 ? 'Veröffentlicht' : 'Entwurf'; ?>
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">
                                        <i class="bi bi-eye me-1"></i> <?php echo $tip['views'] ?? 0; ?> Aufrufe
                                    </small>
                                </div>
                                <div class="col-md-3 text-end action-buttons">
                                    <a href="../tips/view.php?id=<?php echo $tip['id']; ?>" target="_blank" title="Vorschau">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="tip-edit.php?id=<?php echo $tip['id']; ?>" title="Bearbeiten">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="#" onclick="deleteTip(<?php echo $tip['id']; ?>)" title="Löschen" class="text-danger">
                                        <i class="bi bi-trash"></i>
                                    </a>
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

    <!-- Bestätigungs-Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Löschen bestätigen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
                </div>
                <div class="modal-body">
                    Sind Sie sicher, dass Sie dieses Element wirklich löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Löschen</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let deleteModal;
        let itemToDelete = null;

        document.addEventListener('DOMContentLoaded', function() {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            
            document.getElementById('confirmDelete').addEventListener('click', function() {
                if (itemToDelete) {
                    const {id, type} = itemToDelete;
                    fetch('delete-item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}&type=${type}`
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Netzwerk-Antwort war nicht ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const element = document.querySelector(`[data-tip-id="${id}"]`);
                            if (element) {
                                element.remove();
                            }
                            window.location.reload();
                        } else {
                            throw new Error(data.message || 'Unbekannter Fehler beim Löschen');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        window.location.reload();
                    })
                    .finally(() => {
                        deleteModal.hide();
                        itemToDelete = null;
                    });
                }
            });
        });

        function deleteTip(id) {
            itemToDelete = {id, type: 'tip'};
            deleteModal.show();
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
