<?php
// Gestion des produits - Interface admin
// Liste, modification et suppression des produits
 
require_once __DIR__ . '/../config/database.php';

$page_title = 'Gestion des produits';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer tous les produits avec leur stock
$pdo = getDBConnection();
$products = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                i.id,
                i.nom,
                i.description,
                i.prix,
                i.image,
                i.categorie,
                COALESCE(s.quantite_stock, 0) as stock
            FROM items i
            LEFT JOIN stock s ON i.id = s.id_item
            ORDER BY i.id DESC
        ");
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des produits: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la récupération des produits.";
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Lien vers le CSS spécifique pour admin products -->

<link rel="stylesheet" href="/ecommerce/assets/css/admin-products-clean.css">

<div class="products-admin-container">
    <div class="products-admin-header">
        <h2>Gestion des produits</h2>
        <a href="/ecommerce/admin/add_product.php" class="btn btn-primary">Ajouter un produit</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="products-success-message">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']); 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="products-error-message">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="products-table-container">
        <table class="products-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="products-empty-state">
                            Aucun produit disponible. <a href="/ecommerce/admin/add_product.php">Ajouter un produit</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id']); ?></td>
                            <td>
                                <?php if ($product['image'] && $product['image'] !== 'placeholder.jpg' && file_exists(__DIR__ . '/../assets/images/' . $product['image'])): ?>
                                    <img src="/ecommerce/assets/images/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['nom']); ?>"
                                         class="product-image">
                                <?php else: ?>
                                    <div class="product-image-placeholder">
                                        📷
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="product-name"><?php echo htmlspecialchars($product['nom']); ?></div>
                                <?php if ($product['description']): ?>
                                    <div class="product-description">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>
                                        <?php echo strlen($product['description']) > 50 ? '...' : ''; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $categories = [
                                    'vetements_homme' => 'Vêtements Homme',
                                    'vetements_femme' => 'Vêtements Femme',
                                    'accessoires' => 'Accessoires',
                                    'chaussures' => 'Chaussures'
                                ];
                                echo htmlspecialchars($categories[$product['categorie']] ?? $product['categorie']);
                                ?>
                            </td>
                            <td>
                                <span class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> €</span>
                            </td>
                            <td>
                                <?php 
                                $stock = $product['stock'];
                                $stockClass = $stock <= 0 ? 'stock-low' : ($stock <= 5 ? 'stock-warning' : 'stock-ok');
                                ?>
                                <span class="product-stock <?php echo $stockClass; ?>">
                                    <?php echo $stock; ?>
                                </span>
                            </td>
                            <td>
                                <div class="products-actions">
                                    <a href="/ecommerce/admin/product_edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-modifier">
                                        Modifier
                                    </a>
                                    <a href="/ecommerce/admin/product delete.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-supprimer"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                        Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="products-footer">
        <a href="/ecommerce/admin/index.php" class="btn btn-secondary">← Retour au tableau de bord</a>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>