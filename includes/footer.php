</main>

<?php if (!$is_admin): ?>
<footer class="footer">
    <div class="footer-container">
        <div class="footer-col">
            <h3>À propos</h3>
            <p>Auto-école dédiée à offrir les meilleures formations.</p>
        </div>
        <div class="footer-col">
            <h3>Liens rapides</h3>
            <ul>
                <li><a href="<?php echo $base; ?>/index.php">Accueil</a></li>
                <li><a href="<?php echo $base; ?>/inscription.php">Inscription</a></li>
                <li><a href="<?php echo $base; ?>/connexion.php">Connexion</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3>Contact</h3>
            <p> +229 00 00 00 00</p>
            <p> contact@autoecole.com</p>
        </div>
    </div>
    <div class="footer-bottom">
        &copy; <?php echo date('Y'); ?> Auto-École. Tous droits réservés.
    </div>
</footer>
<?php endif; ?>

<script src="<?php echo $base; ?>/assets/js/main.js"></script>
</body>
</html>