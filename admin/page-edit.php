<?php
/**
 * Admin - Édition d'une page CMS
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Modifier une page';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$page = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    if (!$page) {
        header('Location: pages.php');
        exit;
    }
    $page_title = 'Modifier : ' . $page['titre'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titre' => $_POST['titre'] ?? '',
        'slug' => slugify($_POST['titre'] ?? ''),
        'contenu' => $_POST['contenu'] ?? '',
        'meta_title' => $_POST['meta_title'] ?? '',
        'meta_description' => $_POST['meta_description'] ?? '',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'in_menu' => isset($_POST['in_menu']) ? 1 : 0,
        'menu_order' => intval($_POST['menu_order'] ?? 0)
    ];
    
    if ($id) {
        $sql = "UPDATE pages SET titre=?, slug=?, contenu=?, meta_title=?, meta_description=?, 
                is_active=?, in_menu=?, menu_order=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['titre'], $data['slug'], $data['contenu'], $data['meta_title'], 
            $data['meta_description'], $data['is_active'], $data['in_menu'], 
            $data['menu_order'], $id
        ]);
    } else {
        $sql = "INSERT INTO pages (titre, slug, contenu, meta_title, meta_description, is_active, in_menu, menu_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['titre'], $data['slug'], $data['contenu'], $data['meta_title'], 
            $data['meta_description'], $data['is_active'], $data['in_menu'], $data['menu_order']
        ]);
        
        // Créer le fichier PHP correspondant
        $filename = '../' . $data['slug'] . '.php';
        $content = "<?php\nrequire_once 'includes/config.php';\n\n";
        $content .= "\$stmt = \$pdo->prepare(\"SELECT * FROM pages WHERE slug = ? AND is_active = 1\");\n";
        $content .= "\$stmt->execute(['" . $data['slug'] . "']);\n";
        $content .= "\$page = \$stmt->fetch();\n\n";
        $content .= "if (!\$page) { header('HTTP/1.0 404 Not Found'); header('Location: index.php'); exit; }\n\n";
        $content .= "\$page_title = \$page['meta_title'] ?: \$page['titre'];\n";
        $content .= "\$page_description = \$page['meta_description'];\n";
        $content .= "include 'includes/header.php';\n?>\n\n";
        $content .= "<header class=\"page-header\">\n";
        $content .= "    <div class=\"container\">\n";
        $content .= "        <h1 class=\"page-header-title\"><?php echo \$page['titre']; ?></h1>\n";
        $content .= "    </div>\n";
        $content .= "</header>\n\n";
        $content .= "<section class=\"section\">\n";
        $content .= "    <div class=\"container container-narrow\">\n";
        $content .= "        <?php echo \$page['contenu']; ?>\n";
        $content .= "    </div>\n";
        $content .= "</section>\n\n";
        $content .= "<?php include 'includes/footer.php'; ?>\n";
        
        file_put_contents($filename, $content);
    }
    
    header('Location: pages.php');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title"><?php echo $page ? 'Modifier' : 'Ajouter'; ?> une page</h1>
    
    <div class="admin-section">
        <form method="POST" style="max-width: 900px;">
            <div class="form-group">
                <label class="form-label">Titre de la page *</label>
                <input type="text" name="titre" class="form-control" value="<?php echo $page['titre'] ?? ''; ?>" required>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Ordre dans le menu</label>
                    <input type="number" name="menu_order" class="form-control" value="<?php echo $page['menu_order'] ?? '0'; ?>">
                </div>
                <div class="form-check" style="margin-top: 35px;">
                    <input type="checkbox" id="in_menu" name="in_menu" <?php echo ($page['in_menu'] ?? 0) ? 'checked' : ''; ?>>
                    <label for="in_menu">Afficher dans le menu</label>
                </div>
                <div class="form-check" style="margin-top: 35px;">
                    <input type="checkbox" id="is_active" name="is_active" <?php echo ($page['is_active'] ?? 1) ? 'checked' : ''; ?>>
                    <label for="is_active">Page active</label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contenu de la page (HTML autorisé)</label>
                <textarea name="contenu" class="form-control" rows="20"><?php echo $page['contenu'] ?? ''; ?></textarea>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--color-gray-light);">
            <h3 style="margin-bottom: 20px;">SEO</h3>
            
            <div class="form-group">
                <label class="form-label">Meta titre (SEO)</label>
                <input type="text" name="meta_title" class="form-control" value="<?php echo $page['meta_title'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Meta description (SEO)</label>
                <textarea name="meta_description" class="form-control" rows="3"><?php echo $page['meta_description'] ?? ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="pages.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
