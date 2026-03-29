<?php
/**
 * Page Mentions Légales
 */
require_once 'includes/config.php';

$page_title = 'Mentions légales | Maisons ORCA';
$page_description = 'Mentions légales du site Maisons ORCA.';

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1 class="page-header-title">Mentions légales</h1>
    </div>
</header>

<section class="section">
    <div class="container container-narrow">
        <h2>Éditeur du site</h2>
        <p>
            <strong>Maisons ORCA</strong><br>
            119 rue Bordier<br>
            60150 Longueil Annel<br>
            France
        </p>
        <p>
            <strong>Téléphone :</strong> 03 44 00 00 00<br>
            <strong>Email :</strong> contact@maisons-orca.fr
        </p>
        
        <h2 style="margin-top: var(--space-12);">Directeur de la publication</h2>
        <p>M. [Nom du directeur]</p>
        
        <h2 style="margin-top: var(--space-12);">Hébergement</h2>
        <p>
            [Nom de l'hébergeur]<br>
            [Adresse de l'hébergeur]
        </p>
        
        <h2 style="margin-top: var(--space-12);">Propriété intellectuelle</h2>
        <p>L'ensemble du contenu de ce site (textes, images, vidéos, logos) est la propriété exclusive de Maisons ORCA. Toute reproduction, représentation ou diffusion, en tout ou partie, du contenu de ce site par quelque procédé que ce soit est interdite sans l'autorisation expresse et préalable de Maisons ORCA.</p>
        
        <h2 style="margin-top: var(--space-12);">Données personnelles</h2>
        <p>Les informations collectées sur ce site sont traitées conformément à notre <a href="politique-confidentialite.php">politique de confidentialité</a>.</p>
        
        <h2 style="margin-top: var(--space-12);">Cookies</h2>
        <p>Ce site utilise des cookies pour améliorer l'expérience utilisateur. En continuant à naviguer sur ce site, vous acceptez l'utilisation de cookies.</p>
        
        <h2 style="margin-top: var(--space-12);">Crédits photos</h2>
        <p>Les photographies présentées sur ce site sont la propriété de Maisons ORCA ou sont utilisées avec l'autorisation des propriétaires.</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
