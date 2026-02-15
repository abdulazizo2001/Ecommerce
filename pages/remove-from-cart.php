<?php
/**
 * REMOVE-FROM-CART.PHP
 * Supprime un article du panier
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté.";
    redirect('/ecommerce/pages/login.php');
}

$cart_id = (int)($_GET['cart_id'] ?? $_POST['cart_id'] ?? 0);

if ($cart_id <= 0) {
    $_SESSION['error_message'] = "Article non trouvé.";
    redirect('/ecommerce/pages/cart.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/pages/cart.php');
}

try {
    // Vérifier que cet article appartient bien à l'utilisateur et le supprimer
    
    $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND id_user = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Article retiré du panier.";
    } else {
        $_SESSION['error_message'] = "Article non trouvé dans votre panier.";
    }
    
} catch (PDOException $e) {
    error_log("Erreur suppression panier: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la suppression.";
}

redirect('/ecommerce/pages/cart.php');