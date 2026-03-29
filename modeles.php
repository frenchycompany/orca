<?php
/**
 * Page liste des modèles ORCA V2
 */
require_once 'includes/config.php';

$page_title = 'Nos 6 modèles de maisons | Maisons ORCA';
$page_description = 'Découvrez nos 6 modèles de maisons individuelles. Plain-pied ou étage, traditionnel ou contemporain. Prix à partir de 145 000 €.';

// Récupérer les modèles
$modeles = getModeles();

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle">Nos modèles</p>
        <h1 class="page-header-title">6 maisons, votre style</h1>
        <p class="page-header-text">Du plain-pied au 2 étages, du traditionnel au moderne. Trouvez la maison qui vous ressemble parmi notre gamme de modèles standardisés.</p>
    </div>
</header>

<section class="section">
    <div class="container">
        <!-- Filtres -->
        <div class="filters">
            <button class="filter-btn active" data-filter="all">Tous les modèles</button>
            <button class="filter-btn" data-filter="plain-pied">Plain-pied</button>
            <button class="filter-btn" data-filter="1-etage">À étage</button>
            <button class="filter-btn" data-filter="traditionnel">Traditionnel</button>
            <button class="filter-btn" data-filter="contemporain">Contemporain</button>
            <button class="filter-btn" data-filter="moderne">Moderne</button>
        </div>
        
        <!-- Grille des modèles -->
        <div class="modeles-grid">
            <?php foreach ($modeles as $modele): ?>
            <article class="card" data-category="<?php echo $modele['nb_etages']; ?> <?php echo $modele['style']; ?>">
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
                    <div class="card-meta">
                        <span class="card-meta-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                            <?php echo formatSurface($modele['surface_habitable']); ?>
                        </span>
                        <span class="card-meta-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                            <?php echo $modele['nb_chambres']; ?> chambres
                        </span>
                        <span class="card-meta-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <?php echo $modele['nb_salles_bain']; ?> SdB
                        </span>
                    </div>
                    <div class="card-footer">
                        <span class="card-price"><?php echo clean($modele['prix_afficher']); ?></span>
                        <a href="<?php echo url('modele.php?slug=' . $modele['slug']); ?>" class="btn btn-primary btn-sm">Découvrir</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Section: Pourquoi modèles standards -->
<section class="section section-alt">
    <div class="container">
        <div class="grid grid-2" style="align-items: center;">
            <div>
                <p class="section-subtitle">Notre concept</p>
                <h2 class="section-title">Pourquoi des modèles sans personnalisation ?</h2>
                <p>Chez ORCA, nous avons fait le choix de la standardisation pour vous offrir le meilleur rapport qualité-prix.</p>
                <ul style="margin-top: var(--space-6); list-style: none;">
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-4);">
                        <span style="color: var(--color-primary); font-weight: bold;">✓</span>
                        <span><strong>Prix optimisé :</strong> En supprimant les personnalisations, nous négocions les matériaux en gros volumes et optimisons la construction.</span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-4);">
                        <span style="color: var(--color-primary); font-weight: bold;">✓</span>
                        <span><strong>Délai maîtrisé :</strong> Nos équipes connaissent parfaitement chaque modèle. Résultat : une construction plus rapide.</span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-4);">
                        <span style="color: var(--color-primary); font-weight: bold;">✓</span>
                        <span><strong>Pas de surprise :</strong> Le prix affiché est le prix final. Pas d'options cachées, pas de dépassement de budget.</span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-4);">
                        <span style="color: var(--color-primary); font-weight: bold;">✓</span>
                        <span><strong>Qualité validée :</strong> Chaque modèle est testé et éprouvé. Vous choisissez une maison dont la qualité est garantie.</span>
                    </li>
                </ul>
            </div>
            <div>
                <div style="background: var(--color-white); padding: var(--space-8); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
                    <h3 style="margin-bottom: var(--space-4);">Ce qui est inclus</h3>
                    <ul style="list-style: none;">
                        <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-light); display: flex; justify-content: space-between;">
                            <span>Construction clé en main</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                        <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-light); display: flex; justify-content: space-between;">
                            <span>Conformité RE2020</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                        <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-light); display: flex; justify-content: space-between;">
                            <span>Garantie décennale</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                        <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-light); display: flex; justify-content: space-between;">
                            <span>Volets roulants électriques</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                        <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-light); display: flex; justify-content: space-between;">
                            <span>Carrelage et parquet</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                        <li style="padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-light); display: flex; justify-content: space-between;">
                            <span>Porte de garage sectionnelle</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                        <li style="padding: var(--space-3) 0; display: flex; justify-content: space-between;">
                            <span>Chauffage gaz/plancher chauffant</span>
                            <span style="color: var(--color-success); font-weight: bold;">✓</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: CTA -->
<section class="section">
    <div class="container">
        <div class="cta-block">
            <h2 class="cta-block-title">Besoin d'aide pour choisir ?</h2>
            <p class="cta-block-text">Nos conseillers sont là pour vous guider vers le modèle qui correspond le mieux à vos besoins et votre budget.</p>
            <div style="display: flex; gap: var(--space-4); justify-content: center; flex-wrap: wrap;">
                <a href="tel:0344000000" class="btn btn-white btn-lg">Nous appeler</a>
                <a href="<?php echo url('contact.php'); ?>" class="btn btn-outline btn-lg" style="border-color: white; color: white;">Être rappelé</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
