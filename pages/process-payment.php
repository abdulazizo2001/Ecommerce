<?php
require_once __DIR__ . '/../config/database.php';


// Sécurité : Vérifier que l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {
    header('Location: /ecommerce/pages/login.php');
    exit;
}


// Sécurité : Accepter uniquement les requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /ecommerce/pages/cart.php');
    exit;
}

$payment_method = $_POST['payment_method'] ?? 'CB';
$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    header('Location: /ecommerce/pages/payment.php');
    exit;
}

try {

    
// DÉBUT DE LA TRANSACTION : Tout passe ou tout échoue
    $pdo->beginTransaction();
    
    // 1. RÉCUPÉRER LE CONTENU DU PANIER
    
    $stmt = $pdo->prepare("
        SELECT c.id_item, c.quantite, i.prix, i.nom
        FROM panier c
        INNER JOIN items i ON c.id_item = i.id
        WHERE c.id_user = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();
    
    if (empty($cart_items)) {
        throw new Exception("Votre panier est vide.");
    }
    
    // 2. CALCULS FINAUX

    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['prix'] * $item['quantite'];
    }
    $frais_livraison = $total >= 50 ? 0 : 5;
    $total_final = $total + $frais_livraison;
    
    // 3. RÉCUPÉRER LES INFOS DE LIVRAISON (Stockées en session lors de l'étape précédente)

    if (!isset($_SESSION['delivery_info'])) {
        throw new Exception("Informations de livraison manquantes.");
    }
    
    $delivery = $_SESSION['delivery_info'];
    $adresse_livraison = ($delivery['adresse'] ?? '') . ', ' . ($delivery['code_postal'] ?? '') . ' ' . ($delivery['ville'] ?? '');
    $telephone = $delivery['telephone'] ?? '';

    // 4. CRÉER LES LIGNES DE COMMANDE ET MAJ STOCK

    foreach ($cart_items as $item) {
        $montant_item = $item['prix'] * $item['quantite'];
        
        // Insertion dans la table orders

        $stmt = $pdo->prepare("
            INSERT INTO orders (
                id_user, id_item, quantite, montant_total, 
                statut, adresse_livraison, telephone, 
                methode_paiement, date_commande
            ) VALUES (?, ?, ?, ?, 'payee', ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $_SESSION['user_id'], $item['id_item'], $item['quantite'],
            $montant_item, $adresse_livraison, $telephone, $payment_method
        ]);
        
        // Mise à jour du stock physique

        $stmt_stock = $pdo->prepare("
            UPDATE stock SET quantite_stock = quantite_stock - ? 
            WHERE id_item = ?
        ");
        $stmt_stock->execute([$item['quantite'], $item['id_item']]);
    }
    
    
    // 5. GÉNÉRER LA FACTURE (Invoice)
    $stmt = $pdo->prepare("
        INSERT INTO invoice (
            id_user, date_transaction, montant_total, 
            adresse_facturation, ville, code_postal, statut
        ) VALUES (?, NOW(), ?, ?, ?, ?, 'payee')
    ");
    
    $stmt->execute([
        $_SESSION['user_id'], $total_final, 
        $delivery['adresse'], $delivery['ville'], $delivery['code_postal']
    ]);
    
    $invoice_id = $pdo->lastInsertId();

    // C'est ici que le badge repassera à 0

    $stmt = $pdo->prepare("DELETE FROM panier WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
   
    // VALIDATION FINALE DES CHANGEMENTS

    $pdo->commit();
    
    // 7. NETTOYAGE DE LA SESSION

    unset($_SESSION['delivery_info']);
    
    // 8. REDIRECTION VERS LA CONFIRMATION

    $_SESSION['invoice_id'] = $invoice_id;
    $_SESSION['success_message'] = "Paiement effectué avec succès ! Votre panier a été vidé.";
    header('Location: /ecommerce/pages/order-confirmation.php');
    exit;
    
} catch (Exception $e) {

    // En cas d'erreur, on annule tout (le panier reste plein et le stock n'est pas touché)
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erreur Paiement: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur : " . $e->getMessage();
    header('Location: /ecommerce/pages/payment.php');
    exit;
}