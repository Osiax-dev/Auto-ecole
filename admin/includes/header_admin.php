<?php
if (!isset($_SESSION['role'])) {
    header('Location: ../connexion.php');
    exit;
}

$role = $_SESSION['role'];
$nom_complet = $_SESSION['prenom'] . ' ' . $_SESSION['nom'];

// Titre selon le rôle
$titre_role = [
    'moniteur' => 'Espace Moniteur',
    'secretaire' => 'Espace Secrétaire',
    'admin' => 'Espace Administrateur'
];

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titre_role[$role] ?? 'Dashboard' ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<header class="admin-header">
    <div class="admin-header-content">
        <div class="admin-logo">
            <span class="logo-icon">🚗</span>
            <div>
                <h1>Auto-École</h1>
                <p><?= $titre_role[$role] ?></p>
            </div>
        </div>
        
        <div class="admin-user">
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($nom_complet) ?></span>
                <span class="user-role"><?= ucfirst($role) ?></span>
            </div>
            <a href="../deconnexion.php" class="btn-logout">Déconnexion</a>
        </div>
    </div>
</header>

<div class="admin-layout">