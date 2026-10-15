<?php
// Configuration des erreurs
error_reporting(0); // Pas d'affichage d'erreurs PHP
ini_set('display_errors', 0);

// Toujours renvoyer du JSON
header('Content-Type: application/json; charset=utf-8');

// Fonction de réponse JSON
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Fonction de réponse d'erreur
function jsonError($message, $code = 400) {
    jsonResponse(['error' => $message], $code);
}

try {
    // Connexion BDD
    $db = new PDO('mysql:host=localhost;dbname=auto_ecole;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $action = $_GET['action'] ?? '';
    
    // ========================================
    // ACTION 1 : RECHERCHE
    // ========================================
    if ($action === 'search') {
        $terme = trim($_GET['q'] ?? '');
        
        if (strlen($terme) < 2) {
            jsonResponse([]);
        }
        
        $terme_like = '%' . $terme . '%';
        
        $stmt = $db->prepare("SELECT id, nom, prenom, email, telephone, type_permis 
                              FROM utilisateurs 
                              WHERE role = 'apprenant' 
                              AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)
                              ORDER BY nom ASC
                              LIMIT 20");
        $stmt->execute([$terme_like, $terme_like, $terme_like, $terme_like]);
        $apprenants = $stmt->fetchAll();
        
        // Ajouter les infos financières
        foreach ($apprenants as &$a) {
            $a['total_formation'] = getTarif($db, $a['type_permis']);
            $a['deja_paye'] = getTotalPaye($db, $a['id']);
            $a['restant'] = max(0, $a['total_formation'] - $a['deja_paye']);
        }
        
        jsonResponse($apprenants);
    }
    
    // ========================================
    // ACTION 2 : DÉTAILS
    // ========================================
    if ($action === 'details') {
        $id = (int) ($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            jsonError('ID invalide');
        }
        
        $stmt = $db->prepare("SELECT id, nom, prenom, email, telephone, type_permis 
                              FROM utilisateurs 
                              WHERE id = ? AND role = 'apprenant'");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            jsonError('Apprenant introuvable', 404);
        }
        
        $total_formation = getTarif($db, $user['type_permis']);
        $deja_paye = getTotalPaye($db, $id);
        
        $libelles = [
            'code' => '📘 Code de la route',
            'B' => '🚗 Permis B (Voiture)',
            'A1' => '🏍️ Permis A1 (Petite moto)',
            'A2' => '🏍️ Permis A2 (Moto)',
            'A' => '🏍️ Permis A (Grosse moto)',
            'C' => '🚚 Permis C (Poids lourd)',
            'D' => '🚌 Permis D (Bus)',
            'perfectionnement' => '🔄 Perfectionnement',
        ];
        
        jsonResponse([
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'telephone' => $user['telephone'],
            'type_permis' => $user['type_permis'],
            'libelle_permis' => $libelles[$user['type_permis']] ?? $user['type_permis'],
            'total_formation' => $total_formation,
            'deja_paye' => $deja_paye,
            'restant' => max(0, $total_formation - $deja_paye),
        ]);
    }
    
    jsonError('Action invalide');
    
} catch (Exception $e) {
    jsonError('Erreur serveur : ' . $e->getMessage(), 500);
}

// ========================================
// FONCTIONS UTILITAIRES
// ========================================

function getTarif($db, $type) {
    // Tarifs par défaut (fallback)
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
    
    try {
        $stmt = $db->prepare("SELECT prix FROM tarifs_permis WHERE type_permis = ? AND actif = 1 LIMIT 1");
        $stmt->execute([$type]);
        $prix = $stmt->fetchColumn();
        
        if ($prix !== false && $prix !== null) {
            return (int) $prix;
        }
    } catch (Exception $e) {
        // Table n'existe pas, on utilise les tarifs par défaut
    }
    
    return $default[$type] ?? 0;
}

function getTotalPaye($db, $userId) {
    try {
        $stmt = $db->prepare("SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE user_id = ? AND statut = 'valide'");
        $stmt->execute([$userId]);
        return (float) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}