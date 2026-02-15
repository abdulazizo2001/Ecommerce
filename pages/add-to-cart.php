<?php
session_start(); // Ajout indispensable pour gérer le panier

require_once __DIR__ . '/../config/database.php';

// Vérifier que l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour ajouter des articles.";
    header('Location: /ecommerce/pages/login.php');
    exit;
}

$product_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$product_id || $product_id <= 0) {
    $_SESSION['error_message'] = "Produit invalide.";
    header('Location: /ecommerce/pages/articles.php');
    exit;
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    header('Location: /ecommerce/pages/articles.php');
    exit;
}

try {
    // 1. Vérifier l'existence et le stock

    $stmt = $pdo->prepare("
        SELECT i.id, i.nom, s.quantite_stock 
        FROM items i 
        LEFT JOIN stock s ON i.id = s.id_item 
        WHERE i.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error_message'] = "Ce produit n'existe pas.";
    } elseif ($product['quantite_stock'] <= 0) {
        $_SESSION['error_message'] = "Ce produit n'est plus en stock.";
    } else {
        
        // 2. Vérifier si déjà présent dans le panier

        $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE id_user = ? AND id_item = ?");
        $stmt->execute([$_SESSION['user_id'], $product_id]);
        $cart_item = $stmt->fetch();
        
        if ($cart_item) {

            // Mettre à jour (Permet d'ajouter la veste une 2ème, 3ème fois, etc.)

            if ($cart_item['quantite'] < $product['quantite_stock']) {
                $stmt = $pdo->prepare("UPDATE panier SET quantite = quantite + 1 WHERE id = ?");
                $stmt->execute([$cart_item['id']]);
                $_SESSION['success_message'] = "Quantité mise à jour !";
            } else {
                $_SESSION['error_message'] = "Stock maximum atteint.";
            }
        } else {

            // Nouvel ajout (Première fois que le produit entre dans le panier)

            $stmt = $pdo->prepare("INSERT INTO panier (id_user, id_item, quantite) VALUES (?, ?, 1)");
            $stmt->execute([$_SESSION['user_id'], $product_id]);
            $_SESSION['success_message'] = "Article ajouté au panier !";
        }
    }
    
} catch (PDOException $e) {
    error_log("Erreur Panier: " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur technique est survenue.";
}

// Redirection finale vers la page précédente

$referer = $_SERVER['HTTP_REFERER'] ?? '/ecommerce/pages/articles.php';
header('Location: ' . $referer);
exit;
