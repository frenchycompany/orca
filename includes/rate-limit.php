<?php
/**
 * Rate limiting pour les formulaires (anti-spam)
 */

/**
 * Vérifie si l'utilisateur a dépassé la limite de soumissions
 * @param string $action Nom de l'action (ex: 'contact', 'login')
 * @param int $maxAttempts Nombre maximum de tentatives
 * @param int $windowSeconds Fenêtre de temps en secondes
 * @return bool True si autorisé, False si limité
 */
function checkRateLimit($action, $maxAttempts = 5, $windowSeconds = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'rate_limit_' . $action . '_' . md5($ip);
    
    // Initialiser le stockage en session
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    
    $now = time();
    
    // Nettoyer les anciennes entrées
    if (isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = array_filter(
            $_SESSION['rate_limits'][$key],
            function($timestamp) use ($now, $windowSeconds) {
                return ($now - $timestamp) < $windowSeconds;
            }
        );
    } else {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    // Vérifier le nombre de tentatives
    $attempts = count($_SESSION['rate_limits'][$key]);
    
    if ($attempts >= $maxAttempts) {
        return false;
    }
    
    // Enregistrer cette tentative
    $_SESSION['rate_limits'][$key][] = $now;
    
    return true;
}

/**
 * Retourne le temps restant avant la prochaine tentative autorisée
 * @param string $action Nom de l'action
 * @param int $windowSeconds Fenêtre de temps
 * @return int Temps en secondes (0 si pas de limitation)
 */
function getRateLimitRemaining($action, $windowSeconds = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'rate_limit_' . $action . '_' . md5($ip);
    
    if (!isset($_SESSION['rate_limits'][$key]) || empty($_SESSION['rate_limits'][$key])) {
        return 0;
    }
    
    $now = time();
    $oldest = min($_SESSION['rate_limits'][$key]);
    $remaining = $windowSeconds - ($now - $oldest);
    
    return max(0, $remaining);
}

/**
 * Enregistre une tentative échouée (pour les logins)
 * @param string $action Nom de l'action
 */
function recordFailedAttempt($action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key = 'rate_limit_' . $action . '_' . md5($ip);
    
    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    
    $_SESSION['rate_limits'][$key][] = time();
}
