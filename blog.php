<?php
/**
 * Page Blog / Actualités
 */
require_once 'includes/config.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 9;
$offset = ($page - 1) * $per_page;

// Compte total
$total = $pdo->query("SELECT COUNT(*) FROM actualites WHERE is_active = 1")->fetchColumn();
$total_pages = ceil($total / $per_page);

// Récupérer les articles
$stmt = $pdo->prepare("SELECT * FROM actualites WHERE is_active = 1 ORDER BY published_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$per_page, $offset]);
$articles = $stmt->fetchAll();

$page_title = 'Blog & Actualités | Maisons ORCA';
$page_description = 'Découvrez nos actualités, conseils et témoignages sur la construction de maison.';

include 'includes/header.php';
?>

<header class="page-header">
    <div class="container">
        <p class="section-subtitle">Nos actualités</p>
        <h1 class="page-header-title">Blog & Conseils</h1>
        <p class="page-header-text">Toute l'actualité de l'immobilier, nos conseils et les témoignages de nos clients.</p>
    </div>
</header>

<section class="section">
    <div class="container">
        <div class="grid grid-3">
            <?php foreach ($articles as $article): ?>
            <article class="card">
                <div class="card-image">
                    <img src="<?php echo $article['image'] ? url('uploads/actualites/' . $article['image']) : url('images/actualite-default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($article['titre']); ?>">
                    <div class="card-badges">
                        <span class="badge badge-<?php echo $article['categorie']; ?>"><?php echo ucfirst($article['categorie']); ?></span>
                    </div>
                </div>
                <div class="card-content">
                    <p style="font-size: 0.875rem; color: var(--color-gray); margin-bottom: 0.5rem;">
                        <?php echo date('d/m/Y', strtotime($article['published_at'])); ?>
                    </p>
                    <h3 class="card-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                    <p class="card-text"><?php echo htmlspecialchars($article['extrait']); ?></p>
                    <a href="<?php echo url('actualite.php?slug=' . $article['slug']); ?>" class="btn btn-outline btn-sm">Lire la suite</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-outline">← Précédent</a>
            <?php endif; ?>
            
            <span style="padding: 10px;">Page <?php echo $page; ?> / <?php echo $total_pages; ?></span>
            
            <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-outline">Suivant →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.badge-conseil { background: #E3F2FD; color: #1565C0; }
.badge-actualite { background: #E8F5E9; color: #2E7D32; }
.badge-temoignage { background: #F3E5F5; color: #7B1FA2; }
.badge-promo { background: #FFF3E0; color: #E65100; }
</style>

<?php include 'includes/footer.php'; ?>
