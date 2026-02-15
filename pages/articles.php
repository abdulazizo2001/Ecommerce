<?php

// Affiche tous les produits avec filtres par catégorie ET recherche intégrée

require_once __DIR__ . '/../config/database.php';

$page_title = 'Nos Articles';

// Récupérer le terme de recherche

$search_query = $_GET['q'] ?? '';
$search_query = trim($search_query);

// Récupérer le filtre de catégorie si présent

$categorie_filter = $_GET['categorie'] ?? 'all';

// Récupérer tous les produits ou filtrer par catégorie + recherche

$pdo = getDBConnection();
$products = [];

if ($pdo) {
    try {
        // CAS 1: Tous les produits, sans recherche

        if ($categorie_filter === 'all' && empty($search_query)) {
            $stmt = $pdo->query("
                SELECT i.*, s.quantite_stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                ORDER BY i.date_publication DESC
            ");
        }
        // CAS 2: Filtre de catégorie uniquement, sans recherche

        elseif ($categorie_filter !== 'all' && empty($search_query)) {
            $stmt = $pdo->prepare("
                SELECT i.*, s.quantite_stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE i.categorie = ?
                ORDER BY i.date_publication DESC
            ");
            $stmt->execute([$categorie_filter]);
        }
        // CAS 3: Recherche dans toutes les catégories

        elseif ($categorie_filter === 'all' && !empty($search_query)) {
            $stmt = $pdo->prepare("
                SELECT i.*, s.quantite_stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE LOWER(i.nom) LIKE LOWER(?) 
                   OR LOWER(i.description) LIKE LOWER(?)
                ORDER BY i.date_publication DESC
            ");
            $search_term = '%' . $search_query . '%';
            $stmt->execute([$search_term, $search_term]);
        }
        // CAS 4: Recherche + filtre de catégorie

        else {
            $stmt = $pdo->prepare("
                SELECT i.*, s.quantite_stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE i.categorie = ?
                  AND (LOWER(i.nom) LIKE LOWER(?) OR LOWER(i.description) LIKE LOWER(?))
                ORDER BY i.date_publication DESC
            ");
            $search_term = '%' . $search_query . '%';
            $stmt->execute([$categorie_filter, $search_term, $search_term]);
        }
        
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des produits: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container">

    <!-- Titre avec résultats de recherche -->

    <h2 style="text-align: center; color: #333; margin: 2rem 0;">
        <?php if (!empty($search_query)): ?>
            🔍 Résultats pour "<?php echo escape($search_query); ?>"
        <?php else: ?>
            Notre Catalogue
        <?php endif; ?>
    </h2>

    <!-- Affichage du nombre de résultats -->

    <?php if (!empty($search_query)): ?>
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <?php if (count($products) > 0): ?>
                <p style="font-size: 1.1rem; color: #667eea; margin-bottom: 1rem;">
                    <strong><?php echo count($products); ?></strong> produit<?php echo count($products) > 1 ? 's' : ''; ?> trouvé<?php echo count($products) > 1 ? 's' : ''; ?>
                </p>
            <?php else: ?>
                <p style="font-size: 1.1rem; color: #dc3545; margin-bottom: 1rem;">
                    Aucun produit trouvé pour votre recherche.
                </p>
            <?php endif; ?>
           
        </div>
    <?php endif; ?>

    <!-- Filtres par catégorie -->

    <div style="text-align: center; margin-bottom: 2rem; display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
        <a href="?categorie=all<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" 
           class="btn <?php echo $categorie_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
            Tous les produits
        </a>
        <a href="?categorie=vetements_homme<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" 
           class="btn <?php echo $categorie_filter === 'vetements_homme' ? 'btn-primary' : 'btn-secondary'; ?>">
            Vêtements Homme
        </a>
        <a href="?categorie=vetements_femme<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" 
           class="btn <?php echo $categorie_filter === 'vetements_femme' ? 'btn-primary' : 'btn-secondary'; ?>">
            Vêtements Femme
        </a>
        <a href="?categorie=accessoires<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" 
           class="btn <?php echo $categorie_filter === 'accessoires' ? 'btn-primary' : 'btn-secondary'; ?>">
            Accessoires
        </a>
        <a href="?categorie=chaussures<?php echo !empty($search_query) ? '&q=' . urlencode($search_query) : ''; ?>" 
           class="btn <?php echo $categorie_filter === 'chaussures' ? 'btn-primary' : 'btn-secondary'; ?>">
            Chaussures
        </a>
    </div>

    <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="card">
                   
                    <div class="card-img-container">
                        <?php 

                        // Vérifier si l'image existe

                        $image_file = $product['image'] ?? 'placeholder.jpg';
                        $image_path = __DIR__ . '/../assets/images/' . $image_file;
                        $has_valid_image = !empty($image_file) 
                                           && $image_file !== 'placeholder.jpg' 
                                           && file_exists($image_path);
                        ?>
                        
                        <?php if ($has_valid_image): ?>
                            <img src="/ecommerce/assets/images/<?php echo escape($image_file); ?>" 
                                 alt="<?php echo escape($product['nom']); ?>" 
                                 class="card-img">
                        <?php else: ?>
                            <div class="card-img-placeholder">
                                <div style="font-size: 4rem; color: #adb5bd;">📷</div>
                                <p style="color: #6c757d; margin: 0.5rem 0 0 0; font-size: 0.9rem;">Image non disponible</p>
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
                        
                        
                        <?php 

                        // Récupérer la quantité de stock (peut être null si LEFT JOIN)

                        $stock_quantity = $product['quantite_stock'] ?? 0;
                        ?>
                        
                        <?php if ($stock_quantity > 0): ?>
                            <span class="card-stock disponible">
                                ✓ En stock (<?php echo $stock_quantity; ?> disponible<?php echo $stock_quantity > 1 ? 's' : ''; ?>)
                            </span>
                        <?php else: ?>
                            <span class="card-stock rupture">
                                ✗ Rupture de stock
                            </span>
                        <?php endif; ?>
                        
                        <!-- Actions -->
                         
                        <div class="card-actions">
                            <a href="/ecommerce/pages/product-detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-primary">
                                Voir détails
                            </a>
                            
                            <?php if ($stock_quantity > 0): ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                  <a href="/ecommerce/pages/add-to-cart.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-success"
                                 title="Ajouter au panier">
                                 🛒 Ajouter
                                </a>
                                <?php else: ?>
                                    <a href="/ecommerce/pages/login.php" 
                                       class="btn btn-success" 
                                       title="Connectez-vous pour ajouter au panier">
                                        🛒 Ajouter
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 2rem 0;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">
                <?php echo !empty($search_query) ? '🕵️' : '📦'; ?>
            </div>
            <h3 style="color: #333; margin-bottom: 1rem;">
                <?php if (!empty($search_query)): ?>
                    Aucun produit trouvé
                <?php else: ?>
                    Aucun produit disponible
                <?php endif; ?>
            </h3>
            <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">
                <?php if (!empty($search_query)): ?>
                    Désolé, nous n'avons rien trouvé pour "<strong><?php echo escape($search_query); ?></strong>".<br>
                    Essayez avec d'autres mots-clés ou consultez toutes nos catégories.
                <?php else: ?>
                    Aucun produit ne correspond à cette catégorie.
                <?php endif; ?>
            </p>
            <a href="?categorie=all" class="btn btn-primary">Voir tous les produits</a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>