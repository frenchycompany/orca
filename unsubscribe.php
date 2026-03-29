<?php
/**
 * Désinscription newsletter
 */
require_once 'includes/config.php';
require_once 'includes/newsletter.php';

$email = $_GET['email'] ?? '';
$success = false;

if ($email && unsubscribeNewsletter($email)) {
    $success = true;
}

$page_title = 'Désinscription | Maisons ORCA';
include 'includes/header.php';
?>

<section class="section" style="text-align: center; padding: 100px 0;">
    <div class="container" style="max-width: 600px;">
        <?php if ($success): ?>
        <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
        <h1>Désinscription réussie</h1>
        <p>Vous ne recevrez plus nos emails. Nous sommes désolés de vous voir partir !</p>
        <?php else: ?>
        <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
        <h1>Erreur</h1>
        <p>Cet email n'est pas inscrit à notre newsletter ou a déjà été désinscrit.</p>
        <?php endif; ?>
        
        <a href="<?php echo url(); ?>" class="btn btn-primary" style="margin-top: 30px;">Retour à l'accueil</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
