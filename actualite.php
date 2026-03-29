<?php
/**
 * Page détail d'une actualité
 */
require_once 'includes/config.php';

$slug = isset($_GET['slug']) ? clean($_GET['slug']) : '';

if (!$slug) {
    header('Location: blog.php');
    exit;
}

// Récupérer l'article
$stmt = $pdo->prepare("SELECT * FROM actualites WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    header('Location: blog.php');
    exit;
}

// Incrémenter les vues
$pdo->prepare("UPDATE actualites SET nb_vues = nb_vues + 1 WHERE id = ?")->execute([$article['id']]);

// Articles similaires
$stmt = $pdo->prepare("SELECT * FROM actualites WHERE id != ? AND is_active = 1 AND categorie = ? ORDER BY published_at DESC LIMIT 2");
$stmt->execute([$article['id'], $article['categorie']]);
$similaires = $stmt->fetchAll();

$page_title = $article['titre'] . ' | Maisons ORCA';
$page_description = substr(strip_tags($article['extrait']), 0, 160);

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <div style="margin-bottom: 1rem;">
            <span class="badge badge-<?php echo $article['categorie']; ?>"><?php echo ucfirst($article['categorie']); ?></span>
            <span style="color: rgba(255,255,255,0.7); margin-left: 1rem;"><?php echo date('d/m/Y', strtotime($article['published_at'])); ?></span>
        </div>
        <h1 class="page-header-title"><?php echo htmlspecialchars($article['titre']); ?></h1>
    </div>
</header>

<section class="section">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <?php if ($article['image']): ?>
            <img src="<?php echo url('uploads/actualites/' . $article['image']); ?>" 
                 alt="<?php echo htmlspecialchars($article['titre']); ?>"
                 style="width: 100%; border-radius: 12px; margin-bottom: 2rem;">
            <?php endif; ?>
            
            <div class="article-content" style="font-size: 1.125rem; line-height: 1.8;">
                <p style="font-size: 1.25rem; font-weight: 500; margin-bottom: 2rem; color: var(--color-gray-dark);">
                    <?php echo htmlspecialchars($article['extrait']); ?>
                </p>
                
                <?php echo $article['contenu']; ?>
            </div>
            
            <hr style="margin: 3rem 0; border: none; border-top: 1px solid var(--color-gray-light);">
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <a href="<?php echo url('blog.php'); ?>" class="btn btn-outline">← Retour au blog</a>
                <span style="color: var(--color-gray);"><?php echo $article['nb_vues']; ?> vues</span>
            </div>
        </div>
    </div>
</section>

<?php if ($similaires): ?>
<section class="section section-alt">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 2rem;">Articles similaires</h2>
        <div class="grid grid-2" style="max-width: 800px; margin: 0 auto;">
            <?php foreach ($similaires as $sim): ?>
            <article class="card">
                <div class="card-content">
                    <span class="badge badge-<?php echo $sim['categorie']; ?>"><?php echo ucfirst($sim['categorie']); ?></span>
                    <h3 class="card-title" style="margin-top: 1rem;"><?php echo htmlspecialchars($sim['titre']); ?></h3>
                    <a href="<?php echo url('actualite.php?slug=' . $sim['slug']); ?>" class="btn btn-outline btn-sm">Lire</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.badge-conseil { background: #E3F2FD; color: #1565C0; }
.badge-actualite { background: #E8F5E9; color: #2E7D32; }
.badge-temoignage { background: #F3E5F5; color: #7B1FA2; }
.badge-promo { background: #FFF3E0; color: #E65100; }

.article-content h2 { font-size: 1.5rem; margin: 2rem 0 1rem; }
.article-content h3 { font-size: 1.25rem; margin: 1.5rem 0 1rem; }
.article-content p { margin-bottom: 1rem; }
.article-content ul, .article-content ol { margin-left: 2rem; margin-bottom: 1rem; }
</style>

<?php include 'includes/footer.php'; ?>
