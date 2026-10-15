<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

// Vérification session
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['moniteur', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: moniteur.php');
    exit;
}

$crud = new Crud();
$user = $crud->selectById($id);

if (!$user) {
    header('Location: moniteur.php');
    exit;
}

$documents = $crud->selectDocuments($id);

$nom = $user['nom'] ?? '';
$prenom = $user['prenom'] ?? '';
$num = $user['telephone'] ?? '';
$email = $user['email'] ?? '';

// Images
$img1 = ($documents && !empty($documents['image_cip'])) ? $documents['image_cip'] : null;
$img2 = ($documents && !empty($documents['image_visite_medical'])) ? $documents['image_visite_medical'] : null;
$img3 = ($documents && !empty($documents['image_groupe_sanguin'])) ? $documents['image_groupe_sanguin'] : null;

$page_title = "Fiche Utilisateur";
include __DIR__ . '/../includes/header.php';
?>

<style>
.info-page {
    min-height: 70vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background: var(--bg-light);
}
.card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 100%;
    overflow: hidden;
    margin-top: 50px;
}
.card-body {
    padding: 30px;
}
.info-section {
    margin-bottom: 25px;
}
.info-section h2 {
    color: var(--primary);
    font-size: 16px;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 2px solid var(--border);
    padding-bottom: 8px;
}
.info-row {
    display: flex;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--bg-light);
}
.info-row:last-child {
    border-bottom: none;
}
.info-content {
    flex: 1;
}
.info-content label {
    display: block;
    color: var(--text-muted);
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 2px;
}
.info-content span {
    color: var(--text);
    font-size: 15px;
    font-weight: 500;
}
.images-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.image-item {
    aspect-ratio: 1;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
}
.image-item:hover {
    transform: scale(1.05);
}
.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-item.empty {
    color: var(--text-muted);
    font-size: 0.85rem;
    text-align: center;
    padding: 10px;
}
.back-link {
    display: inline-block;
    margin-bottom: 1rem;
    color: var(--primary-light);
    text-decoration: none;
    font-weight: 600;
}
.back-link:hover {
    color: var(--accent);
}
@media (max-width: 480px) {
    .card-body {
        padding: 20px;
    }
    .images-grid {
        gap: 8px;
    }
}
</style>

<div class="info-page">
    <div>
        <a href="javascript:history.back()" class="back-link">← Retour</a>
        <div class="card">
            <div class="card-body">
                <div class="info-section">
                    <h2>Informations personnelles</h2>
                    <div class="info-row">
                        <div class="info-content">
                            <label>Nom</label>
                            <span><?= htmlspecialchars($nom) ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-content">
                            <label>Prénom</label>
                            <span><?= htmlspecialchars($prenom) ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-content">
                            <label>Téléphone</label>
                            <span><?= htmlspecialchars($num) ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-content">
                            <label>Email</label>
                            <span><?= htmlspecialchars($email) ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-content">
                            <label>Type de permis</label>
                            <span><?= htmlspecialchars($user['type_permis'] ?? '-') ?></span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-content">
                            <label>Date d'inscription</label>
                            <span><?= !empty($user['date_inscription']) ? date('d/m/Y', strtotime($user['date_inscription'])) : '-' ?></span>
                        </div>
                    </div>
                </div>
                <div class="info-section">
                    <h2>Documents</h2>
                    <div class="images-grid">
                        <div class="image-item <?= $img1 ? '' : 'empty' ?>">
                            <?php if ($img1): ?>
                                <img src="../uploads/documents/<?= htmlspecialchars($img1) ?>" alt="CIP">
                            <?php else: ?>
                                <span>📇<br>CIP non soumis</span>
                            <?php endif; ?>
                        </div>
                        <div class="image-item <?= $img2 ? '' : 'empty' ?>">
                            <?php if ($img2): ?>
                                <img src="../uploads/documents/<?= htmlspecialchars($img2) ?>" alt="Visite médicale">
                            <?php else: ?>
                                <span>🏥<br>Visite non soumise</span>
                            <?php endif; ?>
                        </div>
                        <div class="image-item <?= $img3 ? '' : 'empty' ?>">
                            <?php if ($img3): ?>
                                <img src="../uploads/documents/<?= htmlspecialchars($img3) ?>" alt="Groupe sanguin">
                            <?php else: ?>
                                <span>🩸<br>Groupe non soumis</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>