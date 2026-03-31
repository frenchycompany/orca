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
                    <div class="footer-logo">Maisons <span>ORCA</span></div>
                    <p class="footer-text">
                        Constructeur de maisons individuelles depuis 1993.<br>
                        6 modèles de qualité à prix maîtrisé en Picardie et Île-de-France.
                    </p>
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
                    <h4 class="footer-title">Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('index.php'); ?>">Accueil</a></li>
                        <li><a href="<?php echo url('constructeur.php'); ?>">Le constructeur</a></li>
                        <li><a href="<?php echo url('modeles.php'); ?>">Nos modèles</a></li>
                        <li><a href="<?php echo url('engagements.php'); ?>">Nos engagements</a></li>
                        <li><a href="<?php echo url('contact.php'); ?>">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 class="footer-title">Nos modèles</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('modele.php?slug=le-coquelicot'); ?>">Le Coquelicot</a></li>
                        <li><a href="<?php echo url('modele.php?slug=la-tulipe'); ?>">La Tulipe</a></li>
                        <li><a href="<?php echo url('modele.php?slug=l-hibiscus'); ?>">L'Hibiscus</a></li>
                        <li><a href="<?php echo url('modele.php?slug=le-lila'); ?>">Le Lila</a></li>
                        <li><a href="<?php echo url('modele.php?slug=l-orchidee'); ?>">L'Orchidée</a></li>
                        <li><a href="<?php echo url('modele.php?slug=le-magnolia'); ?>">Le Magnolia</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4 class="footer-title">Agence principale</h4>
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
                <p>&copy; <?php echo date('Y'); ?> Maisons ORCA. Tous droits réservés.</p>
                <p>
                    <a href="<?php echo url('mentions-legales.php'); ?>">Mentions légales</a> | 
                    <a href="<?php echo url('politique-confidentialite.php'); ?>">Politique de confidentialité</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script src="<?php echo url('js/main.js'); ?>"></script>

    <?php if (isset($page_js)): ?>
    <script src="<?php echo url('js/' . $page_js); ?>"></script>
    <?php endif; ?>

    <!-- Bannière CTA sticky -->
    <div id="cta-banner" style="display:none;position:fixed;bottom:0;left:0;right:0;background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;padding:12px 20px;z-index:9998;box-shadow:0 -4px 15px rgba(0,0,0,0.15);animation:ctaSlide .4s ease;">
        <div style="max-width:900px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <div style="font-weight:700;font-size:15px;">🏠 Estimez votre maison en 2 minutes</div>
                <div style="font-size:12px;opacity:.8;">Gratuit et sans engagement — Rappel sous 24h</div>
            </div>
            <button onclick="document.getElementById('cta-banner').style.display='none';if(window.cbToggleChat)window.cbToggleChat();else window.location.href='<?php echo url('estimation.php'); ?>';" style="padding:10px 24px;background:#fff;color:#1a5653;border:none;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;white-space:nowrap;">Estimer mon projet →</button>
        </div>
        <span onclick="document.getElementById('cta-banner').style.display='none';sessionStorage.setItem('cta_closed','1');" style="position:absolute;top:8px;right:12px;cursor:pointer;opacity:.6;font-size:18px;">×</span>
    </div>

    <!-- Exit intent popup -->
    <div id="exit-popup" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:10001;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;max-width:440px;width:90%;padding:40px 35px;text-align:center;position:relative;animation:ctaSlide .3s ease;">
            <span onclick="document.getElementById('exit-popup').style.display='none';sessionStorage.setItem('exit_shown','1');" style="position:absolute;top:12px;right:16px;cursor:pointer;font-size:24px;color:#999;">×</span>
            <div style="font-size:40px;margin-bottom:15px;">⏳</div>
            <h2 style="font-size:22px;color:#1a5653;margin-bottom:10px;">Attendez !</h2>
            <p style="font-size:15px;color:#555;margin-bottom:20px;">Obtenez votre <strong>estimation gratuite</strong> avant de partir</p>
            <div style="display:flex;flex-direction:column;gap:10px;text-align:left;margin-bottom:25px;padding:0 10px;">
                <div style="font-size:14px;color:#333;">🏠 Maisons à partir de <strong>145 000 €</strong></div>
                <div style="font-size:14px;color:#333;">📞 Rappel gratuit sous <strong>24h</strong></div>
                <div style="font-size:14px;color:#333;">✅ <strong>Sans aucun engagement</strong></div>
            </div>
            <button onclick="document.getElementById('exit-popup').style.display='none';sessionStorage.setItem('exit_shown','1');if(window.cbToggleChat)window.cbToggleChat();else window.location.href='<?php echo url('estimation.php'); ?>';" style="width:100%;padding:14px;background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;border:none;border-radius:10px;cursor:pointer;font-size:16px;font-weight:700;">Estimer mon projet gratuitement</button>
            <div onclick="document.getElementById('exit-popup').style.display='none';sessionStorage.setItem('exit_shown','1');" style="margin-top:12px;font-size:13px;color:#999;cursor:pointer;">Non merci, je continue ma visite</div>
        </div>
    </div>

    <style>
        @keyframes ctaSlide { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    </style>

    <script>
    // Bannière CTA : apparaît après 4 secondes si pas fermée
    if (!sessionStorage.getItem('cta_closed')) {
        setTimeout(function() {
            var b = document.getElementById('cta-banner');
            if (b) b.style.display = 'block';
        }, 4000);
    }

    // Masquer la bannière quand le chatbot s'ouvre
    window.addEventListener('chatbot-opened', function() {
        var b = document.getElementById('cta-banner');
        if (b) b.style.display = 'none';
    });

    // Exit intent (desktop uniquement, 1 fois par session)
    if (!sessionStorage.getItem('exit_shown') && !('ontouchstart' in window)) {
        var exitTriggered = false;
        document.addEventListener('mouseout', function(e) {
            if (exitTriggered) return;
            if (e.clientY < 5 && e.relatedTarget === null) {
                exitTriggered = true;
                document.getElementById('exit-popup').style.display = 'flex';
                sessionStorage.setItem('exit_shown', '1');
            }
        });
    }
    </script>

    <!-- Chatbot -->
    <?php
    // Ne pas charger le widget chatbot flottant sur la landing page estimation
    if (basename($_SERVER['PHP_SELF']) !== 'estimation.php'):
    ?>
    <script>window.chatbotBaseUrl = '';</script>
    <script src="js/chatbot.js"></script>
    <?php endif; ?>
</body>
</html>
