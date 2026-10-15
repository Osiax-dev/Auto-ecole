USE auto_ecole;

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