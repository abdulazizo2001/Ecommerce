<?php
// 1. GESTION DE LA SESSION

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. COMPTAGE DES ARTICLES (Badge du panier)

$cart_count = 0;

// On vérifie si l'utilisateur est connecté avant de requêter la base

if (isLoggedIn() && function_exists('getDBConnection')) {
    $pdo = getDBConnection();
    if ($pdo) {
        try {

            // Utilisation de SUM pour additionner toutes les quantités (ex: 3 vestes = badge "3")

            $stmt = $pdo->prepare("SELECT SUM(quantite) as total FROM panier WHERE id_user = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $result = $stmt->fetch();
            
            // Si le panier est vide, SUM renvoie NULL, donc on force le 0
            
            $cart_count = $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erreur comptage panier: " . $e->getMessage());
            $cart_count = 0;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) . ' - Fashion Shop' : 'Fashion Shop - Vêtements & Accessoires'; ?></title>
    
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>
                <a href="<?php echo url('pages/articles.php'); ?>" class="logo-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shirt">
                        <path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/>
                    </svg> 
                    <span>Fashion Shop</span>
                </a>
            </h1>

            <nav>
                <ul>
                    <li class="nav-search-item">
                        <form action="<?php echo url('pages/articles.php'); ?>" method="GET" class="nav-search-form">
                            <input type="text" name="q" placeholder="Rechercher..." class="nav-search-input" aria-label="Rechercher un produit">
                            <button type="submit" class="nav-search-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                            </button>
                        </form>
                    </li>
                    
                    <li><a href="<?php echo url('index.php'); ?>">Accueil</a></li>
                    <li><a href="<?php echo url('pages/articles.php'); ?>">Articles</a></li>
                    <li><a href="<?php echo url('pages/about.php'); ?>">Qui sommes-nous ?</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li>
                            <a href="<?php echo url('pages/cart.php'); ?>" class="cart-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="<?php echo url('pages/logout.php'); ?>">Déconnexion</a></li>
                        
                        <?php if (isAdmin()): ?>
                            <li><a href="<?php echo url('admin/index.php'); ?>" class="btn-admin">Admin</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="<?php echo url('pages/login.php'); ?>">Connexion</a></li>
                        <li><a href="<?php echo url('pages/register.php'); ?>">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="container">
        <?php foreach (['success', 'error', 'info'] as $type): ?>
            <?php if (isset($_SESSION[$type . '_message'])): ?>
                <div class="alert alert-<?php echo $type; ?>">
                    <?php echo escape($_SESSION[$type . '_message']); ?>
                    <?php unset($_SESSION[$type . '_message']); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>