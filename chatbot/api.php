<?php
/**
 * API Chatbot ORCA
 * Point d'entrée unique - 3 actions : init, message, form
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'init':
        handleInit();
        break;
    case 'message':
        handleMessage();
        break;
    case 'form':
        handleForm();
        break;
    default:
        respond(['error' => 'Action inconnue']);
}

// ======================================================
// HANDLERS
// ======================================================

/**
 * Initialise ou reprend une conversation
 */
function handleInit() {
    $conversation = chatbotGetOrCreateConversation();
    $scenario = chatbotGetScenario();

    if ($conversation['is_new']) {
        $step = $scenario[1];
        chatbotSaveMessage($conversation['id'], 'bot', $step['message'], $step['options'] ?? null);

        respond([
            'conversation_id' => $conversation['id'],
            'step' => 1,
            'message' => $step['message'],
            'options' => $step['options'] ?? null,
            'type' => 'buttons',
            'is_new' => true
        ]);
    }

    // Reprendre conversation existante
    $history = chatbotGetHistory($conversation['id']);
    $currentStepId = (int) $conversation['current_step'];
    $currentStep = $scenario[$currentStepId] ?? null;

    respond([
        'conversation_id' => $conversation['id'],
        'step' => $currentStepId,
        'type' => $currentStep['type'] ?? 'buttons',
        'history' => $history,
        'is_new' => false
    ]);
}

/**
 * Traite un message ou clic bouton de l'utilisateur
 */
function handleMessage() {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!$conversation_id || $message === '') {
        respond(['error' => 'Données manquantes']);
    }

    chatbotSaveMessage($conversation_id, 'user', $message);

    $conversation = chatbotGetConversation($conversation_id);
    if (!$conversation) {
        respond(['error' => 'Conversation introuvable']);
    }

    $scenario = chatbotGetScenario();
    $currentStepId = (int) $conversation['current_step'];
    $currentStep = $scenario[$currentStepId] ?? null;

    // --- Si on est sur une étape à boutons, matcher la réponse ---
    if ($currentStep && isset($currentStep['options'])) {
        $matched = matchOption($message, $currentStep['options']);
        if ($matched) {
            if (isset($currentStep['field'])) {
                chatbotUpdateData($conversation_id, $currentStep['field'], $matched['value']);
            }
            return goToStep($conversation_id, $matched['next'], $scenario);
        }
    }

    // --- Détection d'intention (message libre) ---
    $intention = chatbotDetectIntention($message);
    $msgCount = chatbotCountUserMessages($conversation_id);

    if ($intention) {
        $responseText = $intention['response'];
        chatbotSaveMessage($conversation_id, 'bot', $responseText);

        // Après 3+ messages sans coordonnées → pousser vers le formulaire
        if ($msgCount >= 3) {
            chatbotUpdateStep($conversation_id, 50);
            $formMsg = $responseText . "\n\n👇 **Pour aller plus loin, laissez-moi vos coordonnées :**";
            respond([
                'step' => 50,
                'type' => 'form',
                'message' => $formMsg
            ]);
        }

        respond([
            'step' => $currentStepId,
            'message' => $responseText,
            'options' => [
                ['label' => '💰 Obtenir un devis', 'value' => 'devis', 'next' => 10],
                ['label' => '📅 Prendre RDV', 'value' => 'rdv', 'next' => 50],
                ['label' => '❓ Autre question', 'value' => 'question', 'next' => $currentStepId]
            ]
        ]);
    }

    // --- Message non compris → pousser vers formulaire ---
    if ($msgCount >= 4) {
        chatbotUpdateStep($conversation_id, 50);
        $forceMsg = "Je ne suis pas sûr de pouvoir répondre par écrit. 😊\n\n**Laissez-moi vos coordonnées et un conseiller ORCA vous rappellera gratuitement sous 24h !**";
        chatbotSaveMessage($conversation_id, 'bot', $forceMsg);
        respond([
            'step' => 50,
            'type' => 'form',
            'message' => $forceMsg
        ]);
    }

    // Réponse par défaut
    $defaultMsg = "Je ne suis pas sûr de comprendre. 😊\n\nComment puis-je vous aider ?";
    chatbotSaveMessage($conversation_id, 'bot', $defaultMsg);
    respond([
        'step' => $currentStepId,
        'message' => $defaultMsg,
        'options' => [
            ['label' => '💰 Obtenir un devis', 'value' => 'devis', 'next' => 10],
            ['label' => '🏠 Voir les modèles', 'value' => 'modeles', 'next' => 20],
            ['label' => '📅 Prendre RDV', 'value' => 'rdv', 'next' => 50]
        ]
    ]);
}

/**
 * Soumission du formulaire de coordonnées
 */
function handleForm() {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $formData = json_decode($_POST['data'] ?? '{}', true);

    if (!$conversation_id || empty($formData)) {
        respond(['error' => 'Données manquantes']);
    }

    // Valider les champs requis
    $errors = [];
    if (empty($formData['prenom']) || !chatbotValidateInput($formData['prenom'], 'name')) {
        $errors[] = 'Prénom invalide';
    }
    if (empty($formData['nom']) || !chatbotValidateInput($formData['nom'], 'name')) {
        $errors[] = 'Nom invalide';
    }
    if (empty($formData['email']) || !chatbotValidateInput($formData['email'], 'email')) {
        $errors[] = 'Email invalide';
    }
    if (empty($formData['telephone']) || !chatbotValidateInput($formData['telephone'], 'phone')) {
        $errors[] = 'Numéro de téléphone invalide';
    }

    if (!empty($errors)) {
        respond(['error' => implode(', ', $errors)]);
    }

    // Normaliser le téléphone
    $formData['telephone'] = chatbotNormalizePhone($formData['telephone']);

    // Fusionner avec les données du questionnaire (département, surface, budget, terrain)
    $conversation = chatbotGetConversation($conversation_id);
    $existing = json_decode($conversation['data_collected'] ?? '{}', true) ?: [];
    $allData = array_merge($existing, $formData);

    // Sauvegarder toutes les données
    foreach ($formData as $key => $val) {
        chatbotUpdateData($conversation_id, $key, $val);
    }

    // Créer le lead
    $leadResult = chatbotCreateLead($conversation_id, $allData);

    if (!$leadResult['success']) {
        respond(['error' => 'Erreur lors de l\'enregistrement. Veuillez réessayer.']);
    }

    // Message de confirmation
    $prenom = htmlspecialchars($allData['prenom'] ?? '');
    $scenario = chatbotGetScenario();
    $finalStep = $scenario[55];

    // Construire le message final avec variables
    $msg = $finalStep['message'];
    $msg = str_replace('{{prenom}}', $prenom, $msg);
    $msg = str_replace('{{telephone}}', htmlspecialchars($allData['telephone'] ?? ''), $msg);
    $msg = str_replace('{{surface}}', htmlspecialchars($allData['surface'] ?? '100'), $msg);
    if (strpos($msg, '{{prix_') !== false) {
        $estimates = chatbotCalculateEstimate($allData);
        $msg = str_replace('{{prix_min}}', $estimates['prix_min'], $msg);
        $msg = str_replace('{{prix_max}}', $estimates['prix_max'], $msg);
    }

    chatbotSaveMessage($conversation_id, 'bot', $msg);
    chatbotUpdateStep($conversation_id, 55);

    respond([
        'step' => 55,
        'type' => 'final',
        'message' => $msg,
        'lead_id' => $leadResult['lead_id'],
        'options' => $finalStep['options'] ?? null
    ]);
}

// ======================================================
// UTILITAIRES
// ======================================================

/**
 * Naviguer vers une étape du scénario
 */
function goToStep($conversation_id, $stepId, $scenario) {
    $step = $scenario[$stepId] ?? null;

    if (!$step) {
        $step = $scenario[50];
        $stepId = 50;
    }

    chatbotUpdateStep($conversation_id, $stepId);
    chatbotSaveMessage($conversation_id, 'bot', $step['message'], $step['options'] ?? null);

    respond([
        'step' => $stepId,
        'type' => $step['type'] ?? 'step',
        'message' => $step['message'],
        'options' => $step['options'] ?? null,
        'field' => $step['field'] ?? null
    ]);
}

/**
 * Matcher un message utilisateur avec les options
 */
function matchOption($message, $options) {
    $msg = mb_strtolower(trim($message));

    foreach ($options as $opt) {
        if (mb_strtolower($opt['value']) === $msg) return $opt;
    }

    foreach ($options as $opt) {
        $label = preg_replace('/[\x{1F000}-\x{1FFFF}]|[\x{2600}-\x{27BF}]/u', '', $opt['label']);
        $label = mb_strtolower(trim($label));
        if ($label === $msg) return $opt;
        if (mb_strpos($label, $msg) !== false) return $opt;
        if (mb_strpos($msg, $label) !== false) return $opt;
        similar_text($msg, $label, $percent);
        if ($percent > 70) return $opt;
    }

    return null;
}

/**
 * Réponse JSON
 */
function respond($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
