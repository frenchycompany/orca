<?php
/**
 * Admin - Upload d'images
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Types de fichiers autorisés
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5 Mo

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $type = $_POST['type'] ?? 'maisons'; // maisons, actualites, config
    
    // Vérifier les erreurs
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Erreur lors de l\'upload';
        echo json_encode($response);
        exit;
    }
    
    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $response['message'] = 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WebP';
        echo json_encode($response);
        exit;
    }
    
    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        $response['message'] = 'Fichier trop volumineux (max 5 Mo)';
        echo json_encode($response);
        exit;
    }
    
    // Vérifier que c'est bien une image (sécurité)
    if (!getimagesize($file['tmp_name'])) {
        $response['message'] = 'Fichier image invalide';
        echo json_encode($response);
        exit;
    }
    
    // Créer le dossier si inexistant
    $uploadDir = "../uploads/{$type}/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Générer un nom unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . slugify(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $response['success'] = true;
        $response['message'] = 'Image uploadée avec succès';
        $response['filename'] = $filename;
        $response['url'] = url('uploads/' . $type . '/' . $filename);
    } else {
        $response['message'] = 'Erreur lors de l\'enregistrement';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
