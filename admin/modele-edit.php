<?php
/**
 * Admin - Édition d'un modèle
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Modifier un modèle';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$modele = null;

// Récupérer le modèle si édition
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM modeles WHERE id = ?");
    $stmt->execute([$id]);
    $modele = $stmt->fetch();
    if (!$modele) {
        header('Location: modeles.php');
        exit;
    }
    $page_title = 'Modifier : ' . $modele['nom'];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => $_POST['nom'] ?? '',
        'slug' => slugify($_POST['nom'] ?? ''),
        'slogan' => $_POST['slogan'] ?? '',
        'description' => $_POST['description'] ?? '',
        'points_forts' => $_POST['points_forts'] ?? '',
        'surface_habitable' => floatval($_POST['surface_habitable'] ?? 0),
        'nb_chambres' => intval($_POST['nb_chambres'] ?? 0),
        'nb_salles_bain' => intval($_POST['nb_salles_bain'] ?? 0),
        'nb_etages' => $_POST['nb_etages'] ?? 'plain-pied',
        'style' => $_POST['style'] ?? 'traditionnel',
        'prix_afficher' => $_POST['prix_afficher'] ?? '',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'ordre_affichage' => intval($_POST['ordre_affichage'] ?? 0),
        'meta_title' => $_POST['meta_title'] ?? '',
        'meta_description' => $_POST['meta_description'] ?? ''
    ];
    
    // Gestion de l'image
    if (!empty($_FILES['image_principale']['name'])) {
        $uploadResult = uploadImage($_FILES['image_principale'], 'maisons');
        if ($uploadResult['success']) {
            $data['image_principale'] = $uploadResult['filename'];
        }
    } elseif (isset($_POST['image_principale_existante'])) {
        $data['image_principale'] = $_POST['image_principale_existante'];
    }
    
    if ($id) {
        // Update
        $sql = "UPDATE modeles SET nom=?, slug=?, slogan=?, description=?, points_forts=?, 
                surface_habitable=?, nb_chambres=?, nb_salles_bain=?, nb_etages=?, style=?, 
                prix_afficher=?, is_active=?, ordre_affichage=?, meta_title=?, meta_description=?";
        $params = [
            $data['nom'], $data['slug'], $data['slogan'], $data['description'], $data['points_forts'],
            $data['surface_habitable'], $data['nb_chambres'], $data['nb_salles_bain'], 
            $data['nb_etages'], $data['style'], $data['prix_afficher'], $data['is_active'],
            $data['ordre_affichage'], $data['meta_title'], $data['meta_description']
        ];
        
        if (isset($data['image_principale'])) {
            $sql .= ", image_principale=?";
            $params[] = $data['image_principale'];
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // Insert
        $sql = "INSERT INTO modeles (nom, slug, slogan, description, points_forts, surface_habitable, 
                nb_chambres, nb_salles_bain, nb_etages, style, prix_afficher, is_active, ordre_affichage,
                meta_title, meta_description, image_principale) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nom'], $data['slug'], $data['slogan'], $data['description'], $data['points_forts'],
            $data['surface_habitable'], $data['nb_chambres'], $data['nb_salles_bain'], 
            $data['nb_etages'], $data['style'], $data['prix_afficher'], $data['is_active'],
            $data['ordre_affichage'], $data['meta_title'], $data['meta_description'],
            $data['image_principale'] ?? null
        ]);
        $id = $pdo->lastInsertId();
    }
    
    header('Location: modeles.php');
    exit;
}

// Fonction d'upload sécurisée
function uploadImage($file, $type) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erreur upload'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Type non autorisé'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Fichier trop gros'];
    }
    
    if (!getimagesize($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Image invalide'];
    }
    
    $uploadDir = "../uploads/{$type}/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . slugify(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
    
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Erreur enregistrement'];
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title"><?php echo $modele ? 'Modifier' : 'Ajouter'; ?> un modèle</h1>
    
    <div class="admin-section">
        <form method="POST" enctype="multipart/form-data" style="max-width: 900px;">
            
            <!-- Section Image -->
            <div style="background: var(--color-gray-lighter); padding: 20px; border-radius: 12px; margin-bottom: 30px;">
                <h3 style="margin-bottom: 20px;">🖼️ Image principale</h3>
                
                <?php if (!empty($modele['image_principale'])): ?>
                <div style="margin-bottom: 15px;">
                    <p style="margin-bottom: 10px;">Image actuelle :</p>
                    <img src="../uploads/maisons/<?php echo $modele['image_principale']; ?>" 
                         alt="" style="max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <input type="hidden" name="image_principale_existante" value="<?php echo $modele['image_principale']; ?>">
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label">
                        <?php echo empty($modele['image_principale']) ? 'Choisir une image' : 'Remplacer l\'image'; ?>
                    </label>
                    <input type="file" name="image_principale" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small style="color: var(--color-gray);">Formats acceptés : JPG, PNG, GIF, WebP (max 5 Mo)</small>
                </div>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Nom du modèle *</label>
                    <input type="text" name="nom" class="form-control" value="<?php echo $modele['nom'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Slogan</label>
                    <input type="text" name="slogan" class="form-control" value="<?php echo $modele['slogan'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?php echo $modele['description'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Points forts (un par ligne)</label>
                <textarea name="points_forts" class="form-control" rows="4"><?php echo $modele['points_forts'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Surface (m²)</label>
                    <input type="number" step="0.01" name="surface_habitable" class="form-control" value="<?php echo $modele['surface_habitable'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Chambres</label>
                    <input type="number" name="nb_chambres" class="form-control" value="<?php echo $modele['nb_chambres'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Salles de bain</label>
                    <input type="number" name="nb_salles_bain" class="form-control" value="<?php echo $modele['nb_salles_bain'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Ordre</label>
                    <input type="number" name="ordre_affichage" class="form-control" value="<?php echo $modele['ordre_affichage'] ?? '0'; ?>">
                </div>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="nb_etages" class="form-control">
                        <option value="plain-pied" <?php echo ($modele['nb_etages'] ?? '') == 'plain-pied' ? 'selected' : ''; ?>>Plain-pied</option>
                        <option value="1-etage" <?php echo ($modele['nb_etages'] ?? '') == '1-etage' ? 'selected' : ''; ?>>À étage</option>
                        <option value="2-etages" <?php echo ($modele['nb_etages'] ?? '') == '2-etages' ? 'selected' : ''; ?>>2 étages</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Style</label>
                    <select name="style" class="form-control">
                        <option value="traditionnel" <?php echo ($modele['style'] ?? '') == 'traditionnel' ? 'selected' : ''; ?>>Traditionnel</option>
                        <option value="contemporain" <?php echo ($modele['style'] ?? '') == 'contemporain' ? 'selected' : ''; ?>>Contemporain</option>
                        <option value="moderne" <?php echo ($modele['style'] ?? '') == 'moderne' ? 'selected' : ''; ?>>Moderne</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prix affiché</label>
                    <input type="text" name="prix_afficher" class="form-control" value="<?php echo $modele['prix_afficher'] ?? ''; ?>" placeholder="À partir de 145 000 €">
                </div>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--color-gray-light);">
            <h3 style="margin-bottom: 20px;">SEO</h3>
            
            <div class="form-group">
                <label class="form-label">Meta titre</label>
                <input type="text" name="meta_title" class="form-control" value="<?php echo $modele['meta_title'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Meta description</label>
                <textarea name="meta_description" class="form-control" rows="2"><?php echo $modele['meta_description'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-check" style="margin: 20px 0;">
                <input type="checkbox" id="is_active" name="is_active" <?php echo ($modele['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label for="is_active">Modèle actif (visible sur le site)</label>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="modeles.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
