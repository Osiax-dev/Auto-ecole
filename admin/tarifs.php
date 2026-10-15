<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../connexion.php');
    exit;
}

$crud = new Crud();
$message = null;
$type_message = null;

// Traitement de la mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tarifs'])) {
    $success_count = 0;
    foreach ($_POST['tarifs'] as $id => $prix) {
        if ($crud->updateTarif($id, (int)$prix)) {
            $success_count++;
        }
    }
    if ($success_count > 0) {
        $_SESSION['flash_success'] = "$success_count tarif(s) mis à jour avec succès";
    }
    header('Location: tarifs.php');
    exit;
}

$tarifs = $crud->getAllTarifs();
$flash_success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

$page_title = "Gestion des tarifs";
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<div style="max-width: 900px; margin: 40px auto; padding: 20px;">
    
    <?php if ($flash_success): ?>
        <div style="padding: 12px 16px; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 20px;">
             <?= htmlspecialchars($flash_success) ?>
        </div>
    <?php endif; ?>
    
    <div class="content-section">
        <div class="section-header">
            <h2> Gestion des tarifs des permis</h2>
            <a href="admin.php" class="btn-admin btn-admin-secondary">← Retour</a>
        </div>
        
        <p style="color: var(--text-muted); margin-bottom: 24px;">
            Modifiez les prix de chaque catégorie de permis. Les changements seront pris en compte immédiatement pour les nouveaux paiements.
        </p>
        
        <form method="POST">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Libellé</th>
                        <th>Prix actuel (FCFA)</th>
                        <th>Nouveau prix (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tarifs as $t): ?>
                        <tr>
                            <td data-label="Catégorie">
                                <strong><?= htmlspecialchars($t['type_permis']) ?></strong>
                            </td>
                            <td data-label="Libellé"><?= htmlspecialchars($t['libelle']) ?></td>
                            <td data-label="Prix actuel">
                                <span style="color: var(--primary); font-weight: 700;">
                                    <?= number_format($t['prix'], 0, ',', ' ') ?> FCFA
                                </span>
                            </td>
                            <td data-label="Nouveau prix">
                                <input type="number" 
                                       name="tarifs[<?= $t['id'] ?>]" 
                                       value="<?= $t['prix'] ?>" 
                                       min="0" 
                                       step="1000"
                                       style="width: 100%; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-weight: 600;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
                <a href="admin.php" class="btn-admin btn-admin-secondary">Annuler</a>
                <button type="submit" class="btn-admin btn-admin-primary"> Enregistrer les tarifs</button>
            </div>
        </form>
    </div>
    
    <!-- Info box -->
    <div style="background: #eff6ff; border-left: 4px solid var(--primary-light); padding: 16px; border-radius: 8px; margin-top: 20px;">
        <strong style="color: var(--primary);"> Information :</strong>
        <p style="margin-top: 8px; color: var(--text); font-size: 0.9rem;">
            Les paiements déjà enregistrés ne seront pas modifiés. Seuls les nouveaux paiements utiliseront les nouveaux tarifs.
        </p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>