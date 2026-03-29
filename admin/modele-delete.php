<?php
/**
 * Admin - Suppression d'un modèle
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    // Vérifier si le modèle existe
    $stmt = $pdo->prepare("SELECT id, nom FROM modeles WHERE id = ?");
    $stmt->execute([$id]);
    $modele = $stmt->fetch();
    
    if ($modele) {
        // Supprimer l'image associée si elle existe
        $stmt = $pdo->prepare("SELECT image_principale FROM modeles WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();
        if ($img && file_exists("../uploads/maisons/{$img}")) {
            unlink("../uploads/maisons/{$img}");
        }
        
        // Supprimer le modèle
        $pdo->prepare("DELETE FROM modeles WHERE id = ?")->execute([$id]);
        
        setFlashMessage('success', 'Modèle "' . $modele['nom'] . '" supprimé avec succès');
    } else {
        setFlashMessage('error', 'Modèle introuvable');
    }
}

header('Location: modeles.php');
exit;
