<?php
/**
 * Page Nos Engagements ORCA V2
 */
require_once 'includes/config.php';

$page_title = 'Nos garanties et engagements | Maisons ORCA';
$page_description = 'RE2020, garanties décennales, accompagnement personnalisé. Découvrez tous nos engagements pour votre sérénité.';

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle">Votre sérénité</p>
        <h1 class="page-header-title">Nos engagements</h1>
        <p class="page-header-text">Parce que construire votre maison est un acte important, nous nous engageons à votre côté.</p>
    </div>
</header>

<!-- Section: RE2020 -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: center; gap: var(--space-12);">
            <div>
                <p class="section-subtitle">Normes environnementales</p>
                <h2 class="section-title">RE2020 : bâtir pour l'avenir</h2>
                <p>Toutes nos maisons sont conformes à la réglementation environnementale RE2020, qui remplace la RT2012 depuis le 1er janvier 2022.</p>
                <p style="margin-top: var(--space-4);"><strong>Ce que cela signifie concrètement :</strong></p>
                <ul style="margin-top: var(--space-4); list-style: none;">
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong>Isolation renforcée :</strong> Murs, toiture et menuiseries haute performance pour limiter les déperditions de chaleur.</span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong>Étanchéité à l'air optimisée :</strong> Contrôles rigoureux pour éviter les infiltrations d'air parasites.</span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong>Chauffage performant :</strong> Systèmes de chauffage et production d'eau chaude à haute efficacité énergétique.</span>
                    </li>
                    <li style="display: flex; gap: var(--space-3); margin-bottom: var(--space-3);">
                        <span style="color: var(--color-primary);">✓</span>
                        <span><strong>Économies d'énergie :</strong> Jusqu'à 30% d'économies sur vos factures énergétiques par rapport à une maison ancienne.</span>
                    </li>
                </ul>
            </div>
            <div style="background: var(--color-gray-lighter); border-radius: var(--radius-xl); padding: var(--space-8);">
                <div style="text-align: center; margin-bottom: var(--space-6);">
                    <div style="display: inline-block; background: var(--color-success); color: var(--color-white); padding: var(--space-2) var(--space-4); border-radius: var(--radius-full); font-weight: 600; font-size: var(--text-sm);">✓ 100% RE2020</div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-4);">
                    <div style="text-align: center; padding: var(--space-4); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; color: var(--color-primary);">A</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);">Étiquette énergie</div>
                    </div>
                    <div style="text-align: center; padding: var(--space-4); background: var(--color-white); border-radius: var(--radius-lg);">
                        <div style="font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700; color: var(--color-primary);">A</div>
                        <div style="font-size: var(--text-sm); color: var(--color-gray);">Étiquette climat</div>
                    </div>
                </div>
                <p style="margin-top: var(--space-6); font-size: var(--text-sm); color: var(--color-gray); text-align: center;">
                    Toutes nos maisons atteignent le niveau de performance le plus élevé pour le confort de votre famille et la protection de l'environnement.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Garanties -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Votre protection</p>
            <h2 class="section-title">Toutes les garanties</h2>
            <p class="section-text">Nous vous accompagnons avant, pendant et après la construction avec des garanties solides.</p>
        </div>
        
        <div class="grid grid-4">
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-family: var(--font-secondary); font-size: var(--text-2xl); font-weight: 700;">10</div>
                <h3 style="font-size: var(--text-lg);">Garantie décennale</h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);">Obligatoire pour tout constructeur, elle couvre les dommages affectant la solidité de votre maison.</p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4); font-family: var(--font-secondary); font-size: var(--text-xl); font-weight: 700;">2</div>
                <h3 style="font-size: var(--text-lg);">Garantie biennale</h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);">Elle couvre les équipements et les éléments d'équipement dissociables du bâtiment.</p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 style="font-size: var(--text-lg);">Dommages-ouvrage</h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);">Assurance qui couvre les dommages survenant pendant la construction.</p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto var(--space-4);">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 style="font-size: var(--text-lg);">Responsabilité civile</h3>
                <p style="font-size: var(--text-sm); color: var(--color-gray); margin-top: var(--space-2);">Protection complète en cas de dommages causés à des tiers pendant les travaux.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Accompagnement -->
<section class="section">
    <div class="container">
        <div class="grid grid-2" style="align-items: center; gap: var(--space-12);">
            <div style="order: 2;">
                <p class="section-subtitle">Votre projet</p>
                <h2 class="section-title">Un accompagnement sur mesure</h2>
                <p>Chez ORCA, chaque client est unique. C'est pourquoi nous vous assignons un interlocuteur unique qui vous accompagne du début à la fin de votre projet.</p>
                
                <div style="margin-top: var(--space-8);">
                    <h3 style="font-size: var(--text-lg); margin-bottom: var(--space-4);">Nos engagements :</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">1</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong>Réponse sous 24h</strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);">Nous vous répondons dans les 24h ouvrées à toute demande.</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">2</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong>Devis gratuit et détaillé</strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);">Un devis complet sans frais caché, valable 3 mois.</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">3</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong>Suivi hebdomadaire</strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);">Photos et comptes-rendus de l'avancement chaque semaine.</p>
                            </div>
                        </div>
                        <div style="display: flex; gap: var(--space-4); align-items: flex-start;">
                            <div style="min-width: 40px; width: 40px; height: 40px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 600;">4</div>
                            <div style="flex: 1; min-width: 0;">
                                <strong>SAV réactif</strong>
                                <p style="color: var(--color-gray); font-size: var(--text-sm); margin-top: var(--space-1);">Un service après-vente à votre écoute après la livraison.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="background: var(--color-gray-lighter); border-radius: var(--radius-xl); padding: var(--space-8); order: 1;">
                <h3 style="margin-bottom: var(--space-6);">Ce que disent nos clients</h3>
                <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg); margin-bottom: var(--space-4);">
                    <p style="font-style: italic;">"Un accompagnement irréprochable du début à la fin. Notre conseiller était toujours disponible pour répondre à nos questions."</p>
                    <div style="margin-top: var(--space-4); font-weight: 600;">Marie et Jean D.</div>
                    <div style="font-size: var(--text-sm); color: var(--color-gray);">Maison Tulipe - Compiègne</div>
                </div>
                <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                    <p style="font-style: italic;">"Livraison dans les temps, prix respecté, qualité au rendez-vous. Je recommande ORCA sans hésiter."</p>
                    <div style="margin-top: var(--space-4); font-weight: 600;">Pierre B.</div>
                    <div style="font-size: var(--text-sm); color: var(--color-gray);">Maison Hibiscus - Creil</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: Qualité -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Notre savoir-faire</p>
            <h2 class="section-title">Qualité et personnalisation</h2>
        </div>
        
        <div class="grid grid-3">
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);">Matériaux sélectionnés</h3>
                <p style="color: var(--color-gray);">Nous travaillons avec des fournisseurs reconnus pour la qualité de leurs produits. Briques, menuiseries, isolation : chaque élément est choisi pour sa durabilité.</p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);">Artisans qualifiés</h3>
                <p style="color: var(--color-gray);">Nous collaborons avec des artisans locaux sélectionnés pour leur savoir-faire et leur sérieux. Maçons, couvreurs, électriciens : tous sont des professionnels expérimentés.</p>
            </div>
            <div style="background: var(--color-white); padding: var(--space-6); border-radius: var(--radius-lg);">
                <h3 style="margin-bottom: var(--space-4);">Contrôles qualité</h3>
                <p style="color: var(--color-gray);">Chaque étape de la construction fait l'objet de contrôles rigoureux. Nos conducteurs de travaux vérifient chaque détail pour garantir votre satisfaction.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Process -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Votre projet</p>
            <h2 class="section-title">Processus projet client</h2>
            <p class="section-text">De la première prise de contact à la remise des clés, nous vous accompagnons à chaque étape.</p>
        </div>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <!-- Timeline -->
            <div class="timeline" style="position: relative; padding-left: 80px;">
                <!-- Ligne verticale -->
                <div style="position: absolute; left: 24px; top: 10px; bottom: 10px; width: 2px; background: var(--color-gray-light);"></div>
                
                <!-- Étapes -->
                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">1</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;">Premier contact</h3>
                    <p style="color: var(--color-gray);">Vous nous contactez par téléphone, email ou formulaire. Nous prenons le temps de comprendre votre projet : budget, surface souhaitée, délai, terrain...</p>
                </div>
                
                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">2</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;">Visite et étude</h3>
                    <p style="color: var(--color-gray);">Nous visitons votre terrain pour évaluer la faisabilité technique et les éventuelles contraintes. Nous vous proposons ensuite le modèle le plus adapté.</p>
                </div>
                
                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">3</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;">Signature et permis</h3>
                    <p style="color: var(--color-gray);">Après acceptation du devis, nous signons le contrat. Nous réalisons et déposons votre permis de construire auprès de la mairie.</p>
                </div>
                
                <div style="position: relative; margin-bottom: var(--space-8); padding-bottom: var(--space-4);">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">4</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;">Construction</h3>
                    <p style="color: var(--color-gray);">Votre maison prend vie ! Nous vous tenons informés chaque semaine de l'avancement avec photos et comptes-rendus.</p>
                </div>
                
                <div style="position: relative;">
                    <div style="position: absolute; left: -56px; top: 0; width: 48px; height: 48px; background: var(--color-primary); color: var(--color-white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; z-index: 1;">5</div>
                    <h3 style="margin-bottom: var(--space-2); line-height: 48px;">Livraison</h3>
                    <p style="color: var(--color-gray);">Nous effectuons la remise des clés après une visite complète de la maison. Vous emménagez et profitez de votre nouvelle vie !</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section: CTA -->
<section class="section section-alt">
    <div class="container">
        <div class="cta-block">
            <h2 class="cta-block-title">Convaincu par nos engagements ?</h2>
            <p class="cta-block-text">Demandez votre devis gratuit et découvrez combien vous pouvez économiser avec ORCA.</p>
            <a href="<?php echo url('contact.php'); ?>" class="btn btn-white btn-lg">Demander un devis gratuit</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
