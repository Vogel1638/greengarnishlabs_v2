<?php
/**
 * Functions for managing user favorites
 */
function addToFavorites($user_id, $recipe_id) {
    global $pdo;
    
    try {
        // Check if recipe is already favorited
        $stmt = $pdo->prepare("SELECT * FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Rezept ist bereits in den Favoriten'];
        }
        
        // Add recipe to favorites
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id, created_at) VALUES (?, ?, CURRENT_TIMESTAMP)");
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

function getUserFavorites($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT r.*, f.created_at as favorited_at 
            FROM recipes r 
            JOIN favorites f ON r.id = f.recipe_id 
            WHERE f.user_id = ? 
            ORDER BY f.created_at DESC");
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
} 