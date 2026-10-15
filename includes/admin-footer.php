</main>

<!-- Overlay pour mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
(function() {
    var toggle = document.getElementById('sidebarToggle');
    var sidebars = document.querySelectorAll('.sidebar');
    var overlay = document.getElementById('sidebarOverlay');

    if (!toggle) return;

    function isMobile() {
        return window.innerWidth <= 768;
    }

    function openSidebar() {
        sidebars.forEach(function(s) { s.classList.add('mobile-open'); });
        if (overlay) overlay.classList.add('active');
    }

    function closeSidebar() {
        sidebars.forEach(function(s) { s.classList.remove('mobile-open'); });
        if (overlay) overlay.classList.remove('active');
    }

    toggle.addEventListener('click', function() {
        if (!isMobile()) return;
        var isOpen = sidebars[0] && sidebars[0].classList.contains('mobile-open');
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Fermer si on redimensionne en desktop
    window.addEventListener('resize', function() {
        if (!isMobile()) {
            closeSidebar();
        }
    });
})();
</script>

</body>
</html>