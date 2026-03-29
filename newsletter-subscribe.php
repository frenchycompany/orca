<?php
/**
 * Inscription à la newsletter (API JSON)
 */
require_once 'includes/config.php';
require_once 'includes/newsletter.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$email = $_POST['email'] ?? '';
$nom = $_POST['nom'] ?? '';

$result = subscribeNewsletter($email, $nom);

echo json_encode($result);
