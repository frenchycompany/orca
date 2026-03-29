<?php
/**
 * Admin - Gestion des pages (CMS)
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion des pages';

// Récupérer les pages
$pages = $pdo->query("SELECT * FROM pages ORDER BY menu_order ASC")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Gestion des pages</h1>
        <a href="page-edit.php" class="btn btn-primary">+ Ajouter une page</a>
    </div>
    
    <?php echo displayFlashMessages(); ?>
    
    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Titre</th>
                    <th>URL</th>
                    <th>Dans le menu</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                <tr>
                    <td><?php echo $page['menu_order']; ?></td>
                    <td><strong><?php echo htmlspecialchars($page['titre']); ?></strong></td>
                    <td><?php echo $page['slug']; ?>.php</td>
                    <td><?php echo $page['in_menu'] ? 'Oui' : 'Non'; ?></td>
                    <td>
                        <?php if ($page['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                        <?php else: ?>
                        <span class="badge badge-error">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="page-edit.php?id=<?php echo $page['id']; ?>" class="btn-icon" title="Modifier" style="background: #e3f2fd; color: #1976d2;">✏️</a>
                        <a href="../<?php echo $page['slug']; ?>.php" target="_blank" class="btn-icon" title="Voir" style="background: #f3e5f5; color: #7b1fa2;">👁️</a>
                        <a href="page-delete.php?id=<?php echo $page['id']; ?>" class="btn-icon" title="Supprimer" style="background: #ffebee; color: #c62828;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette page ? Cette action est irréversible.');">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
