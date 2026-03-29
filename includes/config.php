<?php
/**
 * Configuration du site ORCA V2
 */

// Désactiver l'affichage des erreurs en production
// error_reporting(0);
// ini_set('display_errors', 0);

// Mode développement (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'orca');
define('DB_USER', 'orca_user');      // À adapter selon votre configuration
define('DB_PASS', 'votre_mot_de_passe'); // À CHANGER !
define('DB_CHARSET', 'utf8mb4');

// Configuration du site - Détection automatique de l'URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
$dir = dirname($scriptName);

// Construire le chemin de base (sans slash final)
if ($dir === '/' || $dir === '\\') {
    $basePath = '';
} else {
    $basePath = rtrim($dir, '/');
    // Si on est dans /admin/, remonter d'un niveau
    if (strpos($basePath, '/admin') !== false) {
        $basePath = dirname($basePath);
    }
    $basePath = rtrim($basePath, '/');
}

// SITE_URL avec un seul slash entre host et path
$siteUrl = $protocol . $host;
if ($basePath !== '') {
    $siteUrl .= '/' . $basePath . '/';
} else {
    $siteUrl .= '/';
}

define('SITE_URL', $siteUrl);
define('SITE_PATH', dirname(__DIR__) . '/');
define('UPLOADS_URL', SITE_URL . 'uploads/');
define('UPLOADS_PATH', SITE_PATH . 'uploads/');

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

// Fonctions utilitaires
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/chatbot-functions.php';

// Charger la configuration du site
$site_config = getSiteConfig();

/**
 * Récupère la configuration du site avec valeurs par défaut CSS
 */
function getSiteConfig() {
    global $pdo;
    
    // Valeurs par défaut CSS
    $defaults = [
        'color_primary' => '#1a5653',
        'color_primary_dark' => '#124a47',
        'color_primary_light' => '#2d7a76',
        'color_accent' => '#c9a227',
        'color_accent_dark' => '#b08d20',
        'banner_bg_image' => 'images/banner-default.jpg',
        'banner_overlay_opacity' => '0.85',
        'css_version' => '1.0.0',
    ];
    
    try {
        $stmt = $pdo->query("SELECT cle, valeur FROM config");
        $config = $defaults;
        while ($row = $stmt->fetch()) {
            $config[$row['cle']] = $row['valeur'];
        }
        return $config;
    } catch (PDOException $e) {
        return $defaults;
    }
}

/**
 * Génère une URL propre
 */
function url($path = '') {
    if ($path === '') {
        return rtrim(SITE_URL, '/');
    }
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirection
 */
function redirect($path) {
    header("Location: " . url($path));
    exit;
}

/**
 * Protection CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Nettoyage des entrées
 */
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Formatage du prix
 */
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' €';
}

/**
 * Formatage de la date
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Récupère les modèles actifs
 */
function getModeles($limit = null) {
    global $pdo;
    $sql = "SELECT * FROM modeles WHERE is_active = 1 ORDER BY ordre_affichage ASC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $pdo->query($sql)->fetchAll();
}

/**
 * Récupère un modèle par slug
 */
function getModeleBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM modeles WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Récupère les témoignages mis en avant
 */
function getTemoignagesFeatured($limit = 3) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT t.*, m.nom as modele_nom FROM temoignages t LEFT JOIN modeles m ON t.modele_id = m.id WHERE t.is_active = 1 AND t.is_featured = 1 ORDER BY t.created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Récupère les actualités
 */
function getActualites($limit = null, $featured = false) {
    global $pdo;
    $sql = "SELECT * FROM actualites WHERE is_active = 1";
    if ($featured) {
        $sql .= " AND is_featured = 1";
    }
    $sql .= " ORDER BY published_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $pdo->query($sql)->fetchAll();
}

/**
 * Récupère la FAQ
 */
function getFAQ() {
    global $pdo;
    return $pdo->query("SELECT * FROM faq WHERE is_active = 1 ORDER BY ordre_affichage ASC")->fetchAll();
}
