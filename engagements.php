<?php
/**
 * Page Nos Engagements ORCA V2
 */
require_once 'includes/config.php';

$page_title = t('engagements.page_title', 'Nos garanties et engagements | Maisons ORCA');
$page_description = t('engagements.page_description', 'RE2020, garanties décennales, accompagnement personnalisé. Découvrez tous nos engagements pour votre sérénité.');

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle"><?php te('engagements.header_subtitle', 'Votre sérénité'); ?></p>
        <h1 class="page-header-title"><?php te('engagements.header_title', 'Nos engagements'); ?></h1>
        <p class="page-header-text"><?php te('engagements.header_text', 'Parce que construire votre maison est un acte important, nous nous engageons à votre côté.'); ?></p>
    </div>
</header>

<!-- Section: RE2020 -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: center; gap: var(--space-12);">
            <div>
                <p class="section-subtitle"><?php te('engagements.re2020_subtitle', 'Normes environnementales'); ?></p>
                <h2 class="section-title"><?php te('engagements.re2020_title', 'RE2020 : bâtir pour l\'avenir'); ?></h2>
                <p><?php te('engagements.re2020_intro', 'Toutes nos maisons sont conformes à la réglementation environnementale RE2020, qui remplace la RT2012 depuis le 1er janvier 2022.'); ?></p>
                <p style="margin-top: var(--space-4);"><strong><?php te('engagements.re2020_meaning', 'Ce que cela signifie concrètement :'); ?></strong></p>
                <ul style="margin-top: var(--space-4); list-style: none;">
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong><?php te('engagements.re2020_isolation_title', 'Isolation renforcée :'); ?></strong> <?php te('engagements.re2020_isolation_text', 'Murs, toiture et menuiseries haute performance pour limiter les déperditions de chaleur.'); ?></span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong><?php te('engagements.re2020_airtight_title', 'Étanchéité à l\'air optimisée :'); ?></strong> <?php te('engagements.re2020_airtight_text', 'Contrôles rigoureux pour éviter les infiltrations d\'air parasites.'); ?></span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong><?php te('engagements.re2020_heating_title', 'Chauffage performant :'); ?></strong> <?php te('engagements.re2020_heating_text', 'Systèmes de chauffage et production d\'eau chaude à haute efficacité énergétique.'); ?></span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong><?php te('engagements.re2020_savings_title', 'Économies d\'énergie :'); ?></strong> <?php te('engagements.re2020_savings_text', 'Jusqu\'à 30% d\'économies sur vos factures énergétiques par rapport à une maison ancienne.'); ?></span>
                    </li>
                </ul>
            </div>
            <div style="background: var(--color-gray-lighter); border-radius: var(--radius-xl); padding: var(--space-8);">
                <div style="text-align: center; margin-bottom: var(--space-6);">
                    <div style="display: inline-block; background: var(--color-success); color: var(--color-white); padding: var(--space-2) var(--space-4); border-radius: var(--radius-full); font-weight: 600; font-size: var(--text-sm);"><?php te('engagements.re2020_badge', '✓ 100% RE2020'); ?></div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-4);">
                    <div style="text-align: center; padding: var(--space-4); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; color: var(--color-primary);">A</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php te('engagements.re2020_energy_label', 'Étiquette énergie'); ?></div>
                    </div>
                    <div style="text-align: center; padding: var(--space-4); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; color: var(--color-primary);">A</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php te('engagements.re2020_climate_label', 'Étiquette climat'); ?></div>
                    </div>
                </div>
                <p style="margin-top: var(--space-6); font-size: var(--text-sm); color: var(--color-gray); text-align: center;">
                    <?php te('engagements.re2020_performance_text', 'Toutes nos maisons atteignent le niveau de performance le plus élevé pour le confort de votre famille et la protection de l\'environnement.'); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Garanties -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php te('engagements.guarantees_subtitle', 'Votre protection'); ?></p>
            <h2 class="section-title"><?php te('engagements.guarantees_title', 'Toutes les garanties'); ?></h2>
            <p class="section-text"><?php te('engagements.guarantees_text', 'Nous vous accompagnons avant, pendant et après la construction avec des garanties solides.'); ?></p>
        </div>

        <div class="grid grid-4">
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700;">10</div>
                <h3 style="font-size: var(--text-lg);"><?php te('engagements.guarantee_decennial_title', 'Garantie décennale'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);"><?php te('engagements.guarantee_decennial_text', 'Obligatoire pour tout constructeur, elle couvre les dommages affectant la solidité de votre maison.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-family: var(--font-secondary); font-size: var(--text-xl); font-weight: 700;">2</div>
                <h3 style="font-size: var(--text-lg);"><?php te('engagements.guarantee_biennial_title', 'Garantie biennale'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);"><?php te('engagements.guarantee_biennial_text', 'Elle couvre les équipements et les éléments d\'équipement dissociables du bâtiment.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 style="font-size: var(--text-lg);"><?php te('engagements.guarantee_damage_title', 'Dommages-ouvrage'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);"><?php te('engagements.guarantee_damage_text', 'Assurance qui couvre les dommages survenant pendant la construction.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 style="font-size: var(--text-lg);"><?php te('engagements.guarantee_liability_title', 'Responsabilité civile'); ?></h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);"><?php te('engagements.guarantee_liability_text', 'Protection complète en cas de dommages causés à des tiers pendant les travaux.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Accompagnement -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: center; gap: var(--space-12);">
            <div style="order: 2;">
                <p class="section-subtitle"><?php te('engagements.support_subtitle', 'Votre projet'); ?></p>
                <h2 class="section-title"><?php te('engagements.support_title', 'Un accompagnement sur mesure'); ?></h2>
                <p><?php te('engagements.support_intro', 'Chez ORCA, chaque client est unique. C\'est pourquoi nous vous assignons un interlocuteur unique qui vous accompagne du début à la fin de votre projet.'); ?></p>

                <div style="margin-top: var(--space-8);">
                    <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-4);"><?php te('engagements.commitments_heading', 'Nos engagements :'); ?></h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">1</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong><?php te('engagements.commitment_response_title', 'Réponse sous 24h'); ?></strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);"><?php te('engagements.commitment_response_text', 'Nous vous répondons dans les 24h ouvrées à toute demande.'); ?></p>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">2</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong><?php te('engagements.commitment_quote_title', 'Devis gratuit et détaillé'); ?></strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);"><?php te('engagements.commitment_quote_text', 'Un devis complet sans frais caché, valable 3 mois.'); ?></p>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">3</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong><?php te('engagements.commitment_tracking_title', 'Suivi hebdomadaire'); ?></strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);"><?php te('engagements.commitment_tracking_text', 'Photos et comptes-rendus de l\'avancement chaque semaine.'); ?></p>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">4</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong><?php te('engagements.commitment_aftersales_title', 'SAV réactif'); ?></strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);"><?php te('engagements.commitment_aftersales_text', 'Un service après-vente à votre écoute après la livraison.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="background: var(--color-gray-lighter); border-radius: var(--radius-xl); padding: var(--space-8); order: 1;">
                <h3 style="margin-bottom: var(--space-6);"><?php te('engagements.testimonials_heading', 'Ce que disent nos clients'); ?></h3>
                <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); margin-bottom: var(--space-4);">
                    <p style="font-style: italic;"><?php te('engagements.testimonial_1_text', '"Un accompagnement irréprochable du début à la fin. Notre conseiller était toujours disponible pour répondre à nos questions."'); ?></p>
                    <div style="margin-top: var(--space-4); font-weight: 600;"><?php te('engagements.testimonial_1_author', 'Marie et Jean D.'); ?></div>
                    <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php te('engagements.testimonial_1_project', 'Maison Tulipe - Compiègne'); ?></div>
                </div>
                <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                    <p style="font-style: italic;"><?php te('engagements.testimonial_2_text', '"Livraison dans les temps, prix respecté, qualité au rendez-vous. Je recommande ORCA sans hésiter."'); ?></p>
                    <div style="margin-top: var(--space-4); font-weight: 600;"><?php te('engagements.testimonial_2_author', 'Pierre B.'); ?></div>
                    <div style="font-size: var(--text-sm); color: var(--color-gray);"><?php te('engagements.testimonial_2_project', 'Maison Hibiscus - Creil'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: Qualité -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php te('engagements.quality_subtitle', 'Notre savoir-faire'); ?></p>
            <h2 class="section-title"><?php te('engagements.quality_title', 'Qualité et personnalisation'); ?></h2>
        </div>

        <div class="grid grid-3">
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);"><?php te('engagements.quality_materials_title', 'Matériaux sélectionnés'); ?></h3>
                <p style="color: var(--color-gray);"><?php te('engagements.quality_materials_text', 'Nous travaillons avec des fournisseurs reconnus pour la qualité de leurs produits. Briques, menuiseries, isolation : chaque élément est choisi pour sa durabilité.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);"><?php te('engagements.quality_craftsmen_title', 'Artisans qualifiés'); ?></h3>
                <p style="color: var(--color-gray);"><?php te('engagements.quality_craftsmen_text', 'Nous collaborons avec des artisans locaux sélectionnés pour leur savoir-faire et leur sérieux. Maçons, couvreurs, électriciens : tous sont des professionnels expérimentés.'); ?></p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);"><?php te('engagements.quality_controls_title', 'Contrôles qualité'); ?></h3>
                <p style="color: var(--color-gray);"><?php te('engagements.quality_controls_text', 'Chaque étape de la construction fait l\'objet de contrôles rigoureux. Nos conducteurs de travaux vérifient chaque détail pour garantir votre satisfaction.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Process -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle"><?php te('engagements.process_subtitle', 'Votre projet'); ?></p>
            <h2 class="section-title"><?php te('engagements.process_title', 'Processus projet client'); ?></h2>
            <p class="section-text"><?php te('engagements.process_text', 'De la première prise de contact à la remise des clés, nous vous accompagnons à chaque étape.'); ?></p>
        </div>

        <div style="max-width: 800px; margin: 0 auto;">
            <!-- Timeline -->
            <div class="timeline" style="position: relative; padding-left: 80px;">
                <!-- Ligne verticale -->
                <div style="position: absolute; left: 24px; top: 10px; bottom: 10px; width: 2px; background: var(--color-gray-light);"></div>

                <!-- Étapes -->
                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">1</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;"><?php te('engagements.step1_title', 'Premier contact'); ?></h3>
                    <p style="color: var(--color-gray);"><?php te('engagements.step1_text', 'Vous nous contactez par téléphone, email ou formulaire. Nous prenons le temps de comprendre votre projet : budget, surface souhaitée, délai, terrain...'); ?></p>
                </div>

                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">2</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;"><?php te('engagements.step2_title', 'Visite et étude'); ?></h3>
                    <p style="color: var(--color-gray);"><?php te('engagements.step2_text', 'Nous visitons votre terrain pour évaluer la faisabilité technique et les éventuelles contraintes. Nous vous proposons ensuite le modèle le plus adapté.'); ?></p>
                </div>

                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">3</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;"><?php te('engagements.step3_title', 'Signature et permis'); ?></h3>
                    <p style="color: var(--color-gray);"><?php te('engagements.step3_text', 'Après acceptation du devis, nous signons le contrat. Nous réalisons et déposons votre permis de construire auprès de la mairie.'); ?></p>
                </div>

                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">4</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;"><?php te('engagements.step4_title', 'Construction'); ?></h3>
                    <p style="color: var(--color-gray);"><?php te('engagements.step4_text', 'Votre maison prend vie ! Nous vous tenons informés chaque semaine de l\'avancement avec photos et comptes-rendus.'); ?></p>
                </div>

                <div style="position: relative;">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">5</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;"><?php te('engagements.step5_title', 'Livraison'); ?></h3>
                    <p style="color: var(--color-gray);"><?php te('engagements.step5_text', 'Nous effectuons la remise des clés après une visite complète de la maison. Vous emménagez et profitez de votre nouvelle vie !'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: CTA -->
<section class="section section-alt">
    <div class="container">
        <div class="cta-block">
            <h2 class="cta-block-title"><?php te('engagements.cta_title', 'Convaincu par nos engagements ?'); ?></h2>
            <p class="cta-block-text"><?php te('engagements.cta_text', 'Demandez votre devis gratuit et découvrez combien vous pouvez économiser avec ORCA.'); ?></p>
            <a href="<?php echo url('contact.php'); ?>" class="btn btn-white btn-lg"><?php te('engagements.cta_button', 'Demander un devis gratuit'); ?></a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
