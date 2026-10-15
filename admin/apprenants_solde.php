<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'secretaire'])) {
    header('Location: ../connexion.php');
    exit;
}

$crud = new Crud();
$apprenantsSolde = $crud->getApprenantsSolde();
$tousApprenants = $crud->getApprenantsAvecSolde();

$page_title = "Apprenants soldés";
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<style>
.solde-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

.stats-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-box {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 24px;
    border-radius: var(--radius-md);
    text-align: center;
}

.stat-box .number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.stat-box .label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-box.green {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
}

.stat-box.orange {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
</style>

<div class="solde-page">
    <div class="content-section">
        <h2 style="margin-bottom: 24px;">✅ Apprenants ayant soldé leur formation</h2>
        
        <!-- Résumé -->
        <div class="stats-summary">
            <div class="stat-box green">
                <div class="number"><?= count($apprenantsSolde) ?></div>
                <div class="label">Apprenants soldés</div>
            </div>
            <div class="stat-box orange">
                <div class="number"><?= count($tousApprenants) - count($apprenantsSolde) ?></div>
                <div class="label">Apprenants avec restant</div>
            </div>
            <div class="stat-box">
                <div class="number"><?= count($tousApprenants) ?></div>
                <div class="label">Total apprenants</div>
            </div>
        </div>
        
        <?php if (empty($apprenantsSolde)): ?>
            <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                Aucun apprenant n'a encore soldé sa formation.
            </p>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Type permis</th>
                        <th>Total payé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apprenantsSolde as $a): ?>
                        <tr>
                            <td data-label="Nom">
                                <strong><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></strong>
                            </td>
                            <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                            <td data-label="Téléphone"><?= htmlspecialchars($a['telephone']) ?></td>
                            <td data-label="Type permis"><?= htmlspecialchars($a['type_permis']) ?></td>
                            <td data-label="Total payé">
                                <strong style="color: #16a34a;"><?= number_format($a['total_paye'], 0, ',', ' ') ?> FCFA</strong>
                            </td>
                            <td data-label="Statut">
                                <span class="status status-approved">✓ Soldé</span>
                            </td>
                            <td data-label="Actions">
                                <a href="info.php?id=<?= $a['id'] ?>" class="action-btn btn-view">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border);">
            <a href="admin.php" class="btn-admin btn-admin-secondary">← Retour au tableau de bord</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>