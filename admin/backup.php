<?php
/**
 * Admin - Backup de la base de données
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Backup BDD';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_backup'])) {
    $backupDir = '../backups/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $filepath = $backupDir . $filename;
    
    // Créer le backup via mysqldump
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASS),
        escapeshellarg(DB_NAME),
        escapeshellarg($filepath)
    );
    
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($filepath)) {
        // Enregistrer le log
        $logFile = '../logs/backups.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Backup créé : {$filename} par {$_SESSION['admin_username']}\n", FILE_APPEND);
        
        $message = '<div class="alert alert-success">Backup créé avec succès : ' . $filename . '</div>';
    } else {
        $message = '<div class="alert alert-error">Erreur lors de la création du backup</div>';
    }
}

// Liste des backups existants
$backups = [];
$backupDir = '../backups/';
if (is_dir($backupDir)) {
    $files = glob($backupDir . '*.sql');
    rsort($files); // Plus récent en premier
    foreach ($files as $file) {
        $backups[] = [
            'name' => basename($file),
            'size' => round(filesize($file) / 1024 / 1024, 2) . ' Mo',
            'date' => date('d/m/Y H:i:s', filemtime($file))
        ];
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Backup Base de Données</h1>
    
    <?php echo $message; ?>
    
    <div class="admin-section">
        <form method="POST" style="margin-bottom: 30px;">
            <button type="submit" name="create_backup" class="btn btn-primary btn-lg">📦 Créer un backup maintenant</button>
        </form>
        
        <h3>Backups existants</h3>
        <?php if (empty($backups)): ?>
        <p style="color: var(--color-gray);">Aucun backup pour le moment.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nom du fichier</th>
                    <th>Date</th>
                    <th>Taille</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup): ?>
                <tr>
                    <td><?php echo $backup['name']; ?></td>
                    <td><?php echo $backup['date']; ?></td>
                    <td><?php echo $backup['size']; ?></td>
                    <td>
                        <a href="../backups/<?php echo $backup['name']; ?>" download class="btn btn-sm btn-primary">Télécharger</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
