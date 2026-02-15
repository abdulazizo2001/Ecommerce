<?php
require_once __DIR__ . '/../config/database.php';

// Sécurité : Vérifier si l'utilisateur est admin

if (!isAdmin()) {
    header('Location: /ecommerce/index.php');
    exit;
}

$page_title = "Gestion des Commandes";

$pdo = getDBConnection();
$orders = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT o.*, u.nom as client_nom, u.prenom as client_prenom, u.email as client_email, i.nom as produit_nom
            FROM orders o
            JOIN users u ON o.id_user = u.id
            JOIN items i ON o.id_item = i.id
            ORDER BY o.date_commande DESC
        ");
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur admin orders: " . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/admin-orders.css">

<div class="admin-container">
    <div class="admin-header">
        <h1>Liste des Commandes</h1>
        <a href="/ecommerce/admin/index.php" class="btn-back">
            ⬅️ Retour au Dashboard
        </a>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="orders-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="order-id">#<?php echo $order['id']; ?></td>
                            <td>
                                <div class="customer-info">
                                    <span class="customer-name"><?php echo escape($order['client_prenom'] . ' ' . $order['client_nom']); ?></span>
                                    <span class="customer-email"><?php echo escape($order['client_email']); ?></span>
                                </div>
                            </td>
                            <td><?php echo escape($order['produit_nom']); ?></td>
                            <td><?php echo $order['quantite']; ?></td>
                            <td class="order-amount"><?php echo number_format($order['montant_total'], 2, ',', ' '); ?> €</td>
                            <td>
                                <?php 
                                    /** * CORRECTION DU STATUT 
                                     * strtolower : pour gérer les majuscules (Payee -> payee)
                                     * str_replace : pour transformer les espaces en underscores (en cours -> en_cours)
                                     */

                                    $statut_brut = $order['statut'];
                                    $statut_clean = str_replace([' ', '-'], '_', strtolower($statut_brut));
                                ?>
                                <span class="status-badge status-<?php echo $statut_clean; ?>">
                                    <?php 

                                        // Affiche "En cours" joliment au lieu de "en_cours"
                                        
                                        echo ucfirst(str_replace('_', ' ', $statut_brut)); 
                                    ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="order-edit.php?id=<?php echo $order['id']; ?>" class="btn-modifier">
                                    Modifier
                                </a>
                                <a href="order-delete.php?id=<?php echo $order['id']; ?>" 
                                   class="btn-supprimer"
                                   onclick="return confirm('Voulez-vous vraiment supprimer cette commande ?');">
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
            <h3>Aucune commande</h3>
            <p>Il n'y a pas encore de commandes enregistrées.</p>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>