<?php
/**
 * Admin - Gestion des textes du site
 * Permet de modifier TOUS les textes visibles sur le site
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$page_title = 'Gestion des textes';
$message = '';

// Sauvegarder les modifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['texts'])) {
    $count = 0;
    foreach ($_POST['texts'] as $id => $value) {
        $pdo->prepare("UPDATE site_texts SET text_value = ? WHERE id = ?")
            ->execute([trim($value), intval($id)]);
        $count++;
    }
    $message = "$count texte(s) mis à jour !";
}

// Filtre par page
$filter_page = $_GET['page'] ?? '';

// Récupérer toutes les pages distinctes
$pages = $pdo->query("SELECT DISTINCT page FROM site_texts ORDER BY page")->fetchAll(PDO::FETCH_COLUMN);

// Récupérer les textes
$where = '';
$params = [];
if ($filter_page) {
    $where = 'WHERE page = ?';
    $params = [$filter_page];
}
$stmt = $pdo->prepare("SELECT * FROM site_texts $where ORDER BY page ASC, text_key ASC");
$stmt->execute($params);
$texts = $stmt->fetchAll();

// Regrouper par page
$grouped = [];
foreach ($texts as $t) {
    $grouped[$t['page']][] = $t;
}

$pageLabels = [
    'home' => '🏠 Accueil',
    'constructeur' => '🏗️ Le constructeur',
    'engagements' => '✅ Engagements',
    'modeles' => '📋 Modèles (liste)',
    'modele' => '🏠 Modèle (détail)',
    'contact' => '📞 Contact',
    'faq' => '❓ FAQ',
    'footer' => '📎 Pied de page',
    'header' => '📌 En-tête',
    '404' => '⚠️ Page 404',
    'mentions' => '📜 Mentions légales',
    'confidentialite' => '🔒 Confidentialité',
    'conditions' => '📃 CGV',
    'estimation' => '💰 Estimation',
    'global' => '🌐 Global (tout le site)',
];

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1 class="admin-title">📝 Gestion des textes</h1>
        <div style="font-size:13px;color:#888;"><?php echo count($texts); ?> texte(s) éditable(s)</div>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (empty($texts)): ?>
    <div class="admin-section" style="text-align:center;padding:60px;">
        <div style="font-size:40px;margin-bottom:15px;">📝</div>
        <h2 style="margin-bottom:10px;">Aucun texte éditable pour le moment</h2>
        <p style="color:#888;">Les textes apparaîtront ici automatiquement après la première visite de chaque page du site.</p>
        <p style="color:#888;margin-top:10px;">Visitez les pages du site (accueil, modèles, engagements...) pour que les textes se créent.</p>
    </div>
    <?php else: ?>

    <!-- Filtres par page -->
    <div class="admin-section" style="margin-bottom:20px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="textes.php" class="btn <?php echo !$filter_page ? 'btn-primary' : 'btn-outline'; ?>" style="font-size:13px;">Toutes</a>
            <?php foreach ($pages as $p): ?>
            <a href="?page=<?php echo urlencode($p); ?>" class="btn <?php echo $filter_page === $p ? 'btn-primary' : 'btn-outline'; ?>" style="font-size:13px;">
                <?php echo $pageLabels[$p] ?? ucfirst($p); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <form method="POST">
        <?php foreach ($grouped as $page => $pageTexts): ?>
        <div class="admin-section" style="margin-bottom:20px;">
            <h2 style="font-size:16px;margin-bottom:15px;padding-bottom:10px;border-bottom:2px solid #eee;">
                <?php echo $pageLabels[$page] ?? ucfirst($page); ?>
                <span style="font-size:12px;color:#999;font-weight:400;margin-left:10px;"><?php echo count($pageTexts); ?> texte(s)</span>
            </h2>

            <div style="display:flex;flex-direction:column;gap:15px;">
                <?php foreach ($pageTexts as $t): ?>
                <div style="display:grid;grid-template-columns:200px 1fr;gap:15px;align-items:start;">
                    <div>
                        <label style="font-weight:600;font-size:13px;color:#333;display:block;">
                            <?php echo htmlspecialchars($t['text_key']); ?>
                        </label>
                        <?php if ($t['description']): ?>
                        <small style="color:#999;"><?php echo htmlspecialchars($t['description']); ?></small>
                        <?php endif; ?>
                        <small style="display:block;color:#ccc;margin-top:2px;"><?php echo $t['text_type']; ?></small>
                    </div>
                    <div>
                        <?php if ($t['text_type'] === 'textarea' || $t['text_type'] === 'html'): ?>
                        <textarea name="texts[<?php echo $t['id']; ?>]" rows="4"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:13px;font-family:inherit;resize:vertical;"><?php echo htmlspecialchars($t['text_value']); ?></textarea>
                        <?php else: ?>
                        <input type="text" name="texts[<?php echo $t['id']; ?>]"
                            value="<?php echo htmlspecialchars($t['text_value']); ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div style="position:sticky;bottom:0;background:#fff;padding:15px 0;border-top:2px solid #1a5653;text-align:center;">
            <button type="submit" class="btn btn-primary" style="padding:12px 40px;font-size:15px;">💾 Enregistrer toutes les modifications</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
