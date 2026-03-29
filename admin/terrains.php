<?php
/**
 * Admin - Gestion des Terrains
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$page_title = 'Gestion des terrains';

// Filtres
$filter_dept = $_GET['dept'] ?? '';
$filter_dispo = $_GET['dispo'] ?? '';

$where = [];
$params = [];

if ($filter_dept) {
    $where[] = 't.departement = ?';
    $params[] = $filter_dept;
}
if ($filter_dispo === '1') {
    $where[] = 't.is_available = 1';
} elseif ($filter_dispo === '0') {
    $where[] = 't.is_available = 0';
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT t.* FROM terrains t $where_clause ORDER BY t.departement ASC, t.ville ASC");
$stmt->execute($params);
$terrains = $stmt->fetchAll();

// Départements existants pour le filtre
$depts = $pdo->query("SELECT DISTINCT departement FROM terrains ORDER BY departement")->fetchAll(PDO::FETCH_COLUMN);

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1 class="admin-title">Terrains disponibles</h1>
        <a href="terrain-edit.php" class="btn btn-primary">+ Ajouter un terrain</a>
    </div>

    <?php echo displayFlashMessages(); ?>

    <!-- Filtres -->
    <div class="admin-section">
        <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
            <div>
                <label style="display: block; font-size: 13px; margin-bottom: 5px;">Département</label>
                <select name="dept" class="form-control" style="padding: 8px 12px; border: 1px solid var(--color-gray-light); border-radius: 6px;">
                    <option value="">Tous</option>
                    <?php foreach ($depts as $d): ?>
                    <option value="<?php echo $d; ?>" <?php echo $filter_dept == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; margin-bottom: 5px;">Disponibilité</label>
                <select name="dispo" class="form-control" style="padding: 8px 12px; border: 1px solid var(--color-gray-light); border-radius: 6px;">
                    <option value="">Tous</option>
                    <option value="1" <?php echo $filter_dispo === '1' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="0" <?php echo $filter_dispo === '0' ? 'selected' : ''; ?>>Vendu / Réservé</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filtrer</button>
            <a href="terrains.php" class="btn btn-outline">Réinitialiser</a>
        </form>
    </div>

    <!-- Stats rapides -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
        <?php
        $total = count($terrains);
        $disponibles = count(array_filter($terrains, fn($t) => $t['is_available']));
        $prix_moyen = $total > 0 ? array_sum(array_column($terrains, 'prix')) / $total : 0;
        ?>
        <div class="admin-section" style="padding: 15px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: var(--color-primary);"><?php echo $total; ?></div>
            <div style="font-size: 13px; color: #888;">Terrains total</div>
        </div>
        <div class="admin-section" style="padding: 15px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: #27ae60;"><?php echo $disponibles; ?></div>
            <div style="font-size: 13px; color: #888;">Disponibles</div>
        </div>
        <div class="admin-section" style="padding: 15px; text-align: center;">
            <div style="font-size: 28px; font-weight: 700; color: #f39c12;"><?php echo formatPrice($prix_moyen); ?></div>
            <div style="font-size: 13px; color: #888;">Prix moyen</div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Localisation</th>
                    <th>Surface</th>
                    <th>Prix</th>
                    <th>Viabilisé</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($terrains as $t): ?>
                <tr>
                    <td><strong><?php echo clean($t['reference']); ?></strong></td>
                    <td>
                        <?php echo clean($t['ville']); ?> (<?php echo $t['departement']; ?>)<br>
                        <small style="color: #888;"><?php echo $t['code_postal']; ?></small>
                    </td>
                    <td><?php echo number_format($t['surface'], 0, ',', ' '); ?> m²</td>
                    <td><strong><?php echo formatPrice($t['prix']); ?></strong></td>
                    <td>
                        <?php if ($t['est_viabilise']): ?>
                        <span class="badge badge-success">Oui</span>
                        <?php else: ?>
                        <span class="badge badge-warning">Non</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($t['is_available']): ?>
                        <span class="badge badge-success">Disponible</span>
                        <?php else: ?>
                        <span class="badge badge-error">Vendu</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="terrain-edit.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                        <a href="terrain-delete.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce terrain ?')" style="background:#e74c3c;color:#fff;margin-left:5px;">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($terrains)): ?>
                <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">Aucun terrain trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
