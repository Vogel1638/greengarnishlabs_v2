<?php
  session_start();
  require '../includes/db.php';
  
  $query = "SELECT * FROM recipes WHERE status = 1"; 
  $params = [];
  
  if (!empty($_GET['category'])) {
      $query .= " AND category LIKE ?";
      $params[] = '%' . $_GET['category'] . '%';
  }
  
  if (!empty($_GET['difficulty'])) {
      $query .= " AND difficulty LIKE ?";
      $params[] = '%' . $_GET['difficulty'] . '%';
  }
  
  if (!empty($_GET['time'])) {
      $query .= " AND total_time <= ?";
      $params[] = (int)$_GET['time'];
  }
  
  if (isset($_GET['is_vegan']) && $_GET['is_vegan'] !== '') {
    $query .= " AND is_vegan = ?";
    $params[] = (int)$_GET['is_vegan'];
}
  
  $stmt = $pdo->prepare($query);
  $stmt->execute($params);
  $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);  
?>

<?php
include('../templates/header.php');
?>

<div class="recipe-body">
  <h1 class="recipe-title">Köstliche Rezepte für jeden Geschmack</h1>

  <section class="easy-filter">
    <form method="GET" id="filter-form">
      <div class="filter-group">
        <label for="category">Kategorie</label>
        <select name="category">
          <option value=""> Alle</option>
          <option value="Vorspeise" <?php if (isset($_GET['category']) && $_GET['category'] == 'Vorspeise') echo 'selected'; ?>> Vorspeise</option>
          <option value="Hauptgericht" <?php if (isset($_GET['category']) && $_GET['category'] == 'Hauptgericht') echo 'selected'; ?>> Hauptgericht</option>
          <option value="Dessert"<?php if (isset($_GET['category']) && $_GET['category'] == 'Dessert') echo 'selected'; ?>> Dessert</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="difficulty">Schwierigkeit</label>
        <select name="difficulty">
          <option value="">Alle</option>
          <option value="Einfach" <?php if (isset($_GET['difficulty']) && $_GET['difficulty'] == 'Einfach') echo 'selected'; ?>>Einfach</option>
          <option value="Mittel" <?php if (isset($_GET['difficulty']) && $_GET['difficulty'] == 'Mittel') echo 'selected'; ?>>Mittel</option>
          <option value="Schwer" <?php if (isset($_GET['difficulty']) && $_GET['difficulty'] == 'Schwer') echo 'selected'; ?>>Schwer</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="time">Max. Zeit: <span id="time-output"><?php echo isset($_GET['time']) ? $_GET['time'] : 120; ?></span>Min</label>
        <input type="range" id="time" name="time" min="0" max="120" value="<?php echo isset($_GET['time']) ? $_GET['time'] : 120; ?>" oninput="updateTime(this.value)">
      </div>    

      <div class="filter-group">
        <label for="is_vegan">Vegan/Vegetarisch</label>
        <select name="is_vegan">
          <option value="">Beides</option>
          <option value="1" <?php if (isset($_GET['is_vegan']) && $_GET['is_vegan'] == '1') echo 'selected'; ?>>Vegan</option>
          <option value="0" <?php if (isset($_GET['is_vegan']) && $_GET['is_vegan'] == '0') echo 'selected'; ?>>Vegetarisch</option>
        </select>
      </div>


      <button type="submit" class="cta-btn">Filtern</button>
    </form>
  </section>

  <section class="recipe">
      <?php if (empty($recipes)): ?>
        <p class="error">Oops, leider haben wir noch keine Rezepte, die deinen Kriterien entsprechen!</p>
      <?php else: ?>
      <div class="recipes-cards">
        <?php foreach ($recipes as $recipe): ?>
          <article class="recipe-card">
            <!-- Display Image -->
            <img src="<?php echo BASE_URL; ?>public/images/<?php echo $recipe['image']; ?>.png" alt="<?php echo $recipe['title']; ?>">
            
            <!-- Title and vegan Symbol -->
            <h3><?php echo $recipe['title']; ?> 
              <?php if($recipe['is_vegan']) { ?>
                <img src="<?php echo BASE_URL; ?>public/images/vegan-symbol.svg">
              <?php } ?>
            </h3>
            
            <!-- Subtitle -->
            <p class="extra"><?php echo $recipe['subtitle']; ?></p>
            
            <!-- Prep_time -->
            <p>Zubereitungszeit: <?php echo $recipe['prep_time']; ?> Minuten</p>
            
            <!-- Tags -->
            <div class="tags">
              <p class="tag"><?php echo $recipe['category']; ?></p>
              <p class="tag"><?php echo $recipe['difficulty']; ?></p>
            </div>
            
            <!-- Button for the detail page -->
            <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/recipes/view.php?id=<?php echo $recipe['id']; ?>'">Zum Rezept</button>
            </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>




<script>
  function updateTime(value) {
    document.getElementById("time-output").textContent = value;
  }
</script>

<?php
include('../templates/footer.php');
?>
