<?php
/**
 * Admin - Édition d'un témoignage
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Ajouter un témoignage';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$temoignage = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM temoignages WHERE id = ?");
    $stmt->execute([$id]);
    $temoignage = $stmt->fetch();
    if (!$temoignage) {
        header('Location: temoignages.php');
        exit;
    }
    $page_title = 'Modifier un témoignage';
}

// Récupérer les modèles pour le select
$modeles = $pdo->query("SELECT id, nom FROM modeles WHERE is_active = 1 ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => $_POST['nom'] ?? '',
        'prenom' => $_POST['prenom'] ?? '',
        'ville' => $_POST['ville'] ?? '',
        'departement' => $_POST['departement'] ?? '',
        'modele_id' => !empty($_POST['modele_id']) ? intval($_POST['modele_id']) : null,
        'note' => intval($_POST['note'] ?? 5),
        'temoignage' => $_POST['temoignage'] ?? '',
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
    ];

    // Gestion de la photo
    if (!empty($_FILES['photo']['name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;
        $file = $_FILES['photo'];

        if ($file['error'] === UPLOAD_ERR_OK && in_array(mime_content_type($file['tmp_name']), $allowedTypes) && $file['size'] <= $maxSize) {
            $uploadDir = "../uploads/temoignages/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . slugify(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                $data['photo'] = $filename;
            }
        }
    } elseif (isset($_POST['photo_existante'])) {
        $data['photo'] = $_POST['photo_existante'];
    }

    if ($id) {
        $sql = "UPDATE temoignages SET nom=?, prenom=?, ville=?, departement=?, modele_id=?, note=?, temoignage=?, is_active=?, is_featured=?";
        $params = [
            $data['nom'], $data['prenom'], $data['ville'], $data['departement'],
            $data['modele_id'], $data['note'], $data['temoignage'],
            $data['is_active'], $data['is_featured']
        ];
        if (isset($data['photo'])) {
            $sql .= ", photo=?";
            $params[] = $data['photo'];
        }
        $sql .= " WHERE id=?";
        $params[] = $id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $sql = "INSERT INTO temoignages (nom, prenom, ville, departement, modele_id, note, temoignage, is_active, is_featured" . (isset($data['photo']) ? ", photo" : "") . ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?" . (isset($data['photo']) ? ", ?" : "") . ")";
        $params = [
            $data['nom'], $data['prenom'], $data['ville'], $data['departement'],
            $data['modele_id'], $data['note'], $data['temoignage'],
            $data['is_active'], $data['is_featured']
        ];
        if (isset($data['photo'])) {
            $params[] = $data['photo'];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    header('Location: temoignages.php');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <h1 class="admin-title"><?php echo $temoignage ? '✏️ Modifier le témoignage' : '➕ Nouveau témoignage'; ?></h1>
        <a href="temoignages.php" class="btn btn-outline">&larr; Retour aux témoignages</a>
    </div>

    <style>
        .te-card{background:#fff;border-radius:12px;padding:22px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #e8e8e8}
        .te-card h3{font-size:15px;font-weight:700;color:#1a1a1a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
        .te-grid{display:grid;gap:14px}
        .te-grid-2{grid-template-columns:1fr 1fr}
        .te-field{display:flex;flex-direction:column}
        .te-field label{display:block;font-weight:600;font-size:12px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.3px}
        .te-field input[type="text"],
        .te-field input[type="number"],
        .te-field input[type="file"],
        .te-field select,
        .te-field textarea{
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
        .te-field textarea{resize:vertical;min-height:80px}
        .te-field input:focus,
        .te-field select:focus,
        .te-field textarea:focus{
            outline:none!important;
            border-color:#1a5653!important;
            box-shadow:0 0 0 3px rgba(26,86,83,0.1)!important;
        }
        .te-image-preview{text-align:center;margin-bottom:15px}
        .te-image-preview img{max-width:100%;max-height:180px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .te-publish{display:flex;align-items:center;gap:12px;padding:14px;background:#f8f9fa;border-radius:10px;cursor:pointer;border:1.5px solid transparent;transition:all .15s;margin-bottom:10px}
        .te-publish:hover{border-color:#1a5653;background:#fff}
        .te-publish input[type="checkbox"]{width:20px;height:20px;cursor:pointer;accent-color:#1a5653}
        .te-publish span{font-weight:600;font-size:14px;color:#222}
        .te-btn-save{width:100%;padding:14px;font-size:15px;font-weight:700;background:#1a5653;color:#fff;border:none;border-radius:10px;cursor:pointer;transition:background .15s}
        .te-btn-save:hover{background:#0f3d3a}
        .te-btn-cancel{width:100%;margin-top:10px;padding:12px;display:block;text-align:center;background:#fff;color:#666;border:1.5px solid #e0e0e0;border-radius:10px;text-decoration:none;font-size:14px;transition:all .15s}
        .te-btn-cancel:hover{background:#f5f5f5;border-color:#999}
        @media(max-width:1100px){.te-layout{grid-template-columns:1fr!important}}
    </style>

    <form method="POST" enctype="multipart/form-data">
        <div class="te-layout" style="display:grid;grid-template-columns:2fr 1fr;gap:25px;">

            <!-- Colonne principale -->
            <div>
                <!-- Identité -->
                <div class="te-card">
                    <h3>👤 Identité du client</h3>
                    <div class="te-grid te-grid-2">
                        <div class="te-field">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($temoignage['nom'] ?? ''); ?>" placeholder="Nom de famille">
                        </div>
                        <div class="te-field">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="prenom" class="form-control" value="<?php echo htmlspecialchars($temoignage['prenom'] ?? ''); ?>" placeholder="Prénom">
                        </div>
                    </div>
                    <div class="te-grid te-grid-2" style="margin-top:14px;">
                        <div class="te-field">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" class="form-control" value="<?php echo htmlspecialchars($temoignage['ville'] ?? ''); ?>" placeholder="Ex: Toulouse">
                        </div>
                        <div class="te-field">
                            <label class="form-label">Département</label>
                            <input type="text" name="departement" class="form-control" value="<?php echo htmlspecialchars($temoignage['departement'] ?? ''); ?>" maxlength="10" placeholder="Ex: 31">
                        </div>
                    </div>
                </div>

                <!-- Avis -->
                <div class="te-card">
                    <h3>⭐ Avis et témoignage</h3>
                    <div class="te-grid te-grid-2">
                        <div class="te-field">
                            <label class="form-label">Note (1-5)</label>
                            <select name="note" class="form-control">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo ($temoignage['note'] ?? 5) == $i ? 'selected' : ''; ?>><?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="te-field">
                            <label class="form-label">Modèle acheté</label>
                            <select name="modele_id" class="form-control">
                                <option value="">-- Non spécifié --</option>
                                <?php foreach ($modeles as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php echo ($temoignage['modele_id'] ?? '') == $m['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['nom']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="te-field" style="margin-top:14px;">
                        <label class="form-label">Témoignage</label>
                        <textarea name="temoignage" class="form-control" rows="6" placeholder="Le témoignage du client..."><?php echo htmlspecialchars($temoignage['temoignage'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div>
                <!-- Photo -->
                <div class="te-card">
                    <h3>📷 Photo du client</h3>
                    <?php if (!empty($temoignage['photo'])): ?>
                    <div class="te-image-preview">
                        <img src="../uploads/temoignages/<?php echo htmlspecialchars($temoignage['photo']); ?>" alt="">
                        <input type="hidden" name="photo_existante" value="<?php echo htmlspecialchars($temoignage['photo']); ?>">
                    </div>
                    <?php endif; ?>
                    <div class="te-field">
                        <label class="form-label"><?php echo empty($temoignage['photo']) ? 'Choisir une photo' : 'Remplacer la photo'; ?></label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small style="color:#999;font-size:11px;margin-top:6px;display:block;">JPG, PNG, GIF, WebP — max 5 Mo</small>
                    </div>
                </div>

                <!-- Publication -->
                <div class="te-card">
                    <h3>⚡ Publication</h3>
                    <label class="te-publish">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo ($temoignage['is_active'] ?? 1) ? 'checked' : ''; ?>>
                        <span>Témoignage actif</span>
                    </label>
                    <label class="te-publish">
                        <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($temoignage['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Mettre en avant</span>
                    </label>
                    <p style="font-size:12px;color:#999;margin-top:10px;line-height:1.5;">Si décoché, ce témoignage sera masqué du site public mais conservé en base.</p>
                </div>

                <!-- Actions -->
                <div class="te-card" style="position:sticky;top:20px;">
                    <button type="submit" class="te-btn-save">💾 Enregistrer le témoignage</button>
                    <a href="temoignages.php" class="te-btn-cancel">Annuler</a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
