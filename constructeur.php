<?php
/**
 * Page Le Constructeur ORCA V2
 */
require_once 'includes/config.php';

// S'assurer que la config est chargée
global $site_config, $pdo;
if (empty($site_config['site_phone'])) {
    try {
        $stmt = $pdo->query("SELECT cle, valeur FROM config");
        while ($row = $stmt->fetch()) {
            $site_config[$row['cle']] = $row['valeur'];
        }
    } catch (Exception $e) {
        $site_config = [];
    }
}

$page_title = te('constructeur.page_title', 'Qui sommes-nous ? | Constructeur Maisons ORCA');
$page_description = te('constructeur.page_description', 'Découvrez ORCA, constructeur de maisons individuelles depuis 1993. Notre expertise à votre service en Picardie et Île-de-France.');

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle"><?php echo te('constructeur.hero_subtitle', 'Notre histoire'); ?></p>
        <h1 class="page-header-title"><?php echo te('constructeur.hero_title', 'Le constructeur'); ?></h1>
        <p class="page-header-text"><?php echo te('constructeur.hero_text', '30 ans d\'expérience au service de votre projet de construction'); ?></p>
    </div>
</header>

<!-- Section: Présentation -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: center; gap: var(--space-12);">
            <div>
                <p class="section-subtitle"><?php echo te('constructeur.presentation_subtitle', 'Depuis 1993'); ?></p>
                <h2 class="section-title"><?php echo te('constructeur.presentation_title', 'Une entreprise familiale à votre écoute'); ?></h2>
                <p><?php echo t('constructeur.presentation_text1', 'Fondée en 1993 dans l\'Oise, Maisons ORCA est une entreprise familiale qui a construit sa réputation sur des valeurs simples : <strong>transparence, qualité et respect des engagements.</strong>'); ?></p>
                <p style="margin-top: var(--space-4);"><?php echo te('constructeur.presentation_text2', 'Au fil des années, nous avons construit plus de 3 000 maisons individuelles dans la région Picardie et Île-de-France. Notre expertise nous permet aujourd\'hui de proposer des modèles optimisés qui allient qualité et prix compétitifs.'); ?></p>
                <p style="margin-top: var(--space-4);"><?php echo te('constructeur.presentation_text3', 'Notre siège social situé à Longueil-Annel dans l\'Oise est le cœur de notre activité. C\'est de là que nos équipes coordonnent l\'ensemble des chantiers et accompagnent nos clients du début à la fin de leur projet.'); ?></p>
            </div>
            <div style="background: var(--color-gray-lighter); border-radius: var(--radius-xl); padding: var(--space-8); position: relative;">
                <!-- Chiffres clés -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-6);">
                    <div style="text-align: center; padding: var(--space-6); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-3xl); font-weight: 700; color: var(--color-primary);">30+</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.stat_experience', 'Années d\'expérience'); ?></div>
                    </div>
                    <div style="text-align: center; padding: var(--space-6); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-3xl); font-weight: 700; color: var(--color-primary);">3000+</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.stat_houses', 'Maisons construites'); ?></div>
                    </div>
                    <div style="text-align: center; padding: var(--space-6); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-3xl); font-weight: 700; color: var(--color-primary);">6</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.stat_models', 'Modèles disponibles'); ?></div>
                    </div>
                    <div style="text-align: center; padding: var(--space-6); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-3xl); font-weight: 700; color: var(--color-primary);">50+</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.stat_collaborators', 'Collaborateurs'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: Notre philosophie -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('constructeur.philosophy_subtitle', 'Notre différence'); ?></p>
            <h2 class="section-title"><?php echo te('constructeur.philosophy_title', 'La maison pour tous'); ?></h2>
            <p class="section-text"><?php echo te('constructeur.philosophy_text', 'Notre mission est simple : permettre au plus grand nombre de devenir propriétaire d\'une maison neuve de qualité.'); ?></p>
        </div>

        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title"><?php echo te('constructeur.feature1_title', 'Prix transparent'); ?></h3>
                <p class="feature-text"><?php echo te('constructeur.feature1_text', 'Le prix affiché est le prix final. Pas de surprise, pas de dépassement de budget.'); ?></p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="feature-title"><?php echo te('constructeur.feature2_title', 'Construction rapide'); ?></h3>
                <p class="feature-text"><?php echo te('constructeur.feature2_text', 'Grâce à nos modèles standardisés, nous livrons votre maison en 4 à 6 mois après permis.'); ?></p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <h3 class="feature-title"><?php echo te('constructeur.feature3_title', 'Qualité ORCA'); ?></h3>
                <p class="feature-text"><?php echo te('constructeur.feature3_text', 'Des matériaux soigneusement sélectionnés et des artisans qualifiés pour votre maison.'); ?></p>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="feature-title"><?php echo te('constructeur.feature4_title', 'Accompagnement'); ?></h3>
                <p class="feature-text"><?php echo te('constructeur.feature4_text', 'Un interlocuteur unique vous accompagne de la signature à la remise des clés.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Notre méthode -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('constructeur.method_subtitle', 'Notre méthode'); ?></p>
            <h2 class="section-title"><?php echo te('constructeur.method_title', 'Comment ça marche ?'); ?></h2>
            <p class="section-text"><?php echo te('constructeur.method_text', 'De la première prise de contact à la remise des clés, nous vous accompagnons à chaque étape.'); ?></p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: var(--space-4);">
            <!-- Étape 1 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; margin: 0 auto var(--space-4);">1</div>
                <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);"><?php echo te('constructeur.step1_title', 'Premier contact'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.step1_text', 'Vous nous contactez et nous définissons ensemble vos besoins.'); ?></p>
            </div>
            <!-- Étape 2 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; margin: 0 auto var(--space-4);">2</div>
                <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);"><?php echo te('constructeur.step2_title', 'Visite terrain'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.step2_text', 'Nous visitons votre terrain pour vérifier la faisabilité.'); ?></p>
            </div>
            <!-- Étape 3 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; margin: 0 auto var(--space-4);">3</div>
                <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);"><?php echo te('constructeur.step3_title', 'Permis de construire'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.step3_text', 'Nous réalisons et déposons votre permis de construire.'); ?></p>
            </div>
            <!-- Étape 4 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; margin: 0 auto var(--space-4);">4</div>
                <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);"><?php echo te('constructeur.step4_title', 'Construction'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.step4_text', 'Votre maison prend vie. Nous vous tenons informés chaque semaine.'); ?></p>
            </div>
            <!-- Étape 5 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; margin: 0 auto var(--space-4);">5</div>
                <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-2);"><?php echo te('constructeur.step5_title', 'Remise des clés'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray);"><?php echo te('constructeur.step5_text', 'Vous emménagez dans votre nouvelle maison !'); ?></p>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="<?php echo url('contact.php'); ?>" class="btn btn-primary btn-lg"><?php echo te('constructeur.method_cta', 'Commencer mon projet'); ?></a>
        </div>
    </div>
</section>

<!-- Section: Notre équipe -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php echo te('constructeur.team_subtitle', 'Notre équipe'); ?></p>
            <h2 class="section-title"><?php echo te('constructeur.team_title', 'Des professionnels à votre service'); ?></h2>
        </div>

        <div class="grid grid-3">
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 100px; height: 100px; background: var(--color-gray-light); border-radius: 50%; margin: 0 auto var(--space-4);"></div>
                <h3><?php echo te('constructeur.team1_title', 'Commerciaux'); ?></h3>
                <p style="color: var(--color-gray);"><?php echo te('constructeur.team1_text', 'À votre écoute pour définir votre projet et vous guider dans le choix de votre modèle.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 100px; height: 100px; background: var(--color-gray-light); border-radius: 50%; margin: 0 auto var(--space-4);"></div>
                <h3><?php echo te('constructeur.team2_title', 'Techniciens'); ?></h3>
                <p style="color: var(--color-gray);"><?php echo te('constructeur.team2_text', 'Ils étudient votre terrain et assurent la conformité de votre construction.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 100px; height: 100px; background: var(--color-gray-light); border-radius: 50%; margin: 0 auto var(--space-4);"></div>
                <h3><?php echo te('constructeur.team3_title', 'Conducteurs de travaux'); ?></h3>
                <p style="color: var(--color-gray);"><?php echo te('constructeur.team3_text', 'Ils pilotent votre chantier et coordonnent les artisans pour respecter les délais.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Zone d'intervention -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: center;">
            <div>
                <p class="section-subtitle"><?php echo te('constructeur.zone_subtitle', 'Où construire ?'); ?></p>
                <h2 class="section-title"><?php echo te('constructeur.zone_title', 'Notre zone d\'intervention'); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($site_config['zone_intervention'] ?? te('constructeur.zone_default_text', 'Nous intervenons dans un rayon de 100km autour de notre siège dans l\'Oise.'))); ?></p>
            </div>
            <div style="background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-dark) 100%); color: var(--color-white); padding: var(--space-8); border-radius: var(--radius-xl);">
                <h3 style="color: var(--color-white);"><?php echo te('constructeur.zone_cta_title', 'Vous ne savez pas si nous intervenons chez vous ?'); ?></h3>
                <p style="margin-top: var(--space-4);"><?php echo te('constructeur.zone_cta_text', 'Contactez-nous, nous étudierons votre projet avec plaisir.'); ?></p>
                <div style="margin-top: var(--space-6);">
                    <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" style="color: var(--color-white); font-size: var(--text-2xl); font-weight: 700;"><?php echo htmlspecialchars($site_config['site_phone'] ?? '03 44 00 00 00'); ?></a>
                </div>
                <a href="<?php echo url('contact.php'); ?>" class="btn btn-white" style="margin-top: var(--space-6);"><?php echo te('constructeur.zone_cta_button', 'Nous contacter'); ?></a>
            </div>
        </div>
    </div>
</section>

<!-- Section: CTA -->
<section class="section section-alt">
    <div class="container">
        <div class="cta-block">
            <h2 class="cta-block-title"><?php echo te('constructeur.cta_title', 'Prêt à nous rencontrer ?'); ?></h2>
            <p class="cta-block-text"><?php echo te('constructeur.cta_text', 'Venez nous rencontrer à notre siège ou dans l\'une de nos agences. Nous serons ravis de discuter de votre projet.'); ?></p>
            <a href="<?php echo url('contact.php'); ?>" class="btn btn-white btn-lg"><?php echo te('constructeur.cta_button', 'Prendre rendez-vous'); ?></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
