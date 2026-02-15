<?php

require_once __DIR__ . '/../config/database.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour modifier le panier.";
    redirect('/ecommerce/pages/login.php');
}

// Vider le panier
$_SESSION['cart'] = [];
$_SESSION['success_message'] = "Votre panier a été vidé.";

redirect('/ecommerce/pages/panier.php');
?>