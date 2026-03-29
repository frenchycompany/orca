<?php
/**
 * Système de cache simple
 * Génère des fichiers HTML statiques pour les pages publiques
 */

// Dossier de cache
$cacheDir = __DIR__ . '/../cache/';
$cacheEnabled = true; // Désactiver en développement
$cacheDuration = 3600; // 1 heure en secondes

/**
 * Vérifie si une page est en cache
 * @param string $cacheKey Identifiant unique de la page
 * @return string|false Contenu du cache ou false
 */
function getCache($cacheKey) {
    global $cacheDir, $cacheEnabled, $cacheDuration;
    
    if (!$cacheEnabled) {
        return false;
    }
    
    // Pas de cache pour les admins
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
        return false;
    }
    
    // Pas de cache si on a des paramètres POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        return false;
    }
    
    $cacheFile = $cacheDir . md5($cacheKey) . '.html';
    
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    // Vérifier si le cache est expiré
    if (time() - filemtime($cacheFile) > $cacheDuration) {
        unlink($cacheFile);
        return false;
    }
    
    return file_get_contents($cacheFile);
}

/**
 * Sauvegarde une page en cache
 * @param string $cacheKey Identifiant unique
 * @param string $content Contenu HTML
 */
function setCache($cacheKey, $content) {
    global $cacheDir, $cacheEnabled;
    
    if (!$cacheEnabled) {
        return;
    }
    
    // Pas de cache pour les admins
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
        return;
    }
    
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . md5($cacheKey) . '.html';
    
    // Ajouter un commentaire pour indiquer que c'est du cache
    $content .= "\n<!-- Cached: " . date('Y-m-d H:i:s') . " -->";
    
    file_put_contents($cacheFile, $content);
}

/**
 * Vide tout le cache
 */
function clearCache() {
    global $cacheDir;
    
    if (!is_dir($cacheDir)) {
        return;
    }
    
    $files = glob($cacheDir . '*.html');
    foreach ($files as $file) {
        unlink($file);
    }
}

/**
 * Vide le cache d'une page spécifique
 * @param string $cacheKey Identifiant de la page
 */
function clearCacheKey($cacheKey) {
    global $cacheDir;
    
    $cacheFile = $cacheDir . md5($cacheKey) . '.html';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
 * Retourne la taille totale du cache
 */
function getCacheSize() {
    global $cacheDir;
    
    if (!is_dir($cacheDir)) {
        return '0 Mo';
    }
    
    $size = 0;
    $files = glob($cacheDir . '*.html');
    foreach ($files as $file) {
        $size += filesize($file);
    }
    
    return round($size / 1024 / 1024, 2) . ' Mo';
}

/**
 * Retourne le nombre de fichiers en cache
 */
function getCacheCount() {
    global $cacheDir;
    
    if (!is_dir($cacheDir)) {
        return 0;
    }
    
    return count(glob($cacheDir . '*.html'));
}
