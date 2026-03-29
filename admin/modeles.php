<?php
/**
 * Admin - Gestion des modèles
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des modèles';

// Récupérer les modèles
$modeles = $pdo->query("SELECT * FROM modeles ORDER BY ordre_affichage ASC")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Gestion des modèles</h1>
        <a href="modele-edit.php" class="btn btn-primary">+ Ajouter un modèle</a>
    </div>
    
    <?php echo displayFlashMessages(); ?>
    
    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Style</th>
                    <th>Surface</th>
                    <th>Chambres</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modeles as $modele): ?>
                <tr>
                    <td><?php echo $modele['ordre_affichage']; ?></td>
                    <td>
                        <?php if ($modele['image_principale']): ?>
                        <img src="../uploads/maisons/<?php echo $modele['image_principale']; ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                        <?php else: ?>
                        <div style="width: 60px; height: 40px; background: #eee; border-radius: 4px;"></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($modele['nom']); ?></strong></td>
                    <td><?php echo ucfirst($modele['style']); ?></td>
                    <td><?php echo $modele['surface_habitable']; ?> m²</td>
                    <td><?php echo $modele['nb_chambres']; ?></td>
                    <td><?php echo htmlspecialchars($modele['prix_afficher']); ?></td>
                    <td>
                        <?php if ($modele['is_active']): ?>
                        <span class="badge badge-success">Actif</span>
                        <?php else: ?>
                        <span class="badge badge-error">Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="modele-edit.php?id=<?php echo $modele['id']; ?>" class="btn-icon" title="Modifier" style="background: #e3f2fd; color: #1976d2;">✏️</a>
                        <a href="modele-delete.php?id=<?php echo $modele['id']; ?>" class="btn-icon" title="Supprimer" style="background: #ffebee; color: #c62828;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce modèle ? Cette action est irréversible.');">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
