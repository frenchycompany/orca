<?php
/**
 * Admin - Suppression d'une FAQ
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $stmt = $pdo->prepare("SELECT id FROM faq WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM faq WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Question supprimée avec succès');
    } else {
        setFlashMessage('error', 'Question introuvable');
    }
}

header('Location: faq.php');
exit;
