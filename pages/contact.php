<?php
require_once '../config/config.php';

$page_title = "Contactez-nous";

// --- TRAITEMENT DU FORMULAIRE ---
$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $error_message = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse email n'est pas valide.";
    } else {
        $message_sent = true;
    }
}

include '../includes/header.php';
?>

<div class="contact-page">

    <h1 class="page-title">Contactez-nous</h1>

    <?php if ($message_sent): ?>
        <div class="alert alert-success">
            <strong>Message envoyé avec succès !</strong> Nous vous répondrons dans les plus brefs délais.
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <strong>Erreur :</strong> <?php echo escape($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="contact-grid">
        
        <aside class="contact-info-section">
            <h2>Nos coordonnées</h2>
            
            <div class="contact-info-card">
                <div class="contact-details">
                    <h3>📍 Adresse</h3>
                    <p>123 Avenue de la Mode, 75001 Paris, France</p>
                </div>
            </div>

            <div class="contact-info-card">
                <div class="contact-details">
                    <h3>📞 Téléphone</h3>
                    <p><a href="tel:+33123456789">+33 (0)6 16 13 86</a></p>
                </div>
            </div>

            <div class="contact-info-card">
                <div class="contact-details">
                    <h3>📧 Email</h3>
                    <p><a href="mailto:ouedraogo75@gmail.com">abdulazizouedraogo75@gmail.com</a></p>
                    <small>Réponse sous 3 jours</small>
                </div>
            </div>

            <div class="contact-info-card">
                <div class="contact-details">
                    <h3>🕐 Horaires</h3>
                    <p>
                        Lundi - Vendredi : 9h - 18h<br>
                        Samedi : 10h - 17h<br>
                        Dimanche : Fermé
                    </p>
                </div>
            </div>
        </aside>

        <section class="contact-form-section">
            <h2>Envoyez-nous un message</h2>
            
            <form method="POST" class="contact-form-styled">
                <div class="form-group">
                    <label for="nom">Nom complet *</label>
                    <input type="text" id="nom" name="nom" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="sujet">Sujet *</label>
                    <select id="sujet" name="sujet" class="form-control" required>
                        <option value="">Sélectionnez un sujet</option>
                        <option value="commande">Question sur une commande</option>
                        <option value="produit">Question sur un produit</option>
                        <option value="livraison">Problème de livraison</option>
                        <option value="retour">Retour / Remboursement</option>
                        <option value="technique">Problème technique</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="message">Votre message *</label>
                    <textarea id="message" name="message" class="form-control" rows="6" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-large">
                    Envoyer le message
                </button>
            </form>
        </section>

    </div> 
</div> 

<?php include '../includes/footer.php'; ?>