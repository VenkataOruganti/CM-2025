    <!-- Footer -->
    <footer>
        <div class="footer-row-1">
            <div class="footer-content">
                <h3 class="footer-logo">CuttingMaster</h3>
                <p class="footer-tagline"><?php _e('footer.tagline'); ?></p>
                <p class="footer-since"><?php _e('footer.since'); ?></p>
            </div>
        </div>
        <div class="footer-row-2">
            <div class="footer-content">
                <p class="footer-copyright"><?php echo str_replace('{year}', date('Y'), __('footer.copyright')); ?></p>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
                lucide.createIcons();
            }
        });

        <?php if (isset($additionalScripts)): ?>
            <?php echo $additionalScripts; ?>
        <?php endif; ?>
    </script>
</body>
</html>
