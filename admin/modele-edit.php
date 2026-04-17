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
        'meta_description' => $_POST['meta_description'] ?? '',
        'inclus_structure' => $_POST['inclus_structure'] ?? '',
        'inclus_interieur' => $_POST['inclus_interieur'] ?? '',
        'inclus_equipements' => $_POST['inclus_equipements'] ?? ''
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
                prix_afficher=?, is_active=?, ordre_affichage=?, meta_title=?, meta_description=?,
                inclus_structure=?, inclus_interieur=?, inclus_equipements=?";
        $params = [
            $data['nom'], $data['slug'], $data['slogan'], $data['description'], $data['points_forts'],
            $data['surface_habitable'], $data['nb_chambres'], $data['nb_salles_bain'],
            $data['nb_etages'], $data['style'], $data['prix_afficher'], $data['is_active'],
            $data['ordre_affichage'], $data['meta_title'], $data['meta_description'],
            $data['inclus_structure'], $data['inclus_interieur'], $data['inclus_equipements']
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
                meta_title, meta_description, image_principale, inclus_structure, inclus_interieur, inclus_equipements)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nom'], $data['slug'], $data['slogan'], $data['description'], $data['points_forts'],
            $data['surface_habitable'], $data['nb_chambres'], $data['nb_salles_bain'],
            $data['nb_etages'], $data['style'], $data['prix_afficher'], $data['is_active'],
            $data['ordre_affichage'], $data['meta_title'], $data['meta_description'],
            $data['image_principale'] ?? null,
            $data['inclus_structure'], $data['inclus_interieur'], $data['inclus_equipements']
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
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <h1 class="admin-title"><?php echo $modele ? '✏️ ' . htmlspecialchars($modele['nom']) : '➕ Nouveau modèle'; ?></h1>
        <a href="modeles.php" class="btn btn-outline">← Retour aux modèles</a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">

            <!-- Colonne principale -->
            <div>
                <!-- Identité -->
                <div class="admin-section" style="margin-bottom:20px;">
                    <h3 style="font-size:16px;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">🏠 Identité du modèle</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:13px;">Nom *</label>
                            <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($modele['nom'] ?? ''); ?>" required style="padding:10px 14px;border-radius:8px;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:13px;">Slogan</label>
                            <input type="text" name="slogan" class="form-control" value="<?php echo htmlspecialchars($modele['slogan'] ?? ''); ?>" style="padding:10px 14px;border-radius:8px;" placeholder="L'élégance au meilleur prix">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Description</label>
                        <textarea name="description" class="form-control" rows="4" style="padding:10px 14px;border-radius:8px;line-height:1.6;"><?php echo htmlspecialchars($modele['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Points forts <small style="color:#999;font-weight:400;">(un par ligne)</small></label>
                        <textarea name="points_forts" class="form-control" rows="4" style="padding:10px 14px;border-radius:8px;line-height:1.6;" placeholder="Luminosité exceptionnelle&#10;Salon séjour traversant&#10;Cuisine ouverte moderne"><?php echo htmlspecialchars($modele['points_forts'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Caractéristiques -->
                <div class="admin-section" style="margin-bottom:20px;">
                    <h3 style="font-size:16px;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">📐 Caractéristiques</h3>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Surface (m²)</label>
                            <input type="number" step="0.01" name="surface_habitable" class="form-control" value="<?php echo $modele['surface_habitable'] ?? ''; ?>" style="padding:10px;border-radius:8px;text-align:center;font-size:16px;font-weight:700;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Chambres</label>
                            <input type="number" name="nb_chambres" class="form-control" value="<?php echo $modele['nb_chambres'] ?? ''; ?>" style="padding:10px;border-radius:8px;text-align:center;font-size:16px;font-weight:700;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Salles de bain</label>
                            <input type="number" name="nb_salles_bain" class="form-control" value="<?php echo $modele['nb_salles_bain'] ?? ''; ?>" style="padding:10px;border-radius:8px;text-align:center;font-size:16px;font-weight:700;">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Ordre affichage</label>
                            <input type="number" name="ordre_affichage" class="form-control" value="<?php echo $modele['ordre_affichage'] ?? '0'; ?>" style="padding:10px;border-radius:8px;text-align:center;font-size:16px;font-weight:700;">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:15px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Type</label>
                            <select name="nb_etages" class="form-control" style="padding:10px;border-radius:8px;">
                                <option value="plain-pied" <?php echo ($modele['nb_etages'] ?? '') == 'plain-pied' ? 'selected' : ''; ?>>🏠 Plain-pied</option>
                                <option value="1-etage" <?php echo ($modele['nb_etages'] ?? '') == '1-etage' ? 'selected' : ''; ?>>🏡 À étage</option>
                                <option value="2-etages" <?php echo ($modele['nb_etages'] ?? '') == '2-etages' ? 'selected' : ''; ?>>🏘️ 2 étages</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Style</label>
                            <select name="style" class="form-control" style="padding:10px;border-radius:8px;">
                                <option value="traditionnel" <?php echo ($modele['style'] ?? '') == 'traditionnel' ? 'selected' : ''; ?>>Traditionnel</option>
                                <option value="contemporain" <?php echo ($modele['style'] ?? '') == 'contemporain' ? 'selected' : ''; ?>>Contemporain</option>
                                <option value="moderne" <?php echo ($modele['style'] ?? '') == 'moderne' ? 'selected' : ''; ?>>Moderne</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight:600;font-size:12px;color:#666;">Prix affiché</label>
                            <input type="text" name="prix_afficher" class="form-control" value="<?php echo htmlspecialchars($modele['prix_afficher'] ?? ''); ?>" placeholder="À partir de 145 000 €" style="padding:10px;border-radius:8px;">
                        </div>
                    </div>
                </div>

                <!-- Inclusions -->
                <div class="admin-section" style="margin-bottom:20px;">
                    <h3 style="font-size:16px;margin-bottom:8px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">✅ Ce qui est inclus dans le prix</h3>
                    <p style="font-size:12px;color:#999;margin-bottom:15px;">Un élément par ligne. Personnalisable pour chaque modèle.</p>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;">
                        <div>
                            <label style="display:block;font-weight:700;font-size:13px;margin-bottom:8px;color:#1a5653;">🧱 Structure</label>
                            <textarea name="inclus_structure" class="form-control" rows="7" style="padding:10px 12px;border-radius:8px;font-size:13px;line-height:1.7;border:2px solid #e8e8e8;" placeholder="Fondations superficielles&#10;Murs en briques&#10;Charpente traditionnelle"><?php echo htmlspecialchars($modele['inclus_structure'] ?? "Fondations superficielles\nMurs en briques ou parpaings\nCharpente traditionnelle\nCouverture tuiles ou ardoises\nMenuiseries PVC ou ALU"); ?></textarea>
                        </div>
                        <div>
                            <label style="display:block;font-weight:700;font-size:13px;margin-bottom:8px;color:#1a5653;">🛋️ Intérieur</label>
                            <textarea name="inclus_interieur" class="form-control" rows="7" style="padding:10px 12px;border-radius:8px;font-size:13px;line-height:1.7;border:2px solid #e8e8e8;" placeholder="Cloisons et plafonds&#10;Carrelage séjour&#10;Parquet chambres"><?php echo htmlspecialchars($modele['inclus_interieur'] ?? "Cloisons et plafonds\nCarrelage séjour/cuisine\nParquet ou moquette chambres\nCuisine équipée (meubles + électro)\nSalle de bain complète"); ?></textarea>
                        </div>
                        <div>
                            <label style="display:block;font-weight:700;font-size:13px;margin-bottom:8px;color:#1a5653;">⚙️ Équipements</label>
                            <textarea name="inclus_equipements" class="form-control" rows="7" style="padding:10px 12px;border-radius:8px;font-size:13px;line-height:1.7;border:2px solid #e8e8e8;" placeholder="Chauffage gaz&#10;Volets roulants&#10;Porte de garage"><?php echo htmlspecialchars($modele['inclus_equipements'] ?? "Chauffage gaz + eau chaude\nVolets roulants électriques\nPorte de garage sectionnelle\nPortail + interphone\nJardinet clôturé"); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="admin-section" style="margin-bottom:20px;">
                    <h3 style="font-size:16px;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">🔍 SEO</h3>
                    <div class="form-group">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Meta titre</label>
                        <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($modele['meta_title'] ?? ''); ?>" style="padding:10px 14px;border-radius:8px;">
                    </div>
                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label" style="font-weight:600;font-size:13px;">Meta description</label>
                        <textarea name="meta_description" class="form-control" rows="2" style="padding:10px 14px;border-radius:8px;"><?php echo htmlspecialchars($modele['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div>
                <!-- Image -->
                <div class="admin-section" style="margin-bottom:20px;">
                    <h3 style="font-size:16px;margin-bottom:15px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">🖼️ Image</h3>
                    <?php if (!empty($modele['image_principale'])): ?>
                    <div style="margin-bottom:15px;text-align:center;">
                        <img src="../uploads/maisons/<?php echo $modele['image_principale']; ?>" alt=""
                             style="max-width:100%;max-height:220px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                        <input type="hidden" name="image_principale_existante" value="<?php echo $modele['image_principale']; ?>">
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="form-label" style="font-size:13px;">
                            <?php echo empty($modele['image_principale']) ? 'Choisir une image' : 'Remplacer'; ?>
                        </label>
                        <input type="file" name="image_principale" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" style="font-size:12px;">
                        <small style="color:#999;font-size:11px;">JPG, PNG, GIF, WebP — max 5 Mo</small>
                    </div>
                </div>

                <!-- Statut -->
                <div class="admin-section" style="margin-bottom:20px;">
                    <h3 style="font-size:16px;margin-bottom:15px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;">⚡ Publication</h3>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px;background:#f8f9fa;border-radius:8px;">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($modele['is_active'] ?? 1) ? 'checked' : ''; ?> style="width:18px;height:18px;">
                        <span style="font-weight:600;font-size:14px;">Modèle actif</span>
                    </label>
                    <p style="font-size:12px;color:#999;margin-top:8px;">Décochez pour masquer ce modèle du site</p>
                </div>

                <!-- Actions -->
                <div class="admin-section">
                    <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">💾 Enregistrer</button>
                    <a href="modeles.php" class="btn btn-outline" style="width:100%;margin-top:10px;display:block;text-align:center;">Annuler</a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
