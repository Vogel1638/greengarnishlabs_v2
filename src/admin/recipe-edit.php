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

$recipe = [
    'id' => null,
    'title' => '',
    'subtitle' => '',
    'prep_time' => '',
    'difficulty' => 'easy',
    'category' => '',
    'ingredients' => '',
    'steps' => '',
    'serving_tip' => '',
    'image' => '',
    'is_vegan' => 0,
    'status' => 0
];

// Wenn eine ID übergeben wurde, lade das bestehende Rezept
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $existing_recipe = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing_recipe) {
        $recipe = $existing_recipe;
        // Zutaten und Schritte aus JSON laden
        $ingredients = json_decode($recipe['ingredients'], true) ?? [];
        $steps = json_decode($recipe['steps'], true) ?? [];
    }
} else {
    $ingredients = [];
    $steps = [];
}

// Formular wurde gesendet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validierung und Sanitization der Eingaben
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']);
    $prep_time = (int)$_POST['prep_time'];
    $difficulty = trim($_POST['difficulty']);
    $category = trim($_POST['category']);
    $is_vegan = isset($_POST['is_vegan']) ? 1 : 0;
    $status = isset($_POST['status']) ? 1 : 0;

    // Zutaten verarbeiten
    $ingredients = [];
    if (isset($_POST['ingredient_amount']) && isset($_POST['ingredient_unit']) && isset($_POST['ingredient_name'])) {
        for ($i = 0; $i < count($_POST['ingredient_amount']); $i++) {
            if (!empty($_POST['ingredient_amount'][$i]) && !empty($_POST['ingredient_name'][$i])) {
                $ingredients[] = [
                    'amount' => trim($_POST['ingredient_amount'][$i]),
                    'unit' => trim($_POST['ingredient_unit'][$i]),
                    'name' => trim($_POST['ingredient_name'][$i])
                ];
            }
        }
    }
    $ingredients_json = json_encode($ingredients);

    // Zubereitungsschritte verarbeiten
    $steps = [];
    if (isset($_POST['step_title']) && isset($_POST['step_content'])) {
        for ($i = 0; $i < count($_POST['step_title']); $i++) {
            if (!empty($_POST['step_title'][$i]) && !empty($_POST['step_content'][$i])) {
                $steps[] = [
                    'title' => trim($_POST['step_title'][$i]),
                    'content' => trim($_POST['step_content'][$i])
                ];
            }
        }
    }
    $steps_json = json_encode($steps);

    // Bild-Upload verarbeiten
    $image = $recipe['image']; // Behalte das bestehende Bild bei
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $max_size = 5 * 1024 * 1024; // 5MB in Bytes
        
        if ($_FILES['image']['size'] > $max_size) {
            $error = "Das Bild darf nicht größer als 5MB sein.";
        } else if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../../public/images/recipes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Lösche das alte Bild, falls vorhanden
                if ($recipe['image'] && file_exists($upload_dir . $recipe['image'])) {
                    unlink($upload_dir . $recipe['image']);
                }
                $image = $new_filename; // Speichere nur den Dateinamen
            }
        } else {
            $error = "Nur Bilder im Format JPG, PNG oder GIF sind erlaubt.";
        }
    }

    try {
        if ($recipe['id']) {
            // Update bestehendes Rezept
            $stmt = $pdo->prepare("UPDATE recipes SET 
                title = ?, 
                subtitle = ?, 
                prep_time = ?, 
                difficulty = ?, 
                category = ?,
                ingredients = ?, 
                steps = ?,
                image = ?,
                is_vegan = ?,
                status = ?
                WHERE id = ?");
            
            $stmt->execute([
                $title,
                $subtitle,
                $prep_time,
                $difficulty,
                $category,
                $ingredients_json,
                $steps_json,
                $image,
                $is_vegan,
                $status,
                $recipe['id']
            ]);
        } else {
            // Neues Rezept erstellen
            $stmt = $pdo->prepare("INSERT INTO recipes 
                (title, subtitle, prep_time, difficulty, category, ingredients, steps, image, is_vegan, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->execute([
                $title,
                $subtitle,
                $prep_time,
                $difficulty,
                $category,
                $ingredients_json,
                $steps_json,
                $image,
                $is_vegan,
                $status
            ]);
        }

        // Weiterleitung zur Rezeptliste
        header('Location: recipes.php');
        exit;
    } catch (PDOException $e) {
        $error = "Fehler beim Speichern des Rezepts: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $recipe['id'] ? 'Rezept bearbeiten' : 'Neues Rezept'; ?> - GreenGarnishLabs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../public/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar position-fixed h-100">
                <h3 class="text-white text-center mb-4">Admin Panel</h3>
                <nav>
                    <a href="index.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a href="recipes.php" class="active">
                        <i class="bi bi-book me-2"></i> Rezepte
                    </a>
                    <a href="tips.php">
                        <i class="bi bi-lightbulb me-2"></i> Tipps
                    </a>
                    <a href="users.php">
                        <i class="bi bi-people me-2"></i> Benutzer
                    </a>
                </nav>
                <div class="position-absolute bottom-0 w-10 p-3">
                    <a href="../auth/logout.php" class="btn btn-danger w-100">
                        <i class="bi bi-box-arrow-right me-2"></i> Abmelden
                    </a>
                </div>
            </div>

            <!-- Hauptinhalt -->
            <div class="col-md-9 col-lg-10 p-4 offset-md-3 offset-lg-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><?php echo $recipe['id'] ? 'Rezept bearbeiten' : 'Neues Rezept'; ?></h2>
                    <a href="recipes.php" class="btn btn-secondary">
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
                                       value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="subtitle" class="form-label">Untertitel</label>
                                <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                       value="<?php echo htmlspecialchars($recipe['subtitle']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="prep_time" class="form-label">Zubereitungszeit (Minuten)</label>
                                        <input type="number" class="form-control" id="prep_time" name="prep_time" 
                                               value="<?php echo htmlspecialchars($recipe['prep_time']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="difficulty" class="form-label">Schwierigkeitsgrad</label>
                                        <select class="form-select" id="difficulty" name="difficulty" required>
                                            <option value="easy" <?php echo $recipe['difficulty'] === 'easy' ? 'selected' : ''; ?>>Einfach</option>
                                            <option value="medium" <?php echo $recipe['difficulty'] === 'medium' ? 'selected' : ''; ?>>Mittel</option>
                                            <option value="hard" <?php echo $recipe['difficulty'] === 'hard' ? 'selected' : ''; ?>>Schwer</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label">Kategorie</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Bitte wählen...</option>
                                    <option value="Vorspeise" <?php echo $recipe['category'] === 'Vorspeise' ? 'selected' : ''; ?>>Vorspeise</option>
                                    <option value="Hauptgericht" <?php echo $recipe['category'] === 'Hauptgericht' ? 'selected' : ''; ?>>Hauptgericht</option>
                                    <option value="Dessert" <?php echo $recipe['category'] === 'Dessert' ? 'selected' : ''; ?>>Dessert</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Zutaten</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="ingredients-table">
                                        <thead>
                                            <tr>
                                                <th>Menge</th>
                                                <th>Maßeinheit</th>
                                                <th>Zutat</th>
                                                <th>Aktion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ingredients as $index => $ingredient): ?>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="ingredient_amount[]" 
                                                           value="<?php echo htmlspecialchars($ingredient['amount']); ?>" 
                                                           required>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="ingredient_unit[]" required>
                                                        <option value="">Bitte wählen...</option>
                                                        <option value="g" <?php echo $ingredient['unit'] === 'g' ? 'selected' : ''; ?>>g</option>
                                                        <option value="kg" <?php echo $ingredient['unit'] === 'kg' ? 'selected' : ''; ?>>kg</option>
                                                        <option value="ml" <?php echo $ingredient['unit'] === 'ml' ? 'selected' : ''; ?>>ml</option>
                                                        <option value="l" <?php echo $ingredient['unit'] === 'l' ? 'selected' : ''; ?>>l</option>
                                                        <option value="Stk" <?php echo $ingredient['unit'] === 'Stk' ? 'selected' : ''; ?>>Stk</option>
                                                        <option value="EL" <?php echo $ingredient['unit'] === 'EL' ? 'selected' : ''; ?>>EL</option>
                                                        <option value="TL" <?php echo $ingredient['unit'] === 'TL' ? 'selected' : ''; ?>>TL</option>
                                                        <option value="Prise" <?php echo $ingredient['unit'] === 'Prise' ? 'selected' : ''; ?>>Prise</option>
                                                        <option value="Bund" <?php echo $ingredient['unit'] === 'Bund' ? 'selected' : ''; ?>>Bund</option>
                                                        <option value="Dose" <?php echo $ingredient['unit'] === 'Dose' ? 'selected' : ''; ?>>Dose</option>
                                                        <option value="Glas" <?php echo $ingredient['unit'] === 'Glas' ? 'selected' : ''; ?>>Glas</option>
                                                        <option value="Packung" <?php echo $ingredient['unit'] === 'Packung' ? 'selected' : ''; ?>>Packung</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="ingredient_name[]" 
                                                           value="<?php echo htmlspecialchars($ingredient['name']); ?>" 
                                                           required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-ingredient">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="add-ingredient">
                                    <i class="bi bi-plus-lg me-1"></i>Zutat hinzufügen
                                </button>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Zubereitungsschritte</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="steps-table">
                                        <thead>
                                            <tr>
                                                <th>Titel</th>
                                                <th>Inhalt</th>
                                                <th>Aktion</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($steps as $index => $step): ?>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control" 
                                                           name="step_title[]" 
                                                           value="<?php echo htmlspecialchars($step['title']); ?>" 
                                                           required>
                                                </td>
                                                <td>
                                                    <textarea class="form-control" 
                                                              name="step_content[]" 
                                                              rows="3" 
                                                              required><?php echo htmlspecialchars($step['content']); ?></textarea>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-step">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="add-step">
                                    <i class="bi bi-plus-lg me-1"></i>Schritt hinzufügen
                                </button>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="image" class="form-label">Bild</label>
                                <?php if ($recipe['image']): ?>
                                    <div class="mb-2">
                                        <img src="/greengarnishlabs/public/images/<?php echo htmlspecialchars($recipe['image']); ?>.png" 
                                             alt="Aktuelles Rezeptbild" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" max="5242880">
                                <small class="text-muted">Maximale Dateigröße: 5MB. Erlaubte Formate: JPG, PNG, GIF</small>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_vegan" name="is_vegan" 
                                           <?php echo $recipe['is_vegan'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_vegan">Veganes Rezept</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="status" name="status" 
                                           <?php echo $recipe['status'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Veröffentlicht</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Rezept speichern
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

        // Zutaten-Tabelle verwalten
        document.addEventListener('DOMContentLoaded', function() {
            const ingredientsTable = document.getElementById('ingredients-table');
            const addIngredientButton = document.getElementById('add-ingredient');

            // Neue Zutat hinzufügen
            addIngredientButton.addEventListener('click', function() {
                const tbody = ingredientsTable.querySelector('tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control" name="ingredient_amount[]" required>
                    </td>
                    <td>
                        <select class="form-select" name="ingredient_unit[]" required>
                            <option value="">Bitte wählen...</option>
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="ml">ml</option>
                            <option value="l">l</option>
                            <option value="Stk">Stk</option>
                            <option value="EL">EL</option>
                            <option value="TL">TL</option>
                            <option value="Prise">Prise</option>
                            <option value="Bund">Bund</option>
                            <option value="Dose">Dose</option>
                            <option value="Glas">Glas</option>
                            <option value="Packung">Packung</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="ingredient_name[]" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-ingredient">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(newRow);
            });

            // Zutat entfernen
            ingredientsTable.addEventListener('click', function(e) {
                if (e.target.closest('.remove-ingredient')) {
                    e.target.closest('tr').remove();
                }
            });

            // Schritte-Tabelle verwalten
            const stepsTable = document.getElementById('steps-table');
            const addStepButton = document.getElementById('add-step');

            // Neuer Schritt hinzufügen
            addStepButton.addEventListener('click', function() {
                const tbody = stepsTable.querySelector('tbody');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
                    <td>
                        <input type="text" class="form-control" name="step_title[]" required>
                    </td>
                    <td>
                        <textarea class="form-control" name="step_content[]" rows="3" required></textarea>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-step">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(newRow);
            });

            // Schritt entfernen
            stepsTable.addEventListener('click', function(e) {
                if (e.target.closest('.remove-step')) {
                    e.target.closest('tr').remove();
                }
            });
        });
    </script>
</body>
</html> 