<?php

 //Supprimer un utilisateur - Interface admin

require_once __DIR__ . '/../config/database.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID de l'utilisateur à supprimer

$user_id = $_GET['id'] ?? 0;

// Empêcher la suppression de son propre compte

if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error_message'] = "Vous ne pouvez pas supprimer votre propre compte.";
    redirect('/ecommerce/admin/users.php');
}

if ($user_id > 0) {
    $pdo = getDBConnection();
    if ($pdo) {
        try {

            // Supprimer l'utilisateur (les commandes seront supprimées automatiquement grâce à ON DELETE CASCADE)
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            
            if ($stmt->execute([$user_id])) {
                $_SESSION['success_message'] = "Utilisateur supprimé avec succès.";
            } else {
                $_SESSION['error_message'] = "Erreur lors de la suppression de l'utilisateur.";
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression.";
        }
    }
} else {
    $_SESSION['error_message'] = "ID d'utilisateur invalide.";
}

redirect('/ecommerce/admin/users.php');
?>