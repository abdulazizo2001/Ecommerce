<?php
/**
 * Page de validation de commande avant paiement
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Validation de commande';

// Vérifier que l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour passer commande.";
    redirect('/ecommerce/pages/login.php');
}


// TRAITER LE FORMULAIRE SI SOUMIS

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer et valider les données

    $required_fields = ['prenom', 'nom', 'adresse', 'code_postal', 'ville', 'telephone'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ " . $field . " est requis.";
        }
    }
    
    // Validation du code postal (5 chiffres)

    if (!empty($_POST['code_postal']) && !preg_match('/^[0-9]{5}$/', $_POST['code_postal'])) {
        $errors[] = "Le code postal doit contenir 5 chiffres.";
    }
    
    // Si pas d'erreurs, enregistrer dans la session et rediriger

    if (empty($errors)) {
        $_SESSION['delivery_info'] = [
            'prenom' => trim($_POST['prenom']),
            'nom' => trim($_POST['nom']),
            'adresse' => trim($_POST['adresse']),
            'code_postal' => trim($_POST['code_postal']),
            'ville' => trim($_POST['ville']),
            'telephone' => trim($_POST['telephone']),
            'instructions' => trim($_POST['instructions'] ?? '')
        ];
        
        // Rediriger vers la page de paiement

        redirect('/ecommerce/pages/payment.php');
    } else {

        // Afficher les erreurs

        $_SESSION['error_message'] = implode('<br>', $errors);
    }
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

// Si le panier est vide, rediriger

if (empty($cart_items)) {
    $_SESSION['error_message'] = "Votre panier est vide.";
    redirect('/ecommerce/pages/cart.php');
}

// Récupérer les informations de l'utilisateur

$user_info = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_info = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erreur: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <h2 style="color: #333; margin-bottom: 2rem; text-align: center;">
        <span style="font-size: 2rem;">🛒</span> Validation de commande
    </h2>
    
    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 2rem;">

        <!-- Colonne gauche : Informations de livraison et articles -->
        <div>

            <!-- Informations de livraison -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h3 style="color: #333; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.5rem;">📍</span> Adresse de livraison
                </h3>
                
                <form id="deliveryForm" method="POST" action="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Prénom *</label>
                            <input type="text" name="prenom" required
                                   value="<?php echo escape($user_info['prenom'] ?? ''); ?>"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Nom *</label>
                            <input type="text" name="nom" required
                                   value="<?php echo escape($user_info['nom'] ?? ''); ?>"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Adresse *</label>
                        <input type="text" name="adresse" required
                               placeholder="Numéro et nom de rue"
                               style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Code postal *</label>
                            <input type="text" name="code_postal" required
                                   pattern="[0-9]{5}"
                                   placeholder="75001"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Ville *</label>
                            <input type="text" name="ville" required
                                   placeholder="Paris"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Téléphone *</label>
                        <input type="tel" name="telephone" required
                               placeholder="06 12 34 56 78"
                               style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem;">
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500;">Instructions de livraison (optionnel)</label>
                        <textarea name="instructions" rows="3"
                                  placeholder="Code d'accès, étage, etc."
                                  style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; resize: vertical;"></textarea>
                    </div>
                </form>
            </div>
            
            <!-- Articles du panier -->

            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h3 style="color: #333; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-size: 1.5rem;"></span> Votre commande (<?php echo count($cart_items); ?> article<?php echo count($cart_items) > 1 ? 's' : ''; ?>)
                </h3>
                
                <?php foreach ($cart_items as $item): ?>
                    <div style="display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid #eee;">
                        <?php 
                        $image_path = __DIR__ . '/../assets/images/' . ($item['image'] ?? '');
                        $has_image = !empty($item['image']) && file_exists($image_path);
                        ?>
                        
                        <?php if ($has_image): ?>
                            <img src="/ecommerce/assets/images/<?php echo escape($item['image']); ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px;">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                📷
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 0.5rem 0; color: #333;"><?php echo escape($item['nom']); ?></h4>
                            <p style="margin: 0; color: #666;">Quantité : <?php echo $item['quantite']; ?></p>
                            <p style="margin: 0.5rem 0 0 0; font-weight: bold; color: #667eea;">
                                <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Colonne droite : Récapitulatif -->
         
        <div>
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 2rem;">
                <h3 style="color: #333; margin-bottom: 1.5rem;">Récapitulatif</h3>
                
                <div style="border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #666;">Sous-total</span>
                        <span style="font-weight: 500;"><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span style="color: #666;">Livraison</span>
                        <span style="font-weight: 500; color: #28a745;">
                            <?php if ($total >= 50): ?>
                                Gratuite
                            <?php else: ?>
                                5,00 €
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($total < 50): ?>
                        <p style="font-size: 0.85rem; color: #666; margin: 0.5rem 0 0 0;">
                            💡 Plus que <?php echo number_format(50 - $total, 2, ',', ' '); ?> € pour la livraison gratuite
                        </p>
                    <?php endif; ?>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                    <span style="font-size: 1.2rem; font-weight: bold; color: #333;">Total</span>
                    <span style="font-size: 1.2rem; font-weight: bold; color: #667eea;">
                        <?php echo number_format($total + ($total >= 50 ? 0 : 5), 2, ',', ' '); ?> €
                    </span>
                </div>
                
                <button type="submit" form="deliveryForm" 
                        class="btn btn-primary" 
                        style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: 600;">
                    Procéder au paiement 
                </button>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="/ecommerce/pages/cart.php" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">
                        ← Modifier mon panier
                    </a>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; font-size: 0.9rem; color: #666;">
                        <span style="color: #28a745;">✓</span> Paiement sécurisé SSL
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; font-size: 0.9rem; color: #666;">
                        <span style="color: #28a745;">✓</span> Retours gratuits sous 30 jours
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; color: #666;">
                        <span style="color: #28a745;">✓</span> Service client 7j/7
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>