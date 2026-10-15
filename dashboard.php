<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$role = $_SESSION['role'] ?? 'apprenant';

if ($role === 'admin') { header('Location: admin/admin.php'); exit; }
if ($role === 'moniteur') { header('Location: admin/moniteur.php'); exit; }
if ($role === 'secretaire') { header('Location: admin/secretaire.php'); exit; }

header('Location: profil.php');
exit;