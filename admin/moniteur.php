<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

// Vérification de session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['moniteur', 'admin'])) {
    header('Location: ../profil.php');
    exit;
}

// Récupération des données dynamiques depuis la BDD
$crud = new Crud();
$stats = $crud->getStatistiques();
$cours = $crud->getAllCours();
$documents = $crud->getAllDocuments();

// Messages flash
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$page_title = "Espace Moniteur";
include __DIR__ . '/../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">
<style>
    /* ========================================
   BOUTONS D'ACTION COMPACTS - MONITEUR
======================================== */
.actions-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    justify-content: flex-start;
}

.action-btn-compact {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid transparent;
    white-space: nowrap;
    cursor: pointer;
    line-height: 1;
}

.action-btn-compact:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

/* Bouton Voir - Bleu */
.action-btn-compact.btn-view {
    background: #eff6ff;
    color: #1e40af;
    border-color: #bfdbfe;
}
.action-btn-compact.btn-view:hover {
    background: #1e40af;
    color: white;
}

/* Bouton Valider - Vert */
.action-btn-compact.btn-validate {
    background: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}
.action-btn-compact.btn-validate:hover {
    background: #166534;
    color: white;
}

/* Bouton Rejeter - Rouge */
.action-btn-compact.btn-reject {
    background: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
}
.action-btn-compact.btn-reject:hover {
    background: #991b1b;
    color: white;
}

/* Icônes dans les boutons */
.action-btn-compact .icon {
    font-size: 0.85rem;
    line-height: 1;
}

/* Responsive mobile */
@media (max-width: 768px) {
    .actions-container {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 4px;
    }
    .action-btn-compact {
        padding: 5px 8px;
        font-size: 0.7rem;
    }
}
</style>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Espace Moniteur</h2>
            <p>Gestion des cours et documents</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#dashboard" class="active"><span class="icon"></span> Tableau de bord</a></li>
            <li><a href="#cours"><span class="icon"></span> Mes cours</a></li>
            <li><a href="#documents"><span class="icon"></span> Documents apprenants</a></li>
            <li><a href="../deconnexion.php"><span class="icon"></span> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <!-- Messages flash -->
        <?php if ($flash_success): ?>
            <div style="padding: 12px 16px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 20px;">
                 <?= htmlspecialchars($flash_success) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div style="padding: 12px 16px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 8px; margin-bottom: 20px;">
                 <?= htmlspecialchars($flash_error) ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="admin-header">
            <h1>Bienvenue, <?= htmlspecialchars($_SESSION['prenom'] ?? 'Moniteur') ?></h1>
            <!-- <div class="user-badge">
                <div class="user-badge-avatar"><?= strtoupper(substr($_SESSION['prenom'] ?? 'M', 0, 1)) ?></div>
                <div class="user-badge-info">
                    <strong><?= htmlspecialchars($_SESSION['nom'] ?? 'Moniteur') ?></strong>
                    <span>Moniteur</span>
                </div>
            </div> -->
        </div>

        <!-- Stats DYNAMIQUES depuis la BDD -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon blue">📚</div> -->
                </div>
                <div class="stat-card-value"><?= $stats['total_cours'] ?? 0 ?></div>
                <div class="stat-card-label">Cours publiés</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon orange">👥</div> -->
                </div>
                <div class="stat-card-value"><?= $stats['apprenants_actifs'] ?? 0 ?></div>
                <div class="stat-card-label">Apprenants actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon green">📄</div> -->
                </div>
                <div class="stat-card-value"><?= $stats['documents_en_attente'] ?? 0 ?></div>
                <div class="stat-card-label">Documents en attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon purple">✓</div> -->
                </div>
                <div class="stat-card-value"><?= $stats['documents_valides'] ?? 0 ?></div>
                <div class="stat-card-label">Documents validés</div>
            </div>
        </div>

        <!-- Section Cours (DYNAMIQUE) -->
        <div class="content-section" id="cours">
            <div class="section-header">
                <h2>Mes cours PDF</h2>
                <a href="cours_add.php" class="btn-admin btn-admin-primary">+ Ajouter un cours</a>
            </div>

            <?php if (empty($cours)): ?>
                <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Aucun cours disponible. <a href="cours_add.php">Ajoutez-en un !</a>
                </p>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($cours as $c): ?>
                        <div class="course-card">
                            <div class="course-card-header">
                                <!-- <div class="course-card-icon">
                                    <?php 
                                    $icons = [
                                        'code' => '📘', 
                                        'conduite' => '🚗', 
                                        'moto' => '🏍️', 
                                        'poids_lourd' => '🚚', 
                                        'bus' => '🚌'
                                    ];
                                    echo $icons[$c['categorie'] ?? 'code'] ?? '';
                                    ?>
                                </div> -->
                                <span class="status <?= ($c['statut'] ?? 'publie') === 'publie' ? 'status-approved' : 'status-pending' ?>">
                                    <?= ($c['statut'] ?? 'publie') === 'publie' ? 'Publié' : 'Brouillon' ?>
                                </span>
                            </div>
                            <h3><?= htmlspecialchars($c['titre']) ?></h3>
                            <p><?= htmlspecialchars($c['description'] ?? '') ?></p>
                            <div class="course-card-actions">
                                <a href="../uploads/cours/<?= htmlspecialchars($c['fichier']) ?>" 
                                   target="_blank" 
                                   class="action-btn btn-view">Voir</a>
                                <a href="cours_edit.php?id=<?= $c['id'] ?>" 
                                   class="action-btn btn-edit">Modifier</a>
                                <a href="cours_delete.php?id=<?= $c['id'] ?>" 
                                   class="action-btn btn-delete" 
                                   onclick="return confirm('Supprimer ce cours ?');">Supprimer</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section Documents Apprenants (DYNAMIQUE) -->
        <div class="content-section" id="documents">
            <div class="section-header">
                <h2>Documents des apprenants</h2>
            </div>

            <!-- BARRE DE RECHERCHE ET FILTRES -->
            <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
                <input type="text" id="searchInput" placeholder="Rechercher un apprenant..." 
                       style="flex: 1; min-width: 250px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
                <select id="filterStatus" style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem; min-width: 180px;">
                    <option value="all">Tous les statuts</option>
                    <option value="complet">✓ Complet</option>
                    <option value="partiel"> Partiel</option>
                    <option value="incomplet">✗ Incomplet</option>
                </select>
            </div>

            <?php if (empty($documents)): ?>
                <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Aucun document soumis pour le moment.
                </p>
            <?php else: ?>
                <table class="data-table" id="documentsTable">
                    <thead>
                        <tr>
                            <th>Apprenant</th>
                            <th>CIP</th>
                            <th>Visite médicale</th>
                            <th>Groupe sanguin</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <?php 
                            // Calcul du statut global
                            $statuts = [$doc['statut_cip'], $doc['statut_visite'], $doc['statut_groupe']];
                            $valide_count = count(array_filter($statuts, fn($s) => $s === 'valide'));
                            $rejet_count = count(array_filter($statuts, fn($s) => $s === 'rejete'));
                            
                            if ($valide_count === 3) {
                                $statut_global = 'complet';
                                $statut_label = 'Complet';
                                $class_global = 'status-approved';
                            } elseif ($rejet_count > 0) {
                                $statut_global = 'incomplet';
                                $statut_label = 'Incomplet';
                                $class_global = 'status-rejected';
                            } else {
                                $statut_global = 'partiel';
                                $statut_label = 'Partiel';
                                $class_global = 'status-pending';
                            }
                            ?>
                            <tr data-name="<?= htmlspecialchars(strtolower($doc['nom'] . ' ' . $doc['prenom'])) ?>" 
                                data-status="<?= $statut_global ?>">
                                <td data-label="Apprenant">
                                    <strong><?= htmlspecialchars($doc['nom'] . ' ' . $doc['prenom']) ?></strong><br>
                                    <small><?= htmlspecialchars($doc['email']) ?></small>
                                </td>
                                <td data-label="CIP">
                                    <?php if ($doc['image_cip']): ?>
                                        <a href="../uploads/documents/<?= htmlspecialchars($doc['image_cip']) ?>" target="_blank">
                                            <span class="status status-<?= $doc['statut_cip'] === 'valide' ? 'approved' : ($doc['statut_cip'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                                <?= $doc['statut_cip'] === 'valide' ? '✓ Validé' : ($doc['statut_cip'] === 'rejete' ? '✗ Rejeté' : '⏳ En attente') ?>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Non soumis</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Visite médicale">
                                    <?php if ($doc['image_visite_medical']): ?>
                                        <a href="../uploads/documents/<?= htmlspecialchars($doc['image_visite_medical']) ?>" target="_blank">
                                            <span class="status status-<?= $doc['statut_visite'] === 'valide' ? 'approved' : ($doc['statut_visite'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                                <?= $doc['statut_visite'] === 'valide' ? '✓ Validé' : ($doc['statut_visite'] === 'rejete' ? '✗ Rejeté' : '⏳ En attente') ?>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Non soumis</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Groupe sanguin">
                                    <?php if ($doc['image_groupe_sanguin']): ?>
                                        <a href="../uploads/documents/<?= htmlspecialchars($doc['image_groupe_sanguin']) ?>" target="_blank">
                                            <span class="status status-<?= $doc['statut_groupe'] === 'valide' ? 'approved' : ($doc['statut_groupe'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                                <?= $doc['statut_groupe'] === 'valide' ? '✓ Validé' : ($doc['statut_groupe'] === 'rejete' ? '✗ Rejeté' : '⏳ En attente') ?>
                                            </span>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">Non soumis</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Statut">
                                    <span class="status <?= $class_global ?>"><?= $statut_label ?></span>
                                </td>
                                <td data-label="Actions">
                                    <div class="actions-container">
                                        <!-- Bouton Voir (toujours visible) -->
                                        <a href="info.php?id=<?= $doc['user_id'] ?>" 
                                        class="action-btn-compact btn-view" 
                                        title="Voir le profil de l'apprenant">
                                            <!-- <span class="icon">👁️</span> -->
                                            <span>Voir</span>
                                        </a>
                                        
                                        <!-- Actions CIP -->
                                        <?php if ($doc['statut_cip'] !== 'valide' && $doc['image_cip']): ?>
                                            <a href="document_valider.php?id=<?= $doc['id'] ?>&type=cip&statut=valide"
                                            class="action-btn-compact btn-validate"
                                            onclick="return confirm('Valider la CIP de <?= htmlspecialchars($doc['prenom']) ?> <?= htmlspecialchars($doc['nom']) ?> ?');">
                                                <span class="icon">✓</span>
                                                <span>CIP</span>
                                            </a>
                                            <a href="document_valider.php?id=<?= $doc['id'] ?>&type=cip&statut=rejete"
                                            class="action-btn-compact btn-reject"
                                            onclick="return confirm('Rejeter la CIP de <?= htmlspecialchars($doc['prenom']) ?> <?= htmlspecialchars($doc['nom']) ?> ?');">
                                                <span class="icon">✗</span>
                                                <span>CIP</span>
                                            </a>
                                        <?php elseif ($doc['statut_cip'] === 'valide'): ?>
                                            <span class="action-btn-compact btn-validate" style="cursor: default;">
                                                <span class="icon">✓</span>
                                                <span>CIP</span>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- Actions Visite médicale -->
                                        <?php if ($doc['statut_visite'] !== 'valide' && $doc['image_visite_medical']): ?>
                                            <a href="document_valider.php?id=<?= $doc['id'] ?>&type=visite&statut=valide"
                                            class="action-btn-compact btn-validate"
                                            onclick="return confirm('Valider la visite médicale ?');">
                                                <span class="icon">✓</span>
                                                <span>Visite</span>
                                            </a>
                                            <a href="document_valider.php?id=<?= $doc['id'] ?>&type=visite&statut=rejete"
                                            class="action-btn-compact btn-reject"
                                            onclick="return confirm('Rejeter la visite médicale ?');">
                                                <span class="icon">✗</span>
                                                <span>Visite</span>
                                            </a>
                                        <?php elseif ($doc['statut_visite'] === 'valide'): ?>
                                            <span class="action-btn-compact btn-validate" style="cursor: default;">
                                                <span class="icon">✓</span>
                                                <span>Visite</span>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <!-- Actions Groupe sanguin -->
                                        <?php if ($doc['statut_groupe'] !== 'valide' && $doc['image_groupe_sanguin']): ?>
                                            <a href="document_valider.php?id=<?= $doc['id'] ?>&type=groupe&statut=valide"
                                            class="action-btn-compact btn-validate"
                                            onclick="return confirm('Valider le groupe sanguin ?');">
                                                <span class="icon">✓</span>
                                                <span>Groupe</span>
                                            </a>
                                            <a href="document_valider.php?id=<?= $doc['id'] ?>&type=groupe&statut=rejete"
                                            class="action-btn-compact btn-reject"
                                            onclick="return confirm('Rejeter le groupe sanguin ?');">
                                                <span class="icon">✗</span>
                                                <span>Groupe</span>
                                            </a>
                                        <?php elseif ($doc['statut_groupe'] === 'valide'): ?>
                                            <span class="action-btn-compact btn-validate" style="cursor: default;">
                                                <span class="icon">✓</span>
                                                <span>Groupe</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
// Navigation sidebar
document.querySelectorAll('.sidebar-menu a').forEach(link => {
    link.addEventListener('click', (e) => {
        if (link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            const target = link.getAttribute('href').substring(1);
            const section = document.getElementById(target);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});

// Recherche et filtrage des documents
const searchInput = document.getElementById('searchInput');
const filterStatus = document.getElementById('filterStatus');
const table = document.getElementById('documentsTable');

if (searchInput && filterStatus && table) {
    const rows = table.querySelectorAll('tbody tr');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        
        rows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const status = row.dataset.status;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            
            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    filterStatus.addEventListener('change', filterTable);
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>