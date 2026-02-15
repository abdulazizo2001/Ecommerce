<?php
/**
 * CART-ACTIONS.PHP
 * Gère les actions du panier (ajout, modification, suppression)
 * Supporte AJAX et requêtes normales
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {


    // Si requête AJAX

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Vous devez être connecté'
        ]);
        exit();
    }
    
    // Sinon redirection normale

    $_SESSION['error_message'] = "Vous devez être connecté pour ajouter au panier.";
    redirect('/ecommerce/pages/login.php');
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$pdo = getDBConnection();

if (!$pdo) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de connexion à la base de données'
        ]);
        exit();
    }
    
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/pages/articles.php');
}


// ACTION: AJOUTER AU PANIER

if ($action === 'add') {
    $id_article = (int)($_POST['id_article'] ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 1);
    
    if ($id_article <= 0 || $quantite <= 0) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Données invalides'
            ]);
            exit();
        }
        
        $_SESSION['error_message'] = "Données invalides.";
        redirect('/ecommerce/pages/articles.php');
    }
    
    try {

        // Vérifier si le produit existe déjà dans le panier

        $stmt = $pdo->prepare("SELECT id, quantite FROM panier WHERE id_user = ? AND id_item = ?");
        $stmt->execute([$user_id, $id_article]);
        $existing = $stmt->fetch();
        
        if ($existing) {

            // Mettre à jour la quantité existante

            $new_quantite = $existing['quantite'] + $quantite;
            $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
            $stmt->execute([$new_quantite, $existing['id']]);
        } else {
            // Insérer un nouvel article

            $stmt = $pdo->prepare("INSERT INTO panier (id_user, id_item, quantite) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $id_article, $quantite]);
        }
        
        // Compter le nombre total d'articles dans le panier

        $stmt = $pdo->prepare("SELECT SUM(quantite) as total FROM panier WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        $cart_count = $result['total'] ?? 0;
        
        //  SI REQUÊTE AJAX: Répondre en JSON

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Article ajouté au panier',
                'cart_count' => $cart_count
            ]);
            exit();
        }
        
        // SINON: Redirection normale

        $_SESSION['success_message'] = "Article ajouté au panier avec succès !";
        $redirect_url = $_SERVER['HTTP_REFERER'] ?? '/ecommerce/pages/articles.php';
        redirect($redirect_url);
        
    } catch (PDOException $e) {
        error_log("Erreur ajout panier: " . $e->getMessage());
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout au panier'
            ]);
            exit();
        }
        
        $_SESSION['error_message'] = "Erreur lors de l'ajout au panier.";
        redirect('/ecommerce/pages/articles.php');
    }
}


// ACTION: METTRE À JOUR LA QUANTITÉ

if ($action === 'update') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);
    $quantite = (int)($_POST['quantite'] ?? 1);
    
    if ($cart_id <= 0 || $quantite <= 0) {
        $_SESSION['error_message'] = "Données invalides.";
        redirect('/ecommerce/pages/cart.php');
    }
    
    try {
        // Vérifier que cet article appartient bien à l'utilisateur

        $stmt = $pdo->prepare("SELECT id FROM panier WHERE id = ? AND id_user = ?");
        $stmt->execute([$cart_id, $user_id]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE panier SET quantite = ? WHERE id = ?");
            $stmt->execute([$quantite, $cart_id]);
            $_SESSION['success_message'] = "Quantité mise à jour !";
        } else {
            $_SESSION['error_message'] = "Article non trouvé.";
        }
        
    } catch (PDOException $e) {
        error_log("Erreur mise à jour panier: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la mise à jour.";
    }
    
    redirect('/ecommerce/pages/cart.php');
}


// ACTION: SUPPRIMER DU PANIER

if ($action === 'remove') {
    $cart_id = (int)($_GET['cart_id'] ?? $_POST['cart_id'] ?? 0);
    
    if ($cart_id <= 0) {
        $_SESSION['error_message'] = "Article non trouvé.";
        redirect('/ecommerce/pages/cart.php');
    }
    
    try {
        // Vérifier que cet article appartient bien à l'utilisateur
        $stmt = $pdo->prepare("DELETE FROM panier WHERE id = ? AND id_user = ?");
        $stmt->execute([$cart_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = "Article retiré du panier.";
        } else {
            $_SESSION['error_message'] = "Article non trouvé.";
        }
        
    } catch (PDOException $e) {
        error_log("Erreur suppression panier: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la suppression.";
    }
    
    redirect('/ecommerce/pages/cart.php');
}


// ACTION: VIDER LE PANIER

if ($action === 'clear') {
    try {
        $stmt = $pdo->prepare("DELETE FROM panier WHERE id_user = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success_message'] = "Panier vidé avec succès.";
        
    } catch (PDOException $e) {
        error_log("Erreur vidage panier: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors du vidage du panier.";
    }
    
    redirect('/ecommerce/pages/cart.php');
}

// Si aucune action reconnue, rediriger

redirect('/ecommerce/pages/cart.php');