<?php
/**
 * Page de confirmation de commande
 * Adapté à la structure : orders contient id_item directement + table invoice séparée
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Commande confirmée';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['invoice_id'])) {
    redirect('/ecommerce/index.php');
}

$invoice_id = $_SESSION['invoice_id'];
$pdo = getDBConnection();
$invoice = null;
$order_items = [];

if ($pdo) {
    try {
        // Récupérer les détails de la facture

        $stmt = $pdo->prepare("
            SELECT i.*, u.email, u.prenom, u.nom
            FROM invoice i
            INNER JOIN users u ON i.id_user = u.id
            WHERE i.id = ? AND i.id_user = ?
        ");
        $stmt->execute([$invoice_id, $_SESSION['user_id']]);
        $invoice = $stmt->fetch();
        
        // Récupérer les articles de la commande (dernières commandes de l'utilisateur)
        
        $stmt = $pdo->prepare("
            SELECT o.*, it.nom, it.prix, it.image
            FROM orders o
            INNER JOIN items it ON o.id_item = it.id
            WHERE o.id_user = ? 
            AND o.date_commande >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY o.date_commande DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $order_items = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Erreur: " . $e->getMessage());
    }
}

// Nettoyer la session
unset($_SESSION['invoice_id']);

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 800px; margin: 0 auto; padding: 2rem; text-align: center;">
    <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem auto; animation: scaleIn 0.5s ease;">
        <span style="font-size: 3rem; color: white;">✓</span>
    </div>
    
    <h1 style="color: #28a745; margin-bottom: 1rem;">Commande confirmée !</h1>
    <p style="color: #666; font-size: 1.1rem; margin-bottom: 2rem;">
        Merci pour votre commande. Un email de confirmation a été envoyé à <strong><?php echo escape($invoice['email'] ?? ''); ?></strong>
    </p>
    
    <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: left; margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
            <div>
                <p style="margin: 0; color: #666; font-size: 0.9rem;">Numéro de facture</p>
                <p style="margin: 0.25rem 0 0 0; font-size: 1.5rem; font-weight: bold; color: #333;">#<?php echo str_pad($invoice['id'], 6, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div style="text-align: right;">
                <p style="margin: 0; color: #666; font-size: 0.9rem;">Date</p>
                <p style="margin: 0.25rem 0 0 0; font-weight: 600; color: #333;">
                    <?php echo date('d/m/Y à H:i', strtotime($invoice['date_transaction'])); ?>
                </p>
            </div>
        </div>
        
        <h3 style="color: #333; margin-bottom: 1rem;">Détails de la commande</h3>
        
        <?php foreach ($order_items as $item): ?>
            <div style="display: flex; gap: 1rem; padding: 1rem; border-bottom: 1px solid #eee;">
                <?php 
                $image_path = __DIR__ . '/../assets/images/' . ($item['image'] ?? '');
                $has_image = !empty($item['image']) && file_exists($image_path);
                ?>
                
                <?php if ($has_image): ?>
                    <img src="/ecommerce/assets/images/<?php echo escape($item['image']); ?>" 
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; background: #e9ecef; border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        📷
                    </div>
                <?php endif; ?>
                
                <div style="flex: 1;">
                    <p style="margin: 0; font-weight: 600; color: #333;"><?php echo escape($item['nom']); ?></p>
                    <p style="margin: 0.25rem 0 0 0; color: #666; font-size: 0.9rem;">
                        Quantité : <?php echo $item['quantite']; ?> × <?php echo number_format($item['prix'], 2, ',', ' '); ?> €
                    </p>
                </div>
                <div style="text-align: right;">
                    <p style="margin: 0; font-weight: bold; color: #667eea;">
                        <?php echo number_format($item['montant_total'], 2, ',', ' '); ?> €
                    </p>
                </div>
            </div>
        <?php endforeach; ?>
        
       <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #e0e0e0;">
    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; color: #333;">
        <span>Total payé</span>
        <span style="color: #667eea;">
            <?php echo number_format($invoice['montant_total'] ?? 0, 2, ',', ' '); ?> €
        </span>
    </div>
</div>
    
    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; text-align: left; margin-bottom: 2rem;">

        <p style="margin: 0; color: #666;"><?php echo escape($invoice['adresse_facturation']); ?></p>
        <p style="margin: 0.25rem 0 0 0; color: #666;">
            <?php echo escape($invoice['code_postal']); ?> <?php echo escape($invoice['ville']); ?>
        </p>
    </div>
    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; text-align: left; margin-bottom: 1.5rem; border: 1px solid #e9ecef;">
    <h3 style="color: #333; margin: 0 0 0.75rem 0; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
        <span>📍</span> Adresse de livraison
    </h3>
    
    <p style="margin: 0; font-weight: 600; color: #333;">
        <?php echo htmlspecialchars(($invoice['prenom'] ?? '') . ' ' . ($invoice['nom'] ?? '')); ?>
    </p>
    
    <p style="margin: 0.2rem 0; color: #666;">
        <?php echo htmlspecialchars($invoice['adresse_facturation'] ?? ''); ?>
    </p>
    
    <p style="margin: 0; color: #666;">
        <?php echo htmlspecialchars($invoice['code_postal'] ?? ''); ?> <?php echo htmlspecialchars($invoice['ville'] ?? ''); ?>
    </p>
</div>
    
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
        <h3 style="margin: 0 0 1rem 0;">🚚 Livraison estimée</h3>
        <p style="margin: 0; font-size: 1.2rem; font-weight: 600;">
            <?php 
            $date_livraison = date('d/m/Y', strtotime($invoice['date_transaction'] . ' +3 days'));
            echo $date_livraison;
            ?>
        </p>
        <p style="margin: 0.5rem 0 0 0; opacity: 0.9; font-size: 0.9rem;">
            Vous recevrez un email de suivi dès l'expédition
        </p>
    </div>
    
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="/ecommerce/index.php" class="btn btn-primary">
            Retour à l'accueil
        </a>
        <a href="/ecommerce/pages/articles.php" class="btn btn-secondary">
            Continuer mes achats
        </a>
    </div>
</div>

<style>
@keyframes scaleIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>