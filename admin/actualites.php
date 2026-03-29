<?php
/**
 * Admin - Gestion des actualités/blog
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des actualités';

// Récupérer les actualités
$actualites = $pdo->query("SELECT * FROM actualites ORDER BY created_at DESC")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Gestion des actualités</h1>
        <a href="actualite-edit.php" class="btn btn-primary">+ Ajouter un article</a>
    </div>
    
    <?php echo displayFlashMessages(); ?>
    
    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Vues</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($actualites as $actu): ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($actu['created_at'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($actu['titre']); ?></strong></td>
                    <td><?php echo ucfirst($actu['categorie']); ?></td>
                    <td><?php echo $actu['nb_vues']; ?></td>
                    <td>
                        <?php if ($actu['is_active']): ?>
                        <span class="badge badge-success">Publié</span>
                        <?php else: ?>
                        <span class="badge badge-error">Brouillon</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="actualite-edit.php?id=<?php echo $actu['id']; ?>" class="btn-icon" title="Modifier" style="background: #e3f2fd; color: #1976d2;">✏️</a>
                        <a href="../actualite.php?slug=<?php echo $actu['slug']; ?>" target="_blank" class="btn-icon" title="Voir" style="background: #f3e5f5; color: #7b1fa2;">👁️</a>
                        <a href="actualite-delete.php?id=<?php echo $actu['id']; ?>" class="btn-icon" title="Supprimer" style="background: #ffebee; color: #c62828;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.');">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
