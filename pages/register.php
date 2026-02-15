<?php
/**
 * Page d'inscription des nouveaux utilisateurs
 */

require_once __DIR__ . '/../config/config.php';

$page_title = 'Inscription';
$errors = [];
$success = false;

// Vérifier si l'utilisateur est déjà connecté

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Traitement du formulaire d'inscription

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation côté serveur

    if (empty($nom) || strlen($nom) < 2) {
        $errors[] = "Le nom doit contenir au moins 2 caractères.";
    }
    
    if (empty($prenom) || strlen($prenom) < 2) {
        $errors[] = "Le prénom doit contenir au moins 2 caractères.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Si pas d'erreurs, vérifier si l'email existe et créer le compte

    if (empty($errors)) {
        $pdo = getDBConnection();
        if ($pdo) {
            try {

                // Vérifier si l'email existe déjà
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $errors[] = "Cette adresse email est déjà utilisée.";
                } else {

                    // Créer le compte
                    
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'user')");
                    $stmt->execute([$nom, $prenom, $email, $password_hash]);
                    
                    $_SESSION['success_message'] = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
                    redirect('pages/login.php');
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de l'inscription: " . $e->getMessage());
                $errors[] = "Une erreur est survenue lors de la création du compte. Veuillez réessayer.";
            }
        } else {
            $errors[] = "Impossible de se connecter à la base de données.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="register-container">
    <h2 class="register-title">Créer un compte</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="register-form">
        <div class="form-row-grid">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       class="form-control" 
                       value="<?php echo escape($_POST['nom'] ?? ''); ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" 
                       id="prenom" 
                       name="prenom" 
                       class="form-control" 
                       value="<?php echo escape($_POST['prenom'] ?? ''); ?>" 
                       required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="email">Adresse email</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   value="<?php echo escape($_POST['email'] ?? ''); ?>" 
                   required>
        </div>
        
        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control" 
                   required>
            <small class="form-help">Minimum 6 caractères</small>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Confirmer le mot de passe</label>
            <input type="password" 
                   id="password_confirm" 
                   name="password_confirm" 
                   class="form-control" 
                   required>
        </div>
        
        <button type="submit" class="btn btn-primary btn-full">S'inscrire</button>
        
        <p class="register-footer">
            Déjà un compte ? 
            <a href="<?php echo url('pages/login.php'); ?>" class="register-link">Se connecter</a>
        </p>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>