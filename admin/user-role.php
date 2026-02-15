<?php

/**
 * CHANGEMENT DU RÔLE D'UN UTILISATEUR
 */

require_once __DIR__ . '/../config/database.php';

// Vérifier si l'utilisateur est admin

if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé.";
    redirect('/ecommerce/index.php');
}

// Récupérer l'ID de l'utilisateur

$user_id = (int)($_GET['id'] ?? 0);

if ($user_id <= 0) {
    $_SESSION['error_message'] = "Utilisateur non trouvé.";
    redirect('/ecommerce/admin/users.php');
}

// Ne pas permettre de modifier son propre rôle

if ($user_id === $_SESSION['user_id']) {
    $_SESSION['error_message'] = "Vous ne pouvez pas modifier votre propre rôle.";
    redirect('/ecommerce/admin/users.php');
}

$pdo = getDBConnection();

if (!$pdo) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données.";
    redirect('/ecommerce/admin/users.php');
}

// Récupérer l'utilisateur

$user = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error_message'] = "Utilisateur non trouvé.";
        redirect('/ecommerce/admin/users.php');
    }
} catch (PDOException $e) {
    error_log("Erreur récupération utilisateur: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération de l'utilisateur.";
    redirect('/ecommerce/admin/users.php');
}

// Traiter la soumission du formulaire

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_role = $_POST['role'] ?? '';
    
    // Vérifier que le rôle est valide

    if (!in_array($nouveau_role, ['admin', 'user'])) {
        $_SESSION['error_message'] = "Rôle invalide.";
        redirect('/ecommerce/admin/user-role.php?id=' . $user_id);
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$nouveau_role, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $role_nom = $nouveau_role === 'admin' ? 'Administrateur' : 'Utilisateur';
            $_SESSION['success_message'] = "Rôle de {$user['prenom']} {$user['nom']} changé en : $role_nom";
        } else {
            $_SESSION['info_message'] = "Aucune modification effectuée.";
        }
        
        redirect('/ecommerce/admin/users.php');
        
    } catch (PDOException $e) {
        error_log("Erreur modification rôle: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la modification du rôle.";
        redirect('/ecommerce/admin/users.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="edit-container">
    <h1>Modifier le rôle : <?php echo escape($user['prenom'] . ' ' . $user['nom']); ?></h1>
    
    <div class="user-info-box">
        <p><strong>ID:</strong> #<?php echo $user['id']; ?></p>
        <p><strong>Nom:</strong> <?php echo escape($user['prenom'] . ' ' . $user['nom']); ?></p>
        <p><strong>Email:</strong> <?php echo escape($user['email']); ?></p>
        <p><strong>Rôle actuel:</strong> 
            <span style="font-size: 1.2rem; color: <?php echo $user['role'] === 'admin' ? '#667eea' : '#666'; ?>;">
                <?php echo $user['role'] === 'admin' ? ' Administrateur' : ' Utilisateur'; ?>
            </span>
        </p>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="role">Nouveau rôle</label>
            <select name="role" id="role" required>
                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>> Utilisateur</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>> Administrateur</option>
            </select>
        </div>
        
        <div class="role-warning">
            <strong>Attention :</strong>
            <ul>
                <li>Un <strong>Administrateur</strong> a accès à toutes les fonctionnalités (gestion produits, commandes, utilisateurs, stock)</li>
                <li>Un <strong>Utilisateur</strong> peut seulement acheter des produits et voir ses commandes</li>
            </ul>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-save"> Enregistrer</button>
            <a href="/ecommerce/admin/users.php" class="btn-cancel"> Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>