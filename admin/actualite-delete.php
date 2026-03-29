<?php
/**
 * Admin - Suppression d'une actualité
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $stmt = $pdo->prepare("SELECT id, image FROM actualites WHERE id = ?");
    $stmt->execute([$id]);
    $actu = $stmt->fetch();
    
    if ($actu) {
        // Supprimer l'image associée
        if ($actu['image'] && file_exists("../uploads/actualites/{$actu['image']}")) {
            unlink("../uploads/actualites/{$actu['image']}");
        }
        
        $pdo->prepare("DELETE FROM actualites WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Article supprimé avec succès');
    } else {
        setFlashMessage('error', 'Article introuvable');
    }
}

header('Location: actualites.php');
exit;
