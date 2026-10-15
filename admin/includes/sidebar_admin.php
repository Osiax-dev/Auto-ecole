<aside class="admin-sidebar">
    <nav class="sidebar-nav">
        
        <?php if ($role === 'moniteur'): ?>
            <!-- MENU MONITEUR -->
            <a href="moniteur.php" class="sidebar-link <?= $current_page === 'moniteur' ? 'active' : '' ?>">
                <span class="icon">📚</span>
                <span>Gestion des cours</span>
            </a>
            <a href="moniteur.php?section=documents" class="sidebar-link">
                <span class="icon">📄</span>
                <span>Documents apprenants</span>
            </a>

        <?php elseif ($role === 'secretaire'): ?>
            <!-- MENU SECRÉTAIRE -->
            <a href="secretaire.php" class="sidebar-link <?= $current_page === 'secretaire' ? 'active' : '' ?>">
                <span class="icon">💳</span>
                <span>Gestion des paiements</span>
            </a>
            <a href="secretaire.php?section=apprenants" class="sidebar-link">
                <span class="icon">👥</span>
                <span>Liste des apprenants</span>
            </a>

        <?php elseif ($role === 'admin'): ?>
            <!-- MENU ADMIN SUPRÊME -->
            <a href="admin.php" class="sidebar-link <?= $current_page === 'admin' ? 'active' : '' ?>">
                <span class="icon">📊</span>
                <span>Tableau de bord</span>
            </a>
            <a href="admin.php?section=apprenants" class="sidebar-link">
                <span class="icon">👥</span>
                <span>Apprenants</span>
            </a>
            <a href="admin.php?section=paiements" class="sidebar-link">
                <span class="icon">💰</span>
                <span>Tous les paiements</span>
            </a>
            <a href="admin.php?section=cours" class="sidebar-link">
                <span class="icon">📚</span>
                <span>Cours</span>
            </a>
            <a href="admin.php?section=documents" class="sidebar-link">
                <span class="icon">📄</span>
                <span>Documents</span>
            </a>
            <a href="admin.php?section=personnel" class="sidebar-link">
                <span class="icon">👔</span>
                <span>Personnel</span>
            </a>
        <?php endif; ?>

    </nav>
</aside>

<main class="admin-main">