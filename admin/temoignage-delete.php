<?php
/**
 * Admin - Suppression d'un témoignage
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $stmt = $pdo->prepare("SELECT id FROM temoignages WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM temoignages WHERE id = ?")->execute([$id]);
        setFlashMessage('success', 'Témoignage supprimé avec succès');
    } else {
        setFlashMessage('error', 'Témoignage introuvable');
    }
}

header('Location: temoignages.php');
exit;
