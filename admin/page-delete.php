<?php
/**
 * Admin - Suppression d'une page (CMS)
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    // Vérifier que la page existe
    $stmt = $pdo->prepare("SELECT id, slug FROM pages WHERE id = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();
    
    if ($page) {
        // Empêcher la suppression de certaines pages critiques
        $protected_slugs = ['index', 'contact', 'mentions-legales'];
        if (in_array($page['slug'], $protected_slugs)) {
            setFlashMessage('error', 'Cette page est protégée et ne peut pas être supprimée');
        } else {
            $pdo->prepare("DELETE FROM pages WHERE id = ?")->execute([$id]);
            setFlashMessage('success', 'Page supprimée avec succès');
        }
    } else {
        setFlashMessage('error', 'Page introuvable');
    }
}

header('Location: pages.php');
exit;
