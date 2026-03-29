<?php
/**
 * Système d'envoi d'emails
 * 
 * Option 1 : Utiliser mail() natif PHP (simple)
 * Option 2 : Utiliser PHPMailer (recommandé pour production)
 * 
 * Pour installer PHPMailer :
 * cd V2 && composer require phpmailer/phpmailer
 * OU télécharger manuellement dans includes/PHPMailer/
 */

// Configuration SMTP (à modifier dans l'admin)
$smtp_config = [
    'host' => $site_config['smtp_host'] ?? 'smtp.gmail.com',
    'port' => $site_config['smtp_port'] ?? 587,
    'username' => $site_config['smtp_username'] ?? '',
    'password' => $site_config['smtp_password'] ?? '',
    'from_email' => $site_config['site_email'] ?? 'contact@maisons-orca.fr',
    'from_name' => $site_config['site_name'] ?? 'Maisons ORCA',
    'encryption' => 'tls'
];

/**
 * Envoie un email (version simple avec mail())
 * @param string $to Destinataire
 * @param string $subject Sujet
 * @param string $message Corps HTML
 * @param array $attachments Pièces jointes (optionnel)
 * @return bool
 */
function sendEmail($to, $subject, $message, $attachments = []) {
    global $site_config;
    
    $from_email = $site_config['site_email'] ?? 'contact@maisons-orca.fr';
    $from_name = $site_config['site_name'] ?? 'Maisons ORCA';
    
    // Headers
    $headers = "From: {$from_name} <{$from_email}>\r\n";
    $headers .= "Reply-To: {$from_email}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Template HTML
    $htmlMessage = getEmailTemplate($subject, $message);
    
    // Envoi
    $result = mail($to, $subject, $htmlMessage, $headers);
    
    // Logger
    logEmail($to, $subject, $result ? 'success' : 'error');
    
    return $result;
}

/**
 * Envoie un email avec PHPMailer (si disponible)
 */
function sendEmailSMTP($to, $subject, $message, $attachments = []) {
    global $smtp_config, $site_config;
    
    // Vérifier si PHPMailer est disponible
    $phpmailer_path = __DIR__ . '/PHPMailer/PHPMailer.php';
    
    if (!file_exists($phpmailer_path)) {
        // Fallback sur mail() simple
        return sendEmail($to, $subject, $message, $attachments);
    }
    
    // Charger PHPMailer
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
    
    $mail = new PHPMailer\PHPMailer(true);
    
    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = $smtp_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_config['username'];
        $mail->Password = $smtp_config['password'];
        $mail->SMTPSecure = $smtp_config['encryption'];
        $mail->Port = $smtp_config['port'];
        
        // Expéditeur
        $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
        $mail->addReplyTo($smtp_config['from_email'], $smtp_config['from_name']);
        
        // Destinataire
        $mail->addAddress($to);
        
        // Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = getEmailTemplate($subject, $message);
        $mail->AltBody = strip_tags($message);
        
        // Pièces jointes
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
        }
        
        $result = $mail->send();
        logEmail($to, $subject, 'success');
        return true;
        
    } catch (Exception $e) {
        logEmail($to, $subject, 'error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Template HTML des emails
 */
function getEmailTemplate($title, $content) {
    global $site_config;
    
    $site_name = $site_config['site_name'] ?? 'Maisons ORCA';
    $site_url = SITE_URL;
    $primary_color = $site_config['color_primary'] ?? '#C41E3A';
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: {$primary_color}; padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">{$site_name}</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #333333; margin-top: 0;">{$title}</h2>
                            <div style="color: #666666; line-height: 1.6; font-size: 16px;">
                                {$content}
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 14px; margin: 0;">
                                © " . date('Y') . " {$site_name}<br>
                                <a href="{$site_url}" style="color: {$primary_color};">{$site_url}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Logger les emails envoyés
 */
function logEmail($to, $subject, $status) {
    $logDir = __DIR__ . '/../logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logLine = date('Y-m-d H:i:s') . " | {$status} | To: {$to} | Subject: {$subject}" . PHP_EOL;
    file_put_contents($logDir . 'emails.log', $logLine, FILE_APPEND);
}

/**
 * Envoi email de notification pour un nouveau lead
 */
function sendLeadNotification($leadData) {
    global $site_config;
    
    $adminEmail = $site_config['site_email'] ?? 'contact@maisons-orca.fr';
    $subject = 'Nouveau lead ORCA - ' . ucfirst($leadData['type_demande']);
    
    $content = "
        <p><strong>Nouvelle demande reçue :</strong></p>
        <ul>
            <li><strong>Type :</strong> " . ucfirst($leadData['type_demande']) . "</li>
            <li><strong>Nom :</strong> {$leadData['civilite']} {$leadData['prenom']} {$leadData['nom']}</li>
            <li><strong>Email :</strong> {$leadData['email']}</li>
            <li><strong>Téléphone :</strong> {$leadData['telephone']}</li>
            <li><strong>Ville :</strong> {$leadData['ville']}</li>
            <li><strong>Date :</strong> " . date('d/m/Y H:i') . "</li>
        </ul>
        <p><a href='" . url('admin/lead-view.php?id=' . $leadData['id']) . "' style='background: #C41E3A; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voir le lead dans l'admin</a></p>
    ";
    
    return sendEmail($adminEmail, $subject, $content);
}

/**
 * Envoi email de confirmation au client
 */
function sendClientConfirmation($leadData) {
    $subject = 'Votre demande a bien été reçue - Maisons ORCA';
    
    $content = "
        <p>Bonjour {$leadData['prenom']} {$leadData['nom']},</p>
        <p>Nous avons bien reçu votre demande de " . ucfirst($leadData['type_demande']) . ".</p>
        <p>Notre équipe vous recontactera dans les plus brefs délais (sous 24h ouvrées).</p>
        <p>Voici un récapitulatif de votre demande :</p>
        <ul>
            <li><strong>Type :</strong> " . ucfirst($leadData['type_demande']) . "</li>
            <li><strong>Email :</strong> {$leadData['email']}</li>
            <li><strong>Téléphone :</strong> {$leadData['telephone']}</li>
        </ul>
        <p>À très bientôt,<br>L'équipe Maisons ORCA</p>
    ";
    
    return sendEmail($leadData['email'], $subject, $content);
}
