<?php

require_once __DIR__ . '/../config/database.php';

$page_title = 'Mon Panier';

// Vérifier que l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder au panier.";
    redirect('/ecommerce/pages/login.php');
}

// Récupérer le panier de l'utilisateur

$pdo = getDBConnection();
$cart_items = [];
$total = 0;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.id as cart_id,
                c.quantite,
                i.id,
                i.nom,
                i.description,
                i.prix,
                i.image,
                s.quantite_stock
            FROM panier c
            INNER JOIN items i ON c.id_item = i.id
            LEFT JOIN stock s ON i.id = s.id_item
            WHERE c.id_user = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll();
        
        // Calculer le total

        foreach ($cart_items as $item) {
            $total += $item['prix'] * $item['quantite'];
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du panier: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <h2 style="color: #333; margin-bottom: 2rem;">
        <span style="font-size: 2rem;">🛒</span> Mon Panier
        <?php if (!empty($cart_items)): ?>
            <span style="font-size: 1rem; color: #666;">(<?php echo count($cart_items); ?> article<?php echo count($cart_items) > 1 ? 's' : ''; ?>)</span>
        <?php endif; ?>
    </h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']); 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>

        <!-- Panier vide -->

        <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 5rem; margin-bottom: 1rem;">🛒</div>
            <h3 style="color: #333; margin-bottom: 1rem;">Votre panier est vide</h3>
            <p style="color: #666; margin-bottom: 2rem;">Découvrez notre catalogue et ajoutez des articles à votre panier</p>
            <a href="/ecommerce/pages/articles.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Découvrir nos produits
            </a>
        </div>
    <?php else: ?>

        <!-- Panier avec articles -->

        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">

            <!-- Articles du panier -->

            <div>
                <?php foreach ($cart_items as $item): ?>
                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 1rem; display: flex; gap: 1.5rem;">
                        
                    <!-- Image -->

                        <div style="flex-shrink: 0;">
                            <?php 
                            $image_path = __DIR__ . '/../assets/images/' . ($item['image'] ?? '');
                            $has_image = !empty($item['image']) && file_exists($image_path);
                            ?>
                            
                            <?php if ($has_image): ?>
                                <img src="/ecommerce/assets/images/<?php echo escape($item['image']); ?>" 
                                     style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div style="width: 120px; height: 120px; background: #e9ecef; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                                    📷
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Informations produit -->

                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 0.5rem 0; color: #333; font-size: 1.2rem;">
                                <a href="/ecommerce/pages/product-detail.php?id=<?php echo $item['id']; ?>" 
                                   style="color: #333; text-decoration: none;">
                                    <?php echo escape($item['nom']); ?>
                                </a>
                            </h3>
                            <p style="margin: 0 0 1rem 0; color: #666; font-size: 0.9rem;">
                                <?php echo escape(substr($item['description'], 0, 100)); ?>...
                            </p>
                            
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">

                                <!-- Sélecteur de quantité -->

                                <form method="POST" action="/ecommerce/pages/update-cart.php" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <label style="color: #666; font-size: 0.9rem;">Quantité :</label>
                                    <select name="quantite" 
                                            onchange="this.form.submit()"
                                            style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
                                        <?php for ($i = 1; $i <= min(10, $item['quantite_stock']); $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo $i == $item['quantite'] ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </form>

                                <!-- Stock disponible -->

                                <?php if ($item['quantite_stock'] <= 5): ?>
                                    <span style="color: #dc3545; font-size: 0.85rem; font-weight: 500;">
                                         Plus que <?php echo $item['quantite_stock']; ?> en stock
                                    </span>
                                <?php else: ?>
                                    <span style="color: #28a745; font-size: 0.85rem;">
                                        ✓ En stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <!-- Prix -->

                                <div>
                                    <p style="margin: 0; font-size: 1.5rem; font-weight: bold; color: #667eea;">
                                        <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €
                                    </p>
                                    <?php if ($item['quantite'] > 1): ?>
                                        <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #666;">
                                            <?php echo number_format($item['prix'], 2, ',', ' '); ?> € l'unité
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <!-- Bouton supprimer -->

                                <a href="/ecommerce/pages/remove-from-cart.php?cart_id=<?php echo $item['cart_id']; ?>" 
                                   onclick="return confirm('Voulez-vous vraiment supprimer cet article ?');"
                                   style="color: #dc3545; text-decoration: none; font-size: 0.9rem; padding: 0.5rem 1rem; border: 1px solid #dc3545; border-radius: 5px; transition: all 0.3s;">
                                     Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Bouton continuer les achats -->

                <a href="/ecommerce/pages/articles.php" 
                   style="display: inline-flex; align-items: center; gap: 0.5rem; color: #667eea; text-decoration: none; font-size: 1rem; padding: 1rem;">
                    ← Continuer mes achats
                </a>
            </div>

            <!-- Récapitulatif -->

            <div>
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 2rem;">
                    <h3 style="color: #333; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                        Récapitulatif
                    </h3>

                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span style="color: #666;">Sous-total (<?php echo count($cart_items); ?> article<?php echo count($cart_items) > 1 ? 's' : ''; ?>)</span>
                            <span style="font-weight: 600; color: #333;"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                            <span style="color: #666;">Livraison</span>
                            <span style="font-weight: 600; color: <?php echo $total >= 50 ? '#28a745' : '#333'; ?>;">
                                <?php if ($total >= 50): ?>
                                    Gratuite 🎉
                                <?php else: ?>
                                    5,00 €
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if ($total < 50): ?>
                            <div style="background: #fff3cd; padding: 0.75rem; border-radius: 5px; margin: 1rem 0; font-size: 0.85rem; color: #856404;">
                                 Plus que <strong><?php echo number_format(50 - $total, 2, ',', ' '); ?> €</strong> pour profiter de la livraison gratuite !
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="padding-top: 1rem; margin-top: 1rem; border-top: 2px solid #e0e0e0;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #333;">Total</span>
                            <span style="font-size: 1.5rem; font-weight: bold; color: #667eea;">
                                <?php echo number_format($total + ($total >= 50 ? 0 : 5), 2, ',', ' '); ?> €
                            </span>
                        </div>

                        <a href="/ecommerce/pages/checkout.php" 
                           class="btn btn-primary" 
                           style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600; text-align: center; display: block; text-decoration: none;">
                            Passer la commande 
                        </a>

                        <p style="text-align: center; margin: 1rem 0 0 0; font-size: 0.85rem; color: #666;">
                            Paiement sécurisé par SSL
                        </p>
                    </div>

                    <!-- Moyens de paiement acceptés -->

                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 0.75rem; text-align: center;">
                            Moyens de paiement acceptés
                        </p>
                        <div style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                            <div style="width: 50px; height: 32px; background: linear-gradient(135deg, #1a1f71 0%, #0d47a1 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.7rem; font-weight: bold;">VISA</div>
                            <div style="width: 50px; height: 32px; background: linear-gradient(135deg, #eb001b 0%, #f79e1b 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.7rem; font-weight: bold;">MC</div>
                            <div style="width: 50px; height: 32px; background: linear-gradient(135deg, #0070ba 0%, #1546a0 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.7rem; font-weight: bold;">PP</div>
                        </div>
                    </div>

                    <!-- Avantages -->
                     
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e0e0e0;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: #666;">
                            <span style="color: #28a745; font-size: 1.2rem;">✓</span>
                            <span>Retours gratuits sous 30 jours</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; font-size: 0.85rem; color: #666;">
                            <span style="color: #28a745; font-size: 1.2rem;">✓</span>
                            <span>Livraison rapide 2-4 jours</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; color: #666;">
                            <span style="color: #28a745; font-size: 1.2rem;">✓</span>
                            <span>Service client 7j/7</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>