<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/includes/Crud.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('inscription.php');
}

// Récupération et nettoyage
$data = [
    'nom'         => trim($_POST['nom'] ?? ''),
    'prenom'      => trim($_POST['prenom'] ?? ''),
    'email'       => trim($_POST['email'] ?? ''),
    'tel'         => trim($_POST['tel'] ?? ''),
    'type_permis' => trim($_POST['type_permis'] ?? ''),
    'mdp'         => $_POST['mdp'] ?? '',
];
$mdp_confirm = $_POST['mdp_confirm'] ?? '';
$conditions  = isset($_POST['conditions']);

// Validation
$errors = [];
if (strlen($data['nom']) < 2) $errors[] = 'Nom trop court';
if (strlen($data['prenom']) < 2) $errors[] = 'Prénom trop court';
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
if (!preg_match('/^\+?[0-9]{8,15}$/', preg_replace('/\s|-|\./', '', $data['tel']))) {
    $errors[] = 'Téléphone invalide';
}
if (!in_array($data['type_permis'], ['code','B','A1','A2','A','C','D','perfectionnement'])) {
    $errors[] = 'Type de permis invalide';
}
if (strlen($data['mdp']) < 8) $errors[] = 'Mot de passe trop court (8 min)';
if ($data['mdp'] !== $mdp_confirm) $errors[] = 'Mots de passe différents';
if (!$conditions) $errors[] = 'Acceptez les conditions';

if (!empty($errors)) {
    $_SESSION['flash_error'] = implode('<br>', $errors);
    $_SESSION['old_input'] = $data;
    redirect('inscription.php');
}

$crud = new Crud();
$result = $crud->create($data);

if ($result['success']) {
    $loginResult = $crud->connexion($data['email'], $data['mdp']);
    if ($loginResult['success']) {
        redirect($loginResult['redirect']);
    }
}

$_SESSION['flash_error'] = $result['message'];
redirect('inscription.php');