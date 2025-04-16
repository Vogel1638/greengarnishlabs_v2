<?php
  /**
   * Recipe View Page
   * Displays detailed information about a specific recipe
   */

  // Initialize PHP session for user management
  session_start();
  require '../includes/db.php';
  require '../includes/favorites.php';

  // Validate recipe ID from URL parameters
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      echo "Invalid recipe ID!";
      exit;
  }

  // Extract recipe ID from URL
  $recipe_id = $_GET['id'];

  // Fetch recipe data from database
  $stmt = $pdo->prepare("SELECT r.*, 
    GROUP_CONCAT(ri.amount, '|', ri.unit, '|', ri.name) as ingredients_data,
    GROUP_CONCAT(rs.step_number, '|', rs.title, '|', rs.content) as steps_data
    FROM recipes r
    LEFT JOIN recipe_ingredients ri ON r.id = ri.recipe_id
    LEFT JOIN recipe_steps rs ON r.id = rs.recipe_id
    WHERE r.id = ?
    GROUP BY r.id");
  $stmt->execute([$recipe_id]);
  $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if recipe exists
  if (!$recipe) {
      echo "Recipe not found!";
      exit;
  }

  // Increase view count
  $stmt = $pdo->prepare("UPDATE recipes SET views = views + 1 WHERE id = ?");
  $stmt->execute([$recipe_id]);

  // Extract recipe data into variables
  $image = $recipe['image'];
  $title = $recipe['title'];
  $subtitle = $recipe['subtitle'];
  $prep_time = $recipe['prep_time'];
  $difficulty = $recipe['difficulty'];

  // Extrahiere Zutaten und Schritte
  $ingredients = [];
  if (!empty($recipe['ingredients_data'])) {
      foreach (explode(',', $recipe['ingredients_data']) as $ingredient) {
          $parts = explode('|', $ingredient);
          if (count($parts) === 3) {
              $ingredients[] = [
                  'amount' => $parts[0],
                  'unit' => $parts[1],
                  'name' => $parts[2]
              ];
          }
      }
  }

  $steps = [];
  if (!empty($recipe['steps_data'])) {
      foreach (explode(',', $recipe['steps_data']) as $step) {
          $parts = explode('|', $step);
          if (count($parts) === 3) {
              $steps[] = [
                  'title' => $parts[1],
                  'content' => $parts[2]
              ];
          }
      }
  }

  $serving_tip = $recipe['serving_tip'] ?? null; 

  // Check if recipe is favorited by current user
  $is_favorited = false;
  if (isset($_SESSION['user_id'])) {
      $is_favorited = isRecipeFavorited($_SESSION['user_id'], $recipe_id);
  }
?>

<?php include('../templates/header.php'); ?>

<!-- Main container for recipe detail view -->
<div class="recipe-detail-container">
    <!-- Navigation: Back button -->
    <a href="javascript:history.back()" class="back-btn"><-- Back</a>

    <!-- Main recipe content -->
    <div class="recipe-detail-content">
        <!-- Header section with image and basic information -->
        <div class="recipe-header">
            <!-- Recipe image with dynamic path -->
            <div class="recipe-image">
                <img src="<?php echo BASE_URL . 'public/images/' . $image; ?>" alt="<?php echo $title; ?>">
            </div>

            <!-- Information section with title, subtitle and metadata -->
            <div class="recipe-info">
                <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>
                <p class="subtitle"><?php echo htmlspecialchars($recipe['subtitle']); ?></p>

                <!-- Recipe metadata (prep time, difficulty) -->
                <div class="recipe-details">
                    <p><strong>Zubereitungszeit:</strong> <?php echo $prep_time; ?> Minuten</p>
                    <p><strong>Schwierigkeitsgrad:</strong> <?php echo $difficulty; ?></p>
                    
                    <!-- Interactive favorite button -->
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="favorite-btn <?php echo $is_favorited ? 'favorited' : ''; ?>" 
                                onclick="toggleFavorite(<?php echo $_SESSION['user_id']; ?>, <?php echo $recipe_id; ?>)">
                            <?php echo $is_favorited ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen'; ?>
                        </button>
                    <?php else: ?>
                        <button class="cta-btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/auth/login.php'">
                            Anmelden zum Favorisieren
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <hr>
        <div class="recipe-content">
            <!-- Recipe ingredients list -->
            <section class="recipe-ingredients">
                <h2>Zutaten</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Menge</th>
                                <th>Maßeinheit</th>
                                <th>Zutat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ingredients as $ingredient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ingredient['amount']); ?></td>
                                <td><?php echo htmlspecialchars($ingredient['unit']); ?></td>
                                <td><?php echo htmlspecialchars($ingredient['name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <hr>

            <!-- Cooking instructions -->
            <section class="recipe-instructions">
                <h2>Zubereitung</h2>
                <?php foreach ($steps as $index => $step): ?>
                    <div class="step-item mb-4">
                        <h3>Schritt <?php echo $index + 1; ?>: <?php echo htmlspecialchars($step['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($step['content'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- Optional serving tip section -->
            <?php if ($serving_tip) { ?>
                <hr>
                <section class="recipe-serving-tip">
                    <h2>Serviervorschlag</h2>
                    <p><?php echo nl2br($serving_tip); ?></p>
                </section>
            <?php } ?>
        </div>
    </div>
</div>

<script>
function toggleFavorite(userId, recipeId) {
    const button = document.querySelector('.favorite-btn');
    const isFavorited = button.classList.contains('favorited');
    
    fetch('<?php echo BASE_URL; ?>src/recipes/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            recipe_id: recipeId,
            action: isFavorited ? 'remove' : 'add'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerk-Antwort war nicht ok');
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Server-Antwort:', text);
                throw new Error('Ungültige JSON-Antwort vom Server');
            }
        });
    })
    .then(data => {
        if (data.success) {
            button.classList.toggle('favorited');
            button.textContent = isFavorited ? 'Zu Favoriten hinzufügen' : 'Aus Favoriten entfernen';
        } else {
            alert(data.message || 'Ein Fehler ist aufgetreten');
        }
    })
    .catch(error => {
        console.error('Fehler:', error);
        alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
    });
}
</script>

<?php include('../templates/footer.php'); ?>

