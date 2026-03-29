<?php
/**
 * Admin - Suppression d'un inscrit newsletter
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $stmt = $pdo->prepare("SELECT id, email FROM newsletter_subscribers WHERE id = ?");
    $stmt->execute([$id]);
    $sub = $stmt->fetch();
    
    if ($sub) {
        $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Inscrit ' . $sub['email'] . ' supprimé avec succès');
    } else {
        setFlashMessage('error', 'Inscrit introuvable');
    }
}

header('Location: newsletter.php');
exit;
