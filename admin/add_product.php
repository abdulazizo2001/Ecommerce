<?php

 // Ajouter un nouveau produit - Interface admin

require_once __DIR__ . '/../config/database.php';

$page_title = 'Ajouter un Produit';
$errors = [];

// Vérifier que l'utilisateur est admin

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Traitement du formulaire 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = $_POST['prix'] ?? 0;
    $categorie = $_POST['categorie'] ?? '';
    $quantite_stock = $_POST['quantite_stock'] ?? 0;
    $image = trim($_POST['image'] ?? '');
    
    if (empty($nom)) { $errors[] = "Le nom du produit est requis."; }
    if (empty($description)) { $errors[] = "La description est requise."; }
    if ($prix <= 0) { $errors[] = "Le prix doit être supérieur à 0."; }
    if (!in_array($categorie, ['vetements_homme', 'vetements_femme', 'accessoires', 'chaussures'])) {
        $errors[] = "Catégorie invalide.";
    }
    if ($quantite_stock < 0) { $errors[] = "La quantité en stock ne peut pas être négative."; }
    
    if (empty($errors)) {
        $pdo = getDBConnection();
        if ($pdo) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO items (nom, description, prix, categorie, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $description, $prix, $categorie, $image]);
                
                $product_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO stock (id_item, quantite_stock) VALUES (?, ?)");
                $stmt->execute([$product_id, $quantite_stock]);
                
                $pdo->commit();
                $_SESSION['success_message'] = "Produit ajouté avec succès !";
                redirect('/ecommerce/admin/products.php');
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Erreur: " . $e->getMessage());
                $errors[] = "Une erreur est survenue.";
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="/ecommerce/assets/css/admin-forms.css">

<div class="admin-container">
    <div class="admin-header">
        <a href="/ecommerce/admin/products.php" class="back-link">←</a>
        <h2 class="admin-title">Ajouter un nouveau produit</h2>
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
                <input type="text" id="nom" name="nom" class="form-control" 
                       value="<?php echo escape($_POST['nom'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" class="form-control" 
                          required><?php echo escape($_POST['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="prix">Prix (€) *</label>
                    <input type="number" id="prix" name="prix" class="form-control" 
                           step="0.01" min="0.01" value="<?php echo escape($_POST['prix'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="quantite_stock">Quantité en stock *</label>
                    <input type="number" id="quantite_stock" name="quantite_stock" class="form-control" 
                           min="0" value="<?php echo escape($_POST['quantite_stock'] ?? '0'); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie *</label>
                <select id="categorie" name="categorie" class="form-control" required>
                    <option value="">-- Sélectionner une catégorie --</option>
                    <option value="vetements_homme" <?php echo ($_POST['categorie'] ?? '') === 'vetements_homme' ? 'selected' : ''; ?>>Vêtements Homme</option>
                    <option value="vetements_femme" <?php echo ($_POST['categorie'] ?? '') === 'vetements_femme' ? 'selected' : ''; ?>>Vêtements Femme</option>
                    <option value="accessoires" <?php echo ($_POST['categorie'] ?? '') === 'accessoires' ? 'selected' : ''; ?>>Accessoires</option>
                    <option value="chaussures" <?php echo ($_POST['categorie'] ?? '') === 'chaussures' ? 'selected' : ''; ?>>Chaussures</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Nom du fichier image (ex: tshirt.jpg)</label>
                <input type="text" id="image" name="image" class="form-control" 
                       value="<?php echo escape($_POST['image'] ?? 'placeholder.jpg'); ?>" 
                       placeholder="placeholder.jpg">
                <small class="image-help">Les images doivent être placées dans /assets/images/</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-submit">✓ Ajouter le produit</button>
                <a href="/ecommerce/admin/products.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include ROOT_PATH . '/includes/footer.php'; ?>