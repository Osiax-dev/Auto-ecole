<?php
// ========================================
// TARIFS DES PERMIS (depuis la BDD)
// ========================================

/**
 * Récupère le tarif d'un type de permis depuis la BDD
 */
function getTarifPermis($type) {
    static $cache = [];
    
    if (isset($cache[$type])) {
        return $cache[$type];
    }
    
    try {
        $db = new PDO('mysql:host=localhost;dbname=auto_ecole;charset=utf8', 'root', '');
        $stmt = $db->prepare("SELECT prix FROM tarifs_permis WHERE type_permis = ? AND actif = 1");
        $stmt->execute([$type]);
        $prix = $stmt->fetchColumn();
        $cache[$type] = $prix !== false ? (int)$prix : 0;
        return $cache[$type];
    } catch (PDOException $e) {
        // Fallback : tarifs par défaut
        $default = [
            'code' => 50000,
            'B' => 150000,
            'A1' => 100000,
            'A2' => 120000,
            'A' => 150000,
            'C' => 350000,
            'D' => 400000,
            'perfectionnement' => 80000,
        ];
        return $default[$type] ?? 0;
    }
}

/**
 * Récupère tous les tarifs
 */
function getAllTarifs() {
    try {
        $db = new PDO('mysql:host=localhost;dbname=auto_ecole;charset=utf8', 'root', '');
        $stmt = $db->query("SELECT * FROM tarifs_permis WHERE actif = 1 ORDER BY prix ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Formate un prix
 */
function formaterPrix($montant) {
    return number_format($montant, 0, ',', ' ') . ' FCFA';
}