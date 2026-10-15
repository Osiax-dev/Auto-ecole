<?php 
$page_title = "Accueil";
include 'includes/header.php'; 
$tarifs = [];
try {
    $db = new PDO('mysql:host=localhost;dbname=auto_ecole;charset=utf8', 'root', '');
    $stmt = $db->query("SELECT * FROM tarifs_permis WHERE actif = 1 ORDER BY prix ASC");
    $tarifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En cas d'erreur, on utilise les tarifs par défaut
    $tarifs = [
        ['type_permis' => 'code', 'libelle' => 'Code de la route', 'prix' => 50000],
        ['type_permis' => 'A1', 'libelle' => 'Permis A1', 'prix' => 100000],
        ['type_permis' => 'A2', 'libelle' => 'Permis A2', 'prix' => 120000],
        ['type_permis' => 'B', 'libelle' => 'Permis B', 'prix' => 150000],
        ['type_permis' => 'C', 'libelle' => 'Permis C', 'prix' => 350000],
        ['type_permis' => 'D', 'libelle' => 'Permis D', 'prix' => 400000],
    ];
}
?>

<!-- ====== HERO ====== -->
<section class="hero" id="accueil" style="background:url('img/bg0.png');background-size: cover;background-position: center;  background-repeat: no-repeat;display: flex;flex-direction: column;  ">
    <span class="badge"> Bienvenue sur <span> Passion Auto-École</span></span>
    <h1>
        Une expérience<br>
        <span class="dynamic-word" id="dynamicWord">unique</span>
    </h1>
    <p class="subtitle" style = "color: #5f2207;">
        Découvrez Passion une plateforme moderne, rapide et intuitive, conçue pour transformer votre apprentissage de la conduite en réalité.
    </p>
    <div class="buttons">
        <a href="inscription.php" class="btn btn-primary">Commencer maintenant →</a>
        <a href="#contact" class="btn btn-secondary">Contactez-nous</a>
    </div>
</section>

<!-- ====== BLOG / CONSEILS ====== -->
<section class="section" id="blog">
    <div class="section-title">
        <h2>Conseils & astuces pour votre permis</h2>
        <p>Réussissez votre code et votre permis grâce à nos guides pratiques.</p>
    </div>

    <div class="filters">
        <button class="filter-btn active" data-filter="all">Tous</button>
        <button class="filter-btn" data-filter="moto">Moto</button>
        <button class="filter-btn" data-filter="voiture">Voiture</button>
        <button class="filter-btn" data-filter="camion">Camion</button>
        <button class="filter-btn" data-filter="bus">Bus</button>
    </div>

    <div class="posts">
        <article class="post" data-cat="voiture">
            <!-- <div class="post-img">🚗</div> -->
            <div class="post-body">
                <span class="post-tag">Permis B</span>
                <h3>Combien d'heures de conduite sont réellement nécessaires ?</h3>
                <p class="post-excerpt">Moyenne nationale, profil des candidats, rythme idéal… on vous dit tout.</p>
            </div>
        </article>

        <article class="post" data-cat="moto">
            <!-- <div class="post-img">🏍️</div> -->
            <div class="post-body">
                <span class="post-tag">Permis A2</span>
                <h3>Les équipements indispensables pour rouler en moto en sécurité</h3>
                <p class="post-excerpt">Casque, gants, blouson : découvrez ce qu'il faut absolument porter.</p>
            </div>
        </article>

        <article class="post" data-cat="voiture">
            <!-- <div class="post-img">🚦</div> -->
            <div class="post-body">
                <span class="post-tag">Code</span>
                <h3>10 erreurs fréquentes à l'examen du code de la route</h3>
                <p class="post-excerpt">Évitez les pièges classiques et mettez toutes les chances de votre côté.</p>
            </div>
        </article>

        <article class="post" data-cat="camion">
            <!-- <div class="post-img">🚚</div> -->
            <div class="post-body">
                <span class="post-tag">Permis Poids lourd</span>
                <h3>Devenir chauffeur poids lourd : formation et débouchés</h3>
                <p class="post-excerpt">Un métier qui recrute : découvrez le parcours pour obtenir votre permis C.</p>
            </div>
        </article>
    </div>
</section>

<!-- ====== NOS FORMATIONS ====== -->
<!-- SECTION SERVICES -->
<section class="services" id="services">
    <div class="container">
        <div class="section-header">
            <!-- <span class="section-subtitle">Nos offres</span> -->
            <h2 class="section-title">Services & Tarifs</h2>
            <p class="section-description">
                Découvrez nos différentes formations et leurs tarifs. 
                Choisissez la formule adaptée à vos besoins.
            </p>
        </div>

        <?php if (empty($tarifs)): ?>
            <p style="text-align: center; padding: 40px; color: var(--text-muted);">
                Aucun tarif disponible pour le moment.
            </p>
        <?php else: ?>
            <div class="services-grid">
                <?php foreach ($tarifs as $tarif): ?>
                    <?php
                    // Icônes selon le type de permis
                    $icons = [
                        'code' => '',
                        'B' => '',
                        'A1' => '',
                        'A2' => '',
                        'A' => '',
                        'C' => '',
                        'D' => '',
                        'perfectionnement' => ''
                    ];
                    $icon = $icons[$tarif['type_permis']] ?? '';
                    
                    // Descriptions selon le type
                    $descriptions = [
                        'code' => 'Formation complète au code de la route avec examens blancs',
                        'B' => 'Permis voiture avec formation pratique et théorique',
                        'A1' => 'Permis petite moto (125cc) pour les jeunes conducteurs',
                        'A2' => 'Permis moto intermédiaire avec formation complète',
                        'A' => 'Permis grosse moto avec formation spécialisée',
                        'C' => 'Permis poids lourd pour transport de marchandises',
                        'D' => 'Permis bus pour transport de personnes',
                        'perfectionnement' => 'Stage de perfectionnement pour conducteurs expérimentés'
                    ];
                    $description = $descriptions[$tarif['type_permis']] ?? 'Formation professionnelle de qualité';
                    ?>
                    <div class="service-card">
                        <!-- <div class="service-icon"><?= $icon ?></div> -->
                        <h3 class="service-title"><?= htmlspecialchars($tarif['libelle']) ?></h3>
                        <p class="service-description"><?= htmlspecialchars($description) ?></p>
                        <div class="service-price">
                            <span class="price-value"><?= number_format($tarif['prix'], 0, ',', ' ') ?></span>
                            <span class="price-currency">FCFA</span>
                        </div>
                        <ul class="service-features">
                            <li>✓ Formation théorique complète</li>
                            <li>✓ Formation pratique</li>
                            <li>✓ Accompagnement à l'examen</li>
                            <li>✓ Support pédagogique</li>
                        </ul>
                        <a href="inscription.php" class="btn-service">S'inscrire</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* ========================================
   SERVICES - GRILLE DE TARIFS
======================================== */
.services {
    padding: 80px 0;
    background: var(--bg-light);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 40px;
}

.service-card {
    background: var(--bg);
    border-radius: var(--radius-lg);
    padding: 30px 24px;
    text-align: center;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary-light);
}

.service-icon {
    font-size: 3rem;
    margin-bottom: 16px;
    display: inline-block;
    width: 80px;
    height: 80px;
    line-height: 80px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
    border-radius: 50%;
    margin: 0 auto 16px;
}

.service-title {
    font-size: 1.2rem;
    color: var(--primary);
    margin-bottom: 12px;
    font-weight: 700;
}

.service-description {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 20px;
    line-height: 1.5;
    flex-grow: 1;
}

.service-price {
    margin-bottom: 20px;
    padding: 16px 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
}

.price-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--accent);
    display: block;
    margin-bottom: 4px;
}

.price-currency {
    font-size: 0.9rem;
    color: var(--text-muted);
    font-weight: 600;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
    text-align: left;
}

.service-features li {
    padding: 6px 0;
    color: var(--text);
    font-size: 0.9rem;
}

.btn-service {
    display: inline-block;
    padding: 12px 24px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: var(--bg);
    text-decoration: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    transition: all 0.3s;
    margin-top: auto;
}

.btn-service:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
    color: var(--bg);
}

@media (max-width: 768px) {
    .services-grid {
        grid-template-columns: 1fr;
    }
    .service-card {
        padding: 24px 20px;
    }
    .price-value {
        font-size: 1.6rem;
    }
}
</style>

<!-- ====== À PROPOS ====== -->
<section class="section" id="about">
    <div class="section-title">
        <h2>À Propos de Nous</h2>
        <p>Une équipe passionnée dédiée à votre réussite</p>
    </div>

    <div class="mission-grid">
        <div class="mission-card">
            <!-- <div class="mission-icon">🎯</div> -->
            <h3>Notre Mission</h3>
            <p>Former des conducteurs responsables et compétents, prêts à affronter la route en toute sécurité.</p>
        </div>
        <div class="mission-card">
            <!-- <div class="mission-icon">💡</div> -->
            <h3>Notre Vision</h3>
            <p>Devenir l'auto-école de référence dans la région, reconnue pour la qualité de ses formations.</p>
        </div>
        <div class="mission-card">
            <!-- <div class="mission-icon">🤝</div> -->
            <h3>Nos Valeurs</h3>
            <p>Intégrité, excellence et collaboration sont au cœur de tout ce que nous faisons.</p>
        </div>
    </div>
</section>

<!-- ====== ÉQUIPE ====== -->
<section class="section">
    <div class="section-title">
        <h2>Rencontrez Notre Équipe</h2>
        <p>Les talents qui font notre force</p>
    </div>

    <div class="team-grid">
        <div class="profile-card">
            <div class="profile-image">
                <img src="img/directeur.jpg" alt="">
            </div>
            <span class="profile-role">Promoteur</span>
            <h3 class="profile-name">Alexandre K.</h3>
            <p class="profile-bio">Visionnaire et entrepreneur passionné, il a fondé l'auto-école avec l'ambition de révolutionner la formation à la conduite.</p>
        </div>

        <div class="profile-card">
            <div class="profile-image">
                <img src="img/moniteur.jpg" alt="">
            </div>
            <span class="profile-role">Moniteur</span>
            <h3 class="profile-name">Sophie M.</h3>
            <p class="profile-bio">Experte en stratégie et gestion, elle pilote les opérations quotidiennes avec rigueur et créativité.</p>
        </div>

        <div class="profile-card">
            <div class="profile-image">
                <img src="img/secretaire.jpg" alt="">
            </div>
            <span class="profile-role">Secrétaire</span>
            <h3 class="profile-name">Émilie T.</h3>
            <p class="profile-bio">Organisée et méticuleuse, elle assure la coordination administrative avec excellence.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>