<?php
/**
 * Page d'accueil ORCA V2 - Textes éditables
 */
require_once 'includes/config.php';

$page_title = te('home.meta_title', 'Constructeur maison pas chère Picardie Île-de-France | Maisons ORCA');
$page_description = te('home.meta_description', 'Constructeur de maisons individuelles depuis 1993. 6 modèles de qualité à prix maîtrisé. Devis gratuit !');

$modeles = getModeles(6);
$temoignages = getTemoignagesFeatured(3);
$faq = getFAQ();

include 'includes/header.php';
?>

<!-- Hero -->
<?php
$hero_bg = $site_config['banner_bg_image'] ?? '';
$hero_bg_url = $hero_bg ? url($hero_bg) : '';
$hero_opacity = $site_config['banner_overlay_opacity'] ?? '0.85';
?>
<section class="hero <?php if ($hero_bg_url): ?>hero-has-image<?php endif; ?>" <?php if ($hero_bg_url): ?>style="background-image: url('<?php echo $hero_bg_url; ?>');"<?php endif; ?>>
    <div class="hero-overlay" style="opacity: <?php echo $hero_opacity; ?>"></div>
    <div class="hero-gradient-overlay"></div>
    <div class="container">
        <div class="hero-content">
            <p class="hero-subtitle"><?php echo te('home.hero_subtitle', 'Depuis 1993'); ?></p>
            <h1 class="hero-title"><?php echo nl2br(clean($site_config['site_slogan'] ?? t('home.hero_title', "Votre maison neuve à petit prix,\nsans compromis"))); ?></h1>
            <p class="hero-text"><?php echo te('home.hero_text', '6 modèles standardisés de qualité, livrés clé en main. Prix bloqué, pas de surprise. C\'est l\'engagement ORCA.'); ?></p>
            <div class="hero-buttons">
                <a href="<?php echo url('modeles.php'); ?>" class="btn btn-primary btn-lg"><?php echo te('home.hero_btn_modeles', 'Découvrir nos modèles'); ?></a>
                <a href="<?php echo url('contact.php'); ?>" class="btn btn-white btn-lg"><?php echo te('home.hero_btn_devis', 'Demander un devis gratuit'); ?></a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-number"><?php echo te('home.stat1_number', '30+'); ?></div>
                    <div class="hero-stat-label"><?php echo te('home.stat1_label', 'Années d\'expérience'); ?></div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number"><?php echo te('home.stat2_number', '3000+'); ?></div>
                    <div class="hero-stat-label"><?php echo te('home.stat2_label', 'Maisons construites'); ?></div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-number"><?php echo te('home.stat3_number', '6'); ?></div>
                    <div class="hero-stat-label"><?php echo te('home.stat3_label', 'Modèles disponibles'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pourquoi ORCA -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('home.why_subtitle', 'Notre différence'); ?></p>
            <h2 class="section-title"><?php echo te('home.why_title', 'Pourquoi choisir ORCA ?'); ?></h2>
            <p class="section-text"><?php echo te('home.why_text', 'Nous avons simplifié la construction de votre maison en proposant des modèles optimisés et standardisés. Résultat : des prix 20 à 30% moins chers que la concurrence.'); ?></p>
        </div>

        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <h3 class="feature-title"><?php echo te('home.feature1_title', 'Prix maîtrisé'); ?></h3>
                <p class="feature-text"><?php echo te('home.feature1_text', 'Nos modèles standardisés nous permettent de vous proposer des prix 20 à 30% moins chers que la concurrence, sans compromis sur la qualité.'); ?></p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
                <h3 class="feature-title"><?php echo te('home.feature2_title', 'Garanties solides'); ?></h3>
                <p class="feature-text"><?php echo te('home.feature2_text', 'Garantie décennale, biennale, dommages-ouvrage... Tous nos contrats incluent les garanties obligatoires pour votre sécurité.'); ?></p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></div>
                <h3 class="feature-title"><?php echo te('home.feature3_title', 'Livraison clé en main'); ?></h3>
                <p class="feature-text"><?php echo te('home.feature3_text', 'De la fondation à la peinture, nous gérons tout. Vous n\'avez qu\'à emménager dans votre maison neuve et profiter.'); ?></p>
            </div>
            <div class="feature">
                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
                <h3 class="feature-title"><?php echo te('home.feature4_title', 'Accompagnement personnalisé'); ?></h3>
                <p class="feature-text"><?php echo te('home.feature4_text', 'Un interlocuteur unique vous suit du début à la fin. Réponse sous 24h à toutes vos questions.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Modèles -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('home.modeles_subtitle', 'Nos modèles'); ?></p>
            <h2 class="section-title"><?php echo te('home.modeles_title', '6 maisons pensées pour vous'); ?></h2>
            <p class="section-text"><?php echo te('home.modeles_text', 'Du plain-pied à l\'étage, du traditionnel au contemporain, trouvez le modèle qui correspond à votre style et votre budget.'); ?></p>
        </div>

        <div class="modeles-grid">
            <?php foreach ($modeles as $modele): ?>
            <article class="card">
                <div class="card-image">
                    <img src="<?php echo url('uploads/maisons/' . ($modele['image_principale'] ?? 'default.jpg')); ?>" alt="<?php echo clean($modele['nom']); ?>">
                    <div class="card-badges">
                        <?php echo getEtageBadge($modele['nb_etages']); ?>
                        <?php echo getStyleBadge($modele['style']); ?>
                    </div>
                </div>
                <div class="card-content">
                    <h3 class="card-title"><?php echo clean($modele['nom']); ?></h3>
                    <p class="card-text"><?php echo clean($modele['slogan']); ?></p>
                    <div class="card-specs">
                        <span class="card-spec"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg> <?php echo formatSurface($modele['surface_habitable']); ?></span>
                        <span class="card-spec"><svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg> <?php echo $modele['nb_chambres']; ?> chambres</span>
                    </div>
                    <div class="card-footer">
                        <span class="card-price"><?php echo clean($modele['prix_afficher']); ?></span>
                        <a href="<?php echo url('modele.php?slug=' . $modele['slug']); ?>" class="btn btn-outline btn-sm">Découvrir</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: var(--space-8);">
            <a href="<?php echo url('modeles.php'); ?>" class="btn btn-primary btn-lg"><?php echo te('home.modeles_btn', 'Voir tous les modèles'); ?></a>
        </div>
    </div>
</section>

<!-- Témoignages -->
<?php if (!empty($temoignages)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('home.temoignages_subtitle', 'Ils nous font confiance'); ?></p>
            <h2 class="section-title"><?php echo te('home.temoignages_title', 'Ce que disent nos clients'); ?></h2>
        </div>
        <div class="temoignages-grid">
            <?php foreach ($temoignages as $t): ?>
            <div class="temoignage-card">
                <div class="temoignage-stars"><?php echo str_repeat('★', $t['note']); ?></div>
                <p class="temoignage-text">"<?php echo clean($t['temoignage']); ?>"</p>
                <div class="temoignage-author">
                    <div>
                        <div class="temoignage-name"><?php echo clean($t['prenom'] . ' ' . $t['nom']); ?></div>
                        <div class="temoignage-location"><?php echo clean($t['ville']); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Zone d'intervention -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('home.zone_subtitle', 'Notre zone d\'intervention'); ?></p>
            <h2 class="section-title"><?php echo te('home.zone_title', 'Nous construisons près de chez vous'); ?></h2>
        </div>
        <div class="grid grid-2" style="align-items: center; gap: var(--space-12);">
            <div>
                <p><?php echo te('home.zone_text', 'Nous intervenons dans toute la région Hauts-de-France et en Île-de-France. Voici les départements où nous construisons :'); ?></p>
                <div class="departements-grid" style="margin-top: var(--space-6);">
                    <div><strong><?php echo te('home.dept1', 'Oise (60)'); ?></strong></div>
                    <div><strong><?php echo te('home.dept2', 'Aisne (02)'); ?></strong></div>
                    <div><strong><?php echo te('home.dept3', 'Somme (80)'); ?></strong></div>
                    <div><strong><?php echo te('home.dept4', 'Seine-et-Marne (77)'); ?></strong></div>
                    <div><strong><?php echo te('home.dept5', 'Val-d\'Oise (95)'); ?></strong></div>
                    <div><strong><?php echo te('home.dept6', 'Val-de-Marne (94)'); ?></strong></div>
                </div>
            </div>
            <div>
                <div style="background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-dark) 100%); border-radius: var(--radius-xl); padding: var(--space-8); color: var(--color-white);">
                    <h3 style="color: var(--color-white); margin-bottom: var(--space-4);"><?php echo te('home.agence_title', 'Agence principale'); ?></h3>
                    <p><strong><?php echo htmlspecialchars($site_config['site_name'] ?? 'Maisons ORCA'); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($site_config['site_address'] ?? "119 rue Bordier\n60150 Longueil Annel")); ?></p>
                    <p style="margin-top: var(--space-4);"><a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" style="color: var(--color-white); font-size: var(--text-xl);"><strong><?php echo htmlspecialchars($site_config['site_phone'] ?? '03 44 00 00 00'); ?></strong></a></p>
                    <p style="margin-top: var(--space-2);"><a href="mailto:<?php echo htmlspecialchars($site_config['site_email'] ?? 'contact@maisons-orca.fr'); ?>" style="color: var(--color-primary-light);"><?php echo htmlspecialchars($site_config['site_email'] ?? 'contact@maisons-orca.fr'); ?></a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('home.faq_subtitle', 'Questions fréquentes'); ?></p>
            <h2 class="section-title"><?php echo te('home.faq_title', 'Vous avez des questions ?'); ?></h2>
        </div>
        <div class="faq-list" style="max-width: 800px; margin: 0 auto;">
            <?php foreach (array_slice($faq, 0, 3) as $item): ?>
            <details class="faq-item">
                <summary class="faq-question"><?php echo clean($item['question']); ?></summary>
                <div class="faq-answer"><?php echo nl2br(clean($item['reponse'])); ?></div>
            </details>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: var(--space-8);">
            <a href="<?php echo url('faq.php'); ?>" class="btn btn-outline"><?php echo te('home.faq_btn', 'Voir toutes les questions'); ?></a>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section section-alt">
    <div class="container">
        <div class="cta-block">
            <h2 class="cta-block-title"><?php echo te('home.cta_title', 'Prêt à construire votre maison ?'); ?></h2>
            <p class="cta-block-text"><?php echo te('home.cta_text', 'Demandez votre devis gratuit et sans engagement. Notre équipe vous répond sous 24h avec une estimation personnalisée.'); ?></p>
            <a href="<?php echo url('contact.php'); ?>" class="btn btn-white btn-lg"><?php echo te('home.cta_btn', 'Demander un devis gratuit'); ?></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
