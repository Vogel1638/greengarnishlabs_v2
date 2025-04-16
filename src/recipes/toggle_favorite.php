<?php
// Prevent any output before JSON response
ob_start();

session_start();
require '../includes/db.php';
require '../includes/favorites.php';

// Clear the output buffer
ob_clean();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Bitte melden Sie sich an']);
    exit;
}

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id']) || !isset($data['recipe_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage']);
    exit;
}

$user_id = $data['user_id'];
$recipe_id = $data['recipe_id'];
$action = $data['action'];

// Validate user ID
if ($user_id != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Benutzer-ID']);
    exit;
}

// Execute the corresponding action
if ($action === 'add') {
    $result = addToFavorites($user_id, $recipe_id);
} else if ($action === 'remove') {
    $result = removeFromFavorites($user_id, $recipe_id);
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Aktion']);
    exit;
}

// Send response back
header('Content-Type: application/json');
echo json_encode($result); 