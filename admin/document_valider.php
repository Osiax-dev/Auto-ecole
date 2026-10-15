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

// Vérification des paramètres
if (!isset($_GET['id']) || !isset($_GET['type']) || !isset($_GET['statut'])) {
    header('Location: moniteur.php');
    exit;
}

$docId = (int) $_GET['id'];
$type = $_GET['type'];
$statut = $_GET['statut'];

// Validation des paramètres
$types_valides = ['cip', 'visite', 'groupe'];
$statuts_valides = ['valide', 'rejete', 'en_attente'];

if (!in_array($type, $types_valides) || !in_array($statut, $statuts_valides)) {
    header('Location: moniteur.php');
    exit;
}

// Exécution de la validation
$crud = new Crud();
$result = $crud->validerDocument($docId, $type, $statut);

// Message flash
if ($result) {
    $_SESSION['flash_success'] = "Document validé avec succès";
} else {
    $_SESSION['flash_error'] = "Erreur lors de la validation";
}

// Redirection vers moniteur.php
header('Location: moniteur.php');
exit;