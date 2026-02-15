<?php

//Supprimer un produit - Interface admin

require_once __DIR__ . '/../config/database.php';

// Vérifier que l'utilisateur est admin

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID du produit

$product_id = $_GET['id'] ?? 0;

if ($product_id > 0) {
    $pdo = getDBConnection();
    if ($pdo) {
        try {

            // Supprimer le produit (le stock sera supprimé automatiquement grâce à ON DELETE CASCADE)
            
            $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
            
            if ($stmt->execute([$product_id])) {
                $_SESSION['success_message'] = "Produit supprimé avec succès.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la suppression du produit.";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du produit: " . $e->getMessage());
            $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression.";
        }
    }
} else {
    $_SESSION['error_message'] = "ID de produit invalide.";
}

redirect('/ecommerce/admin/products.php');
?>