    </main>
    
    <?php 
    // Charger la config si pas encore disponible
    global $site_config, $pdo;
    if (empty($site_config['site_phone'])) {
        try {
            $stmt = $pdo->query("SELECT cle, valeur FROM config");
            $site_config = [];
            while ($row = $stmt->fetch()) {
                $site_config[$row['cle']] = $row['valeur'];
            }
        } catch (Exception $e) {
            $site_config = [];
        }
    }
    ?>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo"><?php echo te('footer.logo', 'Maisons'); ?> <span>ORCA</span></div>
                    <p class="footer-text"><?php echo htmlspecialchars($site_config['footer_text'] ?? t('footer.description', 'Constructeur de maisons individuelles depuis 1993. 6 modèles de qualité à prix maîtrisé en Picardie et Île-de-France.')); ?></p>
                    <div class="footer-social">
                        <?php if (!empty($site_config['facebook_url'])): ?>
                        <a href="<?php echo $site_config['facebook_url']; ?>" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_config['instagram_url'])): ?>
                        <a href="<?php echo $site_config['instagram_url']; ?>" target="_blank" rel="noopener" aria-label="Instagram">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title"><?php echo te('footer.nav_title', 'Navigation'); ?></h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('index.php'); ?>"><?php echo te('footer.nav_accueil', 'Accueil'); ?></a></li>
                        <li><a href="<?php echo url('constructeur.php'); ?>"><?php echo te('footer.nav_constructeur', 'Le constructeur'); ?></a></li>
                        <li><a href="<?php echo url('modeles.php'); ?>"><?php echo te('footer.nav_modeles', 'Nos modèles'); ?></a></li>
                        <li><a href="<?php echo url('engagements.php'); ?>"><?php echo te('footer.nav_engagements', 'Nos engagements'); ?></a></li>
                        <li><a href="<?php echo url('contact.php'); ?>"><?php echo te('footer.nav_contact', 'Contact'); ?></a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title"><?php echo te('footer.modeles_title', 'Nos modèles'); ?></h4>
                    <ul class="footer-links">
                        <?php
                        // Charger dynamiquement les modèles actifs
                        try {
                            $footer_modeles = $pdo->query("SELECT nom, slug FROM modeles WHERE is_active = 1 ORDER BY ordre_affichage ASC")->fetchAll();
                            foreach ($footer_modeles as $fm):
                        ?>
                        <li><a href="<?php echo url('modele.php?slug=' . $fm['slug']); ?>"><?php echo htmlspecialchars($fm['nom']); ?></a></li>
                        <?php
                            endforeach;
                        } catch (Exception $e) {}
                        ?>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title"><?php echo te('footer.agence_title', 'Agence principale'); ?></h4>
                    <div class="footer-contact-item">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span><?php echo nl2br(htmlspecialchars($site_config['site_address'] ?? "119 rue Bordier\n60150 Longueil Annel")); ?></span>
                    </div>
                    <div class="footer-contact-item">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span><a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '03 44 00 00 00'); ?>"><?php echo htmlspecialchars($site_config['site_phone'] ?? '03 44 00 00 00'); ?></a></span>
                    </div>
                    <div class="footer-contact-item">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span><a href="mailto:<?php echo htmlspecialchars($site_config['site_email'] ?? 'contact@maisons-orca.fr'); ?>"><?php echo htmlspecialchars($site_config['site_email'] ?? 'contact@maisons-orca.fr'); ?></a></span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo te('footer.copyright', 'Maisons ORCA. Tous droits réservés.'); ?></p>
                <p>
                    <a href="<?php echo url('mentions-legales.php'); ?>"><?php echo te('footer.link_mentions', 'Mentions légales'); ?></a> |
                    <a href="<?php echo url('politique-confidentialite.php'); ?>"><?php echo te('footer.link_confidentialite', 'Politique de confidentialité'); ?></a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="<?php echo url('js/main.js'); ?>"></script>

    <?php if (isset($page_js)): ?>
    <script src="<?php echo url('js/' . $page_js); ?>"></script>
    <?php endif; ?>

    <!-- FrenchyBot -->
    <?php if (basename($_SERVER['PHP_SELF']) !== 'estimation.php'): ?>
    <script src="https://bot.frenchycompany.fr/api/v1/embed.js.php?token=83059f1ffd4adf64a5ef5e9a803dd1d2"></script>
    <?php endif; ?>
</body>
</html>
