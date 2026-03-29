<?php
/**
 * API Chatbot ORCA
 * Point d'entrée unique pour toutes les interactions chatbot
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Lire l'action depuis POST ou GET
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
            'is_new' => true
        ]);
    }

    // Reprendre conversation existante
    $history = chatbotGetHistory($conversation['id']);
    respond([
        'conversation_id' => $conversation['id'],
        'step' => (int) $conversation['current_step'],
        'history' => $history,
        'is_new' => false
    ]);
}

/**
 * Traite un message ou clic bouton
 */
function handleMessage() {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!$conversation_id || $message === '') {
        respond(['error' => 'Données manquantes']);
    }

    // Sauvegarder le message utilisateur
    chatbotSaveMessage($conversation_id, 'user', $message);

    $conversation = chatbotGetConversation($conversation_id);
    if (!$conversation) {
        respond(['error' => 'Conversation introuvable']);
    }

    $scenario = chatbotGetScenario();
    $currentStepId = (int) $conversation['current_step'];
    $currentStep = $scenario[$currentStepId] ?? null;
    $data = json_decode($conversation['data_collected'] ?? '{}', true) ?: [];

    // --- Si on est sur une étape de collecte de coordonnées (50-53) ---
    if ($currentStepId >= 50 && $currentStepId <= 53 && $currentStep) {
        return handleCoordInput($conversation_id, $message, $currentStep, $currentStepId, $scenario, $data);
    }

    // --- Si on est sur une étape à boutons, essayer de matcher ---
    if ($currentStep && isset($currentStep['options'])) {
        $matched = matchOption($message, $currentStep['options']);
        if ($matched) {
            if (isset($currentStep['field'])) {
                chatbotUpdateData($conversation_id, $currentStep['field'], $matched['value']);
            }
            return goToStep($conversation_id, $matched['next'], $scenario);
        }
    }

    // --- Détection d'intention pour messages libres ---
    $intention = chatbotDetectIntention($message);
    $msgCount = chatbotCountUserMessages($conversation_id);

    if ($intention) {
        $responseText = $intention['response'];
        chatbotSaveMessage($conversation_id, 'bot', $responseText);

        // Après 3+ messages, pousser vers coordonnées
        if ($msgCount >= 3) {
            chatbotUpdateStep($conversation_id, 50);
            respond([
                'step' => 50,
                'message' => $responseText . "\n\n👇 **Pour aller plus loin, laissez-moi vos coordonnées :**",
                'options' => [
                    ['label' => '✅ Donner mes coordonnées', 'value' => 'coord', 'next' => 50],
                    ['label' => '❓ J\'ai une autre question', 'value' => 'question', 'next' => $currentStepId]
                ]
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

    // --- Message non compris → pousser vers coordonnées ---
    if ($msgCount >= 4) {
        chatbotUpdateStep($conversation_id, 50);
        $forceMsg = "Je ne suis pas sûr de pouvoir répondre par écrit. 😊\n\n**Laissez-moi vos coordonnées et un conseiller ORCA vous rappellera gratuitement sous 24h !**";
        chatbotSaveMessage($conversation_id, 'bot', $forceMsg);
        respond([
            'step' => 50,
            'message' => $forceMsg,
            'options' => [
                ['label' => '✅ OK, je laisse mes coordonnées', 'value' => 'coord', 'next' => 50],
            ]
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
 * Traite la saisie pendant la collecte de coordonnées (étapes 50-53)
 */
function handleCoordInput($conversation_id, $message, $step, $stepId, $scenario, $data) {
    $field = $step['field'];
    $value = trim($message);

    // Valider
    $validationType = $step['validation'] ?? null;
    if ($validationType && !chatbotValidateInput($value, $validationType)) {
        $errorMsg = $step['error'] ?? 'Cette information ne semble pas valide.';
        chatbotSaveMessage($conversation_id, 'bot', $errorMsg);
        respond([
            'step' => $stepId,
            'message' => $errorMsg,
            'retry' => true,
            'field' => $field
        ]);
    }

    // Normaliser téléphone
    if ($field === 'telephone') {
        $value = chatbotNormalizePhone($value);
    }

    // Sauvegarder
    chatbotUpdateData($conversation_id, $field, $value);

    $nextStepId = $step['next'] ?? 55;

    // Si l'étape suivante est 55 (finale), créer le lead
    if ($nextStepId == 55) {
        $conversation = chatbotGetConversation($conversation_id);
        $allData = json_decode($conversation['data_collected'] ?? '{}', true) ?: [];
        $leadResult = chatbotCreateLead($conversation_id, $allData);

        // Préparer le message final avec les variables
        $finalStep = $scenario[55];
        $finalMsg = $finalStep['message'];
        $finalMsg = str_replace('{{prenom}}', htmlspecialchars($allData['prenom'] ?? ''), $finalMsg);

        if (strpos($finalMsg, '{{prix_') !== false) {
            $estimates = chatbotCalculateEstimate($allData);
            $finalMsg = str_replace('{{prix_min}}', $estimates['prix_min'], $finalMsg);
            $finalMsg = str_replace('{{prix_max}}', $estimates['prix_max'], $finalMsg);
        }
        $finalMsg = str_replace('{{telephone}}', htmlspecialchars($allData['telephone'] ?? ''), $finalMsg);
        $finalMsg = str_replace('{{surface}}', htmlspecialchars($allData['surface'] ?? '100'), $finalMsg);

        chatbotSaveMessage($conversation_id, 'bot', $finalMsg);
        chatbotUpdateStep($conversation_id, 55);

        respond([
            'step' => 55,
            'message' => $finalMsg,
            'lead_id' => $leadResult['lead_id'],
            'options' => $finalStep['options'] ?? null,
            'type' => 'final'
        ]);
    }

    return goToStep($conversation_id, $nextStepId, $scenario);
}

/**
 * Soumission du formulaire HTML (fallback)
 */
function handleForm() {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $formData = json_decode($_POST['data'] ?? '{}', true);

    if (!$conversation_id || empty($formData)) {
        respond(['error' => 'Données manquantes']);
    }

    // Valider les champs requis
    if (empty($formData['prenom']) || empty($formData['nom']) ||
        empty($formData['email']) || empty($formData['telephone'])) {
        respond(['error' => 'Tous les champs sont obligatoires']);
    }

    if (!chatbotValidateInput($formData['email'], 'email')) {
        respond(['error' => 'Email invalide']);
    }

    if (!chatbotValidateInput($formData['telephone'], 'phone')) {
        respond(['error' => 'Numéro de téléphone invalide']);
    }

    $formData['telephone'] = chatbotNormalizePhone($formData['telephone']);

    // Fusionner avec données existantes
    $conversation = chatbotGetConversation($conversation_id);
    $existing = json_decode($conversation['data_collected'] ?? '{}', true) ?: [];
    $allData = array_merge($existing, $formData);

    // Sauvegarder les champs individuellement
    foreach ($formData as $key => $val) {
        chatbotUpdateData($conversation_id, $key, $val);
    }

    // Créer le lead
    $leadResult = chatbotCreateLead($conversation_id, $allData);

    $prenom = htmlspecialchars($allData['prenom'] ?? '');
    $msg = "🎉 **Merci {$prenom} !**\n\nVotre demande a bien été enregistrée.\n\n📞 **Un conseiller ORCA vous contactera sous 24h ouvrées.**";

    chatbotSaveMessage($conversation_id, 'bot', $msg);
    chatbotUpdateStep($conversation_id, 55);

    respond([
        'step' => 55,
        'message' => $msg,
        'lead_id' => $leadResult['lead_id'],
        'type' => 'final',
        'options' => [
            ['label' => '🏠 Voir les modèles', 'value' => 'modeles', 'action' => 'link', 'url' => '/modeles.php'],
            ['label' => '❌ Fermer', 'value' => 'close', 'action' => 'close']
        ]
    ]);
}

// ======================================================
// UTILITAIRES
// ======================================================

/**
 * Aller à une étape du scénario
 */
function goToStep($conversation_id, $stepId, $scenario) {
    $step = $scenario[$stepId] ?? null;

    if (!$step) {
        // Fallback : aller à la collecte de coordonnées
        $step = $scenario[50];
        $stepId = 50;
    }

    chatbotUpdateStep($conversation_id, $stepId);
    chatbotSaveMessage($conversation_id, 'bot', $step['message'], $step['options'] ?? null);

    respond([
        'step' => $stepId,
        'message' => $step['message'],
        'options' => $step['options'] ?? null,
        'field' => $step['field'] ?? null,
        'type' => $step['type'] ?? 'step'
    ]);
}

/**
 * Matcher un message avec les options disponibles
 */
function matchOption($message, $options) {
    $msg = mb_strtolower(trim($message));

    // 1. Match exact sur la valeur
    foreach ($options as $opt) {
        if (mb_strtolower($opt['value']) === $msg) {
            return $opt;
        }
    }

    // 2. Match sur le label (sans emojis)
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
 * Envoyer une réponse JSON et terminer
 */
function respond($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
