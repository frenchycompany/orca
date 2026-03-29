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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="admin-title"><?php echo $page_title; ?></h1>
        <a href="terrains.php" class="btn btn-outline">← Retour</a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="admin-section">
        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Référence *</label>
                    <input type="text" name="reference" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"
                        value="<?php echo htmlspecialchars($terrain['reference'] ?? ''); ?>" required placeholder="T-60-001">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Département *</label>
                    <input type="text" name="departement" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"
                        value="<?php echo htmlspecialchars($terrain['departement'] ?? ''); ?>" required placeholder="60">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Ville *</label>
                    <input type="text" name="ville" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"
                        value="<?php echo htmlspecialchars($terrain['ville'] ?? ''); ?>" required>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Code postal</label>
                    <input type="text" name="code_postal" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"
                        value="<?php echo htmlspecialchars($terrain['code_postal'] ?? ''); ?>" placeholder="60200">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Surface (m²) *</label>
                    <input type="number" name="surface" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"
                        value="<?php echo $terrain['surface'] ?? ''; ?>" required min="1">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Prix (€) *</label>
                    <input type="number" name="prix" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"
                        value="<?php echo $terrain['prix'] ?? ''; ?>" required min="1" step="100">
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:500;">Type de terrain</label>
                    <select name="type_terrain" class="form-control" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                        <option value="plat" <?php echo ($terrain['type_terrain'] ?? '') === 'plat' ? 'selected' : ''; ?>>Plat</option>
                        <option value="en_pente" <?php echo ($terrain['type_terrain'] ?? '') === 'en_pente' ? 'selected' : ''; ?>>En pente</option>
                        <option value="boise" <?php echo ($terrain['type_terrain'] ?? '') === 'boise' ? 'selected' : ''; ?>>Boisé</option>
                        <option value="constructible" <?php echo ($terrain['type_terrain'] ?? '') === 'constructible' ? 'selected' : ''; ?>>Constructible</option>
                    </select>
                </div>
                <div style="display:flex;gap:20px;align-items:center;padding-top:25px;">
                    <label><input type="checkbox" name="est_viabilise" <?php echo ($terrain['est_viabilise'] ?? 0) ? 'checked' : ''; ?>> Viabilisé</label>
                    <label><input type="checkbox" name="is_available" <?php echo ($terrain['is_available'] ?? 1) ? 'checked' : ''; ?>> Disponible</label>
                    <label><input type="checkbox" name="is_featured" <?php echo ($terrain['is_featured'] ?? 0) ? 'checked' : ''; ?>> Mis en avant</label>
                </div>
            </div>

            <div style="margin-top:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;">Description</label>
                <textarea name="description" class="form-control" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"><?php echo htmlspecialchars($terrain['description'] ?? ''); ?></textarea>
            </div>
            <div style="margin-top:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:500;">Proximité / Commodités</label>
                <textarea name="proximite" class="form-control" rows="2" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;"><?php echo htmlspecialchars($terrain['proximite'] ?? ''); ?></textarea>
            </div>

            <div style="margin-top:25px;">
                <button type="submit" class="btn btn-primary"><?php echo $id ? 'Enregistrer' : 'Ajouter ce terrain'; ?></button>
                <a href="terrains.php" class="btn btn-outline" style="margin-left:10px;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
