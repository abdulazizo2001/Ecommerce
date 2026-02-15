<?php
// Page d'accueil 
require_once __DIR__ . '/config/config.php';

$page_title = 'Accueil';

// Récupérer les 6 derniers produits pour l'affichage
$pdo = getDBConnection();
$featured_products = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT i.*, s.quantite_stock 
            FROM items i 
            LEFT JOIN stock s ON i.id = s.id_item 
            ORDER BY i.date_publication DESC 
            LIMIT 6
        ");
        $featured_products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des produits: " . $e->getMessage());
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="hero-section">
    <h2>Bienvenue chez Fashion Shop</h2>
    <p>Découvrez notre collection de vêtements et accessoires tendance</p>
    <a href="<?php echo url('pages/articles.php'); ?>" class="btn btn-primary">Découvrir nos produits</a>
</div>

<div class="container">
    <section class="home-products-section">
        <h2 class="page-title">Nos dernières nouveautés</h2>
        
        <?php if (count($featured_products) > 0): ?>
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="card">
                        <div class="card-img-container">
                            <?php 

                            // Vérifier si l'image existe

                            $image_file = $product['image'] ?? 'placeholder.jpg';
                            $image_path = __DIR__ . '/assets/images/' . $image_file;
                            $has_valid_image = !empty($image_file) 
                                               && $image_file !== 'placeholder.jpg' 
                                               && file_exists($image_path);
                            ?>
                            
                            <?php if ($has_valid_image): ?>
                                <img src="<?php echo asset('images/' . $image_file); ?>" 
                                     alt="<?php echo escape($product['nom']); ?>" 
                                     class="card-img">
                            <?php else: ?>
                                <div class="card-img-placeholder">
                                    <div class="placeholder-icon">📷</div>
                                    <p class="placeholder-text">Image non disponible</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <h3 class="card-title"><?php echo escape($product['nom']); ?></h3>
                            
                            <p class="card-text">
                                <?php echo escape(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="card-price">
                                <?php echo number_format($product['prix'], 2, ',', ' '); ?> €
                            </div>
                            
                            <!-- Badge de stock -->

                            <?php if ($product['quantite_stock'] > 0): ?>
                                <span class="card-stock disponible">
                                    ✓ En stock
                                </span>
                            <?php else: ?>
                                <span class="card-stock rupture">
                                    ✗ Rupture de stock
                                </span>
                            <?php endif; ?>
                            
                            <!-- Actions -->

                            <div class="card-actions">
                                <a href="<?php echo url('pages/product-detail.php?id=' . $product['id']); ?>" 
                                   class="btn btn-primary">
                                    Voir le produit
                                </a>
                                
                                <?php if ($product['quantite_stock'] > 0): ?>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="<?php echo url('pages/add-to-cart.php?id=' . $product['id']); ?>" 
                                           class="btn btn-success">
                                            🛒
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo url('pages/login.php'); ?>" 
                                           class="btn btn-success" 
                                           title="Connectez-vous pour ajouter au panier">
                                            🛒
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-products">
                <div class="empty-products-icon">📦</div>
                <h3 class="empty-products-title">Aucun produit disponible</h3>
                <p class="empty-products-text">
                    Revenez bientôt pour découvrir nos nouveautés !
                </p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Section avantages -->

    <section class="home-benefits-section">
        <h2 class="benefits-title">Pourquoi choisir Fashion Shop ?</h2>
        
        <div class="benefits-grid">

            <!-- Livraison -->

            <div class="benefit-card">
                <div class="benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/>
                        <circle cx="7" cy="17" r="2"/>
                        <path d="M9 17h6"/>
                        <circle cx="17" cy="17" r="2"/>
                    </svg>
                </div>
                <h3 class="benefit-title">Livraison rapide</h3>
                <p class="benefit-text">Livraison gratuite dès 50€ d'achat</p>
            </div>
            
            <!-- Paiement sécurisé -->

            <div class="benefit-card">
                <div class="benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="benefit-title">Paiement sécurisé</h3>
                <p class="benefit-text">Vos données sont protégées</p>
            </div>
            
            <!-- Retours gratuits -->
             
            <div class="benefit-card">
                <div class="benefit-icon benefit-icon-emoji">↩</div>
                <h3 class="benefit-title">Retours gratuits</h3>
                <p class="benefit-text">30 jours pour changer d'avis</p>
            </div>
        </div>
    </section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
