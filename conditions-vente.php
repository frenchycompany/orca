<?php
/**
 * Page Conditions Générales de Vente
 */
require_once 'includes/config.php';

$page_title = 'Conditions Générales de Vente | Maisons ORCA';
$page_description = 'Conditions générales de vente de Maisons ORCA. Constructeur de maisons individuelles.';

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1 class="page-header-title">Conditions Générales de Vente</h1>
    </div>
</header>

<section class="section">
    <div class="container container-narrow">
        <p style="margin-bottom: 2rem;"><strong>Dernière mise à jour : <?php echo date('d/m/Y'); ?></strong></p>
        
        <h2>1. Objet</h2>
        <p>Les présentes Conditions Générales de Vente (CGV) régissent les relations contractuelles entre la société Maisons ORCA, ci-après dénommée "le Constructeur", et toute personne physique ou morale souhaitant bénéficier de ses services de construction de maison individuelle, ci-après dénommée "le Client".</p>
        
        <h2 style="margin-top: var(--space-12);">2. Champ d'application</h2>
        <p>Les présentes CGV s'appliquent à toute commande de construction de maison individuelle passée auprès de Maisons ORCA. Toute commande implique l'adhésion entière et sans réserve du Client aux présentes CGV.</p>
        
        <h2 style="margin-top: var(--space-12);">3. Description des services</h2>
        <p>Maisons ORCA propose la construction de maisons individuelles selon des modèles standardisés. Nos prestations comprennent :</p>
        <ul style="margin-left: var(--space-6); margin-bottom: var(--space-4);">
            <li>La construction de la maison selon le modèle choisi</li>
            <li>Les fondations et les travaux de gros œuvre</li>
            <li>La fourniture et pose des menuiseries</li>
            <li>Les installations sanitaires et électriques</li>
            <li>Les finitions intérieures et extérieures</li>
            <li>Le nettoyage final de chantier</li>
        </ul>
        
        <h2 style="margin-top: var(--space-12);">4. Prix et paiement</h2>
        <p><strong>4.1.</strong> Les prix sont indiqués en euros TTC. Le prix convenu est fermé et définitif, sous réserve de l'exactitude des informations fournies par le Client.</p>
        <p><strong>4.2.</strong> Le paiement s'effectue selon l'échéancier suivant :</p>
        <ul style="margin-left: var(--space-6); margin-bottom: var(--space-4);">
            <li>5% à la signature du contrat</li>
            <li>25% à l'ouverture du chantier</li>
            <li>40% à l'achèvement des fondations et du gros œuvre</li>
            <li>25% à l'achèvement des travaux de second œuvre</li>
            <li>5% à la remise des clés</li>
        </ul>
        
        <h2 style="margin-top: var(--space-12);">5. Délai de construction</h2>
        <p>Le délai de construction est indiqué dans le contrat. Il court à compter de l'obtention du permis de construire et des délais légaux de recours. Ce délai peut être prolongé en cas de force majeure ou de circonstances indépendantes de la volonté du Constructeur.</p>
        
        <h2 style="margin-top: var(--space-12);">6. Garanties</h2>
        <p>Le Constructeur est tenu par les garanties légales suivantes :</p>
        <ul style="margin-left: var(--space-6); margin-bottom: var(--space-4);">
            <li><strong>Garantie décennale (10 ans)</strong> : couvre les dommages affectant la solidité de l'ouvrage</li>
            <li><strong>Garantie biennale (2 ans)</strong> : couvre les équipements et éléments d'équipement</li>
            <li><strong>Garantie dommages-ouvrage</strong> : couvre les dommages survenus pendant la construction</li>
            <li><strong>Garantie de parfait achèvement</strong> : couvre les défauts de finition pendant un an</li>
        </ul>
        
        <h2 style="margin-top: var(--space-12);">7. Assurance</h2>
        <p>Le Constructeur souscrit une assurance responsabilité civile décennale couvrant l'ensemble des constructions réalisées. Le Client doit souscrire une assurance dommages-ouvrage avant le début des travaux.</p>
        
        <h2 style="margin-top: var(--space-12);">8. Réception des travaux</h2>
        <p>La réception des travaux a lieu à la demande du Constructeur ou du Client. Elle donne lieu à l'établissement d'un procès-verbal de réception mentionnant éventuellement des réserves. La réception vaut acceptation de l'ouvrage.</p>
        
        <h2 style="margin-top: var(--space-12);">9. Réclamations</h2>
        <p>Toute réclamation doit être adressée par écrit à l'adresse suivante :</p>
        <p style="margin-left: var(--space-6);">
            Maisons ORCA<br>
            119 rue Bordier<br>
            60150 Longueil Annel<br>
            Email : contact@maisons-orca.fr
        </p>
        
        <h2 style="margin-top: var(--space-12);">10. Droit applicable et juridiction compétente</h2>
        <p>Les présentes CGV sont soumises au droit français. En cas de litige, une solution amiable sera recherchée avant toute action judiciaire. À défaut d'accord amiable, les tribunaux compétents seront ceux du lieu du siège social du Constructeur.</p>
        
        <h2 style="margin-top: var(--space-12);">11. Protection des données</h2>
        <p>Les données personnelles collectées sont traitées conformément à notre <a href="politique-confidentialite.php">Politique de confidentialité</a> et au Règlement Général sur la Protection des Données (RGPD).</p>
        
        <hr style="margin: var(--space-12) 0;">
        <p style="font-size: 0.875rem; color: var(--color-gray);">Document mis à jour le <?php echo date('d/m/Y'); ?>.</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
