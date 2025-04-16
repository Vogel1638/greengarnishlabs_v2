<?php
session_start();
require '../includes/db.php';

// Check if ID was passed via URL
if (!isset($_GET['id'])) {
    echo "Tipp or trick not found!";
    exit;
}

$tip_id = $_GET['id'];

// Retrieve recipe data
$stmt = $pdo->prepare("SELECT * FROM tips WHERE id = ?");
$stmt->execute([$tip_id]);
$tip = $stmt->fetch(PDO::FETCH_ASSOC);

// If no recipe was found
if (!$tip) {
    echo "Tipp or trick not found!";
    exit;
}

// Increase view count
$stmt = $pdo->prepare("UPDATE tips SET views = views + 1 WHERE id = ?");
$stmt->execute([$tip_id]);
?>

<?php include('../templates/header.php'); ?>

<div class="tip-detail-container">
    <a href="javascript:history.back()" class="back-btn"><-- Back</a>
    <div class="tip-detail">
        <!-- Rezeptbild und Titel -->
         <div class="tip-img" style="background: url('<?php echo BASE_URL; ?>public/images/tips/<?php echo $tip['image']; ?>.png'); background-position: center; background-repeat: no-repeat; background-size: contain;"></div>
        <h1><?php echo $tip['title']; ?></h1>

        <!-- Rezeptdetails -->
        <?php echo $tip['content']; ?>
    </div>
</div>


<?php include('../templates/footer.php'); ?>
