<?php
/**
 * Admin - Logs du site
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Logs du site';

// Créer le dossier logs si inexistant
$logDir = '../logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Fichiers de logs
$logFiles = [
    'connexions' => 'connexions.log',
    'erreurs' => 'erreurs.log',
    'uploads' => 'uploads.log'
];

// Lecture des logs
$type = $_GET['type'] ?? 'connexions';
$logFile = $logDir . ($logFiles[$type] ?? 'connexions.log');
$logs = [];

if (file_exists($logFile)) {
    $lines = array_filter(array_map('trim', file($logFile)));
    $logs = array_slice(array_reverse($lines), 0, 100); // 100 dernières entrées
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Logs du site</h1>
    
    <div class="admin-section">
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <a href="?type=connexions" class="btn <?php echo $type === 'connexions' ? 'btn-primary' : 'btn-outline'; ?>">Connexions</a>
            <a href="?type=erreurs" class="btn <?php echo $type === 'erreurs' ? 'btn-primary' : 'btn-outline'; ?>">Erreurs</a>
            <a href="?type=uploads" class="btn <?php echo $type === 'uploads' ? 'btn-primary' : 'btn-outline'; ?>">Uploads</a>
        </div>
        
        <?php if (empty($logs)): ?>
        <p style="color: var(--color-gray);">Aucun log pour le moment.</p>
        <?php else: ?>
        <div style="background: var(--color-dark); color: #fff; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 13px; max-height: 600px; overflow-y: auto;">
            <?php foreach ($logs as $log): ?>
            <div style="padding: 5px 0; border-bottom: 1px solid #333;"><?php echo htmlspecialchars($log); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
