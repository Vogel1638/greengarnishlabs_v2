<?php
// Header für JSON-Antwort setzen
header('Content-Type: application/json');

// Admin-Login-Überprüfung
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist und ob er ein Admin ist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Nicht autorisiert']);
    exit;
}

// Datenbankverbindung
require_once '../includes/db.php';

// Überprüfen der Parameter
if (!isset($_POST['id']) || !isset($_POST['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fehlende Parameter']);
    exit;
}

$id = (int)$_POST['id'];
$type = $_POST['type'];

// Validierung des Typs
if (!in_array($type, ['recipe', 'tip'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültiger Typ']);
    exit;
}

try {
    // Tabellenname basierend auf dem Typ
    $table = $type === 'recipe' ? 'recipes' : 'tips';
    
    // Lösch-Query vorbereiten und ausführen
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Erfolgreich gelöscht']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Element nicht gefunden']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Datenbankfehler: ' . $e->getMessage()]);
} 