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
    
    if ($id) {
        $sql = "UPDATE temoignages SET nom=?, prenom=?, ville=?, departement=?, modele_id=?, note=?, temoignage=?, is_active=?, is_featured=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nom'], $data['prenom'], $data['ville'], $data['departement'],
            $data['modele_id'], $data['note'], $data['temoignage'], 
            $data['is_active'], $data['is_featured'], $id
        ]);
    } else {
        $sql = "INSERT INTO temoignages (nom, prenom, ville, departement, modele_id, note, temoignage, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nom'], $data['prenom'], $data['ville'], $data['departement'],
            $data['modele_id'], $data['note'], $data['temoignage'], 
            $data['is_active'], $data['is_featured']
        ]);
    }
    
    header('Location: temoignages.php');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title"><?php echo $temoignage ? 'Modifier' : 'Ajouter'; ?> un témoignage</h1>
    
    <div class="admin-section">
        <form method="POST" style="max-width: 800px;">
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" value="<?php echo $temoignage['nom'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" value="<?php echo $temoignage['prenom'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" value="<?php echo $temoignage['ville'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Département</label>
                    <input type="text" name="departement" class="form-control" value="<?php echo $temoignage['departement'] ?? ''; ?>" maxlength="10">
                </div>
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Modèle acheté</label>
                    <select name="modele_id" class="form-control">
                        <option value="">-- Non spécifié --</option>
                        <?php foreach ($modeles as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo ($temoignage['modele_id'] ?? '') == $m['id'] ? 'selected' : ''; ?>><?php echo $m['nom']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Note (1-5)</label>
                    <select name="note" class="form-control">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($temoignage['note'] ?? 5) == $i ? 'selected' : ''; ?>><?php echo $i; ?> étoile(s)</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Témoignage</label>
                <textarea name="temoignage" class="form-control" rows="6"><?php echo $temoignage['temoignage'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-check" style="margin: 20px 0;">
                <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($temoignage['is_featured'] ?? 0) ? 'checked' : ''; ?>>
                <label for="is_featured">Mettre en avant sur l'accueil</label>
            </div>
            
            <div class="form-check" style="margin: 20px 0;">
                <input type="checkbox" id="is_active" name="is_active" <?php echo ($temoignage['is_active'] ?? 1) ? 'checked' : ''; ?>>
                <label for="is_active">Témoignage actif</label>
            </div>
            
            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="temoignages.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
