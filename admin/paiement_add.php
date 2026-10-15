<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';
require_once __DIR__ . '/../config/tarifs.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['secretaire', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$message = null;
$type_message = null;
$crud = new Crud();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $montant = (float) ($_POST['montant'] ?? 0);
    
    // Récupérer les infos de l'apprenant
    $user = $crud->selectById($userId);
    
    if (!$user || $user['role'] !== 'apprenant') {
        $message = "Apprenant invalide";
        $type_message = 'error';
    } elseif ($montant <= 0) {
        $message = "Le montant doit être supérieur à 0";
        $type_message = 'error';
    } else {
        $total_formation = getTarifPermis($user['type_permis']);
        $deja_paye = $crud->getTotalPaye($userId);
        $restant_avant = $total_formation - $deja_paye;
        $nouveau_restant = max(0, $restant_avant - $montant);
        
        $data = [
            'user_id' => $userId,
            'montant' => $montant,
            'restant' => $nouveau_restant,
            'total_formation' => $total_formation,
            'methode_paiement' => $_POST['methode_paiement'] ?? 'especes',
            'reference' => trim($_POST['reference'] ?? ''),
            'date' => $_POST['date'] ?? date('Y-m-d H:i:s'),
            'statut' => 'valide'
        ];
        
        $result = $crud->addPaiement($data);
        if ($result['success']) {
        // ✅ REDIRECTION SELON LE RÔLE
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: secretaire.php');
        }
        exit;
    }
    }
}

$page_title = "Ajouter un paiement";
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<style>
.paiement-form {
    max-width: 750px;
    margin: 40px auto;
    padding: 20px;
}

/* Recherche apprenant */
.search-wrapper {
    position: relative;
    margin-bottom: 20px;
}

.search-input {
    width: 100%;
    padding: 14px 16px 14px 44px;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2rem;
    color: var(--text-muted);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    margin-top: 4px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 100;
    display: none;
}

.search-results.show {
    display: block;
}

.search-result-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-item:hover {
    background: var(--bg-light);
}

.search-result-item strong {
    color: var(--primary);
    display: block;
    margin-bottom: 2px;
}

.search-result-item small {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.search-result-item .badge {
    display: inline-block;
    padding: 2px 8px;
    background: rgba(59, 130, 246, 0.1);
    color: var(--primary);
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 8px;
}

/* Carte apprenant sélectionné */
.apprenant-card {
    background: black;
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: none;
}

.apprenant-card.show {
    display: block;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.apprenant-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.apprenant-card-header h3 {
    margin: 0;
    font-size: 1.2rem;
}

.apprenant-card-header button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.85rem;
}

.apprenant-card-header button:hover {
    background: rgba(255,255,255,0.3);
}

.apprenant-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.apprenant-info-item {
    background: rgba(255,255,255,0.1);
    padding: 10px;
    border-radius: 6px;
}

.apprenant-info-item label {
    display: block;
    font-size: 0.75rem;
    opacity: 0.8;
    margin-bottom: 2px;
    text-transform: uppercase;
}

.apprenant-info-item span {
    font-weight: 600;
    font-size: 0.95rem;
}

/* Résumé financier */
.finance-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 12px;
}

.finance-box {
    background: rgba(255,255,255,0.15);
    padding: 12px;
    border-radius: 8px;
    text-align: center;
}

.finance-box label {
    display: block;
    font-size: 0.75rem;
    opacity: 0.9;
    margin-bottom: 4px;
    text-transform: uppercase;
}

.finance-box .amount {
    font-size: 1.3rem;
    font-weight: 700;
}

.finance-box.restant .amount {
    color: #fbbf24;
}

/* Form grid */
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 600px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
    }
    .finance-summary {
        grid-template-columns: 1fr;
    }
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: var(--text);
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}
</style>

<div class="paiement-form">
    <div class="content-section">
        <h2 style="margin-bottom: 24px;">Ajouter un paiement</h2>
        
        <?php if ($message): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 8px; background: <?= $type_message === 'error' ? '#fef2f2' : '#f0fdf4' ?>; color: <?= $type_message === 'error' ? '#991b1b' : '#166534' ?>;">
                 <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="formPaiement">
            
            <!-- RECHERCHE APPRENANT -->
            <div style="margin-bottom: 20px;">
                <label class="form-label">Rechercher un apprenant *</label>
                <div class="search-wrapper">
                    <span class="search-icon"></span>
                    <input type="text" 
                           id="searchApprenant" 
                           class="search-input" 
                           placeholder="Tapez le nom, prénom, email ou téléphone..."
                           autocomplete="off">
                    <div class="search-results" id="searchResults"></div>
                </div>
                <input type="hidden" name="user_id" id="userId" required>
            </div>
            
            <!-- CARTE APPRENANT SÉLECTIONNÉ -->
            <div class="apprenant-card" id="apprenantCard">
                <div class="apprenant-card-header">
                    <h3 id="apprenantNomComplet">-</h3>
                    <button type="button" onclick="resetApprenant()">✕ Changer</button>
                </div>
                <div class="apprenant-info-grid">
                    <div class="apprenant-info-item">
                        <label>Email</label>
                        <span id="apprenantEmail">-</span>
                    </div>
                    <div class="apprenant-info-item">
                        <label>Téléphone</label>
                        <span id="apprenantTel">-</span>
                    </div>
                    <div class="apprenant-info-item">
                        <label>Type de permis</label>
                        <span id="apprenantPermis">-</span>
                    </div>
                </div>
                
                <!-- Résumé financier -->
                <div class="finance-summary">
                    <div class="finance-box">
                        <label>Total formation</label>
                        <div class="amount" id="totalFormation">0 FCFA</div>
                    </div>
                    <div class="finance-box">
                        <label>Déjà payé</label>
                        <div class="amount" id="dejaPaye">0 FCFA</div>
                    </div>
                    <div class="finance-box restant">
                        <label>Restant dû</label>
                        <div class="amount" id="restantDu">0 FCFA</div>
                    </div>
                </div>
            </div>
            
            <!-- MONTANT DU PAIEMENT -->
            <div style="margin-bottom: 16px;">
                <label class="form-label">Montant à payer (FCFA) *</label>
                <input type="number" 
                       name="montant" 
                       id="montantInput"
                       required 
                       min="0" 
                       step="1000" 
                       class="form-control"
                       placeholder="Ex: 50000"
                       disabled>
                <small style="color: var(--text-muted); margin-top: 4px; display: block;">
                    Le restant dû sera recalculé automatiquement
                </small>
            </div>
            
            <!-- MÉTHODE + DATE -->
            <div class="form-grid-2">
                <div style="margin-bottom: 16px;">
                    <label class="form-label">Méthode de paiement</label>
                    <select name="methode_paiement" class="form-control">
                        <option value="especes">Espèces</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="virement">Virement bancaire</option>
                    </select>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label class="form-label">Date du paiement</label>
                    <input type="datetime-local" 
                           name="date" 
                           value="<?= date('Y-m-d\TH:i') ?>" 
                           class="form-control">
                </div>
            </div>
            
            <!-- RÉFÉRENCE -->
            <div style="margin-bottom: 24px;">
                <label class="form-label">Référence transaction (optionnel)</label>
                <input type="text" 
                       name="reference" 
                       placeholder="Ex: TXN123456" 
                       class="form-control">
            </div>
            
            <!-- BOUTONS -->
            <div style="display: flex; gap: 12px;">
                <a href="secretaire.php" class="btn-admin btn-admin-secondary">Annuler</a>
                <button type="submit" class="btn-admin btn-admin-primary" id="btnSubmit" disabled>✓ Enregistrer le paiement</button>
            </div>
        </form>
    </div>
</div>

<script>
// ========================================
// VARIABLES GLOBALES
// ========================================
let apprenantSelectionne = null;
let searchTimeout = null;

const searchInput = document.getElementById('searchApprenant');
const searchResults = document.getElementById('searchResults');
const apprenantCard = document.getElementById('apprenantCard');
const userIdInput = document.getElementById('userId');
const montantInput = document.getElementById('montantInput');
const btnSubmit = document.getElementById('btnSubmit');

/// ========================================
// RECHERCHE AJAX (avec debug)
// ========================================
searchInput.addEventListener('input', function() {
    const terme = this.value.trim();
    
    clearTimeout(searchTimeout);
    
    if (terme.length < 2) {
        searchResults.classList.remove('show');
        return;
    }
    
    searchTimeout = setTimeout(() => {
        const url = 'api_apprenants.php?action=search&q=' + encodeURIComponent(terme);
        console.log('Recherche:', url);
        
        fetch(url)
            .then(response => {
                console.log('📡 Status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Réponse brute:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Données parsées:', data);
                    afficherResultats(data);
                } catch (e) {
                    console.error('Erreur JSON:', e);
                    searchResults.innerHTML = '<div style="padding:16px;color:red;">Erreur de réponse serveur</div>';
                    searchResults.classList.add('show');
                }
            })
            .catch(err => {
                console.error('Erreur fetch:', err);
                searchResults.innerHTML = '<div style="padding:16px;color:red;">Erreur de connexion</div>';
                searchResults.classList.add('show');
            });
    }, 300);
});

function afficherResultats(apprenants) {
    if (apprenants.length === 0) {
        searchResults.innerHTML = '<div style="padding: 16px; text-align: center; color: var(--text-muted);">Aucun apprenant trouvé</div>';
        searchResults.classList.add('show');
        return;
    }
    
    searchResults.innerHTML = apprenants.map(a => `
        <div class="search-result-item" onclick="selectionnerApprenant(${a.id})">
            <strong>${a.nom} ${a.prenom} <span class="badge">${a.type_permis}</span></strong>
            <small>${a.email} • ${a.telephone}</small>
        </div>
    `).join('');
    searchResults.classList.add('show');
}

// ========================================
// SÉLECTION D'UN APPRENANT
// ========================================
function selectionnerApprenant(id) {
    console.log('🎯 Sélection ID:', id);
    
    fetch('api_apprenants.php?action=details&id=' + id)
        .then(response => {
            console.log('Status:', response.status);
            console.log('Headers:', response.headers.get('content-type'));
            return response.text();
        })
        .then(text => {
            console.log('Réponse brute:', text);
            
            try {
                const data = JSON.parse(text);
                
                if (data.error) {
                    alert('Erreur : ' + data.error);
                    return;
                }
                
                console.log(' Données:', data);
                apprenantSelectionne = data;
                
                // Remplir la carte
                document.getElementById('apprenantNomComplet').textContent = data.nom + ' ' + data.prenom;
                document.getElementById('apprenantEmail').textContent = data.email;
                document.getElementById('apprenantTel').textContent = data.telephone;
                document.getElementById('apprenantPermis').textContent = data.libelle_permis;
                
                // Remplir les montants
                document.getElementById('totalFormation').textContent = formater(data.total_formation);
                document.getElementById('dejaPaye').textContent = formater(data.deja_paye);
                document.getElementById('restantDu').textContent = formater(data.restant);
                
                // Afficher la carte
                apprenantCard.classList.add('show');
                searchResults.classList.remove('show');
                searchInput.value = data.nom + ' ' + data.prenom;
                searchInput.disabled = true;
                
                // Activer le champ montant
                userIdInput.value = data.id;
                montantInput.disabled = false;
                montantInput.max = data.restant;
                btnSubmit.disabled = false;
                
            } catch (e) {
                console.error('Erreur parse JSON:', e);
                console.error('Texte reçu:', text);
                alert('Erreur : réponse invalide du serveur. Ouvrez la console (F12) pour plus de détails.');
            }
        })
        .catch(err => {
            console.error('Erreur fetch:', err);
            alert('Erreur de connexion au serveur');
        });
}
// ========================================
// RESET
// ========================================
function resetApprenant() {
    apprenantSelectionne = null;
    apprenantCard.classList.remove('show');
    searchInput.value = '';
    searchInput.disabled = false;
    userIdInput.value = '';
    montantInput.value = '';
    montantInput.disabled = true;
    btnSubmit.disabled = true;
    searchInput.focus();
}

// ========================================
// UTILITAIRE FORMATAGE
// ========================================
function formater(montant) {
    return new Intl.NumberFormat('fr-FR').format(montant) + ' FCFA';
}

// ========================================
// FERMER LES RÉSULTATS AU CLIC EXTÉRIEUR
// ========================================
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.classList.remove('show');
    }
});

// ========================================
// VALIDATION AVANT SOUMISSION
// ========================================
document.getElementById('formPaiement').addEventListener('submit', function(e) {
    if (!apprenantSelectionne) {
        e.preventDefault();
        alert('Veuillez sélectionner un apprenant');
        return;
    }
    
    const montant = parseFloat(montantInput.value);
    if (montant <= 0) {
        e.preventDefault();
        alert('Le montant doit être supérieur à 0');
        return;
    }
    
    if (montant > apprenantSelectionne.restant) {
        if (!confirm(`Attention : le montant (${formater(montant)}) dépasse le restant dû (${formater(apprenantSelectionne.restant)}). Continuer ?`)) {
            e.preventDefault();
            return;
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>