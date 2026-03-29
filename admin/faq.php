<?php
/**
 * Admin - Gestion FAQ
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion FAQ';

$faq = $pdo->query("SELECT * FROM faq ORDER BY ordre_affichage ASC, categorie ASC")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Gestion FAQ</h1>
        <a href="faq-edit.php" class="btn btn-primary">+ Ajouter une question</a>
    </div>
    
    <?php echo displayFlashMessages(); ?>
    
    <div class="admin-section">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Catégorie</th>
                    <th>Question</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($faq as $item): ?>
                <tr>
                    <td><?php echo $item['ordre_affichage']; ?></td>
                    <td><?php echo ucfirst($item['categorie']); ?></td>
                    <td><?php echo htmlspecialchars(substr($item['question'], 0, 80)) . (strlen($item['question']) > 80 ? '...' : ''); ?></td>
                    <td>
                        <?php if ($item['is_active']): ?>
                        <span class="badge badge-success">Active</span>
                        <?php else: ?>
                        <span class="badge badge-error">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <a href="faq-edit.php?id=<?php echo $item['id']; ?>" class="btn-icon" title="Modifier" style="background: #e3f2fd; color: #1976d2;">✏️</a>
                        <a href="faq-delete.php?id=<?php echo $item['id']; ?>" class="btn-icon" title="Supprimer" style="background: #ffebee; color: #c62828;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette question ? Cette action est irréversible.');">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
