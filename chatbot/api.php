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

    // --- 0. Si le message est une valeur de bouton contextuel → naviguer ---
    $navMap = [
        'autre' => 40, 'coord' => 50, 'maison' => 10,
        'terrain' => 20, 'terrain_dispo' => 20, 'devis' => 30
    ];
    $msgLower = mb_strtolower(trim($message));
    if (isset($navMap[$msgLower])) {
        return goToStep($cid, $navMap[$msgLower], $scenario);
    }

    // --- 1. Si étape à boutons → matcher le clic ---
    if ($step && isset($step['options'])) {
        $matched = matchOption($message, $step['options']);
        if ($matched) {
            if (isset($step['field'])) {
                chatbotUpdateData($cid, $step['field'], $matched['value']);
            }
            $next = $matched['next'];
            if ($next === 'search_modeles') return handleSearchModeles($cid);
            if ($next === 'search_terrains') return handleSearchTerrains($cid);
            return goToStep($cid, $next, $scenario);
        }
    }

    // --- 2. Texte libre : répondre de façon conversationnelle ---
    $msgCount = chatbotCountUserMessages($cid);

    // Extraire des critères numériques du texte (chambres, budget, surface, département)
    $criteria = chatbotExtractCriteria($message);
    if (!empty($criteria)) {
        foreach ($criteria as $k => $v) chatbotUpdateData($cid, $k, $v);
    }

    // Détecter l'intention (BDD puis fallback)
    $intention = chatbotDetectIntention($message);

    if ($intention) {
        $responseText = $intention['response'] ?? '';
        $action = $intention['action'] ?? '';

        // Si on a des critères précis + intention maison/terrain → recherche directe
        if (!empty($criteria) && $action === 'search_modeles') {
            return handleSearchModeles($cid);
        }
        if (!empty($criteria) && $action === 'search_terrains') {
            return handleSearchTerrains($cid);
        }

        // Si on a une réponse textuelle → la donner d'abord, conversationnellement
        if ($responseText) {
            chatbotSaveMessage($cid, 'bot', $responseText);

            // Après 4+ échanges → ajouter le formulaire sous la réponse
            if ($msgCount >= 4) {
                chatbotUpdateStep($cid, 50);
                respond([
                    'step' => 50,
                    'type' => 'form',
                    'message' => $responseText . "\n\n👇 **Pour aller plus loin, laissez vos coordonnées :**"
                ]);
            }

            // Sinon, répondre avec des options pour continuer la conversation
            // Les options dépendent du contexte de la question
            $nextOptions = buildContextualOptions($intention['key'], $stepId);
            respond([
                'step' => $stepId,
                'message' => $responseText,
                'options' => $nextOptions
            ]);
        }

        // Pas de réponse texte mais une action de redirection
        if ($action === 'search_modeles') return handleSearchModeles($cid);
        if ($action === 'search_terrains') return handleSearchTerrains($cid);
        if ($action === 'scenario_devis') return goToStep($cid, 30, $scenario);
        if ($action === 'scenario_terrain') return goToStep($cid, 20, $scenario);
        if ($action === 'afficher_modeles') return goToStep($cid, 10, $scenario);
        if ($action === 'transfert_humain' || $action === 'redirect:/contact.php') {
            chatbotUpdateStep($cid, 50);
            respond(['step' => 50, 'type' => 'form', 'message' => "Un conseiller va prendre le relais !\n\n👇 **Laissez vos coordonnées :**"]);
        }
    }

    // --- 3. Pas d'intention détectée mais des critères extraits → recherche ---
    if (!empty($criteria)) {
        if (isset($criteria['nb_chambres']) || isset($criteria['budget']) || isset($criteria['type_maison'])) {
            return handleSearchModeles($cid);
        }
        if (isset($criteria['departement'])) {
            return handleSearchTerrains($cid);
        }
    }

    // --- 4. Après 5+ messages sans réponse → formulaire ---
    if ($msgCount >= 5) {
        chatbotUpdateStep($cid, 50);
        $msg = "Je vais être honnête : **un conseiller ORCA pourra bien mieux vous aider** ! 😊\n\nLaissez vos coordonnées, il vous rappelle sous 24h :";
        chatbotSaveMessage($cid, 'bot', $msg);
        respond(['step' => 50, 'type' => 'form', 'message' => $msg]);
    }

    // --- 5. Réponse par défaut : encourager la conversation ---
    $defaultMsg = "Bonne question ! 😊 Je ne suis pas sûr d'avoir la réponse exacte, mais je peux vous orienter.\n\nQue souhaitez-vous savoir ?";
    chatbotSaveMessage($cid, 'bot', $defaultMsg);
    respond([
        'step' => $stepId,
        'message' => $defaultMsg,
        'options' => [
            ['label' => '💰 Les prix des maisons', 'value' => 'prix', 'next' => 40],
            ['label' => '🌿 Les terrains disponibles', 'value' => 'terrain_dispo', 'next' => 20],
            ['label' => '⏱️ Les délais de construction', 'value' => 'delai', 'next' => 40],
            ['label' => '💡 Les aides au financement', 'value' => 'financement', 'next' => 40],
            ['label' => '📋 Être rappelé par un conseiller', 'value' => 'coord', 'next' => 50]
        ]
    ]);
}

/**
 * Construire des options contextuelles selon l'intention détectée
 * Le but : proposer des suites logiques à la conversation, pas rediriger brutalement
 */
function buildContextualOptions($intentionKey, $currentStep) {
    $base = [
        'prix' => [
            ['label' => '🏠 Voir les modèles dans mon budget', 'value' => 'maison', 'next' => 10],
            ['label' => '💰 Obtenir un devis précis', 'value' => 'devis', 'next' => 30],
            ['label' => '❓ J\'ai une autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
        'delai' => [
            ['label' => '📋 Planifier mon projet', 'value' => 'devis', 'next' => 30],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📞 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
        'financement' => [
            ['label' => '💰 Simuler mon budget', 'value' => 'devis', 'next' => 30],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📞 Parler à un conseiller', 'value' => 'coord', 'next' => 50]
        ],
        'rdv' => [
            ['label' => '📋 Laisser mes coordonnées', 'value' => 'coord', 'next' => 50],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40]
        ],
        'garantie' => [
            ['label' => '🏠 Découvrir nos modèles', 'value' => 'maison', 'next' => 10],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
        'search_maison' => [
            ['label' => '🏠 Voir les modèles', 'value' => 'maison', 'next' => 10],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
        'search_terrain' => [
            ['label' => '🌿 Voir les terrains', 'value' => 'terrain', 'next' => 20],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
    ];

    return $base[$intentionKey] ?? [
        ['label' => '🏠 Chercher une maison', 'value' => 'maison', 'next' => 10],
        ['label' => '🌿 Chercher un terrain', 'value' => 'terrain', 'next' => 20],
        ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
        ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
    ];
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
