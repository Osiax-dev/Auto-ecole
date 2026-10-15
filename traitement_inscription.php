<?php
require_once __DIR__ . '/includes/Crud.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inscription.php');
    exit;
}

// Récupération et nettoyage des données
$nom         = trim($_POST['nom'] ?? '');
$prenom      = trim($_POST['prenom'] ?? '');
$email       = trim($_POST['email'] ?? '');
$tel         = trim($_POST['tel'] ?? '');
$type_permis = trim($_POST['type_permis'] ?? '');
$mdp         = $_POST['mdp'] ?? '';
$mdp_confirm = $_POST['mdp_confirm'] ?? '';
$conditions  = isset($_POST['conditions']);

// Validation côté serveur
$errors = [];

if (strlen($nom) < 2) $errors[] = 'Le nom doit contenir au moins 2 caractères';
if (strlen($prenom) < 2) $errors[] = 'Le prénom doit contenir au moins 2 caractères';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
if (!preg_match('/^\+?[0-9]{8,15}$/', preg_replace('/\s|-|\./', '', $tel))) {
    $errors[] = 'Numéro de téléphone invalide';
}
if (!in_array($type_permis, ['code', 'B', 'A1', 'A2', 'A', 'C', 'D', 'perfectionnement'])) {
    $errors[] = 'Type de permis invalide';
}
if (strlen($mdp) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
if ($mdp !== $mdp_confirm) $errors[] = 'Les mots de passe ne correspondent pas';
if (!$conditions) $errors[] = 'Vous devez accepter les conditions';

if (!empty($errors)) {
    $_SESSION['flash_error'] = implode('<br>', $errors);
    $_SESSION['old_input'] = $_POST;
    header('Location: inscription.php');
    exit;
}

// Création du compte
$crud = new Crud();
$result = $crud->create([
    'nom'         => $nom,
    'prenom'      => $prenom,
    'email'       => $email,
    'tel'         => $tel,
    'type_permis' => $type_permis,
    'mdp'         => $mdp
]);

if ($result['success']) {
    // Connexion automatique après inscription
    $loginResult = $crud->connexion($email, $mdp);
    if ($loginResult['success']) {
        header('Location: profil.php');
        exit;
    }
} else {
    $_SESSION['flash_error'] = $result['message'];
    header('Location: inscription.php');
    exit;
}