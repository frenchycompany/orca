<?php
/**
 * Admin - Suppression d'un lead
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    // Vérifier si le lead existe
    $stmt = $pdo->prepare("SELECT id FROM leads WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->fetch()) {
        // Supprimer le lead
        $pdo->prepare("DELETE FROM leads WHERE id = ?")->execute([$id]);
        
        // Logger
        $logFile = '../logs/admin.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Lead #{$id} supprimé par {$_SESSION['admin_username']}\n", FILE_APPEND);
        
        setFlashMessage('success', 'Lead supprimé avec succès');
    } else {
        setFlashMessage('error', 'Lead introuvable');
    }
}

header('Location: leads.php');
exit;
