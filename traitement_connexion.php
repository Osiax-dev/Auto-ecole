<?php
require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/includes/Crud.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('connexion.php');
}

$email = trim($_POST['email'] ?? '');
$mdp   = $_POST['mdp'] ?? '';

if (empty($email) || empty($mdp)) {
    $_SESSION['flash_error'] = 'Veuillez remplir tous les champs';
    redirect('connexion.php');
}

$crud = new Crud();
$result = $crud->connexion($email, $mdp);

if ($result['success']) {
    redirect($result['redirect']);
} else {
    $_SESSION['flash_error'] = $result['message'];
    redirect('connexion.php');
}