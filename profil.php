<?php
session_start();
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/includes/Crud.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'apprenant') {
    redirect('connexion.php');
}


// Seul un apprenant peut accéder à cette page
if ($_SESSION['role'] !== 'apprenant') {
    header('Location: admin/' . ($_SESSION['role'] === 'admin' ? 'admin.php' : $_SESSION['role'] . '.php'));
    exit;
}

// ========================================
// 2. RÉCUPÉRATION DES DONNÉES
// ========================================
$crud = new Crud();
$userId = $_SESSION['user_id'];

// Récupérer l'utilisateur
$user = $crud->selectById($userId);
if (!$user) {
    session_destroy();
    header('Location: connexion.php?error=session_invalide');
    exit;
}

// Récupérer les documents
$documents = $crud->selectDocuments($userId);

// Récupérer les paiements
$paiements = $crud->selectPaiements($userId);

// Récupérer les cours
$cours = $crud->selectCours();

// ========================================
// 3. TRAITEMENT DE L'UPLOAD (si formulaire soumis)
// ========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_docs'])) {
    $result = $crud->uploadDocuments($userId, $_FILES);
    
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
        // Recharger les documents
        $documents = $crud->selectDocuments($userId);
    } else {
        $_SESSION['flash_error'] = $result['message'];
    }
    
    // Rediriger pour éviter la re-soumission du formulaire
    header('Location: profil.php');
    exit;
}

// ========================================
// 4. PRÉPARATION DES VARIABLES D'AFFICHAGE
// ========================================
$nom = strtoupper($user['nom'] ?? 'NOM');
$prenom = ucfirst($user['prenom'] ?? 'Prenom');
$email = $user['email'] ?? '';
$numero = $user['telephone'] ?? '';
$type_permis = $user['type_permis'] ?? 'Non défini';
$date_inscription = !empty($user['date_inscription']) 
    ? date('d/m/Y', strtotime($user['date_inscription'])) 
    : 'Non définie';
$first = substr($nom, 0, 1) . substr($prenom, 0, 1);

// Flash messages
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Images des documents (avec valeurs par défaut)
$img1 = ($documents && !empty($documents['image_cip'])) ? $documents['image_cip'] : null;
$img2 = ($documents && !empty($documents['image_visite_medical'])) ? $documents['image_visite_medical'] : null;
$img3 = ($documents && !empty($documents['image_groupe_sanguin'])) ? $documents['image_groupe_sanguin'] : null;

// Statuts des documents
$statut_cip = ($documents && !empty($documents['statut_cip'])) ? $documents['statut_cip'] : 'non_soumis';
$statut_visite = ($documents && !empty($documents['statut_visite'])) ? $documents['statut_visite'] : 'non_soumis';
$statut_groupe = ($documents && !empty($documents['statut_groupe'])) ? $documents['statut_groupe'] : 'non_soumis';

$page_title = "Mon Espace";
include 'includes/profil-header.php';
?>

<style>
/* ========================================
   PROFIL - VARIABLES
======================================== */
.profil-page {
    background: var(--bg-light);
    min-height: calc(100vh - 80px);
    padding: 30px 20px;
}

.profil-container {
    max-width: 1100px;
    margin: 0 auto;
}

/* ========================================
   FLASH MESSAGES
======================================== */
.flash-message {
    padding: 14px 20px;
    border-radius: var(--radius-sm);
    margin-bottom: 20px;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: fadeIn 0.3s;
}

.flash-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.flash-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* ========================================
   CARTE DE PROFIL
======================================== */
.profile-card {
    background: var(--bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: 24px;
    border: 1px solid var(--border);
}

.cover {
    height: 180px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    position: relative;
}

.profile-info {
    padding: 20px 30px 40px;
    display: flex;
    flex-wrap: wrap;
    gap: 24px;
    align-items: flex-end;
    margin-top: -70px;
    position: relative;
    z-index: 2;
}

.avatar {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 5px solid var(--bg);
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.8rem;
    color: var(--bg);
    font-weight: 700;
    box-shadow: var(--shadow-lg);
}

.profile-text {
    flex: 1;
    min-width: 200px;
}

.profile-text h1 {
    font-size: 1.8rem;
    color: white;
    margin-bottom: 50px;
}

.profile-text .username {
    color: var(--text-muted);
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.categorie {
    display: inline-block;
    color: var(--bg);
    background: var(--accent);
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    margin-top: 8px;
}

/* ========================================
   SECTION UPLOAD DOCUMENTS
======================================== */
.upload-section-title {
    text-align: center;
    font-size: 2rem;
    color: var(--accent);
    margin: 40px 0 30px;
    font-weight: 700;
}

.upload-container {
    background: var(--bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 30px;
    margin-bottom: 30px;
    border: 1px solid var(--border);
}

.upload-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 20px;
}

.upload-zone {
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: 20px;
    border: 1px solid var(--border);
}

.type-doc {
    background: var(--primary);
    color: var(--bg);
    text-align: center;
    padding: 10px;
    border-radius: var(--radius-sm);
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 16px;
}

.file-hidden {
    display: none;
}

.upload-box {
    display: block;
    border: 2px dashed var(--border);
    border-radius: var(--radius-md);
    padding: 30px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: var(--bg);
    overflow: hidden;
}

.upload-box:hover {
    border-color: var(--primary-light);
    background: rgba(59, 130, 246, 0.03);
}

.upload-icon {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.upload-title {
    font-weight: 600;
    color: var(--text);
    margin-bottom: 4px;
    font-size: 0.95rem;
}

.upload-hint {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.preview {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: var(--radius-sm);
}

.file-name {
    margin-top: 12px;
    text-align: center;
    font-size: 0.85rem;
    color: var(--text-muted);
    word-break: break-all;
}

/* Status badge pour les documents */
.doc-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 8px;
}

.status-non-soumis { background: #e5e7eb; color: #4b5563; }
.status-en-attente { background: #fed7aa; color: #9a3412; }
.status-valide { background: #d1fae5; color: #065f46; }
.status-rejete { background: #fee2e2; color: #991b1b; }

.upload-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.btn-action {
    padding: 10px 24px;
    border-radius: var(--radius-sm);
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    font-size: 0.95rem;
}

.btn-confirm {
    background: var(--accent);
    color: var(--bg);
}

.btn-confirm:hover {
    background: var(--accent-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.btn-cancel {
    background: transparent;
    border: 2px solid var(--border);
    color: var(--text);
}

.btn-cancel:hover {
    border-color: var(--primary);
    color: var(--primary);
}

/* ========================================
   TABS
======================================== */
.tabs {
    display: flex;
    gap: 8px;
    background: var(--bg);
    padding: 8px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
    overflow-x: auto;
    border: 1px solid var(--border);
}

.tab {
    padding: 12px 24px;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-weight: 600;
    color: var(--text-muted);
    transition: all 0.3s;
    white-space: nowrap;
    border: none;
    background: none;
    font-size: 0.95rem;
}

.tab.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--bg);
}

.tab:not(.active):hover {
    color: var(--primary);
    background: rgba(59, 130, 246, 0.08);
}

/* ========================================
   CONTENT SECTIONS
======================================== */
.content {
    background: var(--bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 30px;
    border: 1px solid var(--border);
}

.section {
    display: none;
}

.section.active {
    display: block;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.section h2 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 24px;
    font-size: 1.5rem;
}

/* Info grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.info-item {
    padding: 18px;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    background: var(--bg-light);
}

.info-item .label {
    font-size: 0.8rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
    font-weight: 600;
}

.info-item .value {
    font-weight: 600;
    font-size: 1rem;
    color: var(--text);
}

/* Cours PDF */
.course-item {
    background: var(--bg-light);
    padding: 20px;
    border-radius: var(--radius-md);
    margin-bottom: 16px;
    border: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.course-item h3 {
    color: var(--primary);
    font-size: 1.1rem;
    margin-bottom: 4px;
}

.course-item p {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.btn-download {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    color: var(--bg);
    text-decoration: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.btn-download:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
    color: var(--bg);
}

/* Paiements */
.payments-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.payments-table thead {
    background: var(--primary);
    color: var(--bg);
}

.payments-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payments-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
}

.payments-table tbody tr:hover {
    background: var(--bg-light);
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-paid { background: #d1fae5; color: #065f46; }
.status-pending { background: #fed7aa; color: #9a3412; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 12px;
    opacity: 0.5;
}

/* ========================================
   RESPONSIVE MOBILE
======================================== */
@media (max-width: 768px) {
    .profile-info {
        padding: 0 20px 40px;
        justify-content: center;
        text-align: center;
    }
    .avatar {
        width: 100px;
        height: 100px;
        font-size: 2rem;
    }
    .profile-text h1 {
        font-size: 1.4rem;
        color:black;
    }
    .upload-grid {
        grid-template-columns: 1fr;
    }
    .content {
        padding: 20px;
    }
    
    /* ========================================
       TABLE PAIEMENTS - TRANSFORMATION EN CARTES
    ======================================== */
    .payments-table {
        border: none;
        box-shadow: none;
    }
    
    .payments-table thead {
        display: none;
    }
    
    .payments-table,
    .payments-table tbody,
    .payments-table tr,
    .payments-table td {
        display: block;
        width: 100%;
    }
    
    .payments-table tr {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        margin-bottom: 16px;
        padding: 8px;
        box-shadow: var(--shadow-sm);
    }
    
    .payments-table td {
        position: relative;
        padding: 12px 12px 12px 140px;
        border: none;
        border-bottom: 1px solid var(--border);
        text-align: left;
        min-height: 44px;
        display: flex;
        align-items: center;
        word-break: break-word;
    }
    
    .payments-table td:last-child {
        border-bottom: none;
    }
    
    /* ✅ Le label s'affiche via ::before avec le data-label */
    .payments-table td::before {
        content: attr(data-label);
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 120px;
        font-weight: 700;
        color: var(--primary);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Petits écrans */
    @media (max-width: 480px) {
        .payments-table td {
            padding-left: 110px;
        }
        .payments-table td::before {
            width: 95px;
            font-size: 0.7rem;
        }
    }

}
</style>

<div class="profil-page">
    <div class="profil-container">
        
        <!-- FLASH MESSAGES -->
        <?php if ($flash_success): ?>
            <div class="flash-message flash-success">
                ✅ <?= htmlspecialchars($flash_success) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($flash_error): ?>
            <div class="flash-message flash-error">
                ⚠️ <?= htmlspecialchars($flash_error) ?>
            </div>
        <?php endif; ?>

        <!-- CARTE DE PROFIL -->
        <div class="profile-card">
            <div class="cover"></div>
            <div class="profile-info">
                <div class="avatar"><?= htmlspecialchars($first) ?></div>
                <div class="profile-text">
                    <h1><?= htmlspecialchars($nom . ' ' . $prenom) ?></h1>
                    <!-- <div class="username"><?= htmlspecialchars($email) ?></div> -->
                    <strong class="categorie">
                        <?php 
                        $types_permis = [
                            'code' => ' Code de la route',
                            'B' => ' Permis B',
                            'A1' => ' Permis A1',
                            'A2' => ' Permis A2',
                            'A' => ' Permis A',
                            'C' => ' Permis C',
                            'D' => ' Permis D',
                            'perfectionnement' => ' Perfectionnement'
                        ];
                        echo htmlspecialchars($types_permis[$type_permis] ?? $type_permis);
                        ?>
                    </strong>
                </div>
            </div>
        </div>

        <!-- SECTION UPLOAD DOCUMENTS -->
        <h1 class="upload-section-title"> Documents à fournir</h1>
        
        <form class="upload-container" method="POST" enctype="multipart/form-data">
            <div class="upload-grid">
                
                <!-- Document 1 : CIP -->
                 <!-- Document 1 : CIP -->
                <div class="upload-zone">
                    <h2 class="type-doc"> Photo de la carte CIP</h2>
                    <input type="file" name="img1" id="photo1" class="file-hidden" accept="image/*">
                    <label for="photo1" class="upload-box">
                        <?php
                        $img1_final = null;
                        if ($img1) {
                            // Tester les chemins possibles
                            $chemins = [
                                "uploads/documents/" . $img1,
                                "uploads/documents/" . $userId . "/" . $img1
                            ];
                            foreach ($chemins as $c) {
                                if (file_exists($c)) {
                                    $img1_final = $c;
                                    break;
                                }
                            }
                        }
                        ?>
                        <?php if ($img1_final): ?>
                            <img class="preview" src="<?= htmlspecialchars($img1_final) ?>" alt="CIP">
                        <?php else: ?>
                            <div class="upload-placeholder" id="placeholder1">
                                <div class="upload-icon">📁</div>
                                <div class="upload-title">Cliquez pour sélectionner</div>
                                <div class="upload-hint">PNG, JPG jusqu'à 5 Mo</div>
                            </div>
                            <img id="preview1" class="preview" src="" alt="" style="display:none;">
                        <?php endif; ?>
                    </label>
                    <div class="file-name" id="fileName1">
                        <?= $img1 ? htmlspecialchars($img1) : 'Aucun fichier sélectionné' ?>
                    </div>
                    <div class="doc-status status-<?= str_replace('_', '-', $statut_cip) ?>">
                        <?= ucfirst(str_replace('_', ' ', $statut_cip)) ?>
                    </div>
                </div>
                 
                     

                <!-- Document 2 : Visite médicale -->
                 <!-- Document 2 : Visite médicale -->
                <div class="upload-zone">
                    <h2 class="type-doc"> Visite médicale</h2>
                    <input type="file" name="img2" id="photo2" class="file-hidden" accept="image/*">
                    <label for="photo2" class="upload-box">
                        <?php
                        $img2_final = null;
                        if ($img2) {
                            $chemins = [
                                "uploads/documents/" . $img2,
                                "uploads/documents/" . $userId . "/" . $img2
                            ];
                            foreach ($chemins as $c) {
                                if (file_exists($c)) {
                                    $img2_final = $c;
                                    break;
                                }
                            }
                        }
                        ?>
                        <?php if ($img2_final): ?>
                            <img class="preview" src="<?= htmlspecialchars($img2_final) ?>" alt="Visite médicale">
                        <?php else: ?>
                            <div class="upload-placeholder" id="placeholder2">
                                <div class="upload-icon">📁</div>
                                <div class="upload-title">Cliquez pour sélectionner</div>
                                <div class="upload-hint">PNG, JPG jusqu'à 5 Mo</div>
                            </div>
                            <img id="preview2" class="preview" src="" alt="" style="display:none;">
                        <?php endif; ?>
                    </label>
                    <div class="file-name" id="fileName2">
                        <?= $img2 ? htmlspecialchars($img2) : 'Aucun fichier sélectionné' ?>
                    </div>
                    <div class="doc-status status-<?= str_replace('_', '-', $statut_visite) ?>">
                        <?= ucfirst(str_replace('_', ' ', $statut_visite)) ?>
                    </div>
                </div>
                 
                

                <!-- Document 3 : Groupe sanguin -->
                 <!-- Document 3 : Groupe sanguin -->
                <div class="upload-zone">
                    <h2 class="type-doc">Groupe sanguin</h2>
                    <input type="file" name="img3" id="photo3" class="file-hidden" accept="image/*">
                    <label for="photo3" class="upload-box">
                        <?php
                        $img3_final = null;
                        if ($img3) {
                            $chemins = [
                                "uploads/documents/" . $img3,
                                "uploads/documents/" . $userId . "/" . $img3
                            ];
                            foreach ($chemins as $c) {
                                if (file_exists($c)) {
                                    $img3_final = $c;
                                    break;
                                }
                            }
                        }
                        ?>
                        <?php if ($img3_final): ?>
                            <img class="preview" src="<?= htmlspecialchars($img3_final) ?>" alt="Groupe sanguin">
                        <?php else: ?>
                            <div class="upload-placeholder" id="placeholder3">
                                <div class="upload-icon">📁</div>
                                <div class="upload-title">Cliquez pour sélectionner</div>
                                <div class="upload-hint">PNG, JPG jusqu'à 5 Mo</div>
                            </div>
                            <img id="preview3" class="preview" src="" alt="" style="display:none;">
                        <?php endif; ?>
                    </label>
                    <div class="file-name" id="fileName3">
                        <?= $img3 ? htmlspecialchars($img3) : 'Aucun fichier sélectionné' ?>
                    </div>
                    <div class="doc-status status-<?= str_replace('_', '-', $statut_groupe) ?>">
                        <?= ucfirst(str_replace('_', ' ', $statut_groupe)) ?>
                    </div>
                </div>
                 
                                

            </div>

            <div class="upload-actions">
                <button type="button" class="btn-action btn-cancel" onclick="resetUploads()">Annuler</button>
                <button type="submit" name="submit_docs" class="btn-action btn-confirm">✓ Confirmer l'envoi</button>
            </div>
        </form>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab active" data-tab="about"> À propos</button>
            <button class="tab" data-tab="cours"> Cours PDF</button>
            <button class="tab" data-tab="payments"> Paiements</button>
        </div>

        <!-- CONTENT -->
        <div class="content">
            
            <!-- Section À propos -->
            <div class="section active" id="about">
                <h2>Informations personnelles</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Email</div>
                        <div class="value"><?= htmlspecialchars($email) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Numéro de téléphone</div>
                        <div class="value"><?= htmlspecialchars($numero) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Membre depuis</div>
                        <div class="value"><?= htmlspecialchars($date_inscription) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Type de permis</div>
                        <div class="value"><?= htmlspecialchars($types_permis[$type_permis] ?? $type_permis) ?></div>
                    </div>
                </div>
            </div>

            <!-- Section Cours -->
            <div class="section" id="cours">
                <h2>Cours disponibles</h2>
                <?php if (empty($cours)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon"></div>
                        <p>Aucun cours disponible pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($cours as $cours_item): ?>
                        <div class="course-item">
                            <div>
                                <h3><?= htmlspecialchars($cours_item['titre']) ?></h3>
                                <p><?= htmlspecialchars($cours_item['description']) ?></p>
                            </div>
                            <a href="uploads/cours/<?= htmlspecialchars($cours_item['fichier']) ?>" download class="btn-download">
                                Télécharger (PDF)
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Section Paiements -->
            <div class="section" id="payments">
                <h2>Historique des paiements</h2>
                <?php if (empty($paiements)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">💳</div>
                        <p>Aucun paiement enregistré pour le moment.</p>
                    </div>
                <?php else: ?>
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Montant payé</th>
                                <th>Restant dû</th>
                                <th>Total formation</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paiements as $paiement): ?>
                                <tr>
                                    <td data-label="Date"><?= date('d/m/Y', strtotime($paiement['date_paiement'])) ?></td>
                                    <td data-label="Montant payé"><?= number_format($paiement['montant'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Restant dû"><?= number_format($paiement['restant'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Total formation"><?= number_format($paiement['total_formation'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Statut">
                                        <span class="status-badge <?= $paiement['statut'] === 'valide' ? 'status-paid' : 'status-pending' ?>">
                                            <?= $paiement['statut'] === 'valide' ? '✓ Payé' : '⏳ En attente' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// ====== Gestion des uploads ======
function setupUpload(inputId, previewId, placeholderId, fileNameId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);
    const fileName = document.getElementById(fileNameId);

    if (!input) return;

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (file.size > 5 * 1024 * 1024) {
            alert('Le fichier est trop volumineux (max 5 Mo)');
            input.value = '';
            return;
        }

        fileName.textContent = file.name;

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                if (preview) {
                    preview.src = ev.target.result;
                    preview.style.display = 'block';
                }
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

setupUpload('photo1', 'preview1', 'placeholder1', 'fileName1');
setupUpload('photo2', 'preview2', 'placeholder2', 'fileName2');
setupUpload('photo3', 'preview3', 'placeholder3', 'fileName3');

// ====== Reset uploads ======
function resetUploads() {
    ['photo1', 'photo2', 'photo3'].forEach((id, index) => {
        const input = document.getElementById(id);
        const preview = document.getElementById('preview' + (index + 1));
        const placeholder = document.getElementById('placeholder' + (index + 1));
        const fileName = document.getElementById('fileName' + (index + 1));

        if (input) input.value = '';
        if (preview) {
            preview.style.display = 'none';
            preview.src = '';
        }
        if (placeholder) placeholder.style.display = 'block';
        if (fileName) fileName.textContent = 'Aucun fichier sélectionné';
    });
}

// ====== Tabs ======
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
</script>

<?php include 'includes/profil-footer.php'; ?>