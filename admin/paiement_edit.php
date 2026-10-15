<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['secretaire', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$crud = new Crud();
$dbProperty = new ReflectionProperty(Crud::class, 'db');
$dbProperty->setAccessible(true);
$db = $dbProperty->getValue($crud);

// Récupérer le paiement
$stmt = $db->prepare("SELECT * FROM paiements WHERE id = ?");
$stmt->execute([$id]);
$paiement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paiement) {
    header('Location: secretaire.php');
    exit;
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'montant' => (float) ($_POST['montant'] ?? 0),
        'restant' => (float) ($_POST['restant'] ?? 0),
        'total_formation' => (float) ($_POST['total_formation'] ?? 0),
        'methode_paiement' => $_POST['methode_paiement'] ?? 'especes',
        'statut' => $_POST['statut'] ?? 'valide',
        'reference' => trim($_POST['reference'] ?? ''),
        'date' => $_POST['date'] ?? date('Y-m-d H:i:s')
    ];
    
    if ($data['montant'] <= 0) {
        $message = "Le montant doit être supérieur à 0";
    } else {
        $stmt = $db->prepare("UPDATE paiements SET montant = ?, restant = ?, total_formation = ?, methode_paiement = ?, statut = ?, reference_transaction = ?, date_paiement = ? WHERE id = ?");
        $result = $stmt->execute([
            $data['montant'],
            $data['restant'],
            $data['total_formation'],
            $data['methode_paiement'],
            $data['statut'],
            $data['reference'],
            $data['date'],
            $id
        ]);
        
       if ($result) {
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

$page_title = "Modifier un paiement";
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<div style="max-width: 700px; margin: 40px auto; padding: 20px;">
    <div class="content-section">
        <h2 style="margin-bottom: 24px;">✏️ Modifier le paiement</h2>
        
        <?php if ($message): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 8px; background: #fef2f2; color: #991b1b;">
                ⚠️ <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Montant payé (FCFA) *</label>
                    <input type="number" name="montant" value="<?= $paiement['montant'] ?>" required min="0" step="1000" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 600;">Total formation (FCFA) *</label>
                    <input type="number" name="total_formation" value="<?= $paiement['total_formation'] ?>" required min="0" step="1000" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Restant dû (FCFA)</label>
                <input type="number" name="restant" value="<?= $paiement['restant'] ?>" min="0" step="1000" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Méthode de paiement</label>
                <select name="methode_paiement" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="especes" <?= $paiement['methode_paiement'] === 'especes' ? 'selected' : '' ?>>💵 Espèces</option>
                    <option value="mobile_money" <?= $paiement['methode_paiement'] === 'mobile_money' ? 'selected' : '' ?>>📱 Mobile Money</option>
                    <option value="virement" <?= $paiement['methode_paiement'] === 'virement' ? 'selected' : '' ?>>🏦 Virement bancaire</option>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Statut</label>
                <select name="statut" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="valide" <?= $paiement['statut'] === 'valide' ? 'selected' : '' ?>>✓ Payé</option>
                    <option value="en_attente" <?= $paiement['statut'] === 'en_attente' ? 'selected' : '' ?>>⏳ En attente</option>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Référence transaction</label>
                <input type="text" name="reference" value="<?= htmlspecialchars($paiement['reference_transaction'] ?? '') ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Date du paiement</label>
                <input type="datetime-local" name="date" value="<?= date('Y-m-d\TH:i', strtotime($paiement['date_paiement'])) ?>" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <div style="display: flex; gap: 12px;">
                <a href="secretaire.php" class="btn-admin btn-admin-secondary">Annuler</a>
                <button type="submit" class="btn-admin btn-admin-primary">✓ Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>