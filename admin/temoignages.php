<?php
/**
 * Admin - Gestion des témoignages
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des témoignages';

$temoignages = $pdo->query("SELECT t.*, m.nom as modele_nom FROM temoignages t LEFT JOIN modeles m ON t.modele_id = m.id ORDER BY t.created_at DESC")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Gestion des témoignages</h1>
        <a href="temoignage-edit.php" class="btn btn-primary">+ Ajouter un témoignage</a>
    </div>
    
    <?php echo displayFlashMessages(); ?>
    
    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Ville</th>
                    <th>Note</th>
                    <th>Modèle</th>
                    <th>En avant</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($temoignages as $t): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($t['prenom'] . ' ' . $t['nom']); ?></strong></td>
                    <td><?php echo htmlspecialchars($t['ville']); ?></td>
                    <td><?php echo str_repeat('★', $t['note']); ?></td>
                    <td><?php echo $t['modele_nom'] ?? '-'; ?></td>
                    <td><?php echo $t['is_featured'] ? '✓' : '-'; ?></td>
                    <td>
                        <?php if ($t['is_active']): ?>
                        <span class="badge badge-success">Actif</span>
                        <?php else: ?>
                        <span class="badge badge-error">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="temoignage-edit.php?id=<?php echo $t['id']; ?>" class="btn-icon" title="Modifier" style="background: #e3f2fd; color: #1976d2;">✏️</a>
                        <a href="temoignage-delete.php?id=<?php echo $t['id']; ?>" class="btn-icon" title="Supprimer" style="background: #ffebee; color: #c62828;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce témoignage ? Cette action est irréversible.');">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
