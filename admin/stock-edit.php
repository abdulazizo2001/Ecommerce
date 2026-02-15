<?php
/**
 * MODIFICATION DU STOCK D'UN PRODUIT
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est admin

if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID du produit

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    $_SESSION['error_message'] = "Produit non trouvé.";
    redirect('/ecommerce/admin/stock.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/admin/stock.php');
}

// Récupérer le produit et son stock

$product = null;
try {
    $stmt = $pdo->prepare("
        SELECT 
            i.id,
            i.nom,
            i.prix,
            COALESCE(s.quantite_stock, 0) as quantite_stock
        FROM items i
        LEFT JOIN stock s ON i.id = s.id_item
        WHERE i.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $_SESSION['error_message'] = "Produit non trouvé.";
        redirect('/ecommerce/admin/stock.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération produit: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération du produit.";
    redirect('/ecommerce/admin/stock.php');
}

// Traiter la soumission du formulaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouvelle_quantite = (int)($_POST['quantite'] ?? 0);
    
    if ($nouvelle_quantite < 0) {
        $_SESSION['error_message'] = "La quantité ne peut pas être négative.";
        redirect('/ecommerce/admin/stock-edit.php?id=' . $product_id);
    }
    
    try {

        // Vérifier si une entrée stock existe déjà

        $stmt = $pdo->prepare("SELECT id FROM stock WHERE id_item = ?");
        $stmt->execute([$product_id]);
        $stock_exists = $stmt->fetch();
        
        if ($stock_exists) {

            // Mettre à jour le stock existant

            $stmt = $pdo->prepare("UPDATE stock SET quantite_stock = ? WHERE id_item = ?");
            $stmt->execute([$nouvelle_quantite, $product_id]);
        } else {

            // Créer une nouvelle entrée stock

            $stmt = $pdo->prepare("INSERT INTO stock (id_item, quantite_stock) VALUES (?, ?)");
            $stmt->execute([$product_id, $nouvelle_quantite]);
        }
        
        $_SESSION['success_message'] = "Stock mis à jour : " . $product['nom'] . " → " . $nouvelle_quantite . " unités";
        redirect('/ecommerce/admin/stock.php');
        
    } catch (PDOException $e) {
        error_log("Erreur modification stock: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la modification du stock.";
        redirect('/ecommerce/admin/stock.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/admin-stock.css">

<div class="edit-container">
    <h1>Modifier le stock : <?php echo escape($product['nom']); ?></h1>
    
    <div class="product-info-box">
        <p><strong>ID:</strong> #<?php echo $product['id']; ?></p>
        <p><strong>Produit:</strong> <?php echo escape($product['nom']); ?></p>
        <p><strong>Prix:</strong> <?php echo number_format($product['prix'], 2, ',', ' '); ?> €</p>
        <p><strong>Stock actuel:</strong> <span style="font-size: 1.5rem; color: #ff6b35;"><?php echo $product['quantite_stock']; ?></span> unités</p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="quantite">Nouvelle quantité en stock</label>
            <input type="number" 
                   name="quantite" 
                   id="quantite" 
                   value="<?php echo $product['quantite_stock']; ?>" 
                   min="0" 
                   required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-save"> Enregistrer</button>
            <a href="/ecommerce/admin/stock.php" class="btn-cancel"> Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>