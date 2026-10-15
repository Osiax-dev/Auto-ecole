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
    <title><?php echo $page_title ?? 'Administration'; ?> — Auto-École</title>
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/admin.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<header class="header" id="header">
    <div class="container">
        <nav class="nav">
            <!-- Bouton toggle sidebar -->
            <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            
            <a href="<?php echo $base; ?>/admin/admin.php" class="nav-logo admin-logo-text">
                <span class="logo-passion">Passion</span>
                <span class="logo-auto">AUTO-ECOLE</span>
            </a>

            <!-- Info utilisateur admin -->
            <div class="nav-user-info">
                <div class="nav-user-avatar">
                    <?= strtoupper(substr($_SESSION['prenom'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="nav-user-text">
                    <strong><?= htmlspecialchars($_SESSION['prenom'] ?? 'Admin') ?></strong>
                    <span><?= ucfirst($_SESSION['role'] ?? 'admin') ?></span>
                </div>
            </div>
        </nav>
    </div>
</header>

<style>
/* Style spécifique au header admin */
.nav-user-info {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 14px;
    background: var(--bg-light);
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
}

.nav-user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--bg);
    font-weight: 700;
    font-size: 0.95rem;
}

.nav-user-text {
    display: flex;
    flex-direction: column;
    font-size: 0.85rem;
    line-height: 1.3;
}

.nav-user-text strong {
    color: var(--text);
}

.nav-user-text span {
    color: var(--text-muted);
    font-size: 0.75rem;
}

/* Bouton toggle dans le header */
.sidebar-toggle-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-right: 12px;
}

.sidebar-toggle-btn span {
    display: block;
    width: 24px;
    height: 3px;
    background: var(--primary);
    border-radius: 2px;
    transition: 0.3s;
}

.sidebar-toggle-btn:hover span {
    background: var(--accent);
}

/* Responsive */
@media (max-width: 600px) {
    .nav-user-text {
        display: none;
    }
    .nav-user-info {
        padding: 4px;
    }
}
.logo-img {
    height: 70px;
    width: auto;
    object-fit: contain;
}

@media (max-width: 768px) {
    .logo-img {
        height: 45px;
    }
}
/* ========================================
   LOGO PASSION AUTO-ECOLE (TEXTE)
======================================== */
.admin-logo-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1;
    text-decoration: none;
}

.admin-logo-text .logo-passion {
    font-family: 'Dancing Script', cursive;
    font-size: 1.6rem;
    font-weight: 700;
    background: linear-gradient(135deg, #c41e3a, #ff6b35);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    transform: rotate(-2deg);
    display: block;
    margin-bottom: -2px;
}

.admin-logo-text .logo-auto {
    font-family: 'Poppins', sans-serif;
    font-size: 0.7rem;
    font-weight: 800;
    color: #1e3a5f;
    letter-spacing: 2.5px;
    display: block;
}

/* ========================================
   MASQUER LE BOUTON 3 BARRES SUR DESKTOP
======================================== */
@media (min-width: 1024px) {
    .sidebar-toggle-btn {
        display: none !important;
    }
}
</style>

<main>