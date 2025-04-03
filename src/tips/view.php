<?php
session_start();
require '../includes/db.php';

// Überprüfen, ob die ID über die URL übergeben wurde
if (!isset($_GET['id'])) {
    echo "Tipp oder Trick nicht gefunden!";
    exit;
}

$tip_id = $_GET['id'];

// Rezeptdaten abrufen
$stmt = $pdo->prepare("SELECT * FROM tips WHERE id = ?");
$stmt->execute([$tip_id]);
$tip = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn kein Rezept gefunden wurde
if (!$tip) {
    echo "Tipp oder Trick nicht gefunden!";
    exit;
}

// Erhöhe die View-Zählung
$stmt = $pdo->prepare("UPDATE tips SET views = views + 1 WHERE id = ?");
$stmt->execute([$tip_id]);
?>

<?php include('../templates/header.php'); ?>

<div class="tip-detail-container">
    <a href="javascript:history.back()" class="back-btn"><-- Back</a>
    <div class="tip-detail">
        <!-- Rezeptbild und Titel -->
         <div class="tip-img" style="background: url('<?php echo BASE_URL; ?>public/images/<?php echo $tip['image']; ?>.png'); background-position: center; background-repeat: no-repeat; background-size: contain;"></div>
        <h1><?php echo $tip['title']; ?></h1>

        <!-- Rezeptdetails -->
        <?php echo $tip['content']; ?>
    </div>
</div>


<?php include('../templates/footer.php'); ?>
