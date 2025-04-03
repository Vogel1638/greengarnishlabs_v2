<?php
// Admin-Login-Überprüfung
session_start();

// Überprüfen, ob der Benutzer eingeloggt ist und ob er ein Admin ist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Datenbankverbindung
require_once '../includes/db.php';

// Benutzer-ID aus der URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Wenn keine ID angegeben wurde, zur Benutzerliste zurückkehren
if (!$user_id) {
    header('Location: users.php');
    exit;
}

// Prüfen, ob der zu löschende Benutzer existiert und kein Admin ist
$stmt = $pdo->prepare("SELECT role, profile_img FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = "Benutzer nicht gefunden.";
    header('Location: users.php');
    exit;
}

if ($user['role'] === 'admin') {
    $_SESSION['error_message'] = "Administratoren können nicht gelöscht werden.";
    header('Location: users.php');
    exit;
}

if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error_message'] = "Sie können sich nicht selbst löschen.";
    header('Location: users.php');
    exit;
}

try {
    // Profilbild löschen, falls vorhanden
    if ($user['profile_img'] && file_exists('../' . $user['profile_img'])) {
        unlink('../' . $user['profile_img']);
    }
    
    // Benutzer löschen
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    
    $_SESSION['success_message'] = "Benutzer wurde erfolgreich gelöscht.";
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Fehler beim Löschen des Benutzers.";
}

header('Location: users.php');
exit; 