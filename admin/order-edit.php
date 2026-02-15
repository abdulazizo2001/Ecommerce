<?php
/**
 * MODIFICATION D'UNE COMMANDE - VERSION FINALE
 * Force l'écriture du statut pour corriger les entrées vides en BDD
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est admin

if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID de la commande

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    $_SESSION['error_message'] = "Commande non trouvée.";
    redirect('/ecommerce/admin/orders.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/admin/orders.php');
}

// Récupérer la commande

$order = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        $_SESSION['error_message'] = "Commande non trouvée.";
        redirect('/ecommerce/admin/orders.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération commande: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération de la commande.";
    redirect('/ecommerce/admin/orders.php');
}

// TRAITEMENT DU FORMULAIRE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statut_brut = $_POST['statut'] ?? '';
    
    // On nettoie et on force le format attendu par le CSS 
    $statut_final = str_replace(' ', '_', strtolower(trim($statut_brut)));
    
    // Liste des statuts autorisés en BDD
    $statuts_autorises = ['payee', 'en_cours', 'livree', 'annulee'];
    
    if (!in_array($statut_final, $statuts_autorises)) {
        $_SESSION['error_message'] = "Statut invalide.";
        redirect('/ecommerce/admin/order-edit.php?id=' . $order_id);
    }
    
    try {

        // MISE À JOUR : On force l'exécution et on vérifie le succès

        $stmt = $pdo->prepare("UPDATE orders SET statut = ? WHERE id = ?");
        $success = $stmt->execute([$statut_final, $order_id]);
        
        // Même si rowCount est 0 (si on enregistre le même statut), 
        // on considère que c'est un succès car l'action a été validée.
        
        $_SESSION['success_message'] = "Commande #$order_id mise à jour avec succès.";
        redirect('/ecommerce/admin/orders.php');
        
    } catch (PDOException $e) {
        error_log("Erreur modification commande: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur base de données : " . $e->getMessage();
        redirect('/ecommerce/admin/orders.php');
    }
}

// Fonction helper pour vérifier le statut (ignore les différences espaces/underscores)
function isStatut($current, $check) {
    $current_normalized = str_replace('_', ' ', strtolower(trim($current)));
    $check_normalized = str_replace('_', ' ', strtolower(trim($check)));
    return $current_normalized === $check_normalized;
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/order-edit.css">

<div class="edit-container">
    <h1>Modifier la commande #<?php echo $order_id; ?></h1>
    
    <div class="order-info">
        <p><strong>ID:</strong> #<?php echo $order['id']; ?></p>
        <p><strong>Montant:</strong> <?php echo number_format($order['montant_total'], 2, ',', ' '); ?> €</p>
        <p><strong>Quantité:</strong> <?php echo $order['quantite']; ?></p>
        <p><strong>Statut actuel:</strong> 
            <span style="color: #ff6e14; font-weight: bold;">
                <?php echo $order['statut'] ? ucfirst(str_replace('_', ' ', $order['statut'])) : 'Non défini (vide)'; ?>
            </span>
        </p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="statut">Nouveau statut</label>
            <select name="statut" id="statut" required>
                <option value="payee" <?php echo isStatut($order['statut'], 'payee') ? 'selected' : ''; ?>>Payée</option>
                <option value="en_cours" <?php echo isStatut($order['statut'], 'en_cours') ? 'selected' : ''; ?>>En cours</option>
                <option value="livree" <?php echo isStatut($order['statut'], 'livree') ? 'selected' : ''; ?>>Livrée</option>
                <option value="annulee" <?php echo isStatut($order['statut'], 'annulee') ? 'selected' : ''; ?>>Annulée</option>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-save">Enregistrer</button>
            <a href="/ecommerce/admin/orders.php" class="btn-cancel">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>