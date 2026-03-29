<?php
/**
 * Admin - Gestion du cache
 */
require_once '../includes/config.php';
require_once '../includes/cache.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Gestion du cache';
$message = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_all'])) {
        clearCache();
        $message = '<div class="alert alert-success">Cache vidé avec succès !</div>';
    }
}

// Stats
$cacheSize = getCacheSize();
$cacheCount = getCacheCount();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Gestion du cache</h1>
    
    <?php echo $message; ?>
    
    <div class="admin-section">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
            <div style="background: var(--color-gray-lighter); padding: 30px; border-radius: 12px; text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--color-primary);"><?php echo $cacheCount; ?></div>
                <div style="color: var(--color-gray);">Pages en cache</div>
            </div>
            <div style="background: var(--color-gray-lighter); padding: 30px; border-radius: 12px; text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--color-primary);"><?php echo $cacheSize; ?></div>
                <div style="color: var(--color-gray);">Espace utilisé</div>
            </div>
            <div style="background: var(--color-gray-lighter); padding: 30px; border-radius: 12px; text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--color-primary);">1h</div>
                <div style="color: var(--color-gray);">Durée de vie</div>
            </div>
        </div>
        
        <form method="POST" style="text-align: center; padding: 30px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); border-radius: 12px; color: white;">
            <h3 style="color: white; margin-bottom: 15px;">⚠️ Vider le cache</h3>
            <p style="margin-bottom: 20px; opacity: 0.9;">
                Le cache sera regénéré automatiquement au prochain visite.<br>
                À faire après modification de contenu.
            </p>
            <button type="submit" name="clear_all" class="btn btn-white btn-lg" style="background: white; color: #ee5a24;" 
                    onclick="return confirm('Êtes-vous sûr de vouloir vider tout le cache ?')">
                🗑️ Vider tout le cache
            </button>
        </form>
        
        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 8px;">
            <h4 style="margin-bottom: 10px;">ℹ️ Comment fonctionne le cache ?</h4>
            <ul style="margin-left: 20px; line-height: 1.8;">
                <li>Les pages publiques sont automatiquement mises en cache</li>
                <li>Durée de vie : 1 heure</li>
                <li>Le cache est ignoré pour les administrateurs connectés</li>
                <li>Les formulaires (POST) ne sont jamais mis en cache</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
