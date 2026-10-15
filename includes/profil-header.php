<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/init.php';
}
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto-École — <?php echo $page_title ?? 'Mon Espace'; ?></title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<header class="header" id="header">
    <div class="container">
        <nav class="nav">
            <a href="<?php echo url('index.php'); ?>" class="nav-logo">
                <img src="<?php echo url('assets/images/logo.png'); ?>" alt="Passion Auto-École" class="site-logo">
            </a>

            <!-- Menu mobile (contient le badge + déconnexion) -->
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo url('index.php'); ?>">Accueil</a></li>
                <li><a href="<?php echo url('index.php'); ?>#services">Services</a></li>
                <li><a href="<?php echo url('index.php'); ?>#about">À propos</a></li>
                <li class="nav-buttons">
                    <div class="profil-user-badge">
                        <div class="profil-user-avatar">
                            <?= strtoupper(substr($_SESSION['prenom'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="profil-user-text">
                            <strong><?= htmlspecialchars($_SESSION['prenom'] ?? 'Apprenant') ?></strong>
                            <span>Apprenant</span>
                        </div>
                    </div>
                    <a href="<?php echo url('deconnexion.php'); ?>" class="btn-header btn-login"> Déconnexion</a>
                </li>
            </ul>

            <!-- Version desktop (contient le badge + déconnexion) -->
            <div class="nav-buttons desktop-buttons">
                <div class="profil-user-badge">
                    <div class="profil-user-avatar">
                        <?= strtoupper(substr($_SESSION['prenom'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="profil-user-text">
                        <strong><?= htmlspecialchars($_SESSION['prenom'] ?? 'Apprenant') ?></strong>
                        <span>Apprenant</span>
                    </div>
                </div>
                <a href="<?php echo url('deconnexion.php'); ?>" class="btn-header btn-login"> Déconnexion</a>
            </div>

            <button class="burger" id="burger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </nav>
    </div>
</header>

<style>
/* ========================================
   BADGE UTILISATEUR
======================================== */
.profil-user-badge {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 14px;
    background: var(--bg-light);
    border-radius: 30px;
    border: 1px solid var(--border);
    margin-right: 10px;
}
.profil-user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--bg);
    font-weight: 700;
    font-size: 0.9rem;
}
.profil-user-text {
    display: flex;
    flex-direction: column;
    font-size: 0.8rem;
    line-height: 1.3;
}
.profil-user-text strong { color: var(--text); font-size: 0.85rem; }
.profil-user-text span { color: var(--text-muted); font-size: 0.7rem; }

/* ========================================
   MENU BURGER - MASQUÉ PAR DÉFAUT
======================================== */
.burger {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    z-index: 100;
}
.burger span {
    display: block;
    width: 25px;
    height: 3px;
    background: var(--primary);
    border-radius: 2px;
    transition: all 0.3s ease;
}
.burger.active span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.burger.active span:nth-child(2) { opacity: 0; }
.burger.active span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }

/* ========================================
   DESKTOP (> 768px)
   ======================================== */
@media (min-width: 769px) {
    /* ✅ Afficher uniquement le desktop-buttons */
    .desktop-buttons {
        display: flex !important;
    }
    
    /* ✅ Masquer le .nav-buttons dans le menu mobile */
    .nav-menu .nav-buttons {
        display: none !important;
    }
    
    /* Burger masqué en desktop */
    .burger {
        display: none !important;
    }
    
    /* Menu normal en desktop */
    .nav-menu {
        display: flex !important;
        position: static !important;
        width: auto !important;
        height: auto !important;
        background: transparent !important;
        box-shadow: none !important;
        flex-direction: row !important;
        padding: 0 !important;
    }
}

/* ========================================
   MOBILE (≤ 768px)
   ======================================== */
@media (max-width: 768px) {
    /* ✅ Afficher le burger */
    .burger {
        display: flex !important;
    }
    
    /* ✅ Masquer le desktop-buttons */
    .desktop-buttons {
        display: none !important;
    }
    
    /* ✅ Afficher le .nav-buttons dans le menu mobile */
    .nav-menu .nav-buttons {
        display: flex !important;
    }
    
    /* Menu mobile : masqué par défaut */
    .nav-menu {
        position: fixed !important;
        top: 80px !important;
        left: -100% !important;
        width: 80% !important;
        max-width: 300px !important;
        height: calc(100vh - 80px) !important;
        background: var(--bg) !important;
        flex-direction: column !important;
        padding: 30px 20px !important;
        gap: 0 !important;
        box-shadow: 4px 0 20px rgba(0,0,0,0.15) !important;
        transition: left 0.3s ease !important;
        z-index: 90 !important;
        overflow-y: auto !important;
    }
    
    /* Menu actif : glisse depuis la gauche */
    .nav-menu.active {
        left: 0 !important;
    }
    
    .nav-menu li {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .nav-menu li a {
        display: block;
        padding: 12px 16px;
        border-radius: 8px;
    }
    
    .nav-menu li a:hover {
        background: rgba(59, 130, 246, 0.08);
    }
    
    .nav-menu .nav-buttons {
        flex-direction: column !important;
        align-items: stretch !important;
        margin-top: 20px !important;
        padding-top: 20px !important;
        border-top: 1px solid var(--border) !important;
    }
    
    .profil-user-badge {
        margin-right: 0;
        margin-bottom: 12px;
    }
    
    .profil-user-text {
        display: none;
    }
    
    /* Overlay sombre quand menu ouvert */
    .nav-menu.active::before {
        content: '';
        position: fixed;
        top: 80px;
        left: 80%;
        width: 20%;
        height: calc(100vh - 80px);
        background: rgba(0,0,0,0.3);
    }
}
.site-logo{
    width: 100%;
}
.logo-img {
    height: 70px;
    width: auto;
    object-fit: contain;
}

@media (max-width: 768px) {
    .site-logo{
        width: 100%;
    }
    .logo-img {
        height: 45px;
    }
}
</style>

<main>

<script>
// Attendre que le DOM soit chargé
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
        
        // Fermer le menu au clic sur un lien
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