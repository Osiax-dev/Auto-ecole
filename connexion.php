<?php
session_start();

// Si déjà connecté, rediriger selon le rôle
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'apprenant';
    if ($role === 'admin') {
        header('Location: admin/admin.php');
        exit;
    }
    if ($role === 'moniteur') {
        header('Location: admin/moniteur.php');
        exit;
    }
    if ($role === 'secretaire') {
        header('Location: admin/secretaire.php');
        exit;
    }
    header('Location: profil.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if (empty($email) || empty($mdp)) {
        $error = "Veuillez remplir tous les champs";
    } else {
        try {
            $db = new PDO('mysql:host=localhost;dbname=auto_ecole;charset=utf8', 'root', '');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($mdp, $user['mdp'])) {
                // Stocker en session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // 🔑 REDIRECTION SELON LE RÔLE
                $role = $user['role'];
                if ($role === 'admin') {
                    header('Location: admin/admin.php');
                    exit;
                }
                if ($role === 'moniteur') {
                    header('Location: admin/moniteur.php');
                    exit;
                }
                if ($role === 'secretaire') {
                    header('Location: admin/secretaire.php');
                    exit;
                }
                // Par défaut : apprenant
                header('Location: profil.php');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données";
        }
    }
}

$page_title = "Connexion";
include 'includes/header.php';
?>

<section class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Bon retour parmi nous !</h1>
            <p>Connectez-vous à votre espace</p>
        </div>

        <form class="auth-form" method="POST">
            <?php if (isset($error)): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="mdp">Mot de passe</label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="password" id="mdp" name="mdp" required>
                    <button type="button" class="toggle-password" onclick="let i=document.getElementById('mdp');i.type=i.type==='password'?'text':'password'"><img src="img/bx-show.svg" alt=""></button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Se connecter</button>

            <p class="register-prompt">
                Pas de compte ? <a href="inscription.php">Créer un compte</a>
            </p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>