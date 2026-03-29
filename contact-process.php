<?php
/**
 * Traitement du formulaire de contact
 */
require_once 'includes/config.php';
require_once 'includes/rate-limit.php';
require_once 'includes/mailer.php';

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contact.php');
}

// Rate limiting - Max 3 soumissions par 15 minutes
if (!checkRateLimit('contact', 3, 900)) {
    $remaining = ceil(getRateLimitRemaining('contact', 900) / 60);
    setFlashMessage('error', 'Trop de tentatives. Veuillez réessayer dans ' . $remaining . ' minute(s).');
    redirect('contact.php');
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlashMessage('error', 'Erreur de sécurité. Veuillez réessayer.');
    redirect('contact.php');
}

// Récupérer et nettoyer les données
$data = [
    'type_demande' => $_POST['type_demande'] ?? 'devis',
    'civilite' => $_POST['civilite'] ?? 'M',
    'nom' => clean($_POST['nom'] ?? ''),
    'prenom' => clean($_POST['prenom'] ?? ''),
    'email' => clean($_POST['email'] ?? ''),
    'telephone' => clean($_POST['telephone'] ?? ''),
    'code_postal' => clean($_POST['code_postal'] ?? ''),
    'ville' => clean($_POST['ville'] ?? ''),
    'modele_interesse' => !empty($_POST['modele_interesse']) ? (int)$_POST['modele_interesse'] : null,
    'terrain_prevu' => isset($_POST['terrain_prevu']) ? (int)$_POST['terrain_prevu'] : 0,
    'delai_souhaite' => $_POST['delai_souhaite'] ?? '6-mois',
    'commentaire' => clean($_POST['commentaire'] ?? ''),
    'consent' => isset($_POST['consent']) ? 1 : 0
];

// Validation
$errors = [];

if (empty($data['nom'])) {
    $errors[] = 'Le nom est obligatoire';
}

if (empty($data['email']) || !isValidEmail($data['email'])) {
    $errors[] = 'Un email valide est obligatoire';
}

if (empty($data['telephone']) || !isValidPhone($data['telephone'])) {
    $errors[] = 'Un numéro de téléphone valide est obligatoire';
}

if (!$data['consent']) {
    $errors[] = 'Vous devez accepter la politique de confidentialité';
}

// S'il y a des erreurs
if (!empty($errors)) {
    setFlashMessage('error', implode('<br>', $errors));
    redirect('contact.php');
}

// Enregistrer le lead dans la base de données
try {
    saveLead($data);
    $leadId = $pdo->lastInsertId();
    $data['id'] = $leadId;
    
    // Envoyer l'email à l'admin
    sendLeadNotification($data);
    
    // Envoyer la confirmation au client
    sendClientConfirmation($data);
    
    // Message de succès
    $messages = [
        'devis' => 'Votre demande de devis a été envoyée. Nous vous recontactons sous 24h.',
        'rappel' => 'Votre demande de rappel a été enregistrée. Nous vous appelons dès que possible.',
        'info' => 'Votre demande a été envoyée. Nous vous répondons sous 24h.',
        'brochure' => 'Votre demande de brochure a été enregistrée. Vous la recevrez par email.'
    ];
    
    setFlashMessage('success', $messages[$data['type_demande']] ?? 'Votre message a été envoyé avec succès.');
    
    // Redirection vers page de remerciement ou contact
    redirect('contact.php');
    
} catch (Exception $e) {
    setFlashMessage('error', 'Une erreur est survenue. Veuillez réessayer plus tard.');
    redirect('contact.php');
}
