<?php
/**
 * Admin - Ajouter / Modifier un terrain
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$id = intval($_GET['id'] ?? 0);
$terrain = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM terrains WHERE id = ?");
    $stmt->execute([$id]);
    $terrain = $stmt->fetch();
    if (!$terrain) redirect('terrains.php');
}

$page_title = $terrain ? 'Modifier le terrain' : 'Ajouter un terrain';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'reference'     => trim($_POST['reference'] ?? ''),
        'ville'         => trim($_POST['ville'] ?? ''),
        'code_postal'   => trim($_POST['code_postal'] ?? ''),
        'departement'   => trim($_POST['departement'] ?? ''),
        'surface'       => intval($_POST['surface'] ?? 0),
        'prix'          => floatval($_POST['prix'] ?? 0),
        'est_viabilise' => isset($_POST['est_viabilise']) ? 1 : 0,
        'type_terrain'  => $_POST['type_terrain'] ?? 'plat',
        'description'   => trim($_POST['description'] ?? ''),
        'proximite'     => trim($_POST['proximite'] ?? ''),
        'is_available'  => isset($_POST['is_available']) ? 1 : 0,
        'is_featured'   => isset($_POST['is_featured']) ? 1 : 0,
    ];

    if (empty($data['reference']) || empty($data['ville']) || $data['surface'] <= 0 || $data['prix'] <= 0) {
        $error = 'Référence, ville, surface et prix sont obligatoires.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE terrains SET
                    reference=?, ville=?, code_postal=?, departement=?, surface=?, prix=?,
                    est_viabilise=?, type_terrain=?, description=?, proximite=?,
                    is_available=?, is_featured=?
                    WHERE id=?");
                $stmt->execute([
                    $data['reference'], $data['ville'], $data['code_postal'], $data['departement'],
                    $data['surface'], $data['prix'], $data['est_viabilise'], $data['type_terrain'],
                    $data['description'], $data['proximite'], $data['is_available'], $data['is_featured'], $id
                ]);
                setFlashMessage('success', 'Terrain modifié avec succès.');
            } else {
                $stmt = $pdo->prepare("INSERT INTO terrains
                    (reference, ville, code_postal, departement, surface, prix, est_viabilise,
                     type_terrain, description, proximite, is_available, is_featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['reference'], $data['ville'], $data['code_postal'], $data['departement'],
                    $data['surface'], $data['prix'], $data['est_viabilise'], $data['type_terrain'],
                    $data['description'], $data['proximite'], $data['is_available'], $data['is_featured']
                ]);
                setFlashMessage('success', 'Terrain ajouté avec succès.');
            }
            header('Location: terrains.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
    $terrain = $data;
    $terrain['id'] = $id;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div class="te-header">
        <h1 class="admin-title"><?php echo $terrain ? '✏️ ' . htmlspecialchars($terrain['reference'] ?? $page_title) : '➕ Nouveau terrain'; ?></h1>
        <a href="terrains.php" class="btn btn-outline">← Retour aux terrains</a>
    </div>

    <style>
        .te-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
        .te-layout{display:grid;grid-template-columns:2fr 1fr;gap:25px}
        .te-card{background:#fff;border-radius:12px;padding:22px 24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);border:1px solid #e8e8e8}
        .te-card h3{font-size:15px;font-weight:700;color:#1a1a1a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
        .te-grid{display:grid;gap:14px}
        .te-grid-2{grid-template-columns:1fr 1fr}
        .te-card .form-group{margin-bottom:0}
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

    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="te-layout">

            <!-- Colonne principale -->
            <div>
                <!-- Localisation -->
                <div class="te-card">
                    <h3>📍 Localisation</h3>
                    <div class="te-grid te-grid-2">
                        <div class="form-group">
                            <label class="form-label">Référence *</label>
                            <input type="text" name="reference" class="form-control"
                                value="<?php echo htmlspecialchars($terrain['reference'] ?? ''); ?>" required placeholder="T-60-001">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Département *</label>
                            <input type="text" name="departement" class="form-control"
                                value="<?php echo htmlspecialchars($terrain['departement'] ?? ''); ?>" required placeholder="60">
                        </div>
                    </div>
                    <div class="te-grid te-grid-2">
                        <div class="form-group">
                            <label class="form-label">Ville *</label>
                            <input type="text" name="ville" class="form-control"
                                value="<?php echo htmlspecialchars($terrain['ville'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Code postal</label>
                            <input type="text" name="code_postal" class="form-control"
                                value="<?php echo htmlspecialchars($terrain['code_postal'] ?? ''); ?>" placeholder="60200">
                        </div>
                    </div>
                </div>

                <!-- Caractéristiques -->
                <div class="te-card">
                    <h3>📐 Caractéristiques</h3>
                    <div class="te-grid te-grid-2">
                        <div class="form-group">
                            <label class="form-label">Surface (m²) *</label>
                            <input type="number" name="surface" class="form-control"
                                value="<?php echo $terrain['surface'] ?? ''; ?>" required min="1">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" name="prix" class="form-control"
                                value="<?php echo $terrain['prix'] ?? ''; ?>" required min="1" step="100">
                        </div>
                    </div>
                    <div class="te-grid">
                        <div class="form-group">
                            <label class="form-label">Type de terrain</label>
                            <select name="type_terrain" class="form-control">
                                <option value="plat" <?php echo ($terrain['type_terrain'] ?? '') === 'plat' ? 'selected' : ''; ?>>Plat</option>
                                <option value="en_pente" <?php echo ($terrain['type_terrain'] ?? '') === 'en_pente' ? 'selected' : ''; ?>>En pente</option>
                                <option value="boise" <?php echo ($terrain['type_terrain'] ?? '') === 'boise' ? 'selected' : ''; ?>>Boisé</option>
                                <option value="constructible" <?php echo ($terrain['type_terrain'] ?? '') === 'constructible' ? 'selected' : ''; ?>>Constructible</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($terrain['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Proximité / Commodités</label>
                            <textarea name="proximite" class="form-control" rows="3"><?php echo htmlspecialchars($terrain['proximite'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar droite -->
            <div>
                <!-- Options -->
                <div class="te-card">
                    <h3>⚡ Options</h3>
                    <label class="te-publish">
                        <input type="checkbox" name="est_viabilise" <?php echo ($terrain['est_viabilise'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Terrain viabilisé</span>
                    </label>
                    <label class="te-publish">
                        <input type="checkbox" name="is_available" <?php echo ($terrain['is_available'] ?? 1) ? 'checked' : ''; ?>>
                        <span>Disponible à la vente</span>
                    </label>
                    <label class="te-publish">
                        <input type="checkbox" name="is_featured" <?php echo ($terrain['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                        <span>Mis en avant</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="te-card">
                    <button type="submit" class="te-btn-save"><?php echo $id ? '💾 Enregistrer' : '➕ Ajouter ce terrain'; ?></button>
                    <a href="terrains.php" class="te-btn-cancel">Annuler</a>
                </div>
            </div>

        </div>
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
