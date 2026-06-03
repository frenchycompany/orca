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
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <h1 class="admin-title"><?php echo $page ? '✏️ ' . htmlspecialchars($page['titre']) : '➕ Nouvelle page'; ?></h1>
        <a href="pages.php" class="btn btn-outline">&larr; Retour aux pages</a>
    </div>

    <style>
        .pe-card{background:#fff;border-radius:12px;padding:22px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #e8e8e8}
        .pe-card h3{font-size:16px;font-weight:700;color:#1a1a1a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
        .pe-grid{display:grid;gap:15px}
        .pe-grid-2{grid-template-columns:1fr 1fr}
        .pe-field{display:flex;flex-direction:column}
        .pe-field label{display:block;font-weight:600;font-size:12px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.3px}
        .pe-field input[type="text"],
        .pe-field input[type="number"],
        .pe-field input[type="file"],
        .pe-field select,
        .pe-field textarea{
            width:100%!important;
            padding:10px 14px!important;
            border:1.5px solid #e0e0e0!important;
            border-radius:8px!important;
            font-size:14px!important;
            font-family:'Poppins',-apple-system,BlinkMacSystemFont,sans-serif!important;
            background:#fff!important;
            transition:border-color .15s,box-shadow .15s!important;
            box-sizing:border-box!important;
            line-height:1.5!important;
            color:#222!important;
        }
        .pe-field textarea{resize:vertical;min-height:80px}
        .pe-field input:focus,
        .pe-field select:focus,
        .pe-field textarea:focus{
            outline:none!important;
            border-color:#1a5653!important;
            box-shadow:0 0 0 3px rgba(26,86,83,0.1)!important;
        }
        .pe-publish{display:flex;align-items:center;gap:12px;padding:14px;background:#f8f9fa;border-radius:10px;cursor:pointer;border:1.5px solid transparent;transition:all .15s}
        .pe-publish:hover{border-color:#1a5653;background:#fff}
        .pe-publish input[type="checkbox"]{width:20px;height:20px;cursor:pointer;accent-color:#1a5653}
        .pe-publish span{font-weight:600;font-size:14px;color:#222}
        .pe-btn-save{width:100%;padding:14px;font-size:15px;font-weight:700;background:#1a5653;color:#fff;border:none;border-radius:10px;cursor:pointer;transition:background .15s}
        .pe-btn-save:hover{background:#0f3d3a}
        .pe-btn-cancel{width:100%;margin-top:10px;padding:12px;display:block;text-align:center;background:#fff;color:#666;border:1.5px solid #e0e0e0;border-radius:10px;text-decoration:none;font-size:14px;transition:all .15s}
        .pe-btn-cancel:hover{background:#f5f5f5;border-color:#999}
        .pe-image-preview{text-align:center;margin-bottom:15px}
        .pe-image-preview img{max-width:100%;max-height:220px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        @media(max-width:1100px){.pe-layout{grid-template-columns:1fr!important}}
    </style>

    <form method="POST" enctype="multipart/form-data">
        <div class="pe-layout" style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">

            <!-- Colonne principale -->
            <div>
                <!-- Identité de la page -->
                <div class="admin-section pe-card" style="margin-bottom:20px;">
                    <h3>📝 Identité de la page</h3>
                    <div class="pe-grid pe-grid-2">
                        <div class="pe-field">
                            <label class="form-label">Titre de la page <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="titre" class="form-control" value="<?php echo htmlspecialchars($page['titre'] ?? ''); ?>" required placeholder="Titre de votre page">
                        </div>
                        <div class="pe-field">
                            <label class="form-label">Slug (URL)</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($page['slug'] ?? ''); ?>" disabled placeholder="Généré automatiquement">
                        </div>
                    </div>
                    <div class="pe-field" style="margin-top:14px;">
                        <label class="form-label">Template</label>
                        <select name="template" class="form-control">
                            <option value="default">Par défaut</option>
                            <option value="full-width">Pleine largeur</option>
                            <option value="sidebar">Avec barre latérale</option>
                        </select>
                    </div>
                </div>

                <!-- Contenu -->
                <div class="admin-section pe-card" style="margin-bottom:20px;">
                    <h3>📄 Contenu de la page</h3>
                    <div class="pe-field">
                        <label class="form-label">Contenu (HTML autorisé)</label>
                        <textarea name="contenu" class="form-control" rows="20" placeholder="Saisissez le contenu de votre page..."><?php echo htmlspecialchars($page['contenu'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- SEO -->
                <div class="admin-section pe-card" style="margin-bottom:20px;">
                    <h3>🔍 Référencement SEO</h3>
                    <div class="pe-field">
                        <label class="form-label">Meta titre</label>
                        <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($page['meta_title'] ?? ''); ?>" placeholder="Titre pour les moteurs de recherche">
                    </div>
                    <div class="pe-field" style="margin-top:14px;">
                        <label class="form-label">Meta description</label>
                        <textarea name="meta_description" class="form-control" rows="3" placeholder="Description courte affichée dans les résultats Google..."><?php echo htmlspecialchars($page['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div>
                <!-- Image -->
                <div class="admin-section pe-card" style="margin-bottom:20px;">
                    <h3>🖼️ Image de la page</h3>
                    <?php if (!empty($page['image'])): ?>
                    <div class="pe-image-preview">
                        <img src="../uploads/pages/<?php echo $page['image']; ?>" alt="">
                    </div>
                    <?php endif; ?>
                    <div class="pe-field">
                        <label class="form-label"><?php echo !empty($page['image']) ? 'Remplacer l\'image' : 'Choisir une image'; ?></label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color:#999;font-size:11px;margin-top:6px;display:block;">JPG, PNG, GIF, WebP — max 5 Mo</small>
                    </div>
                </div>

                <!-- Publication -->
                <div class="admin-section pe-card" style="margin-bottom:20px;">
                    <h3>⚡ Publication</h3>
                    <label class="pe-publish">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($page['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span>Page active</span>
                    </label>
                    <label class="pe-publish" style="margin-top:10px;">
                        <input type="checkbox" id="in_menu" name="in_menu" <?php echo ($page['in_menu'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Afficher dans le menu</span>
                    </label>
                    <div class="pe-field" style="margin-top:14px;">
                        <label class="form-label">Ordre dans le menu</label>
                        <input type="number" name="menu_order" class="form-control" value="<?php echo htmlspecialchars($page['menu_order'] ?? '0'); ?>">
                    </div>
                    <p style="font-size:12px;color:#999;margin-top:10px;line-height:1.5;">Si la page est inactive, elle sera masquée du site public mais conservée en base.</p>
                </div>

                <!-- Actions -->
                <div class="admin-section pe-card" style="margin-bottom:20px;position:sticky;top:20px;">
                    <button type="submit" class="pe-btn-save">💾 Enregistrer la page</button>
                    <a href="pages.php" class="pe-btn-cancel">Annuler</a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
