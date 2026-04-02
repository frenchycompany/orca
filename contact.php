<?php
/**
 * Page Contact ORCA V2
 */
require_once 'includes/config.php';

$page_title = t('contact.page_title', 'Contactez-nous | Demandez un devis | Maisons ORCA');
$page_description = t('contact.page_description', 'Demandez un devis gratuit ou demandez à être rappelé. Notre équipe vous répond sous 24h.');

// Récupérer le modèle pré-sélectionné si présent
$modele_slug = isset($_GET['modele']) ? clean($_GET['modele']) : '';
$modele_selected = null;
if ($modele_slug) {
    $modele_selected = getModeleBySlug($modele_slug);
}

// Récupérer tous les modèles pour le select
$modeles = getModeles();

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle"><?php echo te('contact.subtitle', 'Contact'); ?></p>
        <h1 class="page-header-title"><?php echo te('contact.title', 'Discutons de votre projet'); ?></h1>
        <p class="page-header-text"><?php echo te('contact.header_text', 'Demandez un devis gratuit ou laissez-nous vos coordonnées pour être rappelé. Réponse sous 24h garantie.'); ?></p>
    </div>
</header>

<section class="section">
    <div class="container">
        <div class="contact-section">
            <!-- Informations de contact -->
            <div class="contact-info">
                <h2 class="contact-info-title"><?php echo te('contact.info_title', 'Nos coordonnées'); ?></h2>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="contact-info-label"><?php echo te('contact.label_address', 'Adresse'); ?></div>
                        <div class="contact-info-value"><?php echo nl2br(htmlspecialchars($site_config['site_address'] ?? "119 rue Bordier\n60150 Longueil Annel")); ?></div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="contact-info-label"><?php echo te('contact.label_phone', 'Téléphone'); ?></div>
                        <div class="contact-info-value">
                            <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" style="color: var(--color-white);">
                                <?php echo $site_config['site_phone'] ?? '03 44 00 00 00'; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="contact-info-label"><?php echo te('contact.label_email', 'Email'); ?></div>
                        <div class="contact-info-value">
                            <a href="mailto:<?php echo $site_config['site_email'] ?? 'contact@maisons-orca.fr'; ?>" style="color: var(--color-white);">
                                <?php echo $site_config['site_email'] ?? 'contact@maisons-orca.fr'; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="contact-info-label"><?php echo te('contact.label_hours', 'Horaires'); ?></div>
                        <div class="contact-info-value">
                            <?php echo te('contact.hours_weekday', 'Lundi au Vendredi : 9h-12h / 14h-18h'); ?><br>
                            <?php echo te('contact.hours_saturday', 'Samedi : 10h-17h sur rendez-vous'); ?>
                        </div>
                    </div>
                </div>

                <div style="margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <h3 style="color: var(--color-white); margin-bottom: var(--space-4);"><?php echo te('contact.zone_title', 'Zone d\'intervention'); ?></h3>
                    <p style="opacity: 0.8; font-size: var(--text-sm);">
                        <?php echo nl2br(htmlspecialchars($site_config['zone_intervention'] ?? t('contact.zone_default', "Nous construisons dans l'Oise (60), l'Aisne (02), la Somme (80), la Seine-et-Marne (77), le Val-d'Oise (95), le Val-de-Marne (94), la Seine-Saint-Denis (93) et l'Essonne (91)."))); ?>
                    </p>
                </div>
            </div>

            <!-- Formulaire -->
            <div class="contact-form-wrapper">
                <h2 style="margin-bottom: var(--space-2);">
                    <?php echo $modele_selected ? t('contact.quote_for', 'Demander un devis pour ') . clean($modele_selected['nom']) : t('contact.quote_free', 'Demander un devis gratuit'); ?>
                </h2>
                <p style="color: var(--color-gray); margin-bottom: var(--space-6);"><?php echo te('contact.form_intro', 'Remplissez le formulaire ci-dessous, nous vous recontactons sous 24h.'); ?></p>

                <?php displayFlashMessage(); ?>

                <form action="contact-process.php" method="POST" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Type de demande -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_request_type', 'Type de demande'); ?></label>
                            <select name="type_demande" class="form-control">
                                <option value="devis"><?php echo te('contact.option_quote', 'Devis gratuit'); ?></option>
                                <option value="rappel"><?php echo te('contact.option_callback', 'Être rappelé'); ?></option>
                                <option value="info"><?php echo te('contact.option_info', 'Demande d\'information'); ?></option>
                                <option value="brochure"><?php echo te('contact.option_brochure', 'Recevoir la brochure'); ?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_civility', 'Civilité'); ?></label>
                            <select name="civilite" class="form-control">
                                <option value="M"><?php echo te('contact.option_mr', 'Monsieur'); ?></option>
                                <option value="Mme"><?php echo te('contact.option_mrs', 'Madame'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Nom et prénom -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_lastname', 'Nom'); ?> <span class="required">*</span></label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_firstname', 'Prénom'); ?></label>
                            <input type="text" name="prenom" class="form-control">
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_email_field', 'Email'); ?> <span class="required">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_phone_field', 'Téléphone'); ?> <span class="required">*</span></label>
                            <input type="tel" name="telephone" class="form-control" placeholder="06 12 34 56 78" required>
                        </div>
                    </div>

                    <!-- Localisation -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_postal_code', 'Code postal'); ?></label>
                            <input type="text" name="code_postal" class="form-control" maxlength="5" placeholder="60150">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_city', 'Ville'); ?></label>
                            <input type="text" name="ville" class="form-control">
                        </div>
                    </div>

                    <!-- Projet -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_model', 'Modèle intéressé'); ?></label>
                            <select name="modele_interesse" class="form-control">
                                <option value=""><?php echo te('contact.option_choose_model', '-- Choisir un modèle --'); ?></option>
                                <?php foreach ($modeles as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php echo ($modele_selected && $modele_selected['id'] == $m['id']) ? 'selected' : ''; ?>>
                                    <?php echo clean($m['nom']); ?> (<?php echo formatSurface($m['surface_habitable']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php echo te('contact.label_land', 'Avez-vous un terrain ?'); ?></label>
                            <select name="terrain_prevu" class="form-control">
                                <option value="0"><?php echo te('contact.option_no_land', 'Non, je recherche'); ?></option>
                                <option value="1"><?php echo te('contact.option_has_land', 'Oui, j\'ai un terrain'); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Délai -->
                    <div class="form-group">
                        <label class="form-label"><?php echo te('contact.label_timeline', 'Délai souhaité pour votre projet'); ?></label>
                        <select name="delai_souhaite" class="form-control">
                            <option value="immediatement"><?php echo te('contact.option_immediately', 'Immédiatement'); ?></option>
                            <option value="3-mois"><?php echo te('contact.option_3_months', 'Dans 3 mois'); ?></option>
                            <option value="6-mois" selected><?php echo te('contact.option_6_months', 'Dans 6 mois'); ?></option>
                            <option value="1-an"><?php echo te('contact.option_1_year', 'Dans 1 an'); ?></option>
                            <option value="plus"><?php echo te('contact.option_later', 'Plus tard'); ?></option>
                        </select>
                    </div>

                    <!-- Message -->
                    <div class="form-group">
                        <label class="form-label"><?php echo te('contact.label_message', 'Message (optionnel)'); ?></label>
                        <textarea name="commentaire" class="form-control" rows="4" placeholder="<?php echo te('contact.placeholder_message', 'Dites-nous en plus sur votre projet...'); ?>"></textarea>
                    </div>

                    <!-- Consentement -->
                    <div class="form-check" style="margin-bottom: var(--space-6);">
                        <input type="checkbox" id="consent" name="consent" required>
                        <label for="consent">
                            <?php echo te('contact.consent_text', 'J\'accepte que mes données personnelles soient utilisées pour me recontacter concernant ma demande.'); ?>
                            <a href="<?php echo url('politique-confidentialite.php'); ?>" target="_blank"><?php echo te('contact.privacy_policy', 'Politique de confidentialité'); ?></a>.
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <?php echo te('contact.submit_button', 'Envoyer ma demande'); ?>
                    </button>

                    <p style="margin-top: var(--space-4); font-size: var(--text-sm); color: var(--color-gray); text-align: center;">
                        <span class="required">*</span> <?php echo te('contact.required_fields', 'Champs obligatoires'); ?>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Section: FAQ rapide -->
<section class="section section-alt">
    <div class="container container-narrow">
        <div class="section-header">
            <h2 class="section-title"><?php echo te('contact.faq_title', 'Questions fréquentes'); ?></h2>
        </div>

        <div class="faq-list">
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo te('contact.faq1_question', 'Combien de temps faut-il pour construire une maison ORCA ?'); ?>
                    <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo te('contact.faq1_answer', 'Le délai de construction d\'une maison ORCA est généralement de 4 à 6 mois après obtention du permis de construire. Ce délai peut varier en fonction des conditions météorologiques et de la complexité du terrain.'); ?></p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo te('contact.faq2_question', 'Puis-je visiter une maison témoin ?'); ?>
                    <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo te('contact.faq2_answer', 'Oui, nous avons plusieurs maisons témoins ouvertes à la visite sur rendez-vous. Contactez-nous pour programmer votre visite.'); ?></p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo te('contact.faq3_question', 'Le prix affiché est-il le prix final ?'); ?>
                    <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo te('contact.faq3_answer', 'Oui, chez ORCA le prix affiché est le prix final. Nos modèles sont standardisés et nous ne proposons pas de personnalisation, ce qui nous permet de garantir des prix sans surprise.'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
