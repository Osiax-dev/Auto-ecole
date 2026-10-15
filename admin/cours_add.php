<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['moniteur', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $crud = new Crud();
    $data = [
        'titre' => trim($_POST['titre'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'categorie' => $_POST['categorie'] ?? 'code',
        'statut' => $_POST['statut'] ?? 'publie'
    ];
    
    if (empty($data['titre'])) {
        $message = "Le titre est obligatoire";
    } elseif (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
        $message = "Veuillez sélectionner un fichier PDF";
    } else {
        $result = $crud->addCours($data, $_FILES['fichier']);
        if ($result['success']) {
        // ✅ REDIRECTION SELON LE RÔLE
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: moniteur.php');
        }
        exit;
    }
}
}

$page_title = "Ajouter un cours";
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/admin.css">

<div style="max-width: 700px; margin: 40px auto; padding: 20px;">
    <div class="content-section">
        <h2 style="margin-bottom: 24px;"> Ajouter un cours</h2>
        
        <?php if ($message): ?>
            <div style="padding: 12px; margin-bottom: 20px; border-radius: 8px; background: #fef2f2; color: #991b1b;">
                 <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Titre du cours *</label>
                <input type="text" name="titre" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Description</label>
                <textarea name="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;"></textarea>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Catégorie *</label>
                <select name="categorie" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="code">Code de la route</option>
                    <option value="conduite">Conduite</option>
                    <option value="moto">Moto</option>
                    <option value="poids_lourd">Poids lourd</option>
                    <option value="bus">Bus</option>
                </select>
            </div>
            
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Fichier PDF *</label>
                <input type="file" name="fichier" accept=".pdf" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
            </div>
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 600;">Statut</label>
                <select name="statut" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px;">
                    <option value="publie">Publié</option>
                    <option value="brouillon">Brouillon</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 12px;">
                <a href="moniteur.php" class="btn-admin btn-admin-secondary">Annuler</a>
                <button type="submit" class="btn-admin btn-admin-primary">✓ Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>