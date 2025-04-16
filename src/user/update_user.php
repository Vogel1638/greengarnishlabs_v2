<!--/**
 * User Update Handler
 * Handles the update of user profile information
 */-->

<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Nicht autorisiert"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['field']) || !isset($data['value'])) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültige Anfrage"]);
    exit;
}

$field = $data['field'];
$value = trim($data['value']);

// Security measures
$allowedFields = ['username', 'email', 'password'];
if (!in_array($field, $allowedFields)) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültiges Feld"]);
    exit;
}

try {
    if ($field === 'password') {
        $hashedPassword = password_hash($value, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET $field = ? WHERE id = ?");
        $stmt->execute([$value, $user_id]);
    }

    echo json_encode(["success" => true, "message" => ucfirst($field) . " wurde aktualisiert"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Datenbankfehler: " . $e->getMessage()]);
}
?>
