<?php
/**
 * Page de détail d'un produit
 */

// 1. On utilise config.php pour avoir accès à la DB et aux fonctions url/escape

require_once __DIR__ . '/../config/config.php';

// Récupérer l'ID du produit
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

if ($product_id > 0) {
    $pdo = getDBConnection();
    if ($pdo) {
        try {

            // Jointure avec la table 'stock' comme dans votre structure
            $stmt = $pdo->prepare("
                SELECT i.*, s.quantite_stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE i.id = ?
            ");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du produit: " . $e->getMessage());
        }
    }
}


// Si le produit n'existe pas, redirection propre vers le catalogue
if (!$product) {
    $_SESSION['error_message'] = "Produit non trouvé.";
    header("Location: " . url('pages/articles.php'));
    exit();
}

$page_title = $product['nom'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div style="margin-bottom: 1rem;">
        <a href="<?php echo url('pages/articles.php'); ?>" style="color: #667eea; text-decoration: none; font-weight: bold;">
            ← Retour au catalogue
        </a>
    </div>

    <div class="product-detail" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
        <div class="product-image-container" style="background: #fff; padding: 10px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <img src="<?php echo url('assets/images/' . ($product['image'] ?? 'placeholder.jpg')); ?>" 
                 alt="<?php echo escape($product['nom']); ?>"
                 style="width: 100%; border-radius: 10px; display: block;"
                 onerror="this.src='<?php echo url('assets/images/placeholder.jpg'); ?>'">
        </div>
        
        <div class="product-info">
            <h1 style="color: #333; margin-bottom: 0.5rem; font-size: 2.5rem;"><?php echo escape($product['nom']); ?></h1>
            
            <div style="margin-bottom: 1.5rem;">
                <span style="display: inline-block; padding: 0.5rem 1rem; background: #f0f2ff; border-radius: 50px; color: #667eea; font-weight: bold; font-size: 0.9rem;">
                    <?php 
                    $categories = [
                        'vetements_homme' => 'Vêtements Homme',
                        'vetements_femme' => 'Vêtements Femme',
                        'accessoires' => 'Accessoires',
                        'chaussures' => 'Chaussures'
                    ];
                    echo $categories[$product['categorie']] ?? $product['categorie'];
                    ?>
                </span>
            </div>
            
            <p style="font-size: 2.2rem; font-weight: bold; color: #764ba2; margin-bottom: 1.5rem;">
                <?php echo number_format($product['prix'], 2, ',', ' '); ?> €
            </p>
            
            <div style="margin-bottom: 2rem;">
                <?php if ($product['quantite_stock'] > 0): ?>
                    <p style="color: #28a745; font-weight: bold; font-size: 1.1rem; display: flex; align-items: center; gap: 5px;">
                        <span>✅</span> En stock (<?php echo $product['quantite_stock']; ?> disponible<?php echo $product['quantite_stock'] > 1 ? 's' : ''; ?>)
                    </p>
                <?php else: ?>
                    <p style="color: #dc3545; font-weight: bold; font-size: 1.1rem;">❌ Rupture de stock</p>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 10px; border: 1px solid #eee;">
                <h3 style="color: #333; margin-bottom: 0.8rem; font-size: 1.2rem;">Description</h3>
                <p style="color: #666; line-height: 1.6;"><?php echo nl2br(escape($product['description'])); ?></p>
            </div>
            
            <?php if ($product['quantite_stock'] > 0): ?>
                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="<?php echo url('pages/articles.php'); ?>" style="margin-bottom: 1rem;">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id_article" value="<?php echo $product['id']; ?>">
                        
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <div style="flex: 0 0 120px;">
                                <label for="quantity" style="font-weight: bold; display: block; margin-bottom: 5px; font-size: 0.9rem;">Quantité :</label>
                                <input type="number" id="quantity" name="quantite" value="1" min="1" 
                                       max="<?php echo $product['quantite_stock']; ?>" 
                                       style="width: 100%; padding: 0.7rem; border: 2px solid #ddd; border-radius: 8px; text-align: center;">
                            </div>
                            <button type="submit" class="btn btn-success" style="flex: 1; padding: 1rem; margin-top: 1.4rem; font-weight: bold; font-size: 1.1rem; border-radius: 8px;">
                                🛒 Ajouter au panier
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div style="padding: 1rem; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px; margin-bottom: 1rem;">
                        <p style="color: #856404; margin: 0; font-size: 0.9rem;">
                            <strong>Connectez-vous</strong> pour commander cet article.
                        </p>
                    </div>
                    <a href="<?php echo url('pages/login.php'); ?>" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 8px;">
                        Se connecter
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>