<?php
/**
 * Admin - Supprimer un terrain
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $pdo->prepare("DELETE FROM terrains WHERE id = ?")->execute([$id]);
    setFlashMessage('success', 'Terrain supprimé.');
}

header('Location: terrains.php');
exit;
