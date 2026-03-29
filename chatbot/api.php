<?php
/**
 * API Chatbot ORCA - Scénarios complets + recherche BDD
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');

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
    $conv = chatbotGetOrCreateConversation();
    $scenario = chatbotGetScenario();

    if ($conv['is_new']) {
        $step = $scenario[1];
        chatbotSaveMessage($conv['id'], 'bot', $step['message'], $step['options']);
        respond([
            'conversation_id' => $conv['id'], 'step' => 1, 'type' => 'buttons',
            'message' => $step['message'], 'options' => $step['options'], 'is_new' => true
        ]);
    }

    $history = chatbotGetHistory($conv['id']);
    $stepId = (int) $conv['current_step'];
    $stepDef = $scenario[$stepId] ?? null;
    respond([
        'conversation_id' => $conv['id'], 'step' => $stepId,
        'type' => $stepDef['type'] ?? 'text', 'history' => $history, 'is_new' => false
    ]);
}

// ======================================================
// MESSAGE
// ======================================================
function handleMessage() {
    $cid = intval($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    if (!$cid || $message === '') respond(['error' => 'Données manquantes']);

    chatbotSaveMessage($cid, 'user', $message);

    $conv = chatbotGetConversation($cid);
    if (!$conv) respond(['error' => 'Conversation introuvable']);

    $scenario = chatbotGetScenario();
    $stepId = (int) $conv['current_step'];
    $step = $scenario[$stepId] ?? null;
    $data = json_decode($conv['data_collected'] ?? '{}', true) ?: [];

    // --- 1. Navigation directe (valeurs de boutons) ---
    $nav = ['go_maison'=>10, 'go_terrain'=>20, 'go_prix'=>30, 'go_question'=>40, 'go_form'=>50,
            'autre'=>40, 'coord'=>50, 'fermer'=>55];
    $val = mb_strtolower(trim($message));
    if (isset($nav[$val])) {
        return goToStep($cid, $nav[$val], $scenario);
    }

    // --- 2. Si étape à boutons → matcher le clic ---
    if ($step && isset($step['options'])) {
        $matched = matchOption($message, $step['options']);
        if ($matched) {
            if (isset($step['field'])) {
                chatbotUpdateData($cid, $step['field'], $matched['value']);
            }
            $next = $matched['next'];

            // Étapes dynamiques : résultats de recherche
            if ($next === 'results_maison') return handleResultsMaison($cid, $scenario);
            if ($next === 'results_terrain') return handleResultsTerrain($cid, $scenario);

            return goToStep($cid, $next, $scenario);
        }
    }

    // --- 3. Mode question libre (étape 40 ou texte quelconque) ---
    $intention = chatbotDetectIntention($message);
    $msgCount = chatbotCountUserMessages($cid);

    if ($intention) {
        $resp = $intention['response'] ?? '';
        $act = $intention['action'] ?? '';

        // Si l'intention a une action de scénario
        if ($act === 'scenario_devis') { if ($resp) chatbotSaveMessage($cid, 'bot', $resp); return goToStep($cid, 30, $scenario); }
        if ($act === 'scenario_terrain') { if ($resp) chatbotSaveMessage($cid, 'bot', $resp); return goToStep($cid, 20, $scenario); }
        if ($act === 'afficher_modeles') { if ($resp) chatbotSaveMessage($cid, 'bot', $resp); return goToStep($cid, 10, $scenario); }
        if ($act === 'transfert_humain' || $act === 'redirect:/contact.php') {
            chatbotSaveMessage($cid, 'bot', $resp ?: 'Un conseiller va vous aider !');
            chatbotUpdateStep($cid, 50);
            respond(['step'=>50, 'type'=>'form', 'message'=> ($resp ?: '') . "\n\n👇 **Laissez vos coordonnées :**"]);
        }

        // Sinon, répondre avec le texte + proposer la suite
        if ($resp) {
            chatbotSaveMessage($cid, 'bot', $resp);

            // Après 4+ échanges → formulaire
            if ($msgCount >= 4) {
                chatbotUpdateStep($cid, 50);
                respond(['step'=>50, 'type'=>'form', 'message'=> $resp . "\n\n👇 **Pour aller plus loin, laissez vos coordonnées :**"]);
            }

            // Options contextuelles selon le sujet
            respond([
                'step' => $stepId,
                'message' => $resp,
                'options' => getFollowUpOptions($intention['key'])
            ]);
        }
    }

    // --- 4. Après 5+ messages non compris → formulaire ---
    if ($msgCount >= 5) {
        chatbotUpdateStep($cid, 50);
        $msg = "Un conseiller ORCA pourra mieux vous répondre ! 😊\n\nLaissez vos coordonnées, il vous rappelle sous 24h :";
        chatbotSaveMessage($cid, 'bot', $msg);
        respond(['step'=>50, 'type'=>'form', 'message'=>$msg]);
    }

    // --- 5. Réponse par défaut ---
    $defaultMsg = "Je n'ai pas la réponse exacte, mais je peux vous aider ! 😊";
    chatbotSaveMessage($cid, 'bot', $defaultMsg);
    respond([
        'step' => $stepId,
        'message' => $defaultMsg,
        'options' => [
            ['label' => '💰 Connaître les prix', 'value' => 'go_prix', 'next' => 30],
            ['label' => '🏠 Chercher une maison', 'value' => 'go_maison', 'next' => 10],
            ['label' => '🌿 Chercher un terrain', 'value' => 'go_terrain', 'next' => 20],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ]
    ]);
}

// ======================================================
// RÉSULTATS MAISON (après le questionnaire)
// ======================================================
function handleResultsMaison($cid, $scenario) {
    $conv = chatbotGetConversation($cid);
    $data = json_decode($conv['data_collected'] ?? '{}', true) ?: [];

    $results = chatbotSearchModeles($data);
    $budget = intval($data['budget'] ?? 0);
    $text = chatbotFormatModeles($results, $budget);
    $text .= "\n**Intéressé ? Laissez vos coordonnées pour recevoir les fiches détaillées et une estimation !**";

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
// RÉSULTATS TERRAIN (après le questionnaire)
// ======================================================
function handleResultsTerrain($cid, $scenario) {
    $conv = chatbotGetConversation($cid);
    $data = json_decode($conv['data_collected'] ?? '{}', true) ?: [];

    $results = chatbotSearchTerrains($data);
    $text = chatbotFormatTerrains($results);
    $text .= "\n**Laissez vos coordonnées pour recevoir les fiches complètes !**";

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
// FORMULAIRE
// ======================================================
function handleForm() {
    $cid = intval($_POST['conversation_id'] ?? 0);
    $formData = json_decode($_POST['data'] ?? '{}', true);
    if (!$cid || empty($formData)) respond(['error' => 'Données manquantes']);

    $errors = [];
    if (empty($formData['prenom']) || !chatbotValidateInput($formData['prenom'], 'name')) $errors[] = 'Prénom';
    if (empty($formData['nom']) || !chatbotValidateInput($formData['nom'], 'name')) $errors[] = 'Nom';
    if (empty($formData['email']) || !chatbotValidateInput($formData['email'], 'email')) $errors[] = 'Email';
    if (empty($formData['telephone']) || !chatbotValidateInput($formData['telephone'], 'phone')) $errors[] = 'Téléphone';
    if (!empty($errors)) respond(['error' => 'Champ(s) invalide(s) : ' . implode(', ', $errors)]);

    $formData['telephone'] = chatbotNormalizePhone($formData['telephone']);

    $conv = chatbotGetConversation($cid);
    $existing = json_decode($conv['data_collected'] ?? '{}', true) ?: [];
    $allData = array_merge($existing, $formData);

    foreach ($formData as $k => $v) chatbotUpdateData($cid, $k, $v);

    $result = chatbotCreateLead($cid, $allData);
    if (!$result['success']) respond(['error' => 'Erreur serveur, réessayez.']);

    $prenom = htmlspecialchars($allData['prenom'] ?? '');
    $scenario = chatbotGetScenario();
    $msg = str_replace('{{prenom}}', $prenom, $scenario[55]['message']);

    chatbotSaveMessage($cid, 'bot', $msg);
    chatbotUpdateStep($cid, 55);

    respond([
        'step' => 55, 'type' => 'final', 'message' => $msg,
        'lead_id' => $result['lead_id'], 'options' => $scenario[55]['options']
    ]);
}

// ======================================================
// UTILITAIRES
// ======================================================

function goToStep($cid, $stepId, $scenario) {
    $step = $scenario[$stepId] ?? $scenario[50];
    if (!isset($scenario[$stepId])) $stepId = 50;

    chatbotUpdateStep($cid, $stepId);
    chatbotSaveMessage($cid, 'bot', $step['message'], $step['options'] ?? null);

    respond([
        'step' => $stepId, 'type' => $step['type'] ?? 'step',
        'message' => $step['message'], 'options' => $step['options'] ?? null
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

function getFollowUpOptions($intentionKey) {
    $map = [
        'prix' => [
            ['label' => '🏠 Voir les modèles', 'value' => 'go_maison', 'next' => 10],
            ['label' => '💰 Grille des prix complète', 'value' => 'go_prix', 'next' => 30],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
        'delai' => [
            ['label' => '💰 Connaître les prix', 'value' => 'go_prix', 'next' => 30],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
        'financement' => [
            ['label' => '💰 Simuler mon budget', 'value' => 'go_prix', 'next' => 30],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📞 Parler à un conseiller', 'value' => 'coord', 'next' => 50]
        ],
        'rdv' => [
            ['label' => '📋 Laisser mes coordonnées', 'value' => 'coord', 'next' => 50],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40]
        ],
        'garantie' => [
            ['label' => '🏠 Voir nos modèles', 'value' => 'go_maison', 'next' => 10],
            ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
            ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
        ],
    ];
    return $map[$intentionKey] ?? [
        ['label' => '🏠 Nos maisons', 'value' => 'go_maison', 'next' => 10],
        ['label' => '🌿 Nos terrains', 'value' => 'go_terrain', 'next' => 20],
        ['label' => '❓ Autre question', 'value' => 'autre', 'next' => 40],
        ['label' => '📋 Être rappelé', 'value' => 'coord', 'next' => 50]
    ];
}

function respond($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
