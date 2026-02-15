<?php
/**
 * SUPPRESSION D'UNE ENTRÉE DE STOCK
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est admin

if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID du produit

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    $_SESSION['error_message'] = "Produit non trouvé.";
    redirect('/ecommerce/admin/stock.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/admin/stock.php');
}

try {
    // Supprimer l'entrée stock
    
    $stmt = $pdo->prepare("DELETE FROM stock WHERE id_item = ?");
    $stmt->execute([$product_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Entrée de stock supprimée avec succès.";
    } else {
        $_SESSION['info_message'] = "Aucune entrée de stock trouvée pour ce produit.";
    }
    
} catch (PDOException $e) {
    error_log("Erreur suppression stock: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la suppression du stock.";
}

redirect('/ecommerce/admin/stock.php');