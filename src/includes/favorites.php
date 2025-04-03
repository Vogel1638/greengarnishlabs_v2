<?php
/**
 * Favorites Management Functions
 */
function addToFavorites($user_id, $recipe_id) {
    global $pdo;
    
    try {
        // Prüfen ob das Rezept bereits favorisiert ist
        $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Rezept ist bereits in den Favoriten'];
        }
        
        // Füge das Rezept zu den Favoriten hinzu
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $recipe_id]);
        
        return ['success' => true, 'message' => 'Rezept wurde zu den Favoriten hinzugefügt'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Fehler beim Hinzufügen zu den Favoriten'];
    }
}

function removeFromFavorites($user_id, $recipe_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        
        return ['success' => true, 'message' => 'Rezept wurde aus den Favoriten entfernt'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Fehler beim Entfernen aus den Favoriten'];
    }
}

function isRecipeFavorited($user_id, $recipe_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
} 