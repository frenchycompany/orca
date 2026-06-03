<?php
/**
 * Affichage d'une page CMS dynamique
 * URL: /page.php?slug=nom-de-la-page
 */
require_once 'includes/config.php';

$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

if (empty($slug)) {
    redirect('index.php');
}

$stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    header('HTTP/1.0 404 Not Found');
    redirect('404.php');
}

$page_title = $page['meta_title'] ?: $page['titre'] . ' | ' . ($site_config['site_name'] ?? 'Maisons ORCA');
$page_description = $page['meta_description'] ?: '';

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <h1 class="page-header-title"><?php echo htmlspecialchars($page['titre']); ?></h1>
    </div>
</header>

<section class="section">
    <div class="container container-narrow">
        <div class="cms-content" style="font-size: 16px; line-height: 1.8; color: #333;">
            <?php echo $page['contenu']; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
