<!--/**
 * Main Landing Page
 * 
 * This file serves as the main entry point for the website, featuring:
 * - Category navigation
 * - Latest tips display
 * - Responsive design elements
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */-->

<?php
// Initialize session and database connection
session_start();
require '../src/includes/db.php';

// Fetch latest recipes for homepage display
$stmt = $pdo->prepare("
    SELECT recipes.id, recipes.title, recipes.subtitle, recipes.image, recipes.prep_time, recipes.is_vegan, recipes.category, recipes.difficulty
    FROM recipes
    ORDER BY recipes.created_at DESC
    LIMIT 4
");
$stmt->execute();
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest tips for homepage display
$tipsstmt = $pdo->prepare("
    SELECT tips.id, tips.title, tips.image, tips.content
    FROM tips
    ORDER BY tips.created_at DESC
    LIMIT 3
");
$tipsstmt->execute();
$tips = $tipsstmt->fetchALL(PDO::FETCH_ASSOC);
?>

<?php
// Include header template
include('../src/templates/header.php');
?>

<!-- Hero Section with Main Call-to-Action -->
<section class="hero">
  <div class="hero-content">
    <h1 id="hero-title" class="title">Vegane Vielfalt<br><span class="subtitle">Entdecken & Geniessen</span></h1>
    <a href="#" id="hero-btn" class="cta-btn">Rezepte durchstöbern</a>
  </div>
</section>

<section class="newest-recipe">
  <div class="section-title">
    <h2 id="newest-recipes-title">Unsere neusten Kreationen</h2>
  </div>
  <div class="newest-recipes-cards">
    <?php if (empty($recipes)): ?>
      <p>Oops, leider haben wir noch keine Rezepte!</p>
    <?php else: ?>
      <?php foreach ($recipes as $recipe): ?>
        <article class="recipe-card">
          <!-- Display Image -->
          <img src="images/recipes/<?php echo $recipe['image']; ?>" alt="<?php echo $recipe['title']; ?>">
          
          <!-- Title and vegan Symbol -->
          <h3><?php echo $recipe['title']; ?> 
            <?php if($recipe['is_vegan']) { ?>
              <img src="images/vegan-symbol.svg">
            <?php } ?>
          </h3>
          
          <!-- Subtitle -->
          <p class="extra"><?php echo $recipe['subtitle']; ?></p>
          
          <!-- Prep_time -->
          <p>Zubereitungszeit: <?php echo $recipe['prep_time']; ?> Minuten</p>
          
          <!-- Tags -->
          <div class="tags">
            <a href="<?php echo BASE_URL; ?>src/recipes/index.php?category=<?php echo urlencode($recipe['category']); ?>" class="tag"><?php echo $recipe['category']; ?></a>
            <a href="<?php echo BASE_URL; ?>src/recipes/index.php?difficulty=<?php echo urlencode($recipe['difficulty']); ?>" class="tag"><?php echo $recipe['difficulty']; ?></a>
          </div>
          
          <!-- Button for the detail page -->
          <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/recipes/view.php?id=<?php echo $recipe['id']; ?>'">Zum Rezept</button>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Recipe Categories Section -->
<section class="categories">
  <div class="section-title">
    <h2 id="categorie-title">Köstliche Vielfalt für jeden Gang</h2>
  </div>
  <div class="categorie-cards">
    <!-- Appetizer Category Card -->
    <article class="categorie-card">
      <h3 class="categorie-title">Vorspeise</h3>
      <img src="images/vorspeise.png" alt="Appetizer Category">
      <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/recipes/index.php?category=Vorspeise'">Vorspeisen anzeigen</button>
    </article>
    
    <!-- Main Course Category Card -->
    <article class="categorie-card">
      <h3 class="categorie-title">Hauptgerichte</h3>
      <img src="images/hauptgericht.png" alt="Main Course Category">
      <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/recipes/index.php?category=Hauptgericht'">Hauptgerichte anzeigen</button>
    </article>
    
    <!-- Dessert Category Card -->
    <article class="categorie-card">
      <h3 class="categorie-title">Dessert</h3>
      <img src="images/dessert.png" alt="Dessert Category">
      <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/recipes/index.php?category=Dessert'">Dessert anzeigen</button>
    </article>
  </div>
</section>

<!-- Latest Tips Section -->
<section class="tips">
  <div class="section-title">
    <h2>Neueste Tipps & Tricks</h2>
  </div>
  <div class="tip-cards">
    <?php foreach ($tips as $tip): ?>
      <article class="tip-card">
        <img src="<?php echo BASE_URL; ?>public/images/tips/<?php echo $tip['image']; ?>" alt="<?php echo $tip['title']; ?>">
        <h3><?php echo $tip['title']; ?></h3>
        <p><?php echo substr($tip['content'], 0, 150); ?>...</p>
        <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/tips/view.php?id=<?php echo $tip['id']; ?>'">Weiterlesen</button>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php
// Include footer template
include('../src/templates/footer.php');
?>
