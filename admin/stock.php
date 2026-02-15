<?php
/**
 * PAGE ADMIN - GESTION DU STOCK
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier que l'utilisateur est admin
if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/pages/login.php');
}

$page_title = 'Gestion du Stock';

// Seuil de stock faible
$seuil_faible = 10;
$seuil_critique = 5;

// Récupérer tous les produits avec leur stock
$pdo = getDBConnection();
$products = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                i.id,
                i.nom,
                i.prix,
                i.image,
                COALESCE(s.quantite_stock, 0) as quantite_stock
            FROM items i
            LEFT JOIN stock s ON i.id = s.id_item
            ORDER BY s.quantite_stock ASC, i.nom ASC
        ");
        $products = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Erreur récupération stock: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/admin-stock.css">

<div class="admin-container">
    <div class="admin-header">
        <h1> Gestion du Stock</h1>
        <a href="/ecommerce/admin/index.php" class="btn-back">
            ⬅️ Retour au tableau de bord
        </a>
    </div>


    <!-- Liste des produits -->
    <?php if (!empty($products)): ?>
        <div class="stock-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Stock actuel</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $stock_class = '';
                        $stock_text = '';
                        if ($product['quantite_stock'] == 0) {
                            $stock_class = 'stock-rupture';
                            $stock_text = 'Rupture';
                        } elseif ($product['quantite_stock'] <= $seuil_critique) {
                            $stock_class = 'stock-critique';
                            $stock_text = 'Critique';
                        } elseif ($product['quantite_stock'] <= $seuil_faible) {
                            $stock_class = 'stock-faible';
                            $stock_text = 'Faible';
                        } else {
                            $stock_class = 'stock-ok';
                            $stock_text = 'OK';
                        }
                        ?>
                        <tr>
                            <td class="product-id">#<?php echo $product['id']; ?></td>
                            <td>
                                <div class="product-info">
                                    <span class="product-name"><?php echo escape($product['nom']); ?></span>
                                </div>
                            </td>
                            <td class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> €</td>
                            <td class="stock-quantity">
                                <span class="quantity-badge <?php echo $stock_class; ?>">
                                    <?php echo $product['quantite_stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $stock_class; ?>">
                                    <?php echo $stock_text; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="stock-edit.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-modifier">
                                    Modifier
                                </a>
                                <a href="stock-delete.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-supprimer"
                                   onclick="return confirm('Supprimer ce produit du stock ?');">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>Aucun produit</h3>
            <p>Il n'y a pas encore de produits dans le stock.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>