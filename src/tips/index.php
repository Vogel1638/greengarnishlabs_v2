<!--/**
 * Tips and Tricks Overview Page
 * 
 * This file displays a list of all cooking tips and tricks with:
 * - Search functionality
 * - Pagination support
 * - Responsive card layout
 * 
 * @package GreenGarnishLabs
 * @version 1.0.0
 */-->

<?php
  session_start();
  require '../includes/db.php';
  
  // Initialize base query for tips retrieval
  $query = "SELECT * FROM tips WHERE status = 1"; 
  $params = [];

  // Add search functionality if search term is provided
  if (!empty($_GET['search'])) {
      $searchTerm = '%' . $_GET['search'] . '%';
      $query .= " AND (title LIKE ? OR content LIKE ?)";
      $params[] = $searchTerm;
      $params[] = $searchTerm;
  }

  // Execute prepared statement to prevent SQL injection
  $stmt = $pdo->prepare($query);
  $stmt->execute($params);

  // Fetch all matching tips
  $tips = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<?php
include('../templates/header.php');
?>

<div class="tips-body">
  <h1 class="tips-title">Köstliche Rezepte für jeden Geschmack</h1>

  <main>
    <!-- Search Section -->
    <section class="searchbar-container">
      <form method="GET" class="searchbar">
        <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Titel oder Inhalt">
        <button type="submit" class="searchbar-btn"><img src="../../public/images/magnifying-glass-solid.svg" alt="Search"></button>
      </form>
    </section>

    <!-- Tips Display Section -->
    <section class="tip">
        <?php if (empty($tips)): ?>
          <!-- Display message when no tips are available -->
          <p class="error">Oops, leider haben wir noch keine Tipps & Trickszu diesem Thema!</p>
        <?php else: ?>
        <div class="tip-cards">
          <?php foreach ($tips as $tip): ?>
            <article class="tip-card">
              <!-- Display tip image with fallback -->
              <img src="<?php echo BASE_URL; ?>public/images/<?php echo $tip['image']; ?>.png" alt="<?php echo $tip['title']; ?>">
              
              <!-- Tip title -->
              <h3><?php echo $tip['title']; ?> </h3>
                          
              <!-- Truncated content preview (first 150 characters) -->
              <p><?php echo substr($tip['content'], 0, 150); ?>...</p>
              
              <!-- Read more button linking to full tip view -->
              <button class="btn" onclick="window.location.href='<?php echo BASE_URL; ?>src/tips/view.php?id=<?php echo $tip['id']; ?>'">Weiterlese</button>
              </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </main>
  
<?php
include('../templates/footer.php');
?>
