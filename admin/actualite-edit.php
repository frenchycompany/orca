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

    // Gestion de l'image
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/actualites/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mimeType, $allowedTypes) && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . slugify(pathinfo($_FILES['image']['name'], PATHINFO_FILENAME)) . '.' . $extension;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $data['image'] = $filename;
            }
        }
    } elseif (isset($_POST['image_existante'])) {
        $data['image'] = $_POST['image_existante'];
    }

    if ($id) {
        $sql = "UPDATE actualites SET titre=?, slug=?, extrait=?, contenu=?, categorie=?,
                is_active=?, is_featured=?, published_at=?";
        $params = [
            $data['titre'], $data['slug'], $data['extrait'], $data['contenu'],
            $data['categorie'], $data['is_active'], $data['is_featured'],
            $data['published_at']
        ];
        if (isset($data['image'])) {
            $sql .= ", image=?";
            $params[] = $data['image'];
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $sql = "INSERT INTO actualites (titre, slug, extrait, contenu, categorie, is_active, is_featured, published_at" . (isset($data['image']) ? ", image" : "") . ")
                VALUES (?, ?, ?, ?, ?, ?, ?, ?" . (isset($data['image']) ? ", ?" : "") . ")";
        $params = [
            $data['titre'], $data['slug'], $data['extrait'], $data['contenu'],
            $data['categorie'], $data['is_active'], $data['is_featured'], $data['published_at']
        ];
        if (isset($data['image'])) {
            $params[] = $data['image'];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    header('Location: actualites.php');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <h1 class="admin-title"><?php echo $actualite ? '✏️ ' . htmlspecialchars($actualite['titre']) : '➕ Nouvel article'; ?></h1>
        <a href="actualites.php" class="btn btn-outline">← Retour aux articles</a>
    </div>

    <style>
        .ae-card{background:#fff;border-radius:12px;padding:22px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #e8e8e8}
        .ae-card h3{font-size:15px;font-weight:700;color:#1a1a1a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
        .ae-grid{display:grid;gap:15px}
        .ae-grid-2{grid-template-columns:1fr 1fr}
        .ae-field{display:flex;flex-direction:column}
        .ae-field label{display:block;font-weight:600;font-size:12px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.3px}
        .ae-field input[type="text"],
        .ae-field input[type="datetime-local"],
        .ae-field input[type="file"],
        .ae-field select,
        .ae-field textarea{
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
        .ae-field textarea{resize:vertical;min-height:80px}
        .ae-field input:focus,
        .ae-field select:focus,
        .ae-field textarea:focus{
            outline:none!important;
            border-color:#1a5653!important;
            box-shadow:0 0 0 3px rgba(26,86,83,0.1)!important;
        }
        .ae-image-preview{text-align:center;margin-bottom:15px}
        .ae-image-preview img{max-width:100%;max-height:220px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .ae-publish{display:flex;align-items:center;gap:12px;padding:14px;background:#f8f9fa;border-radius:10px;cursor:pointer;border:1.5px solid transparent;transition:all .15s;margin-bottom:10px}
        .ae-publish:hover{border-color:#1a5653;background:#fff}
        .ae-publish input[type="checkbox"]{width:20px;height:20px;cursor:pointer;accent-color:#1a5653}
        .ae-publish span{font-weight:600;font-size:14px;color:#222}
        .ae-btn-save{width:100%;padding:14px;font-size:15px;font-weight:700;background:#1a5653;color:#fff;border:none;border-radius:10px;cursor:pointer;transition:background .15s}
        .ae-btn-save:hover{background:#0f3d3a}
        .ae-btn-cancel{width:100%;margin-top:10px;padding:12px;display:block;text-align:center;background:#fff;color:#666;border:1.5px solid #e0e0e0;border-radius:10px;text-decoration:none;font-size:14px;transition:all .15s}
        .ae-btn-cancel:hover{background:#f5f5f5;border-color:#999}
        @media(max-width:1100px){.ae-layout{grid-template-columns:1fr!important}}
    </style>

    <form method="POST" enctype="multipart/form-data">
        <div class="ae-layout" style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">

            <!-- Colonne principale -->
            <div>
                <!-- Contenu de l'article -->
                <div class="ae-card">
                    <h3>📝 Contenu de l'article</h3>
                    <div class="ae-field">
                        <label>Titre <span style="color:#e74c3c;">*</span></label>
                        <input type="text" name="titre" class="form-control" value="<?php echo htmlspecialchars($actualite['titre'] ?? ''); ?>" required placeholder="Titre de l'article">
                    </div>
                    <div class="ae-field" style="margin-top:14px;">
                        <label>Extrait (chapo)</label>
                        <textarea name="extrait" class="form-control" rows="3" placeholder="Court resume de l'article..."><?php echo htmlspecialchars($actualite['extrait'] ?? ''); ?></textarea>
                    </div>
                    <div class="ae-field" style="margin-top:14px;">
                        <label>Contenu complet</label>
                        <textarea name="contenu" class="form-control" rows="12" placeholder="Redigez le contenu de l'article..."><?php echo htmlspecialchars($actualite['contenu'] ?? ''); ?></textarea>
                        <small style="color:#999;font-size:11px;margin-top:6px;display:block;">HTML autorise</small>
                    </div>
                </div>

                <!-- SEO / Meta -->
                <div class="ae-card">
                    <h3>🔍 Referencement SEO</h3>
                    <div class="ae-grid ae-grid-2">
                        <div class="ae-field">
                            <label>Meta titre</label>
                            <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($actualite['meta_title'] ?? ''); ?>" placeholder="Titre pour les moteurs de recherche">
                        </div>
                        <div class="ae-field">
                            <label>Slug</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($actualite['slug'] ?? ''); ?>" disabled placeholder="Genere automatiquement">
                        </div>
                    </div>
                    <div class="ae-field" style="margin-top:14px;">
                        <label>Meta description</label>
                        <textarea name="meta_description" class="form-control" rows="3" placeholder="Description courte affichee dans les resultats Google..."><?php echo htmlspecialchars($actualite['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div>
                <!-- Image -->
                <div class="ae-card">
                    <h3>🖼️ Image de l'article</h3>
                    <?php if (!empty($actualite['image'])): ?>
                    <div class="ae-image-preview">
                        <img src="../uploads/actualites/<?php echo htmlspecialchars($actualite['image']); ?>" alt="">
                        <input type="hidden" name="image_existante" value="<?php echo htmlspecialchars($actualite['image']); ?>">
                    </div>
                    <?php endif; ?>
                    <div class="ae-field">
                        <label><?php echo empty($actualite['image']) ? 'Choisir une image' : 'Remplacer l\'image'; ?></label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color:#999;font-size:11px;margin-top:6px;display:block;">JPG, PNG, GIF, WebP — max 5 Mo</small>
                    </div>
                </div>

                <!-- Categorie -->
                <div class="ae-card">
                    <h3>📂 Categorie</h3>
                    <div class="ae-field">
                        <label>Categorie de l'article</label>
                        <select name="categorie" class="form-control">
                            <option value="actualite" <?php echo ($actualite['categorie'] ?? '') == 'actualite' ? 'selected' : ''; ?>>Actualite</option>
                            <option value="conseil" <?php echo ($actualite['categorie'] ?? '') == 'conseil' ? 'selected' : ''; ?>>Conseil</option>
                            <option value="temoignage" <?php echo ($actualite['categorie'] ?? '') == 'temoignage' ? 'selected' : ''; ?>>Temoignage</option>
                            <option value="promo" <?php echo ($actualite['categorie'] ?? '') == 'promo' ? 'selected' : ''; ?>>Promotion</option>
                        </select>
                    </div>
                    <div class="ae-field" style="margin-top:14px;">
                        <label>Date de publication</label>
                        <input type="datetime-local" name="published_at" class="form-control"
                               value="<?php echo isset($actualite['published_at']) ? date('Y-m-d\TH:i', strtotime($actualite['published_at'])) : date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>

                <!-- Publication -->
                <div class="ae-card">
                    <h3>⚡ Publication</h3>
                    <label class="ae-publish">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($actualite['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span>Publie (visible sur le site)</span>
                    </label>
                    <label class="ae-publish">
                        <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($actualite['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Mettre en avant</span>
                    </label>
                    <p style="font-size:12px;color:#999;margin-top:10px;line-height:1.5;">Les articles mis en avant apparaissent sur la page d'accueil.</p>
                </div>

                <!-- Actions -->
                <div class="ae-card" style="position:sticky;top:20px;">
                    <button type="submit" class="ae-btn-save">💾 Enregistrer l'article</button>
                    <a href="actualites.php" class="ae-btn-cancel">Annuler</a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
