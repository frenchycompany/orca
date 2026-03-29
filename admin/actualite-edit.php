<?php
/**
 * Admin - Édition d'une actualité
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Ajouter un article';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$actualite = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM actualites WHERE id = ?");
    $stmt->execute([$id]);
    $actualite = $stmt->fetch();
    if (!$actualite) {
        header('Location: actualites.php');
        exit;
    }
    $page_title = 'Modifier : ' . $actualite['titre'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => $_POST['titre'] ?? '',
        'slug' => slugify($_POST['titre'] ?? ''),
        'extrait' => $_POST['extrait'] ?? '',
        'contenu' => $_POST['contenu'] ?? '',
        'categorie' => $_POST['categorie'] ?? 'actualite',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'published_at' => $_POST['published_at'] ?? date('Y-m-d H:i:s')
    ];
    
    if ($id) {
        $sql = "UPDATE actualites SET titre=?, slug=?, extrait=?, contenu=?, categorie=?, 
                is_active=?, is_featured=?, published_at=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['titre'], $data['slug'], $data['extrait'], $data['contenu'], 
            $data['categorie'], $data['is_active'], $data['is_featured'], 
            $data['published_at'], $id
        ]);
    } else {
        $sql = "INSERT INTO actualites (titre, slug, extrait, contenu, categorie, is_active, is_featured, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['titre'], $data['slug'], $data['extrait'], $data['contenu'], 
            $data['categorie'], $data['is_active'], $data['is_featured'], $data['published_at']
        ]);
    }
    
    header('Location: actualites.php');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title"><?php echo $actualite ? 'Modifier' : 'Ajouter'; ?> un article</h1>
    
    <div class="admin-section">
        <form method="POST" style="max-width: 900px;">
            <div class="form-group">
                <label class="form-label">Titre *</label>
                <input type="text" name="titre" class="form-control" value="<?php echo $actualite['titre'] ?? ''; ?>" required>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <select name="categorie" class="form-control">
                        <option value="actualite" <?php echo ($actualite['categorie'] ?? '') == 'actualite' ? 'selected' : ''; ?>>Actualité</option>
                        <option value="conseil" <?php echo ($actualite['categorie'] ?? '') == 'conseil' ? 'selected' : ''; ?>>Conseil</option>
                        <option value="temoignage" <?php echo ($actualite['categorie'] ?? '') == 'temoignage' ? 'selected' : ''; ?>>Témoignage</option>
                        <option value="promo" <?php echo ($actualite['categorie'] ?? '') == 'promo' ? 'selected' : ''; ?>>Promotion</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date de publication</label>
                    <input type="datetime-local" name="published_at" class="form-control" 
                           value="<?php echo isset($actualite['published_at']) ? date('Y-m-d\TH:i', strtotime($actualite['published_at'])) : date('Y-m-d\TH:i'); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Extrait (chapô)</label>
                <textarea name="extrait" class="form-control" rows="3"><?php echo $actualite['extrait'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contenu complet</label>
                <textarea name="contenu" class="form-control" rows="15"><?php echo $actualite['contenu'] ?? ''; ?></textarea>
                <small style="color: var(--color-gray);">HTML autorisé</small>
            </div>
            
            <div class="form-check" style="margin: 20px 0;">
                <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($actualite['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                <label for="is_featured">Mettre en avant sur la page d'accueil</label>
            </div>
            
            <div class="form-check" style="margin: 20px 0;">
                <input type="checkbox" id="is_active" name="is_active" <?php echo ($actualite['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label for="is_active">Publié (visible sur le site)</label>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="actualites.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
