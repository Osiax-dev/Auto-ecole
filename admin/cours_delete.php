<?php
session_start();
require_once __DIR__ . '/../includes/Crud.php';

// 1. Vérification de la session et du rôle
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'moniteur'])) {
    header('Location: ../connexion.php');
    exit;
}

// 2. Récupération de l'ID du cours
$id = (int) ($_GET['id'] ?? 0);

// Si l'ID est invalide, on redirige
if ($id <= 0) {
    $redirectPage = ($_SESSION['role'] === 'admin') ? 'admin.php' : 'moniteur.php';
    header('Location: ' . $redirectPage);
    exit;
}

// 3. Connexion PDO locale (contourne la propriété private $db)
try {
    $db = new PDO('mysql:host=localhost;dbname=auto_ecole;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// 4. Récupérer le nom du fichier PDF avant de supprimer la ligne en BDD
$stmt = $db->prepare("SELECT fichier FROM cours WHERE id = ?");
$stmt->execute([$id]);
$cours = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cours && !empty($cours['fichier'])) {
    // 5. Supprimer le fichier physique du serveur
    $filePath = __DIR__ . '/../uploads/cours/' . $cours['fichier'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// 6. Supprimer l'enregistrement dans la base de données
$stmt = $db->prepare("DELETE FROM cours WHERE id = ?");
$stmt->execute([$id]);

// 7. Redirection selon le rôle (Admin ou Moniteur)
if ($_SESSION['role'] === 'admin') {
    header('Location: admin.php');
} else {
    header('Location: moniteur.php');
}
exit;