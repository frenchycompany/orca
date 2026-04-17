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

    <style>
        /* Styles scopés à la page d'édition modèle */
        .me-card{background:#fff;border-radius:12px;padding:22px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #e8e8e8}
        .me-card h3{font-size:15px;font-weight:700;color:#1a1a1a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
        .me-card h3 .me-badge{font-size:11px;font-weight:500;color:#999;margin-left:auto}
        .me-grid{display:grid;gap:14px}
        .me-grid-2{grid-template-columns:1fr 1fr}
        .me-grid-3{grid-template-columns:1fr 1fr 1fr}
        .me-grid-4{grid-template-columns:repeat(4,1fr)}
        .me-field{display:flex;flex-direction:column}
        .me-field label{display:block;font-weight:600;font-size:12px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.3px}
        .me-field label small{font-weight:400;text-transform:none;color:#999;letter-spacing:0;margin-left:4px}
        .me-field input[type="text"],
        .me-field input[type="number"],
        .me-field input[type="file"],
        .me-field select,
        .me-field textarea{
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
        .me-field textarea{resize:vertical;min-height:80px}
        .me-field input:focus,
        .me-field select:focus,
        .me-field textarea:focus{
            outline:none!important;
            border-color:#1a5653!important;
            box-shadow:0 0 0 3px rgba(26,86,83,0.1)!important;
        }
        .me-field input[type="number"]{text-align:center;font-weight:700;font-size:16px!important}
        .me-inclus-col{background:#fafbfc;padding:14px;border-radius:10px;border:1px solid #e8e8e8}
        .me-inclus-col label{display:block;font-weight:700;font-size:13px;margin-bottom:10px;color:#1a5653;text-transform:none;letter-spacing:0}
        .me-inclus-col textarea{background:#fff!important;min-height:160px!important;font-size:13px!important}
        .me-image-preview{text-align:center;margin-bottom:15px}
        .me-image-preview img{max-width:100%;max-height:220px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .me-publish{display:flex;align-items:center;gap:12px;padding:14px;background:#f8f9fa;border-radius:10px;cursor:pointer;border:1.5px solid transparent;transition:all .15s}
        .me-publish:hover{border-color:#1a5653;background:#fff}
        .me-publish input[type="checkbox"]{width:20px;height:20px;cursor:pointer;accent-color:#1a5653}
        .me-publish span{font-weight:600;font-size:14px;color:#222}
        .me-btn-save{width:100%;padding:14px;font-size:15px;font-weight:700;background:#1a5653;color:#fff;border:none;border-radius:10px;cursor:pointer;transition:background .15s}
        .me-btn-save:hover{background:#0f3d3a}
        .me-btn-cancel{width:100%;margin-top:10px;padding:12px;display:block;text-align:center;background:#fff;color:#666;border:1.5px solid #e0e0e0;border-radius:10px;text-decoration:none;font-size:14px;transition:all .15s}
        .me-btn-cancel:hover{background:#f5f5f5;border-color:#999}
        @media(max-width:1100px){.me-layout{grid-template-columns:1fr!important}}
    </style>

    <form method="POST" enctype="multipart/form-data">
        <div class="me-layout" style="display:grid;grid-template-columns:minmax(0,2.2fr) minmax(280px,1fr);gap:25px;">

            <!-- Colonne principale -->
            <div>
                <!-- Identité -->
                <div class="me-card">
                    <h3>🏠 Identité du modèle</h3>
                    <div class="me-grid me-grid-2">
                        <div class="me-field">
                            <label>Nom <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars($modele['nom'] ?? ''); ?>" required placeholder="Ex: Le Coquelicot">
                        </div>
                        <div class="me-field">
                            <label>Slogan</label>
                            <input type="text" name="slogan" value="<?php echo htmlspecialchars($modele['slogan'] ?? ''); ?>" placeholder="L'élégance au meilleur prix">
                        </div>
                    </div>
                    <div class="me-field" style="margin-top:14px;">
                        <label>Description</label>
                        <textarea name="description" rows="4" placeholder="Décrivez le modèle en quelques phrases..."><?php echo htmlspecialchars($modele['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="me-field" style="margin-top:14px;">
                        <label>Points forts <small>(un par ligne)</small></label>
                        <textarea name="points_forts" rows="5" placeholder="Luminosité exceptionnelle&#10;Salon séjour traversant&#10;Cuisine ouverte moderne"><?php echo htmlspecialchars($modele['points_forts'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Caractéristiques -->
                <div class="me-card">
                    <h3>📐 Caractéristiques</h3>
                    <div class="me-grid me-grid-4">
                        <div class="me-field">
                            <label>Surface (m²)</label>
                            <input type="number" step="0.01" name="surface_habitable" value="<?php echo $modele['surface_habitable'] ?? ''; ?>">
                        </div>
                        <div class="me-field">
                            <label>Chambres</label>
                            <input type="number" name="nb_chambres" value="<?php echo $modele['nb_chambres'] ?? ''; ?>">
                        </div>
                        <div class="me-field">
                            <label>Salles de bain</label>
                            <input type="number" name="nb_salles_bain" value="<?php echo $modele['nb_salles_bain'] ?? ''; ?>">
                        </div>
                        <div class="me-field">
                            <label>Ordre</label>
                            <input type="number" name="ordre_affichage" value="<?php echo $modele['ordre_affichage'] ?? '0'; ?>">
                        </div>
                    </div>
                    <div class="me-grid me-grid-3" style="margin-top:14px;">
                        <div class="me-field">
                            <label>Type</label>
                            <select name="nb_etages">
                                <option value="plain-pied" <?php echo ($modele['nb_etages'] ?? '') == 'plain-pied' ? 'selected' : ''; ?>>🏠 Plain-pied</option>
                                <option value="1-etage" <?php echo ($modele['nb_etages'] ?? '') == '1-etage' ? 'selected' : ''; ?>>🏡 À étage</option>
                                <option value="2-etages" <?php echo ($modele['nb_etages'] ?? '') == '2-etages' ? 'selected' : ''; ?>>🏘️ 2 étages</option>
                            </select>
                        </div>
                        <div class="me-field">
                            <label>Style</label>
                            <select name="style">
                                <option value="traditionnel" <?php echo ($modele['style'] ?? '') == 'traditionnel' ? 'selected' : ''; ?>>Traditionnel</option>
                                <option value="contemporain" <?php echo ($modele['style'] ?? '') == 'contemporain' ? 'selected' : ''; ?>>Contemporain</option>
                                <option value="moderne" <?php echo ($modele['style'] ?? '') == 'moderne' ? 'selected' : ''; ?>>Moderne</option>
                            </select>
                        </div>
                        <div class="me-field">
                            <label>Prix affiché</label>
                            <input type="text" name="prix_afficher" value="<?php echo htmlspecialchars($modele['prix_afficher'] ?? ''); ?>" placeholder="À partir de 145 000 €">
                        </div>
                    </div>
                </div>

                <!-- Inclusions -->
                <div class="me-card">
                    <h3>✅ Ce qui est inclus dans le prix <span class="me-badge">Un élément par ligne</span></h3>
                    <div class="me-grid me-grid-3">
                        <div class="me-inclus-col">
                            <label>🧱 Structure</label>
                            <div class="me-field">
                                <textarea name="inclus_structure" rows="7" placeholder="Fondations superficielles&#10;Murs en briques&#10;Charpente traditionnelle"><?php echo htmlspecialchars($modele['inclus_structure'] ?? "Fondations superficielles\nMurs en briques ou parpaings\nCharpente traditionnelle\nCouverture tuiles ou ardoises\nMenuiseries PVC ou ALU"); ?></textarea>
                            </div>
                        </div>
                        <div class="me-inclus-col">
                            <label>🛋️ Intérieur</label>
                            <div class="me-field">
                                <textarea name="inclus_interieur" rows="7" placeholder="Cloisons et plafonds&#10;Carrelage séjour&#10;Parquet chambres"><?php echo htmlspecialchars($modele['inclus_interieur'] ?? "Cloisons et plafonds\nCarrelage séjour/cuisine\nParquet ou moquette chambres\nCuisine équipée (meubles + électro)\nSalle de bain complète"); ?></textarea>
                            </div>
                        </div>
                        <div class="me-inclus-col">
                            <label>⚙️ Équipements</label>
                            <div class="me-field">
                                <textarea name="inclus_equipements" rows="7" placeholder="Chauffage gaz&#10;Volets roulants&#10;Porte de garage"><?php echo htmlspecialchars($modele['inclus_equipements'] ?? "Chauffage gaz + eau chaude\nVolets roulants électriques\nPorte de garage sectionnelle\nPortail + interphone\nJardinet clôturé"); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="me-card">
                    <h3>🔍 Référencement SEO</h3>
                    <div class="me-field">
                        <label>Meta titre</label>
                        <input type="text" name="meta_title" value="<?php echo htmlspecialchars($modele['meta_title'] ?? ''); ?>" placeholder="Titre pour les moteurs de recherche">
                    </div>
                    <div class="me-field" style="margin-top:14px;">
                        <label>Meta description</label>
                        <textarea name="meta_description" rows="3" placeholder="Description courte affichée dans les résultats Google..."><?php echo htmlspecialchars($modele['meta_description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div>
                <!-- Image -->
                <div class="me-card">
                    <h3>🖼️ Image principale</h3>
                    <?php if (!empty($modele['image_principale'])): ?>
                    <div class="me-image-preview">
                        <img src="../uploads/maisons/<?php echo $modele['image_principale']; ?>" alt="">
                        <input type="hidden" name="image_principale_existante" value="<?php echo $modele['image_principale']; ?>">
                    </div>
                    <?php endif; ?>
                    <div class="me-field">
                        <label><?php echo empty($modele['image_principale']) ? 'Choisir une image' : 'Remplacer l\'image'; ?></label>
                        <input type="file" name="image_principale" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color:#999;font-size:11px;margin-top:6px;display:block;">JPG, PNG, GIF, WebP — max 5 Mo</small>
                    </div>
                </div>

                <!-- Publication -->
                <div class="me-card">
                    <h3>⚡ Publication</h3>
                    <label class="me-publish">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($modele['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span>Modèle actif sur le site</span>
                    </label>
                    <p style="font-size:12px;color:#999;margin-top:10px;line-height:1.5;">Si décoché, ce modèle sera masqué du site public mais conservé en base.</p>
                </div>

                <!-- Actions -->
                <div class="me-card" style="position:sticky;top:20px;">
                    <button type="submit" class="me-btn-save">💾 Enregistrer le modèle</button>
                    <a href="modeles.php" class="me-btn-cancel">Annuler</a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
