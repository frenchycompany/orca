<?php
/**
 * Page FAQ
 */
require_once 'includes/config.php';

$page_title = 'FAQ - Questions fréquentes | Maisons ORCA';
$page_description = 'Trouvez les réponses à vos questions sur la construction de maison avec ORCA.';

// Récupérer la FAQ
$faq_items = getFAQ();

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle">FAQ</p>
        <h1 class="page-header-title">Questions fréquentes</h1>
        <p class="page-header-text">Trouvez les réponses aux questions les plus fréquemment posées.</p>
    </div>
</header>

<section class="section">
    <div class="container container-narrow">
        <div class="faq-list">
            <?php foreach ($faq_items as $item): ?>
            <div class="faq-item">
                <button class="faq-question">
                    <?php echo clean($item['question']); ?>
                    <svg class="faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="faq-answer">
                    <p><?php echo nl2br(clean($item['reponse'])); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div style="margin-top: var(--space-12); background: var(--color-gray-lighter); padding: var(--space-8); border-radius: var(--radius-lg); text-align: center;">
            <h3>Vous ne trouvez pas la réponse à votre question ?</h3>
            <p style="margin-top: var(--space-4);">Notre équipe est à votre disposition pour vous aider.</p>
            <div style="margin-top: var(--space-6);">
                <a href="<?php echo url('contact.php'); ?>" class="btn btn-primary">Contactez-nous</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
