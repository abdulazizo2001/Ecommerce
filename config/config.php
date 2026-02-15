<?php

// Démarrer la session si elle n'est pas déjà démarrée

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




// Nom du dossier du projet
define('PROJECT_FOLDER', 'ecommerce');

// URL de base du site 
define('BASE_URL', 'http://localhost/' . PROJECT_FOLDER);

// Chemin absolu du projet sur le serveur
define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . PROJECT_FOLDER);


// CONFIGURATION DE LA BASE DE DONNÉES

define('DB_HOST', 'localhost');
define('DB_NAME', 'fashion_shop');
define('DB_USER', 'root');
define('DB_PASS', ''); // Vide par défaut sur XAMPP


/**
 * Fonction pour obtenir une connexion PDO à la base de données
 * 
 * @return PDO|null Retourne l'objet PDO ou null en cas d'erreur
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        return null;
    }
}

/**
 * Fonction pour échapper les sorties
 * @param string $string La chaîne à échapper
 * @return string La chaîne échappée
 */
function escape($string) {
   return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Fonction pour rediriger vers une autre page
 * @param string $path Le chemin relatif 
 */
function redirect($path) {

    // Si le chemin commence par /, c'est déjà un chemin absolu depuis la racine
    if (strpos($path, '/') === 0) {
        header("Location: " . $path);
    } else {
        // Sinon, ajouter l'URL de base
        header("Location: " . BASE_URL . '/' . $path);
    }
    exit();
}

/**
 * Fonction pour générer une URL complète
 * @param string $path Le chemin 
 * @return string L'URL complète
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Fonction pour générer le chemin vers les assets
 * @param string $path Le chemin de l'asset (ex: 'css/style.css')
 * @return string L'URL complète de l'asset
 */
function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur est admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}


 //Rediriger si l'utilisateur n'est pas connecté
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
        redirect('pages/login.php');
    }
}


 // Rediriger si l'utilisateur n'est pas admin
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error_message'] = "Accès non autorisé.";
        redirect('index.php');
    }
}



// Mode debug (à désactiver en production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

?>