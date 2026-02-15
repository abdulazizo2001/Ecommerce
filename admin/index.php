<?php
/**
 * Tableau de bord d'administration
 * Page principale du back-office
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Administration';

// Vérifier que l'utilisateur est admin

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer les statistiques

$pdo = getDBConnection();
$stats = [
    'total_products' => 0,
    'total_users' => 0,
    'total_orders' => 0,
    'low_stock' => 0
];

if ($pdo) {
    try {

        // Nombre total de produits

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
        $stats['total_products'] = $stmt->fetch()['count'];
        
        // Nombre total d'utilisateurs

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
        $stats['total_users'] = $stmt->fetch()['count'];
        
        // Nombre total de commandes

        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $stmt->fetch()['count'];
        
        // Produits en rupture ou faible stock
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM stock WHERE quantite_stock <= 5");
        $stats['low_stock'] = $stmt->fetch()['count'];
        
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/admin-dashboard.css">

<div class="admin-dashboard-container">
    <h2 class="admin-dashboard-title">Tableau de bord Admin</h2>

    <!-- Cartes de statistiques -->

    <div class="dashboard-stats-grid">
        
        <!-- Carte Produits -->

        <div class="stat-card">
            <h3 class="stat-title">Produits</h3>
            <div class="stat-number"><?php echo $stats['total_products']; ?></div>
            <p class="stat-label">Total des produits</p>
            <a href="/ecommerce/admin/products.php" class="stat-btn">Gérer</a>
        </div>
        
        <!-- Carte Utilisateurs -->

        <div class="stat-card">
            <h3 class="stat-title">Utilisateurs</h3>
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <p class="stat-label">Utilisateurs inscrits</p>
            <a href="/ecommerce/admin/users.php" class="stat-btn">Gérer</a>
        </div>
        
        <!-- Carte Commandes -->

        <div class="stat-card">
            <h3 class="stat-title">Commandes</h3>
            <div class="stat-number"><?php echo $stats['total_orders']; ?></div>
            <p class="stat-label">Total des commandes</p>
            <a href="/ecommerce/admin/orders.php" class="stat-btn">Gérer</a>
        </div>
        
        <!-- Carte Stock faible -->

        <div class="stat-card <?php echo $stats['low_stock'] > 0 ? 'stat-card-warning' : ''; ?>">
            <h3 class="stat-title">Stock faible</h3>
            <div class="stat-number <?php echo $stats['low_stock'] > 0 ? 'stat-number-danger' : ''; ?>">
                <?php echo $stats['low_stock']; ?>
            </div>
            <p class="stat-label">Produits à réapprovisionner</p>
            <a href="/ecommerce/admin/stock.php" class="stat-btn <?php echo $stats['low_stock'] > 0 ? 'stat-btn-danger' : ''; ?>">Gérer</a>
        </div>
        
    </div>

    <!-- Accès rapide -->
     
    <section class="quick-access-section">
        <h3 class="quick-access-title">Accès rapide</h3>
        <div class="quick-access-grid">
            
            <a href="/ecommerce/admin/products.php" class="quick-access-card">
                <div class="quick-access-icon">📦</div>
                <h4 class="quick-access-heading">Gestion des produits</h4>
                <p class="quick-access-text">Ajouter, modifier ou supprimer des produits</p>
            </a>
            
            <a href="/ecommerce/admin/users.php" class="quick-access-card">
                <div class="quick-access-icon">👥</div>
                <h4 class="quick-access-heading">Gestion des utilisateurs</h4>
                <p class="quick-access-text">Visualiser et gérer les comptes utilisateurs</p>
            </a>
            
            <a href="/ecommerce/index.php" class="quick-access-card">
                <div class="quick-access-icon">🏠</div>
                <h4 class="quick-access-heading">Retour au site</h4>
                <p class="quick-access-text">Voir le site comme un utilisateur</p>
            </a>
            
        </div>
    </section>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>