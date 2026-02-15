<?php
/**
 * SUPPRESSION D'UNE COMMANDE
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est admin

if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID de la commande

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    $_SESSION['error_message'] = "Commande non trouvée.";
    redirect('/ecommerce/admin/orders.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/admin/orders.php');
}

try {
    // Supprimer la commande
    
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success_message'] = "Commande #$order_id supprimée avec succès.";
    } else {
        $_SESSION['error_message'] = "Commande non trouvée.";
    }
    
} catch (PDOException $e) {
    error_log("Erreur suppression commande: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la suppression de la commande.";
}

redirect('/ecommerce/admin/orders.php');