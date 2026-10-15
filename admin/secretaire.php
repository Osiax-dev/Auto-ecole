<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}

$role = $_SESSION['role'] ?? '';
if (!in_array($role, ['secretaire', 'admin'])) {
    header('Location: ../profil.php');
    exit;
}

// Récupération des données dynamiques
$crud = new Crud();
$stats = $crud->getStatistiques();
$paiements = $crud->getAllPaiements();
$apprenants = $crud->getAllApprenants();

// Messages flash
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$page_title = "Espace Secrétaire";
include __DIR__ . '/../includes/admin-header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Espace Secrétaire</h2>
            <p>Gestion des paiements</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#dashboard" class="active"><span class="icon"></span> Tableau de bord</a></li>
            <li><a href="#paiements"><span class="icon"></span> Paiements</a></li>
            <li><a href="#historique"><span class="icon"></span> Historique</a></li>
            <li><a href="../deconnexion.php"><span class="icon"></span> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main">

        <!-- Messages flash -->
        <?php if ($flash_success): ?>
            <div style="padding: 12px 16px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 20px;">
                ✅ <?= htmlspecialchars($flash_success) ?>
            </div>
        <?php endif; ?>
        <?php if ($flash_error): ?>
            <div style="padding: 12px 16px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?= htmlspecialchars($flash_error) ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="admin-header">
            <h1>Bienvenue, <?= htmlspecialchars($_SESSION['prenom'] ?? 'Secrétaire') ?></h1>
            <!-- <div class="user-badge">
                <div class="user-badge-avatar"><?= strtoupper(substr($_SESSION['prenom'] ?? 'S', 0, 1)) ?></div>
                <div class="user-badge-info">
                    <strong><?= htmlspecialchars($_SESSION['nom'] ?? 'Secrétaire') ?></strong>
                    <span>Secrétaire</span>
                </div>
            </div> -->
        </div>

        <!-- Stats DYNAMIQUES -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon green">💰</div> -->
                </div>
                <div class="stat-card-value"><?= number_format($stats['total_paiements'] ?? 0, 0, ',', ' ') ?></div>
                <div class="stat-card-label">Total encaissé (FCFA)</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon orange">⏳</div> -->
                </div>
                <div class="stat-card-value"><?= number_format($stats['paiements_en_attente'] ?? 0, 0, ',', ' ') ?></div>
                <div class="stat-card-label">En attente (FCFA)</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon blue">📊</div> -->
                </div>
                <div class="stat-card-value"><?= count($paiements) ?></div>
                <div class="stat-card-label">Total paiements</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-header">
                    <!-- <div class="stat-card-icon purple">👥</div> -->
                </div>
                <div class="stat-card-value"><?= $stats['total_apprenants'] ?? 0 ?></div>
                <div class="stat-card-label">Apprenants inscrits</div>
            </div>
        </div>

        <!-- Section Paiements -->
        <div class="content-section" id="paiements">
            <div class="section-header">
                <h2>Gestion des paiements</h2>
                <!-- ✅ BOUTON CONNECTÉ -->
                <a href="paiement_add.php" class="btn-admin btn-admin-primary">+ Ajouter un paiement</a>
            </div>

            <!-- BARRE DE RECHERCHE ET FILTRES -->
            <div style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
                <input type="text" id="searchPaiement" placeholder="Rechercher un apprenant..." 
                       style="flex: 1; min-width: 250px; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem;">
                <select id="filterStatut" style="padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem; min-width: 180px;">
                    <option value="all">Tous les statuts</option>
                    <option value="valide">✓ Payé</option>
                    <option value="en_attente">En attente</option>
                </select>
            </div>

            <?php if (empty($paiements)): ?>
                <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Aucun paiement enregistré. <a href="paiement_add.php">Ajoutez-en un !</a>
                </p>
            <?php else: ?>
                <table class="data-table" id="tablePaiements">
                    <thead>
                        <tr>
                            <th>Apprenant</th>
                            <th>Type permis</th>
                            <th>Montant payé</th>
                            <th>Restant dû</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paiements as $p): ?>
                            <tr data-name="<?= htmlspecialchars(strtolower($p['nom'] . ' ' . $p['prenom'])) ?>" 
                                data-statut="<?= htmlspecialchars($p['statut']) ?>">
                                <td data-label="Apprenant">
                                    <strong><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></strong><br>
                                    <small><?= htmlspecialchars($p['email']) ?></small>
                                </td>
                                <td data-label="Type permis"><?= htmlspecialchars($p['type_permis'] ?? '-') ?></td>
                                <td data-label="Montant payé"><strong><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</strong></td>
                                <td data-label="Restant dû"><?= number_format($p['restant'], 0, ',', ' ') ?> FCFA</td>
                                <td data-label="Total"><?= number_format($p['total_formation'], 0, ',', ' ') ?> FCFA</td>
                                <td data-label="Date"><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                                <td data-label="Méthode">
                                    <?php 
                                    $methodes = ['mobile_money' => 'Mobile', 'especes' => 'Espèces', 'virement' => 'Virement'];
                                    echo $methodes[$p['methode_paiement']] ?? $p['methode_paiement'];
                                    ?>
                                </td>
                                <td data-label="Statut">
                                    <span class="status <?= $p['statut'] === 'valide' ? 'status-approved' : 'status-pending' ?>">
                                        <?= $p['statut'] === 'valide' ? 'Payé' : 'En attente' ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <!-- <a href="info.php?id=<?= $p['user_id'] ?>" class="action-btn btn-view">Voir</a> -->
                                    <!-- <a href="paiement_edit.php?id=<?= $p['id'] ?>" class="action-btn btn-edit">Modifier</a> -->
                                    <a href="paiement_delete.php?id=<?= $p['id'] ?>" 
                                    class="action-btn btn-delete"
                                    onclick="return confirm('Supprimer ce paiement ?');">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Historique récent -->
        <div class="content-section" id="historique">
            <div class="section-header">
                <h2>Historique récent</h2>
            </div>

            <?php if (empty($paiements)): ?>
                <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                    Aucun paiement enregistré pour le moment.
                </p>
            <?php else: ?>
                <div class="payment-history">
                    <?php foreach (array_slice($paiements, 0, 5) as $p): ?>
                        <div class="payment-item">
                            <div class="payment-item-info">
                                <h4><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?> - Permis <?= htmlspecialchars($p['type_permis'] ?? '?') ?></h4>
                                <p>Paiement reçu le <?= date('d/m/Y', strtotime($p['date_paiement'])) ?> à <?= date('H:i', strtotime($p['date_paiement'])) ?></p>
                            </div>
                            <div class="payment-item-amount">+<?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</div>
                        </div>
                    <?php endforeach; ?>
                </div>
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

// Recherche et filtrage des paiements
const searchPaiement = document.getElementById('searchPaiement');
const filterStatut = document.getElementById('filterStatut');
const tablePaiements = document.getElementById('tablePaiements');

if (searchPaiement && filterStatut && tablePaiements) {
    const rows = tablePaiements.querySelectorAll('tbody tr');
    
    function filterPaiements() {
        const searchTerm = searchPaiement.value.toLowerCase();
        const statutFilter = filterStatut.value;
        
        rows.forEach(row => {
            const name = row.dataset.name.toLowerCase();
            const statut = row.dataset.statut;
            
            const matchesSearch = name.includes(searchTerm);
            const matchesStatut = statutFilter === 'all' || statut === statutFilter;
            
            row.style.display = (matchesSearch && matchesStatut) ? '' : 'none';
        });
    }
    
    searchPaiement.addEventListener('input', filterPaiements);
    filterStatut.addEventListener('change', filterPaiements);
}
</script>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>