<?php

// Page de connexion des utilisateurs

require_once __DIR__ . '/../config/config.php';

$page_title = 'Connexion';
$errors = [];

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation côté serveur
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }
    
    // Si pas d'erreurs de validation, vérifier les identifiants
    if (empty($errors)) {
        $pdo = getDBConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['mot_de_passe'])) {

                    // Connexion réussie - créer la session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nom'] = $user['prenom'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    $_SESSION['success_message'] = "Bienvenue " . escape($user['prenom']) . " !";
                    
                    // Rediriger vers la page d'accueil ou admin selon le rôle
                    
                    if ($user['role'] === 'admin') {
                        redirect('admin/index.php');
                    } else {
                        redirect('index.php');
                    }
                } else {
                    $errors[] = "Email ou mot de passe incorrect.";
                }
            } catch (PDOException $e) {
                error_log("Erreur lors de la connexion: " . $e->getMessage());
                $errors[] = "Une erreur est survenue. Veuillez réessayer.";
            }
        } else {
            $errors[] = "Impossible de se connecter à la base de données.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 500px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="text-align: center; color: #333; margin-bottom: 2rem;">Se connecter</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
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
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Se connecter</button>
        
        <p style="text-align: center; margin-top: 1rem;">
            Pas encore de compte ? 
            <a href="<?php echo url('pages/register.php'); ?>" style="color: #667eea; font-weight: bold;">S'inscrire</a>
        </p>
    </form>

<?php include __DIR__ . '/../includes/footer.php'; ?>