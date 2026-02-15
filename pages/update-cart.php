<?php
/**
 * UPDATE-CART.PHP
 * Met à jour la quantité d'un article dans le panier
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté.";
    redirect('/ecommerce/pages/login.php');
}

$cart_id = (int)($_POST['cart_id'] ?? 0);
$quantite = (int)($_POST['quantite'] ?? 1);

if ($cart_id <= 0 || $quantite <= 0) {
    $_SESSION['error_message'] = "Données invalides.";
    redirect('/ecommerce/pages/cart.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/pages/cart.php');
}

try {
    // Vérifier que cet article appartient bien à l'utilisateur

    $stmt = $pdo->prepare("SELECT id FROM panier WHERE id = ? AND id_user = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    if ($stmt->fetch()) {

        // Mettre à jour la quantité
        
        $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
        $stmt->execute([$quantite, $cart_id]);
        $_SESSION['success_message'] = "Quantité mise à jour !";
    } else {
        $_SESSION['error_message'] = "Article non trouvé dans votre panier.";
    }
    
} catch (PDOException $e) {
    error_log("Erreur mise à jour panier: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la mise à jour.";
}

redirect('/ecommerce/pages/cart.php');