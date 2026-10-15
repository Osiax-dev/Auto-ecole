-- ========================================
-- BASE DE DONNÉES AUTO-ÉCOLE (UNIFIÉE)
-- ========================================

CREATE DATABASE IF NOT EXISTS auto_ecole 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE auto_ecole;

-- ========================================
-- TABLE : utilisateurs (remplace l'ancienne table 'info')
-- ========================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(20) NOT NULL,
    mdp VARCHAR(255) NOT NULL,
    type_permis VARCHAR(20) DEFAULT 'B',
    role ENUM('apprenant', 'moniteur', 'secretaire', 'admin') DEFAULT 'apprenant',
    statut ENUM('en_attente', 'valide', 'rejete') DEFAULT 'en_attente',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ========================================
-- TABLE : documents (remplace l'ancienne table 'img')
-- ========================================
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_cip VARCHAR(255) DEFAULT NULL,
    image_visite_medical VARCHAR(255) DEFAULT NULL,
    image_groupe_sanguin VARCHAR(255) DEFAULT NULL,
    statut_cip ENUM('non_soumis', 'en_attente', 'valide', 'rejete') DEFAULT 'non_soumis',
    statut_visite ENUM('non_soumis', 'en_attente', 'valide', 'rejete') DEFAULT 'non_soumis',
    statut_groupe ENUM('non_soumis', 'en_attente', 'valide', 'rejete') DEFAULT 'non_soumis',
    date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================================
-- TABLE : paiements (remplace l'ancienne table 'payemant')
-- ========================================
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    restant DECIMAL(10, 2) NOT NULL,
    total_formation DECIMAL(10, 2) NOT NULL,
    methode_paiement ENUM('mobile_money', 'especes', 'virement') DEFAULT 'especes',
    statut ENUM('en_attente', 'valide', 'echoue') DEFAULT 'valide',
    reference_transaction VARCHAR(100) DEFAULT NULL,
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================================
-- TABLE : cours (NOUVELLE)
-- ========================================
CREATE TABLE IF NOT EXISTS cours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    fichier VARCHAR(255) NOT NULL,
    categorie ENUM('code', 'conduite', 'moto', 'poids_lourd', 'bus') DEFAULT 'code',
    statut ENUM('brouillon', 'publie') DEFAULT 'publie',
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- TABLE : notifications (NOUVELLE)
-- ========================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    lu TINYINT(1) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS tarifs_permis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_permis VARCHAR(20) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    prix INT NOT NULL,
    actif TINYINT(1) DEFAULT 1,
    date_maj DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insertion des tarifs par défaut
INSERT INTO tarifs_permis (type_permis, libelle, prix) VALUES
('code', '📘 Code de la route', 50000),
('B', '🚗 Permis B (Voiture)', 150000),
('A1', '🏍️ Permis A1 (Petite moto)', 100000),
('A2', '🏍️ Permis A2 (Moto)', 120000),
('A', '🏍️ Permis A (Grosse moto)', 150000),
('C', '🚚 Permis C (Poids lourd)', 350000),
('D', '🚌 Permis D (Bus)', 400000),
('perfectionnement', '🔄 Perfectionnement', 80000);