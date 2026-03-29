<?php
/**
 * API Chatbot ORCA - Version intelligente
 * Recherche en BDD modèles + terrains + intentions
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'init':    handleInit(); break;
    case 'message': handleMessage(); break;
    case 'form':    handleForm(); break;
    default:        respond(['error' => 'Action inconnue']);
}

// ======================================================
// INIT
// ======================================================

function handleInit() {
    $conversation = chatbotGetOrCreateConversation();
    $scenario = chatbotGetScenario();

    if ($conversation['is_new']) {
        $step = $scenario[1];
        chatbotSaveMessage($conversation['id'], 'bot', $step['message'], $step['options'] ?? null);
        respond([
            'conversation_id' => $conversation['id'],
            'step' => 1,
            'type' => 'buttons',
            'message' => $step['message'],
            'options' => $step['options'] ?? null,
            'is_new' => true
        ]);
    }

    $history = chatbotGetHistory($conversation['id']);
    $stepId = (int) $conversation['current_step'];
    $stepDef = $scenario[$stepId] ?? null;
    respond([
        'conversation_id' => $conversation['id'],
        'step' => $stepId,
        'type' => $stepDef['type'] ?? 'text',
        'history' => $history,
        'is_new' => false
    ]);
}

// ======================================================
// MESSAGE (texte libre ou clic bouton)
// ======================================================

function handleMessage() {
    $cid = intval($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if (!$cid || $message === '') {
        respond(['error' => 'Données manquantes']);
    }

    chatbotSaveMessage($cid, 'user', $message);

    $conv = chatbotGetConversation($cid);
    if (!$conv) respond(['error' => 'Conversation introuvable']);

    $scenario = chatbotGetScenario();
    $stepId = (int) $conv['current_step'];
    $step = $scenario[$stepId] ?? null;
    $data = json_decode($conv['data_collected'] ?? '{}', true) ?: [];

    // --- 1. Si étape à boutons → matcher ---
    if ($step && isset($step['options'])) {
        $matched = matchOption($message, $step['options']);
        if ($matched) {
            if (isset($step['field'])) {
                chatbotUpdateData($cid, $step['field'], $matched['value']);
            }

            $next = $matched['next'];

            // Étapes spéciales : recherche en BDD
            if ($next === 'search_modeles') {
                return handleSearchModeles($cid);
            }
            if ($next === 'search_terrains') {
                return handleSearchTerrains($cid);
            }

            return goToStep($cid, $next, $scenario);
        }
    }

    // --- 2. Mode question libre (étape 40) ou texte quelconque ---
    // Essayer de détecter une intention
    $intention = chatbotDetectIntention($message);
    $msgCount = chatbotCountUserMessages($cid);

    if ($intention) {
        // Action spéciale : recherche auto
        if ($intention['action'] === 'search_modeles') {
            $criteria = chatbotExtractCriteria($message);
            if (!empty($criteria)) {
                foreach ($criteria as $k => $v) chatbotUpdateData($cid, $k, $v);
            }
            return handleSearchModeles($cid);
        }
        if ($intention['action'] === 'search_terrains') {
            $criteria = chatbotExtractCriteria($message);
            if (!empty($criteria)) {
                foreach ($criteria as $k => $v) chatbotUpdateData($cid, $k, $v);
            }
            return handleSearchTerrains($cid);
        }

        // Réponse textuelle (depuis BDD ou fallback)
        if ($intention['response']) {
            chatbotSaveMessage($cid, 'bot', $intention['response']);

            // Après 3+ échanges → pousser vers le formulaire
            if ($msgCount >= 3) {
                chatbotUpdateStep($cid, 50);
                respond([
                    'step' => 50,
                    'type' => 'form',
                    'message' => $intention['response'] . "\n\n👇 **Pour aller plus loin, laissez-moi vos coordonnées :**"
                ]);
            }

            respond([
                'step' => $stepId,
                'message' => $intention['response'],
                'options' => [
                    ['label' => '🏠 Chercher une maison', 'value' => 'maison', 'next' => 10],
                    ['label' => '🌿 Chercher un terrain', 'value' => 'terrain', 'next' => 20],
                    ['label' => '📋 Laisser mes coordonnées', 'value' => 'coord', 'next' => 50]
                ]
            ]);
        }
    }

    // --- 3. Essayer d'extraire des critères du texte libre ---
    $criteria = chatbotExtractCriteria($message);
    if (!empty($criteria)) {
        foreach ($criteria as $k => $v) chatbotUpdateData($cid, $k, $v);

        // Si on a des critères maison
        if (isset($criteria['nb_chambres']) || isset($criteria['budget']) || isset($criteria['type_maison'])) {
            return handleSearchModeles($cid);
        }
        // Si on a un département → proposer terrains
        if (isset($criteria['departement'])) {
            return handleSearchTerrains($cid);
        }
    }

    // --- 4. Après 4+ messages non compris → formulaire ---
    if ($msgCount >= 4) {
        chatbotUpdateStep($cid, 50);
        $msg = "Je vais être honnête : **un conseiller ORCA pourra mieux vous aider que moi !** 😊\n\nLaissez vos coordonnées, il vous rappelle sous 24h :";
        chatbotSaveMessage($cid, 'bot', $msg);
        respond(['step' => 50, 'type' => 'form', 'message' => $msg]);
    }

    // --- 5. Réponse par défaut ---
    $defaultMsg = "Je n'ai pas bien compris, mais je peux vous aider ! 😊\n\nQue cherchez-vous ?";
    chatbotSaveMessage($cid, 'bot', $defaultMsg);
    respond([
        'step' => $stepId,
        'message' => $defaultMsg,
        'options' => [
            ['label' => '🏠 Une maison', 'value' => 'maison', 'next' => 10],
            ['label' => '🌿 Un terrain', 'value' => 'terrain', 'next' => 20],
            ['label' => '💰 Un devis', 'value' => 'devis', 'next' => 30],
            ['label' => '❓ Poser une question', 'value' => 'question', 'next' => 40]
        ]
    ]);
}

// ======================================================
// RECHERCHE MODÈLES
// ======================================================

function handleSearchModeles($cid) {
    $conv = chatbotGetConversation($cid);
    $data = json_decode($conv['data_collected'] ?? '{}', true) ?: [];

    $results = chatbotSearchModeles($data);
    $text = chatbotFormatModeles($results);

    $text .= "\n**Envie d'en savoir plus ? Laissez-moi vos coordonnées !**";

    chatbotSaveMessage($cid, 'bot', $text);
    chatbotUpdateStep($cid, 50);

    respond([
        'step' => 50,
        'type' => 'results_then_form',
        'message' => $text,
        'results_count' => count($results)
    ]);
}

// ======================================================
// RECHERCHE TERRAINS
// ======================================================

function handleSearchTerrains($cid) {
    $conv = chatbotGetConversation($cid);
    $data = json_decode($conv['data_collected'] ?? '{}', true) ?: [];

    $results = chatbotSearchTerrains($data);
    $text = chatbotFormatTerrains($results);

    $text .= "\n**Intéressé ? Laissez vos coordonnées pour recevoir les fiches détaillées !**";

    chatbotSaveMessage($cid, 'bot', $text);
    chatbotUpdateStep($cid, 50);

    respond([
        'step' => 50,
        'type' => 'results_then_form',
        'message' => $text,
        'results_count' => count($results)
    ]);
}

// ======================================================
// FORMULAIRE COORDONNÉES
// ======================================================

function handleForm() {
    $cid = intval($_POST['conversation_id'] ?? 0);
    $formData = json_decode($_POST['data'] ?? '{}', true);

    if (!$cid || empty($formData)) {
        respond(['error' => 'Données manquantes']);
    }

    $errors = [];
    if (empty($formData['prenom']) || !chatbotValidateInput($formData['prenom'], 'name'))
        $errors[] = 'Prénom invalide';
    if (empty($formData['nom']) || !chatbotValidateInput($formData['nom'], 'name'))
        $errors[] = 'Nom invalide';
    if (empty($formData['email']) || !chatbotValidateInput($formData['email'], 'email'))
        $errors[] = 'Email invalide';
    if (empty($formData['telephone']) || !chatbotValidateInput($formData['telephone'], 'phone'))
        $errors[] = 'Téléphone invalide (ex: 06 12 34 56 78)';

    if (!empty($errors)) {
        respond(['error' => implode(', ', $errors)]);
    }

    $formData['telephone'] = chatbotNormalizePhone($formData['telephone']);

    // Fusionner avec données du questionnaire
    $conv = chatbotGetConversation($cid);
    $existing = json_decode($conv['data_collected'] ?? '{}', true) ?: [];
    $allData = array_merge($existing, $formData);

    foreach ($formData as $key => $val) {
        chatbotUpdateData($cid, $key, $val);
    }

    $leadResult = chatbotCreateLead($cid, $allData);

    if (!$leadResult['success']) {
        respond(['error' => 'Erreur lors de l\'enregistrement. Réessayez.']);
    }

    $prenom = htmlspecialchars($allData['prenom'] ?? '');
    $scenario = chatbotGetScenario();
    $finalStep = $scenario[55];

    $msg = str_replace('{{prenom}}', $prenom, $finalStep['message']);
    chatbotSaveMessage($cid, 'bot', $msg);
    chatbotUpdateStep($cid, 55);

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

function goToStep($cid, $stepId, $scenario) {
    $step = $scenario[$stepId] ?? null;
    if (!$step) { $step = $scenario[50]; $stepId = 50; }

    chatbotUpdateStep($cid, $stepId);
    chatbotSaveMessage($cid, 'bot', $step['message'], $step['options'] ?? null);

    respond([
        'step' => $stepId,
        'type' => $step['type'] ?? 'step',
        'message' => $step['message'],
        'options' => $step['options'] ?? null,
        'field' => $step['field'] ?? null
    ]);
}

function matchOption($message, $options) {
    $msg = mb_strtolower(trim($message));
    foreach ($options as $opt) {
        if (mb_strtolower($opt['value']) === $msg) return $opt;
    }
    foreach ($options as $opt) {
        $label = preg_replace('/[\x{1F000}-\x{1FFFF}]|[\x{2600}-\x{27BF}]/u', '', $opt['label']);
        $label = mb_strtolower(trim($label));
        if ($label === $msg || mb_strpos($label, $msg) !== false || mb_strpos($msg, $label) !== false) return $opt;
        similar_text($msg, $label, $pct);
        if ($pct > 70) return $opt;
    }
    return null;
}

function respond($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
