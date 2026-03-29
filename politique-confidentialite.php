<?php
/**
 * Page Politique de Confidentialité
 */
require_once 'includes/config.php';

$page_title = 'Politique de confidentialité | Maisons ORCA';
$page_description = 'Politique de confidentialité du site Maisons ORCA.';

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1 class="page-header-title">Politique de confidentialité</h1>
    </div>
</header>

<section class="section">
    <div class="container container-narrow">
        <h2>Introduction</h2>
        <p>Maisons ORCA s'engage à protéger la vie privée des utilisateurs de son site internet. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos données personnelles.</p>
        
        <h2 style="margin-top: var(--space-12);">Données collectées</h2>
        <p>Nous collectons les données suivantes lorsque vous remplissez nos formulaires de contact :</p>
        <ul style="margin-left: var(--space-6);">
            <li>Nom et prénom</li>
            <li>Adresse email</li>
            <li>Numéro de téléphone</li>
            <li>Adresse postale</li>
            <li>Informations sur votre projet immobilier</li>
        </ul>
        
        <h2 style="margin-top: var(--space-12);">Finalité de la collecte</h2>
        <p>Vos données sont collectées pour les finalités suivantes :</p>
        <ul style="margin-left: var(--space-6);">
            <li>Vous recontacter suite à votre demande de renseignements</li>
            <li>Vous établir un devis personnalisé</li>
            <li>Vous accompagner dans votre projet de construction</li>
            <li>Vous envoyer des informations sur nos offres (avec votre consentement)</li>
        </ul>
        
        <h2 style="margin-top: var(--space-12);">Base légale</h2>
        <p>Le traitement de vos données est fondé sur votre consentement, que vous donnez en cochant la case prévue à cet effet dans nos formulaires.</p>
        
        <h2 style="margin-top: var(--space-12);">Durée de conservation</h2>
        <p>Vos données sont conservées pendant 3 ans à compter de votre dernier contact avec nous, sauf opposition de votre part.</p>
        
        <h2 style="margin-top: var(--space-12);">Destinataires des données</h2>
        <p>Vos données sont destinées exclusivement aux services commerciaux et techniques de Maisons ORCA. Elles ne sont pas transmises à des tiers.</p>
        
        <h2 style="margin-top: var(--space-12);">Vos droits</h2>
        <p>Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez des droits suivants :</p>
        <ul style="margin-left: var(--space-6);">
            <li><strong>Droit d'accès :</strong> obtenir la confirmation que des données vous concernant sont traitées</li>
            <li><strong>Droit de rectification :</strong> faire corriger les données inexactes vous concernant</li>
            <li><strong>Droit à l'effacement :</strong> demander la suppression de vos données</li>
            <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos données</li>
            <li><strong>Droit à la portabilité :</strong> récupérer vos données dans un format structuré</li>
        </ul>
        <p style="margin-top: var(--space-4);">Pour exercer ces droits, contactez-nous par email à <a href="mailto:contact@maisons-orca.fr">contact@maisons-orca.fr</a> ou par courrier à l'adresse indiquée dans les mentions légales.</p>
        
        <h2 style="margin-top: var(--space-12);">Sécurité</h2>
        <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données contre tout accès non autorisé, altération ou destruction.</p>
        
        <h2 style="margin-top: var(--space-12);">Cookies</h2>
        <p>Notre site utilise des cookies techniques nécessaires à son fonctionnement et des cookies de mesure d'audience. Vous pouvez gérer vos préférences en matière de cookies via les paramètres de votre navigateur.</p>
        
        <h2 style="margin-top: var(--space-12);">Modification de la politique</h2>
        <p>Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment. Les modifications prendront effet dès leur publication sur le site.</p>
        
        <h2 style="margin-top: var(--space-12);">Contact</h2>
        <p>Pour toute question concernant cette politique de confidentialité, vous pouvez nous contacter à <a href="mailto:contact@maisons-orca.fr">contact@maisons-orca.fr</a>.</p>
        
        <p style="margin-top: var(--space-12); font-size: var(--text-sm); color: var(--color-gray);">Dernière mise à jour : <?php echo date('d/m/Y'); ?></p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
