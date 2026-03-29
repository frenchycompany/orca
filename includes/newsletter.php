<?php
/**
 * Système de newsletter
 */

/**
 * Inscrit un email à la newsletter
 * @param string $email
 * @param string $nom
 * @return array ['success' => bool, 'message' => string]
 */
function subscribeNewsletter($email, $nom = '') {
    global $pdo;
    
    // Vérifier l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email invalide'];
    }
    
    try {
        // Vérifier si déjà inscrit
        $stmt = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Cet email est déjà inscrit'];
        }
        
        // Insérer
        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, nom, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$email, $nom]);
        
        // Envoyer email de confirmation
        global $site_config;
        $subject = 'Bienvenue à la newsletter Maisons ORCA';
        $content = "
            <p>Bonjour {$nom},</p>
            <p>Merci de votre inscription à notre newsletter !</p>
            <p>Vous recevrez désormais nos actualités, promotions et conseils construction.</p>
            <p>À bientôt,<br>L'équipe Maisons ORCA</p>
        ";
        
        require_once __DIR__ . '/mailer.php';
        sendEmail($email, $subject, $content);
        
        return ['success' => true, 'message' => 'Inscription réussie !'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }
}

/**
 * Désinscrit un email
 * @param string $email
 * @return bool
 */
function unsubscribeNewsletter($email) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    
    return $stmt->rowCount() > 0;
}

/**
 * Récupère tous les inscrits actifs
 */
function getNewsletterSubscribers() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT * FROM newsletter_subscribers WHERE is_active = 1 ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Envoie une newsletter
 * @param string $subject
 * @param string $content
 * @return array ['sent' => int, 'failed' => int]
 */
function sendNewsletter($subject, $content) {
    global $pdo, $site_config;
    
    require_once __DIR__ . '/mailer.php';
    
    $subscribers = getNewsletterSubscribers();
    $sent = 0;
    $failed = 0;
    
    foreach ($subscribers as $subscriber) {
        $emailContent = $content . "
            <hr style='margin: 30px 0;'>
            <p style='font-size: 12px; color: #999;'>
                Vous recevez cet email car vous êtes inscrit à notre newsletter.<br>
                <a href='" . url('unsubscribe.php?email=' . urlencode($subscriber['email'])) . "'>Se désinscrire</a>
            </p>
        ";
        
        if (sendEmail($subscriber['email'], $subject, $emailContent)) {
            $sent++;
        } else {
            $failed++;
        }
        
        // Petite pause pour ne pas saturer le serveur SMTP
        usleep(100000); // 0.1 seconde
    }
    
    // Logger l'envoi
    $logFile = __DIR__ . '/../logs/newsletter.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logLine = date('Y-m-d H:i:s') . " - Newsletter envoyée : {$sent} succès, {$failed} échecs - Sujet: {$subject}\n";
    file_put_contents($logFile, $logLine, FILE_APPEND);
    
    return ['sent' => $sent, 'failed' => $failed];
}
