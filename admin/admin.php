<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
if ($role !== 'admin') {
    header('Location: ../profil.php');
    exit;
}

// Récupération des données dynamiques
$crud = new Crud();
$stats = $crud->getStatistiques();
$apprenants = $crud->getAllApprenants();
$paiements = $crud->getAllPaiements();
$cours = $crud->getAllCours();
$documents = $crud->getAllDocuments();
$apprenantsSoldes = $crud->getApprenantsSoldes();        // ← NOUVEAU
$apprenantsImpayes = $crud->getApprenantsImpayes();      // ← NOUVEAU

// Messages flash
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$page_title = "Administration";
include __DIR__ . '/../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Administration</h2>
            <p>Contrôle total du système</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#dashboard" class="active"><span class="icon"></span> Tableau de bord</a></li>
            <li><a href="#section-apprenants"><span class="icon"></span> Apprenants</a></li>
            <li><a href="#section-paiements"><span class="icon"></span> Paiements</a></li>
            <li><a href="#section-cours"><span class="icon"></span> Cours</a></li>
            <li><a href="#section-documents"><span class="icon"></span> Documents</a></li>
            <li><a href="#section-soldes"><span class="icon"></span> Apprenants soldés</a></li>
            <li><a href="tarifs.php"><span class="icon"></span> Tarifs des permis</a></li>
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
            <h1>Administration Générale</h1>
            <!-- <div class="user-badge">
                <div class="user-badge-avatar">A</div>
                <div class="user-badge-info">
                    <strong><?= htmlspecialchars($_SESSION['nom'] ?? 'Admin') ?></strong>
                    <span>Administrateur</span>
                </div>
            </div> -->
        </div>

        <!-- Stats globales DYNAMIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon blue"></div> -->
                </div>
                <div class="stat-card-value"><?= $stats['total_apprenants'] ?? 0 ?></div>
                <div class="stat-card-label">Total apprenants</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon green"></div> -->
                </div>
                <div class="stat-card-value"><?= number_format($stats['total_paiements'] ?? 0, 0, ',', ' ') ?></div>
                <div class="stat-card-label">Revenus totaux (FCFA)</div>
            </div>
            <!-- <div class="stat-card">
                <div class="stat-card-header">
                    
                </div>
                <div class="stat-card-value"><?= number_format($stats['paiements_en_attente'] ?? 0, 0, ',', ' ') ?></div>
                <div class="stat-card-label">Impayés (FCFA)</div>
            </div> -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon purple"></div> -->
                </div>
                <div class="stat-card-value"><?= $stats['total_cours'] ?? 0 ?></div>
                <div class="stat-card-label">Cours disponibles</div>
            </div>
        </div>
        <!-- Tabs -->
        <div class="admin-tabs">
            <button class="admin-tab active" data-section="section-apprenants"> Apprenants</button>
            <button class="admin-tab" data-section="section-paiements"> Paiements</button>
            <button class="admin-tab" data-section="section-soldes"> Soldés</button>
            <button class="admin-tab" data-section="section-cours"> Cours</button>
            <button class="admin-tab" data-section="section-documents"> Documents</button>
        </div>

        <!-- Section Apprenants -->
        <div class="admin-section active" id="section-apprenants">
            <div class="content-section">
                <div class="section-header">
                    <h2>Gestion des apprenants</h2>
                </div>

                <!-- Barre de recherche -->
                <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
                    <input type="text" id="searchApprenant" placeholder=" Rechercher un apprenant..." 
                           style="flex: 1; min-width: 250px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px;">
                    <select id="filterStatutApprenant" style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; min-width: 180px;">
                        <option value="all">Tous les statuts</option>
                        <option value="valide">✓ Actif</option>
                        <option value="en_attente"> En attente</option>
                        <option value="rejete">✗ Rejeté</option>
                    </select>
                </div>

                <?php if (empty($apprenants)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucun apprenant inscrit pour le moment.
                    </p>
                <?php else: ?>
                    <table class="data-table" id="tableApprenants">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Type permis</th>
                                <th>Date inscription</th>
                                <!-- <th>Statut</th> -->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apprenants as $a): ?>
                                <tr data-name="<?= htmlspecialchars(strtolower($a['nom'] . ' ' . $a['prenom'])) ?>"
                                    data-statut="<?= htmlspecialchars($a['statut']) ?>">
                                    <td data-label="Nom"><strong><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></strong></td>
                                    <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                                    <td data-label="Téléphone"><?= htmlspecialchars($a['telephone']) ?></td>
                                    <td data-label="Type permis"><?= htmlspecialchars($a['type_permis'] ?? '-') ?></td>
                                    <td data-label="Date inscription"><?= date('d/m/Y', strtotime($a['date_inscription'])) ?></td>
                                    <!-- <td data-label="Statut">
                                        <span class="status status-<?= $a['statut'] === 'valide' ? 'approved' : ($a['statut'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $a['statut'])) ?>
                                        </span>
                                    </td> -->
                                    <td data-label="Actions" class="user-actions">
                                        <!-- <a href="info.php?id=<?= $a['id'] ?>" class="action-btn btn-view">Voir</a> -->
                                        <a href="apprenant_delete.php?id=<?= $a['id'] ?>" 
                                           class="action-btn btn-delete"
                                           onclick="return confirm('⚠️ Supprimer définitivement cet apprenant ?');">Supprimer</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Paiements -->
        <div class="admin-section" id="section-paiements">
            <div class="content-section">
                <div class="section-header">
                    <h2>Tous les paiements</h2>
                    <a href="paiement_add.php" class="btn-admin btn-admin-primary">+ Ajouter un paiement</a>
                </div>

                <!-- Résumé -->
                <div class="payments-summary">
                    <div class="payment-summary-card">
                        <h3>Total encaissé</h3>
                        <div class="amount"><?= number_format($stats['total_paiements'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                        <div class="details">Depuis le début</div>
                    </div>
                    <div class="payment-summary-card">
                        <h3>Nombre de paiements</h3>
                        <div class="amount"><?= count($paiements) ?></div>
                        <div class="details">Transactions</div>
                    </div>
                    <div class="payment-summary-card">
                        <h3>En attente</h3>
                        <div class="amount"><?= number_format($stats['paiements_en_attente'] ?? 0, 0, ',', ' ') ?> FCFA</div>
                        <div class="details">À encaisser</div>
                    </div>
                </div>

                <?php if (empty($paiements)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucun paiement enregistré.
                    </p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Apprenant</th>
                                <th>Type permis</th>
                                <th>Montant payé</th>
                                <th>Restant</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Méthode</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paiements as $p): ?>
                                <tr>
                                    <td data-label="Apprenant"><strong><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></strong></td>
                                    <td data-label="Type permis"><?= htmlspecialchars($p['type_permis'] ?? '-') ?></td>
                                    <td data-label="Montant payé"><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Restant"><?= number_format($p['restant'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Total"><?= number_format($p['total_formation'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Date"><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                                    <td data-label="Méthode">
                                        <?php 
                                        $methodes = ['mobile_money' => ' Mobile', 'especes' => ' Espèces', 'virement' => ' Virement'];
                                        echo $methodes[$p['methode_paiement']] ?? $p['methode_paiement'];
                                        ?>
                                    </td>
                                    <td data-label="Statut">
                                        <span class="status <?= $p['statut'] === 'valide' ? 'status-approved' : 'status-pending' ?>">
                                            <?= $p['statut'] === 'valide' ? '✓ ' : ' En attente' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Cours -->
        <div class="admin-section" id="section-cours">
            <div class="content-section">
                <div class="section-header">
                    <h2>Gestion des cours</h2>
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
                                        $icons = ['code' => '📘', 'conduite' => '🚗', 'moto' => '🏍️', 'poids_lourd' => '🚚', 'bus' => '🚌'];
                                        echo $icons[$c['categorie'] ?? 'code'] ?? '📄';
                                        ?>
                                    </div> -->
                                    <span class="status <?= ($c['statut'] ?? 'publie') === 'publie' ? 'status-approved' : 'status-pending' ?>">
                                        <?= ($c['statut'] ?? 'publie') === 'publie' ? 'Publié' : 'Brouillon' ?>
                                    </span>
                                </div>
                                <h3><?= htmlspecialchars($c['titre']) ?></h3>
                                <p><?= htmlspecialchars($c['description'] ?? '') ?></p>
                                <div class="course-card-actions">
                                    <a href="../uploads/cours/<?= htmlspecialchars($c['fichier']) ?>" target="_blank" class="action-btn btn-view">Voir</a>
                                    <a href="cours_edit.php?id=<?= $c['id'] ?>" class="action-btn btn-edit">Modifier</a>
                                    <a href="cours_delete.php?id=<?= $c['id'] ?>" 
                                       class="action-btn btn-delete"
                                       onclick="return confirm('Supprimer ce cours ?');">Supprimer</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Documents -->
        <div class="admin-section" id="section-documents">
            <div class="content-section">
                <div class="section-header">
                    <h2>Documents des apprenants</h2>
                </div>

                <?php if (empty($documents)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucun document soumis pour le moment.
                    </p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Apprenant</th>
                                <th>CIP</th>
                                <th>Visite médicale</th>
                                <th>Groupe sanguin</th>
                                <th>Statut global</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                                <?php 
                                $statuts = [$doc['statut_cip'], $doc['statut_visite'], $doc['statut_groupe']];
                                $valide_count = count(array_filter($statuts, fn($s) => $s === 'valide'));
                                $rejet_count = count(array_filter($statuts, fn($s) => $s === 'rejete'));
                                
                                if ($valide_count === 3) {
                                    $statut_global = 'Complet';
                                    $class_global = 'status-approved';
                                } elseif ($rejet_count > 0) {
                                    $statut_global = 'Incomplet';
                                    $class_global = 'status-rejected';
                                } else {
                                    $statut_global = 'Partiel';
                                    $class_global = 'status-pending';
                                }
                                ?>
                                <tr>
                                    <td data-label="Apprenant"><strong><?= htmlspecialchars($doc['nom'] . ' ' . $doc['prenom']) ?></strong></td>
                                    <td data-label="CIP">
                                        <span class="status status-<?= $doc['statut_cip'] === 'valide' ? 'approved' : ($doc['statut_cip'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                            <?= $doc['statut_cip'] === 'valide' ? '✓' : ($doc['statut_cip'] === 'rejete' ? '✗' : 'En attent') ?>
                                        </span>
                                    </td>
                                    <td data-label="Visite médicale">
                                        <span class="status status-<?= $doc['statut_visite'] === 'valide' ? 'approved' : ($doc['statut_visite'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                            <?= $doc['statut_visite'] === 'valide' ? '✓' : ($doc['statut_visite'] === 'rejete' ? '✗' : 'En attente') ?>
                                        </span>
                                    </td>
                                    <td data-label="Groupe sanguin">
                                        <span class="status status-<?= $doc['statut_groupe'] === 'valide' ? 'approved' : ($doc['statut_groupe'] === 'rejete' ? 'rejected' : 'pending') ?>">
                                            <?= $doc['statut_groupe'] === 'valide' ? '✓' : ($doc['statut_groupe'] === 'rejete' ? 'Accepté' : 'En attente') ?>
                                        </span>
                                    </td>
                                    <td data-label="Statut global">
                                        <span class="status <?= $class_global ?>"><?= $statut_global ?></span>
                                    </td>
                                    <td data-label="Actions">
                                        <a href="info.php?id=<?= $doc['user_id'] ?>" class="action-btn btn-view">Voir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <!-- Section Apprenants Soldés -->
        <div class="admin-section" id="section-soldes">
            <div class="content-section">
                <div class="section-header">
                    <h2> Apprenants ayant soldé leur formation</h2>
                    <span style="background: #d1fae5; color: #065f46; padding: 6px 14px; border-radius: 20px; font-weight: 600;">
                        <?= count($apprenantsSoldes) ?> apprenant(s)
                    </span>
                </div>

                <?php if (empty($apprenantsSoldes)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucun apprenant n'a encore soldé sa formation.
                    </p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Type permis</th>
                                <th>Total formation</th>
                                <th>Total payé</th>
                                <th>Date inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apprenantsSoldes as $a): ?>
                                <tr>
                                    <td data-label="Nom"><strong><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></strong></td>
                                    <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                                    <td data-label="Type permis"><?= htmlspecialchars($a['type_permis'] ?? '-') ?></td>
                                    <td data-label="Total formation"><?= number_format($a['total_formation'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Total payé">
                                        <span style="color: #065f46; font-weight: 700;">
                                            <?= number_format($a['total_paye'], 0, ',', ' ') ?> FCFA
                                        </span>
                                    </td>
                                    <td data-label="Date inscription"><?= date('d/m/Y', strtotime($a['date_inscription'])) ?></td>
                                    <td data-label="Actions">
                                        <a href="info.php?id=<?= $a['id'] ?>" class="action-btn btn-view">Voir</a>
                                        <span class="status status-approved">✓ Soldé</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Bonus : Section des impayés -->
            <div class="content-section" style="margin-top: 24px;">
                <div class="section-header">
                    <h2> Apprenants avec impayés</h2>
                    <span style="background: #fee2e2; color: #991b1b; padding: 6px 14px; border-radius: 20px; font-weight: 600;">
                        <?= count($apprenantsImpayes) ?> apprenant(s)
                    </span>
                </div>

                <?php if (empty($apprenantsImpayes)): ?>
                    <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Aucun impayé. Tous les apprenants sont à jour !
                    </p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom complet</th>
                                <th>Type permis</th>
                                <th>Total formation</th>
                                <th>Déjà payé</th>
                                <th>Restant dû</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($apprenantsImpayes as $a): ?>
                                <tr>
                                    <td data-label="Nom"><strong><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></strong></td>
                                    <td data-label="Type permis"><?= htmlspecialchars($a['type_permis'] ?? '-') ?></td>
                                    <td data-label="Total formation"><?= number_format($a['total_formation'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Déjà payé"><?= number_format($a['total_paye'], 0, ',', ' ') ?> FCFA</td>
                                    <td data-label="Restant dû">
                                        <span style="color: #991b1b; font-weight: 700;">
                                            <?= number_format($a['restant_du'], 0, ',', ' ') ?> FCFA
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <a href="info.php?id=<?= $a['id'] ?>" class="action-btn btn-view">Voir</a>
                                        <a href="paiement_add.php" class="action-btn btn-edit">+ Paiement</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<script>
// Navigation tabs
document.querySelectorAll('.admin-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
        
        tab.classList.add('active');
        document.getElementById(tab.dataset.section).classList.add('active');
    });
});

// Navigation sidebar
document.querySelectorAll('.sidebar-menu a').forEach(link => {
    link.addEventListener('click', (e) => {
        if (link.getAttribute('href').startsWith('#')) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            const target = link.getAttribute('href').substring(1);
            
            // Activer le tab correspondant
            document.querySelectorAll('.admin-tab').forEach(tab => {
                if (tab.dataset.section === target) {
                    tab.click();
                }
            });
        }
    });
});

// Recherche et filtrage des apprenants
const searchApprenant = document.getElementById('searchApprenant');
const filterStatutApprenant = document.getElementById('filterStatutApprenant');
const tableApprenants = document.getElementById('tableApprenants');

if (searchApprenant && filterStatutApprenant && tableApprenants) {
    const rows = tableApprenants.querySelectorAll('tbody tr');
    
    function filterApprenants() {
        const searchTerm = searchApprenant.value.toLowerCase();
        const statutFilter = filterStatutApprenant.value;
        
        rows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const statut = row.dataset.statut;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatut = statutFilter === 'all' || statut === statutFilter;
            
            row.style.display = (matchesSearch && matchesStatut) ? '' : 'none';
        });
    }
    
    searchApprenant.addEventListener('input', filterApprenants);
    filterStatutApprenant.addEventListener('change', filterApprenants);
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>