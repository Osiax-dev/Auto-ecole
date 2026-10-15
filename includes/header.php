<?php
$is_admin = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false);
$base = $is_admin ? '..' : '.';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-École — <?php echo $page_title ?? 'Bienvenue'; ?></title>
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">
    <?php if ($is_admin): ?>
        <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/admin.css">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<header class="header" id="header">
    <div class="container">
        <nav class="nav">
            <?php if ($is_admin): ?>
                <!-- Bouton toggle sidebar (admin uniquement) -->
                <button id="sidebarToggle" class="sidebar-toggle-btn" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            <?php endif; ?>

            <a href="<?php echo $base; ?>/index.php" class="nav-logo">
                <img src="<?php echo $base; ?>/assets/images/logo.png" alt="Passion Auto-École" class="logo-img">
            </a>

            <?php if (!$is_admin): ?>
                <!-- Menu de navigation -->
                <ul class="nav-menu" id="navMenu">
                    <li><a href="<?php echo $base; ?>/index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="<?php echo $base; ?>/index.php#blog">Blog</a></li>
                    <li><a href="<?php echo $base; ?>/index.php#services">Services</a></li>
                    <li><a href="<?php echo $base; ?>/index.php#about">À propos</a></li>
                    
                    <!-- ✅ BOUTONS POUR MOBILE (dans le menu) -->
                    <li class="nav-mobile-buttons">
                        <a href="<?php echo $base; ?>/connexion.php" class="btn-header btn-login">Connexion</a>
                        <a href="<?php echo $base; ?>/inscription.php" class="btn-header btn-register">S'inscrire</a>
                    </li>
                </ul>

                <!-- ✅ BOUTONS POUR DESKTOP (en dehors du menu) -->
                <div class="nav-desktop-buttons">
                    <a href="<?php echo $base; ?>/connexion.php" class="btn-header btn-login">Connexion</a>
                    <a href="<?php echo $base; ?>/inscription.php" class="btn-header btn-register">S'inscrire</a>
                </div>

                <button class="burger" id="burger" aria-label="Menu">
                    <span></span><span></span><span></span>
                </button>
            <?php endif; ?>
        </nav>
    </div>
</header>

<style>
/* ========================================
   GESTION DES BOUTONS - PAS DE DOUBLE AFFICHAGE
======================================== */

/* Par défaut : masquer les deux versions */
.nav-mobile-buttons,
.nav-desktop-buttons {
    display: none;
}

/* DESKTOP (> 768px) : Afficher UNIQUEMENT les boutons desktop */
@media (min-width: 769px) {
    .nav-desktop-buttons {
        display: flex !important;
        gap: 10px;
        align-items: center;
    }
    
    .nav-mobile-buttons {
        display: none !important;
    }
}

/* MOBILE (≤ 768px) : Afficher UNIQUEMENT les boutons dans le menu */
@media (max-width: 768px) {
    .nav-desktop-buttons {
        display: none !important;
    }
    
    .nav-mobile-buttons {
        display: flex !important;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        width: 100%;
    }
    
    .nav-mobile-buttons a {
        width: 100%;
        text-align: center;
        padding: 12px 20px !important;
    }
}
</style>

<main>

<?php if (!$is_admin): ?>
<script>
// Menu burger pour mobile
document.addEventListener('DOMContentLoaded', function() {
    const burger = document.getElementById('burger');
    const navMenu = document.getElementById('navMenu');
    
    if (burger && navMenu) {
        burger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            navMenu.classList.toggle('active');
            burger.classList.toggle('active');
        });
        
        // Fermer au clic sur un lien
        navMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                burger.classList.remove('active');
            });
        });
        
        // Fermer au clic en dehors
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !burger.contains(e.target)) {
                navMenu.classList.remove('active');
                burger.classList.remove('active');
            }
        });
    }
});
</script>
<?php endif; ?>