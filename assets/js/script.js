/**
 * ============================================================================
 * FASHION SHOP - E-COMMERCE JAVASCRIPT
 * ============================================================================
 * 
 * @description Script JavaScript professionnel pour site e-commerce
 * @version 2.1.0
 * @author Fashion Shop Development Team
 * @license MIT
 * 
 * Fonctionnalités :
 * - Validation avancée des formulaires
 * - Gestion intelligente du panier (✅ AVEC AJAX)
 * - Recherche et filtrage en temps réel
 * - Animations fluides et optimisées
 * - Gestion d'erreurs robuste
 * - UX/UI améliorée
 * - Performance optimisée
 * - Compatible tous navigateurs modernes
 * 
 * ============================================================================
 */

'use strict';


const FashionShop = (function() {
    
    
    // CONFIGURATION
    
    const CONFIG = {
        debounceDelay: 300,
        animationDuration: 300,
        notificationDuration: 4000,
        minPasswordLength: 6,
        minNameLength: 2,
        minDescriptionLength: 10,
        searchMinChars: 2,
        enableDebug: false
    };
    
   
    // UTILITAIRES
   
    const Utils = {
        
        // Validation d'email avec regex RFC 5322
        isValidEmail(email) {
            const regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            return regex.test(String(email).toLowerCase());
        },
        
        
         // Formatage de prix en français
        formatPrice(price) {
            if (isNaN(price)) return '0,00';
            return parseFloat(price)
                .toFixed(2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        },
        
        //Parsing sécurisé de prix
        parsePrice(priceString) {
            if (!priceString) return 0;
            const cleaned = String(priceString)
                .replace(/[^0-9.,]/g, '')
                .replace(',', '.');
            const parsed = parseFloat(cleaned);
            return isNaN(parsed) ? 0 : parsed;
        },
        
        //Debounce pour optimiser les events
        debounce(func, wait = CONFIG.debounceDelay) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        //Throttle pour limiter la fréquence d'exécution
        throttle(func, limit = 100) {
            let inThrottle;
            return function(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },
        
        
         //Escape HTML pour prévenir XSS
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        // Vérifier si un élément est visible dans le viewport
        isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },
        
        
         //Logger conditionnel pour debug
        log(...args) {
            if (CONFIG.enableDebug) {
                console.log('[Fashion Shop]', ...args);
            }
        },
        
        
        //Gestion d'erreurs
        handleError(error, context = 'Unknown') {
            console.error(`[Fashion Shop Error - ${context}]`, error);
            this.showNotification('Une erreur est survenue. Veuillez réessayer.', 'error');
        },
        
        
         //Afficher une notification toast
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fs-notification fs-notification-${type}`;
            notification.textContent = message;
            
            const styles = {
                position: 'fixed',
                bottom: '20px',
                right: '20px',
                padding: '1rem 1.5rem',
                borderRadius: '8px',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                zIndex: '10000',
                maxWidth: '400px',
                opacity: '0',
                transform: 'translateY(20px)',
                transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)',
                fontWeight: '500',
                fontSize: '0.95rem'
            };
            
            const colors = {
                success: { bg: '#28a745', color: '#fff' },
                error: { bg: '#dc3545', color: '#fff' },
                warning: { bg: '#ffc107', color: '#000' },
                info: { bg: '#667eea', color: '#fff' }
            };
            
            Object.assign(notification.style, styles, {
                backgroundColor: colors[type]?.bg || colors.info.bg,
                color: colors[type]?.color || colors.info.color
            });
            
            document.body.appendChild(notification);
            
            // Animation d'entrée
            requestAnimationFrame(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateY(0)';
            });
            
            // Auto-suppression
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateY(20px)';
                setTimeout(() => notification.remove(), CONFIG.animationDuration);
            }, CONFIG.notificationDuration);
        }
    };
    
    // VALIDATION DES FORMULAIRES
    
    
    const FormValidator = {
        
        
         //Valider le formulaire d'inscription
         
        validateRegister(form) {
            try {
                const fields = {
                    nom: form.querySelector('#nom'),
                    prenom: form.querySelector('#prenom'),
                    email: form.querySelector('#email'),
                    password: form.querySelector('#password'),
                    passwordConfirm: form.querySelector('#password_confirm')
                };
                
                // Validation du nom
                if (!this._validateField(fields.nom, 'nom', value => value.length >= CONFIG.minNameLength, 
                    `Le nom doit contenir au moins ${CONFIG.minNameLength} caractères.`)) {
                    return false;
                }
                
                // Validation du prénom
                if (!this._validateField(fields.prenom, 'prénom', value => value.length >= CONFIG.minNameLength,
                    `Le prénom doit contenir au moins ${CONFIG.minNameLength} caractères.`)) {
                    return false;
                }
                
                // Validation de l'email
                if (!this._validateField(fields.email, 'email', Utils.isValidEmail,
                    'Veuillez entrer une adresse email valide.')) {
                    return false;
                }
                
                // Validation du mot de passe
                if (!this._validateField(fields.password, 'mot de passe', value => value.length >= CONFIG.minPasswordLength,
                    `Le mot de passe doit contenir au moins ${CONFIG.minPasswordLength} caractères.`)) {
                    return false;
                }
                
                // Validation de la correspondance des mots de passe
                if (fields.password && fields.passwordConfirm) {
                    if (fields.password.value !== fields.passwordConfirm.value) {
                        this._showFieldError(fields.passwordConfirm, 'Les mots de passe ne correspondent pas.');
                        return false;
                    }
                }
                
                return true;
            } catch (error) {
                Utils.handleError(error, 'FormValidator.validateRegister');
                return false;
            }
        },
        
        // Valider le formulaire de connexion

        validateLogin(form) {
            try {
                const email = form.querySelector('#email');
                const password = form.querySelector('#password');
                
                if (!this._validateField(email, 'email', value => value.trim().length > 0,
                    'Veuillez entrer votre email.')) {
                    return false;
                }
                
                if (!this._validateField(email, 'email', Utils.isValidEmail,
                    'Veuillez entrer une adresse email valide.')) {
                    return false;
                }
                
                if (!this._validateField(password, 'mot de passe', value => value.length > 0,
                    'Veuillez entrer votre mot de passe.')) {
                    return false;
                }
                
                return true;
            } catch (error) {
                Utils.handleError(error, 'FormValidator.validateLogin');
                return false;
            }
        },
        
        
         //Valider le formulaire de produit (admin)
        validateProduct(form) {
            try {
                const nom = form.querySelector('#nom');
                const description = form.querySelector('#description');
                const prix = form.querySelector('#prix');
                const quantite = form.querySelector('#quantite_stock');
                const categorie = form.querySelector('#categorie');
                
                if (!this._validateField(nom, 'nom du produit', value => value.length >= 3,
                    'Le nom du produit doit contenir au moins 3 caractères.')) {
                    return false;
                }
                
                if (description && !this._validateField(description, 'description', 
                    value => value.length >= CONFIG.minDescriptionLength,
                    `La description doit contenir au moins ${CONFIG.minDescriptionLength} caractères.`)) {
                    return false;
                }
                
                if (!this._validateField(prix, 'prix', value => parseFloat(value) > 0,
                    'Le prix doit être supérieur à 0.')) {
                    return false;
                }
                
                if (quantite && !this._validateField(quantite, 'quantité', value => parseInt(value) >= 0,
                    'La quantité ne peut pas être négative.')) {
                    return false;
                }
                
                if (categorie && !this._validateField(categorie, 'catégorie', value => value !== '',
                    'Veuillez sélectionner une catégorie.')) {
                    return false;
                }
                
                return true;
            } catch (error) {
                Utils.handleError(error, 'FormValidator.validateProduct');
                return false;
            }
        },
        
        
        //Méthode interne de validation de champ
        _validateField(field, fieldName, validator, errorMessage) {
            if (!field) return true;
            
            const value = field.value.trim();
            
            if (!validator(value)) {
                this._showFieldError(field, errorMessage);
                return false;
            }
            
            this._clearFieldError(field);
            return true;
        },
        
        /**
         * Afficher une erreur sur un champ
         */
        _showFieldError(field, message) {
            field.classList.add('is-invalid');
            field.focus();
            
            // Supprimer l'ancien message d'erreur
            const oldError = field.parentNode.querySelector('.field-error');
            if (oldError) oldError.remove();
            
            // Créer le nouveau message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            errorDiv.style.cssText = 'color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem;';
            
            field.parentNode.insertBefore(errorDiv, field.nextSibling);
            
            Utils.showNotification(message, 'error');
        },
        
        /**
         * Effacer l'erreur d'un champ
         */
        _clearFieldError(field) {
            field.classList.remove('is-invalid');
            const error = field.parentNode.querySelector('.field-error');
            if (error) error.remove();
        }
    };
    
    // ========================================================================
    // GESTION DU PANIER
    // ========================================================================
    
    const CartManager = {
        
        /**
         * Initialiser la gestion du panier
         */
        init() {
            this._bindQuantityInputs();
            this._bindRemoveButtons();
            this._initAjaxCart(); // ✅ NOUVEAU : Ajout au panier en AJAX
            this.updateTotal();
        },
        
        /**
         * ✅ NOUVEAU : Initialiser l'ajout au panier en AJAX (sans redirection)
         */
        _initAjaxCart() {
            const addToCartButtons = document.querySelectorAll('.add-to-cart-async');
            
            if (addToCartButtons.length === 0) {
                Utils.log('No AJAX cart buttons found');
                return;
            }
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault(); // ✅ EMPÊCHE LA REDIRECTION
                    
                    const productId = button.getAttribute('data-id');
                    const originalText = button.innerHTML;
                    
                    // Désactiver le bouton pendant l'envoi
                    button.disabled = true;
                    button.innerHTML = '⏳ Ajout...';
                    button.style.opacity = '0.6';
                    
                    // Créer les données du formulaire
                    const formData = new FormData();
                    formData.append('action', 'add');
                    formData.append('id_article', productId);
                    formData.append('quantite', 1);
                    
                    // Envoyer la requête AJAX
                    fetch('/ecommerce/pages/cart-actions.php', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // ✅ SUCCÈS
                            button.innerHTML = '✅ Ajouté !';
                            button.style.background = '#28a745';
                            button.style.opacity = '1';
                            
                            // Mettre à jour le badge du panier
                            this._updateCartBadge(data.cart_count);
                            
                            // Afficher une notification
                            Utils.showNotification('✅ Article ajouté au panier !', 'success');
                            
                            // Réinitialiser le bouton après 2 secondes
                            setTimeout(() => {
                                button.disabled = false;
                                button.innerHTML = originalText;
                                button.style.background = '';
                            }, 2000);
                            
                            Utils.log('Product added to cart:', { productId, cart_count: data.cart_count });
                        } else {
                            // ❌ ERREUR
                            button.innerHTML = '❌ Erreur';
                            button.style.opacity = '1';
                            Utils.showNotification('❌ ' + (data.message || 'Erreur lors de l\'ajout'), 'error');
                            
                            setTimeout(() => {
                                button.disabled = false;
                                button.innerHTML = originalText;
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX:', error);
                        button.innerHTML = '❌ Erreur';
                        button.style.opacity = '1';
                        Utils.showNotification('❌ Erreur de connexion', 'error');
                        
                        setTimeout(() => {
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }, 2000);
                    });
                });
            });
            
            Utils.log('AJAX cart initialized:', addToCartButtons.length, 'buttons');
        },
        
        /**
         * ✅ NOUVEAU : Mettre à jour le badge du panier dans le header
         */
        _updateCartBadge(count) {
            const cartBadge = document.querySelector('.cart-badge');
            
            if (cartBadge) {
                // Badge existe, le mettre à jour
                cartBadge.textContent = count;
                
                // Animation de pulse
                cartBadge.style.animation = 'none';
                setTimeout(() => {
                    cartBadge.style.animation = 'pulse 0.3s ease';
                }, 10);
            } else if (count > 0) {
                // Badge n'existe pas, le créer
                const cartLink = document.querySelector('a[href*="cart.php"]');
                if (cartLink) {
                    const badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    badge.textContent = count;
                    cartLink.appendChild(badge);
                    
                    // Animation d'apparition
                    badge.style.opacity = '0';
                    badge.style.transform = 'scale(0)';
                    setTimeout(() => {
                        badge.style.transition = 'all 0.3s ease';
                        badge.style.opacity = '1';
                        badge.style.transform = 'scale(1)';
                    }, 10);
                }
            }
            
            Utils.log('Cart badge updated:', count);
        },
        
        /**
         * Lier les événements aux inputs de quantité
         */
        _bindQuantityInputs() {
            const inputs = document.querySelectorAll('input[name="quantity"]');
            
            inputs.forEach(input => {
                // Validation en temps réel
                input.addEventListener('input', Utils.debounce((e) => {
                    this._validateQuantity(e.target);
                }, 300));
                
                // Mise à jour au changement
                input.addEventListener('change', (e) => {
                    this._updateLineTotal(e.target);
                    this.updateTotal();
                });
                
                // Prévenir les valeurs négatives
                input.addEventListener('keypress', (e) => {
                    if (e.key === '-' || e.key === '+' || e.key === 'e') {
                        e.preventDefault();
                    }
                });
            });
        },
        
        /**
         * Valider la quantité
         */
        _validateQuantity(input) {
            let value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max')) || 999;
            const min = parseInt(input.getAttribute('min')) || 1;
            
            if (isNaN(value) || value < min) {
                input.value = min;
                Utils.showNotification(`La quantité minimale est ${min}.`, 'warning');
            } else if (value > max) {
                input.value = max;
                Utils.showNotification(`Stock maximum atteint : ${max} article(s).`, 'warning');
            }
        },
        
        /**
         * Mettre à jour le total d'une ligne
         */
        _updateLineTotal(input) {
            try {
                const row = input.closest('tr');
                if (!row) return;
                
                const priceElement = row.querySelector('.unit-price, [data-price]');
                const subtotalElement = row.querySelector('.subtotal');
                
                if (!priceElement || !subtotalElement) return;
                
                const price = Utils.parsePrice(
                    priceElement.dataset.price || priceElement.textContent
                );
                const quantity = parseInt(input.value) || 0;
                const subtotal = price * quantity;
                
                subtotalElement.textContent = Utils.formatPrice(subtotal) + ' €';
                subtotalElement.dataset.subtotal = subtotal;
                
                Utils.log('Line total updated:', { price, quantity, subtotal });
            } catch (error) {
                Utils.handleError(error, 'CartManager._updateLineTotal');
            }
        },
        
        /**
         * Mettre à jour le total du panier
         */
        updateTotal() {
            try {
                const subtotalElements = document.querySelectorAll('.subtotal');
                let total = 0;
                
                subtotalElements.forEach(element => {
                    const value = parseFloat(element.dataset.subtotal) || 
                                  Utils.parsePrice(element.textContent);
                    total += value;
                });
                
                const totalElement = document.querySelector('.cart-total-amount, .cart-total h3');
                if (totalElement) {
                    const formattedTotal = Utils.formatPrice(total) + ' €';
                    
                    if (totalElement.tagName === 'H3') {
                        totalElement.textContent = `Total: ${formattedTotal}`;
                    } else {
                        totalElement.textContent = formattedTotal;
                    }
                    
                    totalElement.dataset.total = total;
                }
                
                Utils.log('Cart total updated:', total);
            } catch (error) {
                Utils.handleError(error, 'CartManager.updateTotal');
            }
        },
        
        /**
         * Lier les boutons de suppression
         */
        _bindRemoveButtons() {
            const removeButtons = document.querySelectorAll('.remove-from-cart, a[href*="remove-from-cart"]');
            
            removeButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    const confirmed = confirm('Voulez-vous vraiment retirer cet article du panier ?');
                    if (!confirmed) {
                        e.preventDefault();
                    }
                });
            });
        }
    };
    
    // ========================================================================
    // RECHERCHE ET FILTRAGE
    // ========================================================================
    
    const SearchFilter = {
        
        /**
         * Initialiser la recherche
         */
        init() {
            const searchInput = document.querySelector('#search-products, .search-input');
            if (!searchInput) return;
            
            searchInput.addEventListener('input', Utils.debounce((e) => {
                this.filterProducts(e.target.value);
            }, CONFIG.debounceDelay));
            
            Utils.log('Search initialized');
        },
        
        /**
         * Filtrer les produits
         */
        filterProducts(searchTerm) {
            try {
                const term = searchTerm.toLowerCase().trim();
                const products = document.querySelectorAll('.card, .product-card');
                
                if (term.length < CONFIG.searchMinChars && term.length > 0) {
                    return;
                }
                
                let visibleCount = 0;
                
                products.forEach(product => {
                    const title = product.querySelector('.card-title, .product-title');
                    const description = product.querySelector('.card-text, .product-description');
                    const category = product.dataset.category;
                    
                    if (!title) return;
                    
                    const titleText = title.textContent.toLowerCase();
                    const descText = description ? description.textContent.toLowerCase() : '';
                    const categoryText = category ? category.toLowerCase() : '';
                    
                    const isVisible = !term || 
                        titleText.includes(term) || 
                        descText.includes(term) ||
                        categoryText.includes(term);
                    
                    const container = product.closest('.product-item') || product.parentElement;
                    
                    if (isVisible) {
                        container.style.display = '';
                        product.style.opacity = '0';
                        requestAnimationFrame(() => {
                            product.style.transition = 'opacity 0.3s ease';
                            product.style.opacity = '1';
                        });
                        visibleCount++;
                    } else {
                        container.style.display = 'none';
                    }
                });
                
                // Afficher un message si aucun résultat
                this._toggleNoResults(visibleCount === 0 && term.length > 0);
                
                Utils.log('Products filtered:', { term, visibleCount });
            } catch (error) {
                Utils.handleError(error, 'SearchFilter.filterProducts');
            }
        },
        
        /**
         * Afficher/masquer le message "Aucun résultat"
         */
        _toggleNoResults(show) {
            let noResultsMsg = document.querySelector('.no-results-message');
            
            if (show) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results-message';
                    noResultsMsg.innerHTML = `
                        <div style="text-align: center; padding: 3rem; color: #666;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                            <h3>Aucun produit trouvé</h3>
                            <p>Essayez avec d'autres mots-clés</p>
                        </div>
                    `;
                    
                    const grid = document.querySelector('.products-grid');
                    if (grid) grid.parentNode.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        }
    };
    
    // ========================================================================
    // GESTION DES ALERTES
    // ========================================================================
    
    const AlertManager = {
        
        /**
         * Initialiser la gestion des alertes
         */
        init() {
            const alerts = document.querySelectorAll('.alert');
            
            alerts.forEach(alert => {
                this._addCloseButton(alert);
                this._makeInteractive(alert);
            });
            
            Utils.log('Alerts initialized:', alerts.length);
        },
        
        /**
         * Ajouter un bouton de fermeture
         */
        _addCloseButton(alert) {
            // Vérifier si le bouton existe déjà
            if (alert.querySelector('.alert-close')) return;
            
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.setAttribute('aria-label', 'Fermer');
            closeBtn.style.cssText = `
                position: absolute;
                top: 50%;
                right: 1rem;
                transform: translateY(-50%);
                background: none;
                border: none;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
                opacity: 0.7;
                transition: opacity 0.2s;
                padding: 0;
                width: 1.5rem;
                height: 1.5rem;
            `;
            
            closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
            closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');
            closeBtn.addEventListener('click', () => this._closeAlert(alert));
            
            alert.style.position = 'relative';
            alert.style.paddingRight = '3rem';
            alert.appendChild(closeBtn);
        },
        
        /**
         * Fermer une alerte avec animation
         */
        _closeAlert(alert) {
            alert.style.transition = 'all 0.3s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            
            setTimeout(() => {
                alert.style.maxHeight = '0';
                alert.style.padding = '0';
                alert.style.margin = '0';
                alert.style.overflow = 'hidden';
                
                setTimeout(() => alert.remove(), CONFIG.animationDuration);
            }, CONFIG.animationDuration);
        },
        
        /**
         * Rendre l'alerte interactive
         */
        _makeInteractive(alert) {
            alert.style.cursor = 'default';
            alert.style.userSelect = 'text';
        }
    };
    
    // ========================================================================
    // GESTION DES IMAGES
    // ========================================================================
    
    const ImageManager = {
        
        /**
         * Initialiser la gestion des images
         */
        init() {
            this._handleImageErrors();
            this._initLazyLoading();
            this._initImagePreview();
        },
        
        /**
         * Gérer les erreurs de chargement d'images
         */
        _handleImageErrors() {
            const images = document.querySelectorAll('img');
            
            images.forEach(img => {
                if (img.dataset.errorHandled) return;
                
                img.addEventListener('error', function() {
                    if (!this.dataset.errorHandled) {
                        this.src = this.dataset.fallback || '/ecommerce/assets/images/placeholder.jpg';
                        this.alt = 'Image non disponible';
                        this.dataset.errorHandled = 'true';
                        Utils.log('Image fallback loaded:', this.src);
                    }
                });
            });
        },
        
        /**
         * Lazy loading des images
         */
        _initLazyLoading() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                                observer.unobserve(img);
                            }
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },
        
        /**
         * Prévisualisation d'image avant upload
         */
        _initImagePreview() {
            const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    
                    // Validation du type
                    if (!file.type.startsWith('image/')) {
                        Utils.showNotification('Veuillez sélectionner une image valide.', 'error');
                        input.value = '';
                        return;
                    }
                    
                    // Validation de la taille (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        Utils.showNotification('L\'image ne doit pas dépasser 5 Mo.', 'error');
                        input.value = '';
                        return;
                    }
                    
                    this._previewImage(file, input);
                });
            });
        },
        
        /**
         * Afficher l'aperçu de l'image
         */
        _previewImage(file, input) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                let preview = document.getElementById('image-preview');
                
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'image-preview';
                    preview.style.cssText = `
                        max-width: 100%;
                        max-height: 300px;
                        margin-top: 1rem;
                        border-radius: 8px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        display: block;
                    `;
                    input.parentNode.appendChild(preview);
                }
                
                preview.src = e.target.result;
                preview.style.opacity = '0';
                
                requestAnimationFrame(() => {
                    preview.style.transition = 'opacity 0.3s ease';
                    preview.style.opacity = '1';
                });
                
                Utils.log('Image preview loaded');
            };
            
            reader.onerror = () => {
                Utils.handleError(new Error('Failed to read file'), 'ImageManager._previewImage');
            };
            
            reader.readAsDataURL(file);
        }
    };
    
    // ========================================================================
    // UI ENHANCEMENTS
    // ========================================================================
    
    const UIEnhancements = {
        
        /**
         * Initialiser les améliorations UI
         */
        init() {
            this._initSmoothScroll();
            this._initCharacterCounters();
            this._initTooltips();
            this._initConfirmations();
            this._addLoadingStates();
        },
        
        /**
         * Smooth scroll pour les ancres
         */
        _initSmoothScroll() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const target = document.querySelector(targetId);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        },
        
        /**
         * Compteurs de caractères pour textarea
         */
        _initCharacterCounters() {
            document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
                const maxLength = parseInt(textarea.getAttribute('maxlength'));
                
                const counter = document.createElement('div');
                counter.className = 'char-counter';
                counter.style.cssText = `
                    text-align: right;
                    font-size: 0.875rem;
                    margin-top: 0.25rem;
                    color: #666;
                    transition: color 0.2s;
                `;
                
                const updateCounter = () => {
                    const remaining = maxLength - textarea.value.length;
                    counter.textContent = `${remaining} caractères restants`;
                    
                    if (remaining < 20) {
                        counter.style.color = '#dc3545';
                        counter.style.fontWeight = 'bold';
                    } else if (remaining < 50) {
                        counter.style.color = '#ffc107';
                        counter.style.fontWeight = 'normal';
                    } else {
                        counter.style.color = '#666';
                        counter.style.fontWeight = 'normal';
                    }
                };
                
                textarea.addEventListener('input', updateCounter);
                textarea.parentNode.appendChild(counter);
                updateCounter();
            });
        },
        
        /**
         * Tooltips simples
         */
        _initTooltips() {
            document.querySelectorAll('[data-tooltip]').forEach(element => {
                element.style.position = 'relative';
                element.style.cursor = 'help';
                
                element.addEventListener('mouseenter', function() {
                    const tooltip = document.createElement('div');
                    tooltip.className = 'fs-tooltip';
                    tooltip.textContent = this.dataset.tooltip;
                    tooltip.style.cssText = `
                        position: absolute;
                        bottom: 100%;
                        left: 50%;
                        transform: translateX(-50%);
                        background: #333;
                        color: white;
                        padding: 0.5rem 0.75rem;
                        border-radius: 4px;
                        font-size: 0.875rem;
                        white-space: nowrap;
                        margin-bottom: 0.5rem;
                        z-index: 1000;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(tooltip);
                });
                
                element.addEventListener('mouseleave', function() {
                    const tooltip = this.querySelector('.fs-tooltip');
                    if (tooltip) tooltip.remove();
                });
            });
        },
        
        /**
         * Confirmations de suppression
         */
        _initConfirmations() {
            // Suppressions
            document.querySelectorAll('a[href*="delete"], .btn-danger[href]').forEach(link => {
                if (link.dataset.confirmBound) return;
                
                link.addEventListener('click', function(e) {
                    const itemName = this.dataset.itemName || 'cet élément';
                    const message = `Êtes-vous sûr de vouloir supprimer ${itemName} ?\n\nCette action est irréversible.`;
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                        return false;
                    }
                });
                
                link.dataset.confirmBound = 'true';
            });
            
            // Vider le panier
            document.querySelectorAll('a[href*="clear-cart"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('Voulez-vous vraiment vider votre panier ?\n\nTous les articles seront retirés.')) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
        },
        
        /**
         * États de chargement pour les formulaires
         */
        _addLoadingStates() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        const originalText = submitBtn.textContent || submitBtn.value;
                        submitBtn.disabled = true;
                        submitBtn.style.opacity = '0.6';
                        submitBtn.style.cursor = 'wait';
                        
                        if (submitBtn.tagName === 'BUTTON') {
                            submitBtn.innerHTML = '<span>⏳ Chargement...</span>';
                        } else {
                            submitBtn.value = 'Chargement...';
                        }
                        
                        // Réactiver après 10 secondes (sécurité)
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.style.opacity = '1';
                            submitBtn.style.cursor = 'pointer';
                            if (submitBtn.tagName === 'BUTTON') {
                                submitBtn.textContent = originalText;
                            } else {
                                submitBtn.value = originalText;
                            }
                        }, 10000);
                    }
                });
            });
        }
    };
    
    // ========================================================================
    // INITIALISATION PRINCIPALE
    // ========================================================================
    
    function init() {
        try {
            Utils.log('Initializing Fashion Shop...');
            
            // Initialiser tous les modules
            FormValidator;
            CartManager.init();
            SearchFilter.init();
            AlertManager.init();
            ImageManager.init();
            UIEnhancements.init();
            
            // Lier les validations de formulaires
            const registerForm = document.querySelector('form[action*="register"]');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    if (!FormValidator.validateRegister(this)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
            
            const loginForm = document.querySelector('form[action*="login"]');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    if (!FormValidator.validateLogin(this)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
            
            const productForm = document.querySelector('form[action*="product-add"], form[action*="product-edit"]');
            if (productForm) {
                productForm.addEventListener('submit', function(e) {
                    if (!FormValidator.validateProduct(this)) {
                        e.preventDefault();
                        return false;
                    }
                });
            }
            
            Utils.log('Fashion Shop initialized successfully! ✅');
            console.log('%c🛍️ Fashion Shop Ready! (v2.1.0 - AJAX Cart Enabled)', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 5px;');
            
        } catch (error) {
            Utils.handleError(error, 'init');
        }
    }
    
    // ========================================================================
    // API PUBLIQUE
    // ========================================================================
    
    return {
        init,
        Utils,
        FormValidator,
        CartManager,
        SearchFilter,
        AlertManager,
        ImageManager,
        UIEnhancements,
        version: '2.1.0'
    };
    
})();

// ============================================================================
// AUTO-INITIALISATION
// ============================================================================

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', FashionShop.init);
} else {
    FashionShop.init();
}

// Export global pour compatibilité
window.FashionShop = FashionShop;