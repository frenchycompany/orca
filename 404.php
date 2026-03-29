<?php
/**
 * Page 404 personnalisée
 */
require_once 'includes/config.php';

header('HTTP/1.0 404 Not Found');

$page_title = 'Page non trouvée | Maisons ORCA';
$page_description = 'La page que vous recherchez n\'existe pas ou a été déplacée.';

include 'includes/header.php';
?>

<section style="padding: 100px 0; text-align: center; background: linear-gradient(135deg, var(--color-secondary) 0%, var(--color-dark) 100%); color: white;">
    <div class="container">
        <div style="font-size: 120px; font-weight: 700; opacity: 0.3; line-height: 1;">404</div>
        <h1 style="color: white; font-size: 48px; margin: 20px 0;">Page non trouvée</h1>
        <p style="font-size: 20px; opacity: 0.8; max-width: 500px; margin: 0 auto 40px;">
            Oups ! La page que vous recherchez semble avoir pris la clé des champs...
        </p>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo url(); ?>" class="btn btn-primary btn-lg">Retour à l'accueil</a>
            <a href="<?php echo url('modeles.php'); ?>" class="btn btn-outline btn-lg" style="border-color: white; color: white;">Voir nos modèles</a>
        </div>
        
        <!-- Suggestions -->
        <div style="margin-top: 60px; text-align: left; max-width: 600px; margin-left: auto; margin-right: auto;">
            <h3 style="color: white; margin-bottom: 20px;">Vous cherchiez peut-être :</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <a href="<?php echo url('modeles.php'); ?>" style="background: rgba(255,255,255,0.1); padding: 15px 20px; border-radius: 8px; color: white; text-decoration: none; transition: all 0.3s;">
                    🏠 Nos modèles de maisons
                </a>
                <a href="<?php echo url('constructeur.php'); ?>" style="background: rgba(255,255,255,0.1); padding: 15px 20px; border-radius: 8px; color: white; text-decoration: none; transition: all 0.3s;">
                    👷 Le constructeur
                </a>
                <a href="<?php echo url('contact.php'); ?>" style="background: rgba(255,255,255,0.1); padding: 15px 20px; border-radius: 8px; color: white; text-decoration: none; transition: all 0.3s;">
                    📞 Nous contacter
                </a>
                <a href="<?php echo url('engagements.php'); ?>" style="background: rgba(255,255,255,0.1); padding: 15px 20px; border-radius: 8px; color: white; text-decoration: none; transition: all 0.3s;">
                    ✓ Nos engagements
                </a>
            </div>
        </div>
        
        <!-- Barre de recherche -->
        <div style="margin-top: 50px;">
            <p style="opacity: 0.8; margin-bottom: 15px;">Ou contactez-nous directement :</p>
            <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" style="font-size: 24px; color: white; text-decoration: none; font-weight: 600;">
                <?php echo $site_config['site_phone'] ?? '03 44 00 00 00'; ?>
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
