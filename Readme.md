# 🛍️ Fashion Shop - Site E-commerce en PHP

Site e-commerce de vêtements et accessoires développé en PHP avec MySQL.

# 📝 Description du Projet

Fashion Shop est une application web e-commerce complète développée en PHP. Ce projet simule une boutique en ligne fonctionnelle permettant aux utilisateurs de parcourir un catalogue, de gérer un panier et de passer des commandes, tout en offrant aux administrateurs une interface de gestion robuste (Back-office).

# 🚀 Fonctionnalités Clés

  # 👤 Front-Office (Expérience Client)

    Accueil Dynamique : Présentation visuelle de la boutique et des produits phares.

    Catalogue Interactif : Consultation de la liste des articles avec fiches détaillées (images, descriptions, prix).

    Qui sommes-nous ? : Page de présentation du concept et de l'équipe.

    Système d'Authentification : Inscription et connexion sécurisées des utilisateurs.

    Gestion du Panier : Ajout, modification de quantité et suppression d'articles avec calcul du total en temps réel.

 # 🔐 Back-Office (Administration)

    Tableau de Bord : Vue d'ensemble et statistiques simplifiées de la boutique.

    Gestion CRUD Produits : Interface complète pour ajouter, modifier, lister et supprimer des articles (incluant la gestion des images et du stock).

    Gestion des Utilisateurs & Rôles : Visualisation des inscrits, suppression de comptes et gestion des droits d'accès.

    Suivi des Commandes : Gestion du cycle de vie des commandes (modification des statuts, suppression).

   ## 🛠️ Stack Technique

    Backend : PHP 7.4+ (Architecture modulaire).

    Base de Données : MySQL (Modèle Relationnel avec 5 tables principales).

    Frontend : HTML5, CSS3 (Design responsive), JavaScript.

    Serveur Local : Environnement XAMPP 

## 🚀 Installation

### Prérequis

1. **XAMPP** installé sur votre machine
   - Télécharger : [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Version recommandée : XAMPP 8.0 ou supérieure

### Étapes d'installation

#### 1. Cloner le projet

```bash
git clone https://github.com/abdulazizo2001/Ecommerce.git
```

#### 2. Placer le projet dans XAMPP

Copiez le dossier `ecommerce` dans le répertoire `htdocs` de XAMPP :
- **Windows** : `C:\xampp\htdocs\`
- **Mac** : `/Applications/XAMPP/htdocs/`
- **Linux** : `/opt/lampp/htdocs/`

#### 3. Démarrer les services XAMPP

Lancez XAMPP Control Panel et démarrez :
- ✅ Apache
- ✅ MySQL

#### 4. Créer la base de données

1. Ouvrez votre navigateur et accédez à phpMyAdmin :
   ```
   http://localhost/phpmyadmin
   ```

2. Créez une nouvelle base de données nommée `fashion_shop`

3. Importez le fichier SQL :
   - Cliquez sur la base `fashion_shop`
   - Allez dans l'onglet "Importer"
   - Sélectionnez le fichier `database/database.sql`
   - Cliquez sur "Exécuter"

#### 5. Configuration de la base de données

Le fichier de configuration est déjà paramétré pour XAMPP par défaut dans `config/database.php` :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fashion_shop');
define('DB_USER', 'root');
define('DB_PASS', ''); 
```

Si vous avez modifié votre configuration MySQL, ajustez ces valeurs.

#### 6. Accéder au site

Ouvrez votre navigateur et accédez à :
```
 http://localhost/ecommerce/
```

### Créer un compte utilisateur
Vous pouvez créer un nouveau compte utilisateur via la page d'inscription.

## 📁 Structure du Projet

```
ecommerce/
│
├── admin/                      # Back-office administration
│   ├── add_product.php        # Ajouter un produit
│   ├──index.php              # Page d'accueil admin
│   ├──order-delete.php       # Supprimer une commande
│   ├──order-edit.php         # Modifier une commande
│   ├──orders.php             # Gestion des commandes
│   ├──product delete.php     # Supprimer un produit
│   ├──product-edit.php       # Modifier un produit
│   ├──products.php           # Gestion des produits
│   ├──stock-delete.php       # Supprimer du stock
│   ├──stock-edit.php         # Modifier du stock
│   ├──stock.php              # Gestion du stock
│   ├──user-role.php           #gestion des rôles d'utilisateurs
│   ├──users.php              # Gestion des utilisateurs
│   └──user-delete.php        # Supprimer un utilisateur
│
├── assets/                     # Ressources statiques
│   ├── css/
│   │   └── style.css          # Feuille de style principale
│   └── images/                # Images des produits
|   ├── js/
|       └── script.js          # Fichier JavaScript principal
│ 
├── config/                     # Configuration
│   └── config.php            # Fichier de configuration
│   └── database.php           # Connexion à la base de données
│
├── database/                   # Base de données
│   └── database.sql           # Script SQL d'installation
│
├── includes/                   # Fichiers réutilisables
│   ├── header.php             # En-tête du site
│   └── footer.php             # Pied de page
│
├── pages/                      # Pages du site
│   ├── about.php              # Qui sommes-nous
│   ├──  add-to-cart.php       # Ajouter au panier
│   ├── articles.php           # Catalogue produits
│   ├── cart-actions.php       # Actions sur le panier
│   ├── cart.php               # Panier
│   ├── checkout.php           # Vérification de la commande
│   ├── clear-cart.php         # Vider le panier
│   ├── contact.php            # Contact
│   ├── login.php              # Connexion
│   ├── logout.php             # Déconnexion
│   ├── order-confirmation.php # Confirmation de la commande
│   ├── panier.php             # Panier
│   ├── privacy-policy.php     # Politique de confidentialité
│   ├── process-payment.php    # Traitement du paiement
│   ├── product-detail.php     # Détail d'un produit
│   ├── register.php           # Inscription
│   ├── remove-from-cart.php   # Retirer du panier
│   ├── update-cart.php        # Modifier le panier
│
├── .htaccess                   # Configuration Apache
├── index.php                   # Page d'accueil
└── README.md                   # Ce fichier
```

## 🗄️ Structure de la Base de Données

### Tables

1. **users** : Informations des utilisateurs
   - `id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `date_inscription`

2. **items** : Produits du catalogue
   - `id`, `nom`, `description`, `prix`, `categorie`, `image`, `date_publication`

3. **stock** : Quantités en stock
   - `id`, `id_item`, `quantite_stock`, `date_mise_a_jour`


4. **orders** : Commandes des utilisateurs
   - `id`, `id_user`, `id_item`,  `quantite`, `montant_total','statut','adresse_livraison','telephone','methode_paiement' prix_unitaire`, `date_commande`

5. **invoice** : Factures
   - `id`, `id_user`, `date_transaction`, `montant_total`, `adresse_facturation`, `ville`, `code_postal`, `statut`

## 🔒 Sécurité

Le projet implémente plusieurs mesures de sécurité :

- ✅ Hachage des mots de passe avec `password_hash()`
- ✅ Requêtes préparées PDO (protection contre les injections SQL)
- ✅ Validation des formulaires côté serveur et client
- ✅ Protection XSS avec `htmlspecialchars()`
- ✅ Vérification de l'email unique lors de l'inscription
- ✅ Gestion des sessions sécurisée
- ✅ Contrôle d'accès pour les pages admin



## 👥 Équipe de Développement

Projet académique réalisé dans le cadre d'un cours de développement web PHP/MySQL.


## 📄 Licence

Projet académique - Usage éducatif uniquement

---

**À très bientôt pour la présentation.  🚀**
