<?php
require_once __DIR__ . '/../config/init.php';

class Crud {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    // ========================================
    // 🔐 AUTHENTIFICATION
    // ========================================
    
    public function create($data) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }

            $hashedPassword = password_hash($data['mdp'], PASSWORD_BCRYPT);

            $stmt = $this->db->prepare("INSERT INTO utilisateurs (nom, prenom, email, telephone, mdp, type_permis, role, statut) 
                                        VALUES (?, ?, ?, ?, ?, ?, 'apprenant', 'en_attente')");
            $result = $stmt->execute([
                strtoupper($data['nom']),
                ucfirst($data['prenom']),
                $data['email'],
                $data['tel'],
                $hashedPassword,
                $data['type_permis']
            ]);

            return $result 
                ? ['success' => true, 'message' => 'Inscription réussie']
                : ['success' => false, 'message' => 'Erreur lors de l\'inscription'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    public function connexion($email, $mdp) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }

            if (!password_verify($mdp, $user['mdp'])) {
                return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['role'] = $user['role'];

            if (in_array($user['role'], ['admin', 'moniteur', 'secretaire'])) {
                return ['success' => true, 'redirect' => 'admin/' . $user['role'] . '.php'];
            }
            return ['success' => true, 'redirect' => 'profil.php'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ========================================
    // 👤 UTILISATEURS
    // ========================================
    
    public function selectById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function selectAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM utilisateurs WHERE role = 'apprenant' ORDER BY date_inscription DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllApprenants() {
        return $this->selectAll();
    }

    public function deleteApprenant($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM utilisateurs WHERE id = ? AND role = 'apprenant'");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updateApprenant($id, $data) {
        try {
            $stmt = $this->db->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, telephone = ?, type_permis = ?, statut = ? WHERE id = ? AND role = 'apprenant'");
            return $stmt->execute([
                $data['nom'],
                $data['prenom'],
                $data['email'],
                $data['telephone'],
                $data['type_permis'],
                $data['statut'],
                $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ========================================
    // 📄 DOCUMENTS (MONITEUR)
    // ========================================
    
    public function uploadDocuments($userId, $files) {
    try {
        // Créer le dossier s'il n'existe pas
        $uploadDir = __DIR__ . '/../uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (!is_writable($uploadDir)) {
            return ['success' => false, 'message' => 'Dossier non accessible en écriture'];
        }

        $images = [];
        $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $taille_max = 5 * 1024 * 1024;
        
        // Mapping des champs du formulaire vers les types
        $mapping = [
            'img1' => 'cip',
            'img2' => 'visite',
            'img3' => 'groupe'
        ];
        
        // Traiter chaque fichier
        foreach ($mapping as $key => $type) {
            if (isset($files[$key]) && $files[$key]['error'] === UPLOAD_ERR_OK) {
                $file = $files[$key];
                
                if ($file['size'] > $taille_max) {
                    return ['success' => false, 'message' => "Fichier $type trop volumineux"];
                }
                
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $extensions_autorisees)) {
                    return ['success' => false, 'message' => "Extension .$ext non autorisée"];
                }
                
                // Générer un nom unique
                $newName = $userId . '_' . time() . '_' . $key . '.' . $ext;
                $path = $uploadDir . $newName;
                
                if (!move_uploaded_file($file['tmp_name'], $path)) {
                    return ['success' => false, 'message' => "Erreur upload $type"];
                }
                
                $images[$type] = $newName;
            }
        }

        if (empty($images)) {
            return ['success' => false, 'message' => 'Aucun fichier à uploader'];
        }

        // Vérifier si l'utilisateur a déjà des documents
        $stmt = $this->db->prepare("SELECT id FROM documents WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch();

        if ($exists) {
            // ✅ UPDATE CORRIGÉ
            $setClauses = [];
            $params = [];
            
            // Construire les clauses SET et les paramètres
            if (isset($images['cip'])) {
                $setClauses[] = "image_cip = ?";
                $setClauses[] = "statut_cip = 'en_attente'";
                $params[] = $images['cip'];
            }
            if (isset($images['visite'])) {
                $setClauses[] = "image_visite_medical = ?";
                $setClauses[] = "statut_visite = 'en_attente'";
                $params[] = $images['visite'];
            }
            if (isset($images['groupe'])) {
                $setClauses[] = "image_groupe_sanguin = ?";
                $setClauses[] = "statut_groupe = 'en_attente'";
                $params[] = $images['groupe'];
            }
            
            $params[] = $userId;
            $sql = "UPDATE documents SET " . implode(', ', $setClauses) . " WHERE user_id = ?";
            
        } else {
            // ✅ INSERT CORRIGÉ
            $sql = "INSERT INTO documents (user_id, image_cip, image_visite_medical, image_groupe_sanguin, statut_cip, statut_visite, statut_groupe) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $userId,
                $images['cip'] ?? null,
                $images['visite'] ?? null,
                $images['groupe'] ?? null,
                isset($images['cip']) ? 'en_attente' : 'non_soumis',
                isset($images['visite']) ? 'en_attente' : 'non_soumis',
                isset($images['groupe']) ? 'en_attente' : 'non_soumis',
            ];
        }

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);
        
        return $result 
            ? ['success' => true, 'message' => count($images) . ' document(s) uploadé(s)']
            : ['success' => false, 'message' => 'Erreur BDD'];

    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
    }
}
    public function selectDocuments($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM documents WHERE user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getAllDocuments() {
        try {
            $stmt = $this->db->query("SELECT d.*, u.nom, u.prenom, u.email, u.type_permis
                                      FROM documents d 
                                      JOIN utilisateurs u ON d.user_id = u.id 
                                      ORDER BY d.date_upload DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getDocumentsByApprenant($userId) {
        try {
            $stmt = $this->db->prepare("SELECT d.*, u.nom, u.prenom, u.email 
                                        FROM documents d 
                                        JOIN utilisateurs u ON d.user_id = u.id 
                                        WHERE d.user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function validerDocument($docId, $type, $statut) {
        try {
            $colonnes_valides = ['cip', 'visite', 'groupe'];
            $statuts_valides = ['non_soumis', 'en_attente', 'valide', 'rejete'];
            
            if (!in_array($type, $colonnes_valides) || !in_array($statut, $statuts_valides)) {
                return false;
            }
            
            $colonne = 'statut_' . $type;
            $stmt = $this->db->prepare("UPDATE documents SET $colonne = ? WHERE id = ?");
            return $stmt->execute([$statut, $docId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ========================================
    // 💳 PAIEMENTS (SECRÉTAIRE + ADMIN)
    // ========================================
    
    public function selectPaiements($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM paiements WHERE user_id = ? ORDER BY date_paiement DESC");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllPaiements() {
        try {
            $stmt = $this->db->query("SELECT p.*, u.nom, u.prenom, u.email, u.type_permis 
                                      FROM paiements p 
                                      JOIN utilisateurs u ON p.user_id = u.id 
                                      ORDER BY p.date_paiement DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function addPaiement($data) {
        try {
            // Vérifier que l'apprenant existe
            $stmt = $this->db->prepare("SELECT id FROM utilisateurs WHERE id = ? AND role = 'apprenant'");
            $stmt->execute([$data['user_id']]);
            if (!$stmt->fetch()) {
                return ['success' => false, 'message' => 'Apprenant introuvable'];
            }

            $stmt = $this->db->prepare("INSERT INTO paiements (user_id, montant, restant, total_formation, methode_paiement, statut, reference_transaction, date_paiement) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['user_id'],
                $data['montant'],
                $data['restant'],
                $data['total_formation'],
                $data['methode_paiement'] ?? 'especes',
                $data['statut'] ?? 'valide',
                $data['reference'] ?? null,
                $data['date'] ?? date('Y-m-d H:i:s')
            ]);
            
            return $result 
                ? ['success' => true, 'message' => 'Paiement enregistré']
                : ['success' => false, 'message' => 'Erreur d\'enregistrement'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    public function deletePaiement($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM paiements WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ========================================
    // 📚 COURS (MONITEUR + ADMIN)
    // ========================================
    
    public function selectCours($categorie = null) {
        try {
            if ($categorie) {
                $stmt = $this->db->prepare("SELECT * FROM cours WHERE categorie = ? AND statut = 'publie' ORDER BY date_ajout DESC");
                $stmt->execute([$categorie]);
            } else {
                $stmt = $this->db->query("SELECT * FROM cours WHERE statut = 'publie' ORDER BY date_ajout DESC");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllCours() {
        try {
            $stmt = $this->db->query("SELECT * FROM cours ORDER BY date_ajout DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getCoursById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM cours WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function addCours($data, $file) {
        try {
            $uploadDir = UPLOAD_DIR . 'cours/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                return ['success' => false, 'message' => 'Seuls les fichiers PDF sont acceptés'];
            }
            
            $newName = uniqid() . '_' . time() . '.pdf';
            $path = $uploadDir . $newName;
            
            if (!move_uploaded_file($file['tmp_name'], $path)) {
                return ['success' => false, 'message' => 'Erreur lors de l\'upload du fichier'];
            }

            $stmt = $this->db->prepare("INSERT INTO cours (titre, description, fichier, categorie, statut) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $data['titre'],
                $data['description'] ?? '',
                $newName,
                $data['categorie'] ?? 'code',
                $data['statut'] ?? 'publie'
            ]);
            
            return $result 
                ? ['success' => true, 'message' => 'Cours ajouté']
                : ['success' => false, 'message' => 'Erreur BDD'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    public function updateCours($id, $data, $file = null) {
        try {
            $cours = $this->getCoursById($id);
            if (!$cours) {
                return ['success' => false, 'message' => 'Cours introuvable'];
            }

            $fichier = $cours['fichier'];
            
            // Si un nouveau fichier est fourni
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $uploadDir = UPLOAD_DIR . 'cours/';
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    return ['success' => false, 'message' => 'Seuls les PDF sont acceptés'];
                }
                $newName = uniqid() . '_' . time() . '.pdf';
                $path = $uploadDir . $newName;
                
                if (move_uploaded_file($file['tmp_name'], $path)) {
                    // Supprimer l'ancien fichier
                    $oldPath = $uploadDir . $fichier;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                    $fichier = $newName;
                }
            }

            $stmt = $this->db->prepare("UPDATE cours SET titre = ?, description = ?, fichier = ?, categorie = ?, statut = ? WHERE id = ?");
            $result = $stmt->execute([
                $data['titre'],
                $data['description'] ?? '',
                $fichier,
                $data['categorie'] ?? 'code',
                $data['statut'] ?? 'publie',
                $id
            ]);
            
            return $result 
                ? ['success' => true, 'message' => 'Cours modifié']
                : ['success' => false, 'message' => 'Erreur BDD'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    public function deleteCours($id) {
        try {
            $cours = $this->getCoursById($id);
            if ($cours) {
                $path = UPLOAD_DIR . 'cours/' . $cours['fichier'];
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            $stmt = $this->db->prepare("DELETE FROM cours WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // ========================================
    // 📊 STATISTIQUES
    // ========================================
    
    public function getStatistiques() {
        try {
            $stats = [];
            $stats['total_apprenants'] = $this->db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'apprenant'")->fetchColumn();
            $stats['apprenants_actifs'] = $this->db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'apprenant' AND statut = 'valide'")->fetchColumn();
            $stats['apprenants_en_attente'] = $this->db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'apprenant' AND statut = 'en_attente'")->fetchColumn();
            $stats['total_cours'] = $this->db->query("SELECT COUNT(*) FROM cours WHERE statut = 'publie'")->fetchColumn();
            $stats['documents_en_attente'] = $this->db->query("SELECT COUNT(*) FROM documents WHERE statut_cip = 'en_attente' OR statut_visite = 'en_attente' OR statut_groupe = 'en_attente'")->fetchColumn();
            $stats['documents_valides'] = $this->db->query("SELECT COUNT(*) FROM documents WHERE statut_cip = 'valide' AND statut_visite = 'valide' AND statut_groupe = 'valide'")->fetchColumn();
            $stats['total_paiements'] = $this->db->query("SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE statut = 'valide'")->fetchColumn();
            $stats['paiements_en_attente'] = $this->db->query("SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE statut = 'en_attente'")->fetchColumn();
            return $stats;
        } catch (PDOException $e) {
            return [
                'total_apprenants' => 0,
                'apprenants_actifs' => 0,
                'total_cours' => 0,
                'documents_en_attente' => 0,
                'total_paiements' => 0
            ];
        }
    }
    // ========================================
// 💰 CALCUL DES PAIEMENTS D'UN APPRENANT
// ========================================

    /**
     * Calcule le total déjà payé par un apprenant
     */
    public function getTotalPaye($userId) {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(montant), 0) FROM paiements WHERE user_id = ? AND statut = 'valide'");
            $stmt->execute([$userId]);
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Recherche des apprenants (pour l'AJAX)
     */
    public function rechercherApprenants($terme) {
        try {
            $terme = '%' . $terme . '%';
            $stmt = $this->db->prepare("SELECT id, nom, prenom, email, telephone, type_permis 
                                        FROM utilisateurs 
                                        WHERE role = 'apprenant' 
                                        AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)
                                        ORDER BY nom ASC
                                        LIMIT 20");
            $stmt->execute([$terme, $terme, $terme, $terme]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    // ========================================
// 💰 GESTION DES TARIFS
// ========================================

/**
 * Récupère tous les tarifs
 */
public function getAllTarifs() {
    try {
        $stmt = $this->db->query("SELECT * FROM tarifs_permis ORDER BY prix ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Met à jour un tarif
 */
public function updateTarif($id, $prix) {
    try {
        $stmt = $this->db->prepare("UPDATE tarifs_permis SET prix = ? WHERE id = ?");
        return $stmt->execute([(int)$prix, (int)$id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Récupère le tarif d'un type de permis
 */
public function getTarifByType($type) {
    try {
        $stmt = $this->db->prepare("SELECT prix FROM tarifs_permis WHERE type_permis = ? AND actif = 1");
        $stmt->execute([$type]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int)$result : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// ========================================
// 👥 APPRENANTS SOLDÉS
// ========================================

/**
 * Récupère les apprenants qui ont soldé leur formation
 */
public function getApprenantsSoldes() {
    try {
        $sql = "SELECT u.*, 
                COALESCE(SUM(p.montant), 0) as total_paye,
                t.prix as total_formation
                FROM utilisateurs u
                LEFT JOIN paiements p ON p.user_id = u.id AND p.statut = 'valide'
                LEFT JOIN tarifs_permis t ON t.type_permis = u.type_permis AND t.actif = 1
                WHERE u.role = 'apprenant'
                GROUP BY u.id, t.prix
                HAVING total_paye >= total_formation AND total_formation > 0
                ORDER BY u.date_inscription DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Récupère les apprenants avec solde restant (impayés)
 */
public function getApprenantsImpayes() {
    try {
        $sql = "SELECT u.*, 
                COALESCE(SUM(p.montant), 0) as total_paye,
                t.prix as total_formation,
                (t.prix - COALESCE(SUM(p.montant), 0)) as restant_du
                FROM utilisateurs u
                LEFT JOIN paiements p ON p.user_id = u.id AND p.statut = 'valide'
                LEFT JOIN tarifs_permis t ON t.type_permis = u.type_permis AND t.actif = 1
                WHERE u.role = 'apprenant'
                GROUP BY u.id, t.prix
                HAVING restant_du > 0
                ORDER BY restant_du DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
    


}