<?php
/**
 * Page du panier d'achat
 * Affiche les produits dans le panier et permet de gérer les quantités
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Mon Panier';

// Vérifier que l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder au panier.";
    redirect('/ecommerce/pages/login.php');
}

// Initialiser le panier s'il n'existe pas

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Récupérer les produits du panier

$cart_products = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $pdo = getDBConnection();
    if ($pdo) {
        try {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $stmt = $pdo->prepare("
                SELECT i.*, s.quantite_stock 
                FROM items i 
                LEFT JOIN stock s ON i.id = s.id_item 
                WHERE i.id IN ($placeholders)
            ");
            $stmt->execute($ids);
            
            while ($product = $stmt->fetch()) {
                $quantity = $_SESSION['cart'][$product['id']];
                $subtotal = $product['prix'] * $quantity;
                $total += $subtotal;
                
                $cart_products[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du panier: " . $e->getMessage());
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<h2 style="text-align: center; color: #333; margin-bottom: 2rem;">Mon Panier</h2>

<?php if (empty($cart_products)): ?>
    <div style="text-align: center; padding: 3rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
        <h3 style="color: #666; margin-bottom: 1rem;">Votre panier est vide</h3>
        <p style="color: #999; margin-bottom: 2rem;">Découvrez notre catalogue et ajoutez des articles à votre panier</p>
        <a href="/ecommerce/pages/articles.php" class="btn btn-primary">Voir les produits</a>
    </div>
<?php else: ?>
    <div class="cart-table">
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_products as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="/ecommerce/assets/images/<?php echo escape($item['product']['image'] ?? 'placeholder.jpg'); ?>" 
                                     alt="<?php echo escape($item['product']['nom']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;"
                                     onerror="this.src='/ecommerce/assets/images/placeholder.jpg'">
                                <div>
                                    <strong><?php echo escape($item['product']['nom']); ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        Stock disponible: <?php echo $item['product']['quantite_stock']; ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td><strong><?php echo number_format($item['product']['prix'], 2, ',', ' '); ?> €</strong></td>
                        <td>
                            <form method="POST" action="/ecommerce/pages/update-cart.php" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <input type="number" 
                                       name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" 
                                       max="<?php echo $item['product']['quantite_stock']; ?>"
                                       style="width: 80px; padding: 0.5rem; border: 2px solid #ddd; border-radius: 5px;">
                                <button type="submit" class="btn btn-secondary" style="margin-left: 0.5rem; padding: 0.5rem 1rem;">
                                    Modifier
                                </button>
                            </form>
                        </td>
                        <td><strong style="color: #667eea;"><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> €</strong></td>
                        <td>
                            <a href="/ecommerce/pages/remove-from-cart.php?id=<?php echo $item['product']['id']; ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Êtes-vous sûr de vouloir retirer cet article du panier ?')">
                                Retirer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="cart-total">
        <h3>Total: <?php echo number_format($total, 2, ',', ' '); ?> €</h3>
        
        <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
            <a href="/ecommerce/pages/clear-cart.php" 
               class="btn btn-secondary"
               onclick="return confirm('Êtes-vous sûr de vouloir vider le panier ?')">
                Vider le panier
            </a>
            <a href="/ecommerce/pages/articles.php" class="btn btn-primary">
                Continuer mes achats
            </a>
        </div>
        
        <div style="margin-top: 2rem; padding: 1.5rem; background: #e7f3ff; border-left: 4px solid #667eea; border-radius: 5px; text-align: left;">
            <h4 style="color: #333; margin-bottom: 0.5rem;">ℹ️ Information</h4>
            <p style="color: #666; margin: 0;">
                Cette version du site ne gère pas encore les paiements en ligne. 
                Le panier vous permet de sélectionner vos produits favoris.
            </p>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>