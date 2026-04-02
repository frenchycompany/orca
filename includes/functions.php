<?php
/**
 * Fonctions utilitaires du site ORCA
 */

// ======================================================
// TEXTES ÉDITABLES
// Usage: t('page.cle', 'Texte par défaut')
// ======================================================

$_site_texts_cache = null;

/**
 * Récupère un texte éditable. Si pas en BDD, retourne le défaut.
 * En mode admin (GET ?edit_texts=1), entoure le texte d'un cadre cliquable.
 */
function t($key, $default = '') {
    global $pdo, $_site_texts_cache;

    // Charger le cache une seule fois
    if ($_site_texts_cache === null) {
        $_site_texts_cache = [];
        try {
            $stmt = $pdo->query("SELECT CONCAT(page, '.', text_key) as full_key, text_value FROM site_texts");
            while ($row = $stmt->fetch()) {
                $_site_texts_cache[$row['full_key']] = $row['text_value'];
            }
        } catch (Exception $e) {
            // Table pas encore créée
        }
    }

    $value = $_site_texts_cache[$key] ?? $default;

    // Auto-insertion si le texte n'existe pas encore en BDD
    if (!isset($_site_texts_cache[$key]) && $default !== '') {
        try {
            $parts = explode('.', $key, 2);
            if (count($parts) === 2) {
                $type = (strlen($default) > 100 || strpos($default, "\n") !== false) ? 'textarea' : 'text';
                if (strpos($default, '<') !== false) $type = 'html';
                $pdo->prepare("INSERT IGNORE INTO site_texts (page, text_key, text_value, text_type) VALUES (?, ?, ?, ?)")
                    ->execute([$parts[0], $parts[1], $default, $type]);
                $_site_texts_cache[$key] = $default;
            }
        } catch (Exception $e) {}
    }

    return $value;
}

/**
 * Version HTML-safe de t()
 */
function te($key, $default = '') {
    return htmlspecialchars(t($key, $default));
}

/**
 * Version avec nl2br pour les textes multilignes
 */
function tnl($key, $default = '') {
    return nl2br(htmlspecialchars(t($key, $default)));
}

/**
 * Affiche un message flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'alert-success' : ($flash['type'] === 'error' ? 'alert-error' : 'alert-info');
        echo '<div class="alert ' . $class . '">' . clean($flash['message']) . '</div>';
    }
}

/**
 * Alias pour compatibilité - retourne le HTML du message flash
 */
function displayFlashMessages() {
    $flash = getFlashMessage();
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
        $icon = $flash['type'] === 'success' ? '✓' : '✗';
        return '<div class="alert ' . $class . '"><span class="alert-icon">' . $icon . '</span>' . clean($flash['message']) . '</div>';
    }
    return '';
}

/**
 * Génère un slug à partir d'une chaîne
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Tronque un texte
 */
function truncate($text, $length = 150, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Validation email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validation téléphone
 */
function isValidPhone($phone) {
    return preg_match('/^(0[1-9])(?:[ \-\.]?[0-9]{2}){4}$/', $phone);
}

/**
 * Récupère l'IP du visiteur
 */
function getClientIP() {
    $ipkeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipkeys as $key) {
        if (array_key_exists($key, $_SERVER) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
            return $_SERVER[$key];
        }
    }
    return '0.0.0.0';
}

/**
 * Envoie un email
 */
function sendEmail($to, $subject, $message, $from = null) {
    global $site_config;
    $from = $from ?: $site_config['site_email'];
    $headers = "From: " . $site_config['site_name'] . " <" . $from . ">\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}

/**
 * Enregistre un lead
 */
function saveLead($data) {
    global $pdo;
    
    $sql = "INSERT INTO leads (type_demande, civilite, nom, prenom, email, telephone, 
            code_postal, ville, departement, modele_interesse, surface_souhaitee, 
            budget_estime, terrain_prevu, delai_souhaite, commentaire, 
            source, page_source, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['type_demande'] ?? 'devis',
        $data['civilite'] ?? 'M',
        $data['nom'],
        $data['prenom'] ?? null,
        $data['email'],
        $data['telephone'] ?? null,
        $data['code_postal'] ?? null,
        $data['ville'] ?? null,
        $data['departement'] ?? null,
        $data['modele_interesse'] ?? null,
        $data['surface_souhaitee'] ?? null,
        $data['budget_estime'] ?? null,
        $data['terrain_prevu'] ?? 0,
        $data['delai_souhaite'] ?? '6-mois',
        $data['commentaire'] ?? null,
        $data['source'] ?? 'site-web',
        $data['page_source'] ?? $_SERVER['REQUEST_URI'],
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

/**
 * Récupère les départements (simplifié)
 */
function getDepartements() {
    return [
        '02' => 'Aisne',
        '08' => 'Ardennes',
        '27' => 'Eure',
        '28' => 'Eure-et-Loir',
        '45' => 'Loiret',
        '51' => 'Marne',
        '59' => 'Nord',
        '60' => 'Oise',
        '77' => 'Seine-et-Marne',
        '78' => 'Yvelines',
        '91' => 'Essonne',
        '92' => 'Hauts-de-Seine',
        '93' => 'Seine-Saint-Denis',
        '94' => 'Val-de-Marne',
        '95' => 'Val-d\'Oise'
    ];
}

/**
 * Obtenir le nom d'un département
 */
function getDepartementName($code) {
    $depts = getDepartements();
    return $depts[$code] ?? $code;
}

/**
 * Calcul de mensualité (simplifié)
 */
function calculMensualite($montant, $taux = 3.5, $annees = 20) {
    $tauxMensuel = $taux / 100 / 12;
    $nbMensualites = $annees * 12;
    $mensualite = $montant * $tauxMensuel / (1 - pow(1 + $tauxMensuel, -$nbMensualites));
    return round($mensualite, 2);
}

/**
 * Formatage des surfaces
 */
function formatSurface($surface) {
    return number_format($surface, 0, ',', ' ') . ' m²';
}

/**
 * Badge style pour les modèles
 */
function getStyleBadge($style) {
    $badges = [
        'traditionnel' => '<span class="badge badge-traditionnel">Traditionnel</span>',
        'contemporain' => '<span class="badge badge-contemporain">Contemporain</span>',
        'moderne' => '<span class="badge badge-moderne">Moderne</span>'
    ];
    return $badges[$style] ?? '';
}

/**
 * Badge étage
 */
function getEtageBadge($etage) {
    $badges = [
        'plain-pied' => '<span class="badge badge-plainpied">Plain-pied</span>',
        '1-etage' => '<span class="badge badge-etage">À étage</span>',
        '2-etages' => '<span class="badge badge-etage">2 étages</span>'
    ];
    return $badges[$etage] ?? '';
}
