<?php
require_once '../config/config.php';
$page_title = "Politique de Confidentialité";
include '../includes/header.php';
?>

<div class="container">
    <div class="privacy-policy-container">
        <h1 class="page-title">Politique de Confidentialité</h1>
        
        <div class="privacy-last-update">
            <strong>Dernière mise à jour :</strong> <?php echo date('d/m/Y'); ?>
        </div>

        <!-- Introduction -->

        <section class="privacy-section">
            <p class="privacy-intro">
                Fashion Shop accorde une grande importance à la protection de vos données personnelles. 
                Cette politique de confidentialité vous informe sur la manière dont nous collectons, 
                utilisons et protégeons vos informations personnelles conformément au Règlement Général 
                sur la Protection des Données (RGPD).
            </p>
        </section>

        <!-- 1. Responsable du traitement -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">1. Responsable du Traitement des Données</h2>
            <div class="privacy-content">
                <p><strong>Fashion Shop</strong></p>
                <p>Email : abdulaziz.ouedraogo75@gmail.com</p>
                <p>Téléphone : +33 06 23 16 13 86</p>
                <p>Adresse : 123 Avenue de la Mode, 75001 Paris, France</p>
            </div>
        </section>

        <!-- 2. Données collectées -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">2. Données Personnelles Collectées</h2>
            <div class="privacy-content">
                <p>Nous collectons les informations suivantes :</p>
                
                <h3 class="privacy-subsection-title">2.1. Lors de la création de compte</h3>
                <ul class="privacy-list">
                    <li>Nom et prénom</li>
                    <li>Adresse email</li>
                    <li>Mot de passe (crypté)</li>
                    <li>Numéro de téléphone (optionnel)</li>
                </ul>

                <h3 class="privacy-subsection-title">2.2. Lors d'une commande</h3>
                <ul class="privacy-list">
                    <li>Adresse de livraison</li>
                    <li>Adresse de facturation</li>
                    <li>Informations de paiement (traitées de manière sécurisée par nos prestataires)</li>
                    <li>Historique des commandes</li>
                </ul>

                <h3 class="privacy-subsection-title">2.3. Navigation sur le site</h3>
                <ul class="privacy-list">
                    <li>Adresse IP</li>
                    <li>Type de navigateur</li>
                    <li>Pages visitées</li>
                    <li>Cookies (voir section dédiée)</li>
                </ul>
            </div>
        </section>

        <!-- 3. Utilisation des données -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">3. Utilisation de Vos Données</h2>
            <div class="privacy-content">
                <p>Vos données personnelles sont utilisées pour :</p>
                <ul class="privacy-list">
                    <li><strong>Gestion des commandes :</strong> Traiter vos achats, livraisons et retours</li>
                    <li><strong>Service client :</strong> Répondre à vos questions et demandes</li>
                    <li><strong>Sécurité :</strong> Prévenir la fraude et sécuriser votre compte</li>
                    <li><strong>Communication :</strong> Vous envoyer des confirmations de commande et informations importantes</li>
                    <li><strong>Amélioration :</strong> Améliorer nos services et votre expérience d'achat</li>
                    <li><strong>Marketing (avec consentement) :</strong> Vous envoyer nos offres et nouveautés</li>
                </ul>
            </div>
        </section>

        <!-- 4. Base légale -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">4. Base Légale du Traitement</h2>
            <div class="privacy-content">
                <p>Nous traitons vos données sur les bases légales suivantes :</p>
                <ul class="privacy-list">
                    <li><strong>Exécution du contrat :</strong> Pour traiter vos commandes</li>
                    <li><strong>Obligation légale :</strong> Pour la comptabilité et les obligations fiscales</li>
                    <li><strong>Intérêt légitime :</strong> Pour la prévention de la fraude</li>
                    <li><strong>Consentement :</strong> Pour les communications marketing (révocable à tout moment)</li>
                </ul>
            </div>
        </section>

        <!-- 5. Partage des données -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">5. Partage de Vos Données</h2>
            <div class="privacy-content">
                <p>Vos données peuvent être partagées avec :</p>
                <ul class="privacy-list">
                    <li><strong>Prestataires de paiement :</strong> Pour sécuriser les transactions (Stripe, PayPal)</li>
                    <li><strong>Services de livraison :</strong> Pour l'expédition de vos commandes (Colissimo, Chronopost)</li>
                    <li><strong>Hébergeur web :</strong> Pour le stockage sécurisé des données</li>
                    <li><strong>Services d'emailing :</strong> Pour l'envoi de newsletters (si consentement)</li>
                </ul>
                <p class="privacy-note">
                    <strong>Important :</strong> Nous ne vendons jamais vos données personnelles à des tiers.
                </p>
            </div>
        </section>

        <!-- 6. Durée de conservation -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">6. Durée de Conservation des Données</h2>
            <div class="privacy-content">
                <ul class="privacy-list">
                    <li><strong>Données de compte :</strong> Conservées jusqu'à la suppression de votre compte</li>
                    <li><strong>Données de commande :</strong> 10 ans (obligations comptables et fiscales)</li>
                    <li><strong>Cookies :</strong> 13 mois maximum</li>
                    <li><strong>Données marketing :</strong> 3 ans après votre dernière interaction</li>
                </ul>
            </div>
        </section>

        <!-- 7. Cookies -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">7. Cookies</h2>
            <div class="privacy-content">
                <p>Nous utilisons des cookies pour améliorer votre expérience :</p>
                
                <h3 class="privacy-subsection-title">7.1. Cookies essentiels</h3>
                <p>Nécessaires au fonctionnement du site (panier, connexion). Ils ne peuvent pas être désactivés.</p>

                <h3 class="privacy-subsection-title">7.2. Cookies de performance</h3>
                <p>Nous aident à comprendre comment vous utilisez le site (anonymes).</p>

                <h3 class="privacy-subsection-title">7.3. Cookies marketing</h3>
                <p>Utilisés pour vous proposer des publicités pertinentes (avec votre consentement).</p>

                <p class="privacy-note">
                    Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.
                </p>
            </div>
        </section>

        <!-- 8. Vos droits RGPD -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">8. Vos Droits (RGPD)</h2>
            <div class="privacy-content">
                <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                
                <div class="privacy-rights-grid">
                    <div class="privacy-right-card">
                        <div class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-scan-eye-icon lucide-scan-eye"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="1"/><path d="M18.944 12.33a1 1 0 0 0 0-.66 7.5 7.5 0 0 0-13.888 0 1 1 0 0 0 0 .66 7.5 7.5 0 0 0 13.888 0"/></svg></div>
                        <h4>Droit d'accès</h4>
                        <p>Obtenir une copie de vos données personnelles</p>
                    </div>

                    <div class="privacy-right-card">
                        <div class="right-icon"><svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-pen-icon lucide-user-round-pen"><path d="M2 21a8 8 0 0 1 10.821-7.487"/><path d="M21.378 16.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><circle cx="10" cy="8" r="5"/></svg></div>
                        <h4>Droit de rectification</h4>
                        <p>Corriger vos données inexactes ou incomplètes</p>
                    </div>

                    <div class="privacy-right-card">
                        <div class="right-icon">🗑️</div>
                        <h4>Droit à l'effacement</h4>
                        <p>Supprimer vos données dans certaines conditions</p>
                    </div>

                    <div class="privacy-right-card">
                        <div class="right-icon">⛔</div>
                        <h4>Droit d'opposition</h4>
                        <p>Vous opposer au traitement de vos données</p>
                    </div>

                    <div class="privacy-right-card">
                        <div class="right-icon">📦</div>
                        <h4>Droit à la portabilité</h4>
                        <p>Récupérer vos données dans un format lisible</p>
                    </div>

                    <div class="privacy-right-card">
                        <div class="right-icon">⏸️</div>
                        <h4>Droit à la limitation</h4>
                        <p>Limiter temporairement le traitement</p>
                    </div>
                </div>

                <p class="privacy-highlight">
                    <strong>Pour exercer vos droits :</strong> Contactez-nous à 
                    <a href="mailto:privacy@fashionshop.fr">privacy@fashionshop.fr</a>
                    avec une copie de votre pièce d'identité.
                </p>
            </div>
        </section>

        <!-- 9. Sécurité -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">9. Sécurité de Vos Données</h2>
            <div class="privacy-content">
                <p>Nous mettons en œuvre des mesures de sécurité appropriées :</p>
                <ul class="privacy-list">
                    <li>Cryptage SSL/TLS pour toutes les communications</li>
                    <li>Mots de passe hashés et sécurisés</li>
                    <li>Serveurs sécurisés et régulièrement mis à jour</li>
                    <li>Accès restreint aux données personnelles</li>
                    <li>Sauvegardes régulières</li>
                    <li>Surveillance continue contre les intrusions</li>
                </ul>
            </div>
        </section>

        <!-- 10. Transferts internationaux -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">10. Transferts Internationaux</h2>
            <div class="privacy-content">
                <p>
                    Vos données sont hébergées en France et dans l'Union Européenne. 
                    Si des transferts hors UE sont nécessaires, nous veillons à ce qu'ils respectent 
                    les garanties appropriées du RGPD (clauses contractuelles types).
                </p>
            </div>
        </section>

        <!-- 11. Mineurs -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">11. Protection des Mineurs</h2>
            <div class="privacy-content">
                <p>
                    Notre site n'est pas destiné aux personnes de moins de 16 ans. 
                    Si vous êtes un parent et pensez que votre enfant nous a fourni des données, 
                    contactez-nous immédiatement à <a href="mailto:contact@fashionshop.fr">contact@fashionshop.fr</a>.
                </p>
            </div>
        </section>

        <!-- 12. Modifications -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">12. Modifications de cette Politique</h2>
            <div class="privacy-content">
                <p>
                    Nous pouvons mettre à jour cette politique de confidentialité. 
                    Les modifications importantes vous seront notifiées par email ou via une bannière sur le site. 
                    La date de dernière mise à jour est indiquée en haut de cette page.
                </p>
            </div>
        </section>

        <!-- 13. Contact -->

        <section class="privacy-section">
            <h2 class="privacy-section-title">13. Nous Contacter</h2>
            <div class="privacy-content">
                <p>Pour toute question concernant cette politique ou vos données personnelles :</p>
                
                <div class="privacy-contact-box">
                    <h4>📧 Email</h4>
                    <p><a href="mailto:privacy@fashionshop.fr">privacy@fashionshop.fr</a></p>
                    
                    <h4>📞 Téléphone</h4>
                    <p>+33 (0)1 23 45 67 89</p>
                    
                    <h4>📬 Courrier</h4>
                    <p>
                        Fashion Shop - Service Protection des Données<br>
                        123 Avenue de la Mode<br>
                        75001 Paris, France
                    </p>
                </div>

                <p class="privacy-note">
                    <strong>CNIL :</strong> Vous avez également le droit de déposer une réclamation auprès de la 
                    Commission Nationale de l'Informatique et des Libertés (CNIL) si vous estimez que vos droits 
                    ne sont pas respectés.
                </p>
            </div>
        </section>

        <!-- Bouton retour -->
         
        <div class="privacy-back-btn">
            <a href="<?php echo url('index.php'); ?>" class="btn btn-primary">
                ← Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>