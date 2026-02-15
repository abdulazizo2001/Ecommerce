<?php

 // Modifier un produit existant

require_once __DIR__ . '/../config/database.php';

$page_title = 'Modifier un produit';
$errors = [];

// Vérifier que l'utilisateur est admin

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID du produit

$product_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$product_id || $product_id <= 0) {
    $_SESSION['error_message'] = "ID de produit invalide.";
    redirect('/ecommerce/admin/products.php');
    exit;
}

$pdo = getDBConnection();
$product = null;

// Récupérer les données du produit

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
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
            WHERE i.id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $_SESSION['error_message'] = "Produit introuvable.";
            redirect('/ecommerce/admin/products.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du produit: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la récupération du produit.";
        redirect('/ecommerce/admin/products.php');
        exit;
    }
}

// Traitement du formulaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = $_POST['prix'] ?? 0;
    $categorie = $_POST['categorie'] ?? '';
    $image = trim($_POST['image'] ?? 'placeholder.jpg');
    $quantite_stock = $_POST['quantite_stock'] ?? 0;
    
    // Validation

    if (empty($nom)) {
        $errors[] = "Le nom du produit est requis.";
    }
    
    if (empty($description)) {
        $errors[] = "La description est requise.";
    }
    
    if ($prix <= 0) {
        $errors[] = "Le prix doit être supérieur à 0.";
    }
    
    if (!in_array($categorie, ['vetements_homme', 'vetements_femme', 'accessoires', 'chaussures'])) {
        $errors[] = "Catégorie invalide.";
    }
    
    if ($quantite_stock < 0) {
        $errors[] = "La quantité en stock ne peut pas être négative.";
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Mettre à jour le produit

            $stmt = $pdo->prepare("
                UPDATE items 
                SET nom = ?, description = ?, prix = ?, categorie = ?, image = ?
                WHERE id = ?
            ");
            $stmt->execute([$nom, $description, $prix, $categorie, $image, $product_id]);
            
            // Mettre à jour le stock

            $stmt = $pdo->prepare("
                UPDATE stock 
                SET quantite_stock = ?
                WHERE id_item = ?
            ");
            $stmt->execute([$quantite_stock, $product_id]);
            
            $pdo->commit();
            
            $_SESSION['success_message'] = "Produit modifié avec succès !";
            redirect('/ecommerce/admin/products.php');
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erreur lors de la modification du produit: " . $e->getMessage());
            $errors[] = "Une erreur est survenue lors de la modification du produit.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Lien vers le CSS dédié aux formulaires admin -->

<link rel="stylesheet" href="/ecommerce/assets/css/admin-forms.css">

<div class="admin-container">
    <div class="admin-header">
        <a href="/ecommerce/admin/products.php" class="back-link">←</a>
        <h2 class="admin-title">Modifier le produit</h2>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="form-card">
        <form method="POST" action="">
            <div class="form-group">
                <label for="nom">Nom du produit *</label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       class="form-control" 
                       value="<?php echo escape($_POST['nom'] ?? $product['nom']); ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          required><?php echo escape($_POST['description'] ?? $product['description']); ?></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="prix">Prix (€) *</label>
                    <input type="number" 
                           id="prix" 
                           name="prix" 
                           class="form-control" 
                           step="0.01" 
                           min="0.01"
                           value="<?php echo escape($_POST['prix'] ?? $product['prix']); ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="quantite_stock">Quantité en stock *</label>
                    <input type="number" 
                           id="quantite_stock" 
                           name="quantite_stock" 
                           class="form-control" 
                           min="0"
                           value="<?php echo escape($_POST['quantite_stock'] ?? $product['stock']); ?>" 
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie *</label>
                <select id="categorie" name="categorie" class="form-control" required>
                    <option value="">-- Sélectionner une catégorie --</option>
                    <?php 
                    $current_cat = $_POST['categorie'] ?? $product['categorie'];
                    $categories = [
                        'vetements_homme' => 'Vêtements Homme',
                        'vetements_femme' => 'Vêtements Femme',
                        'accessoires' => 'Accessoires',
                        'chaussures' => 'Chaussures'
                    ];
                    foreach ($categories as $value => $label):
                    ?>
                        <option value="<?php echo $value; ?>" <?php echo $current_cat === $value ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Nom du fichier image</label>
                <input type="text" 
                       id="image" 
                       name="image" 
                       class="form-control" 
                       value="<?php echo escape($_POST['image'] ?? $product['image']); ?>" 
                       placeholder="placeholder.jpg">
                <small class="image-help">Les images doivent être placées dans /assets/images/</small>
                
                <?php if ($product['image'] && $product['image'] !== 'placeholder.jpg'): ?>
                    <div class="image-preview">
                        <img src="/ecommerce/assets/images/<?php echo escape($product['image']); ?>" 
                             alt="Aperçu du produit"
                             class="preview-img"
                             onerror="this.src='/ecommerce/assets/images/placeholder.jpg'">
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-submit">Enregistrer les modifications</button>
                <a href="/ecommerce/admin/products.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>