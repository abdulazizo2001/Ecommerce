
CREATE DATABASE IF NOT EXISTS fashion_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fashion_shop;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des produits (items)
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    categorie ENUM('vetements_homme', 'vetements_femme', 'accessoires', 'chaussures') NOT NULL,
    image VARCHAR(255),
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_categorie (categorie)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table de stock
CREATE TABLE IF NOT EXISTS stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_item INT NOT NULL,
    quantite_stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_item) REFERENCES items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_item (id_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des factures
CREATE TABLE IF NOT EXISTS invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    montant_total DECIMAL(10, 2) NOT NULL,
    adresse_facturation VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    statut ENUM('en_attente', 'payee', 'annulee') DEFAULT 'en_attente',
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (id_user),
    INDEX idx_date (date_transaction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_item INT NOT NULL,
    id_invoice INT,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (id_invoice) REFERENCES invoice(id) ON DELETE SET NULL,
    INDEX idx_user_item (id_user, id_item)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion d'un utilisateur administrateur par défaut
INSERT INTO users (nom, prenom, email, mot_de_passe, role) VALUES 
('Admin', 'Système', 'admin@fashion-shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insertion de produits 
INSERT INTO items (nom, description, prix, categorie, image) VALUES 
('T-shirt Basique Blanc', 'T-shirt en coton 100% biologique, coupe classique, disponible en plusieurs tailles', 24.99, 'vetements_homme', 'tshirt-blanc.jpg'),
('Jean Slim Bleu', 'Jean slim fit en denim stretch pour un confort optimal', 59.99, 'vetements_homme', 'jean-slim.jpg'),
('Robe d\'été Florale', 'Robe légère à motifs floraux, parfaite pour l\'été', 45.99, 'vetements_femme', 'robe-florale.jpg'),
('Veste en Cuir Noir', 'Veste en cuir véritable, style biker, doublure intérieure', 149.99, 'vetements_femme', 'veste-cuir.jpg'),
('Casquette Baseball', 'Casquette ajustable en coton, broderie logo', 19.99, 'accessoires', 'casquette.jpg'),
('Sac à Main Cuir', 'Sac à main en cuir synthétique avec bandoulière amovible', 79.99, 'accessoires', 'sac-main.jpg'),
('Baskets Running', 'Chaussures de sport respirantes avec semelle amortissante', 89.99, 'chaussures', 'baskets-running.jpg'),
('Bottines Chelsea', 'Bottines en daim avec élastiques latéraux', 99.99, 'chaussures', 'bottines-chelsea.jpg'),
('Pull Col Rond Gris', 'Pull en laine mérinos, doux et chaud', 54.99, 'vetements_homme', 'pull-gris.jpg'),
('Écharpe en Laine', 'Écharpe douce et chaude, parfaite pour l\'hiver', 29.99, 'accessoires', 'echarpe-laine.jpg');

Insertion du stock pour les produits

INSERT INTO stock (id_item, quantite_stock) VALUES 
(1, 50),
(2, 30),
(3, 25),
(4, 15),
(5, 100),
(6, 20),
(7, 40),
(8, 18),
(9, 35),
(10, 60);