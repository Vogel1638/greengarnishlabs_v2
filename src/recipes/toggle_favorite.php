<?php
// Verhindere jegliche Ausgabe vor dem JSON-Response
ob_start();

session_start();
require '../includes/db.php';
require '../includes/favorites.php';

// Lösche den Output-Buffer
ob_clean();

// Prüfe ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bitte melden Sie sich an']);
    exit;
}

// Hole JSON-Daten aus dem Request-Body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['recipe_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$user_id = $data['user_id'];
$recipe_id = $data['recipe_id'];
$action = $data['action'];

// Validiere die Benutzer-ID
if ($user_id != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Benutzer-ID']);
    exit;
}

// Führe die entsprechende Aktion aus
if ($action === 'add') {
    $result = addToFavorites($user_id, $recipe_id);
} else if ($action === 'remove') {
    $result = removeFromFavorites($user_id, $recipe_id);
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);
    exit;
}

// Sende die Antwort zurück
header('Content-Type: application/json');
echo json_encode($result); 