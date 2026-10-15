<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['secretaire', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: secretaire.php');
    exit;
}

$crud = new Crud();
$crud->deletePaiement((int) $_GET['id']);

if ($crud) {
    // ✅ REDIRECTION SELON LE RÔLE
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: secretaire.php');
    }
    exit;
}
exit;