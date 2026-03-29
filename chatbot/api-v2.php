<?php
/**
 * API Chatbot ORCA v2 - Multi-scénarios intelligent
 * Objectif : Obtenir les coordonnées du prospect à tout prix
 */
require_once '../includes/config.php';
require_once '../includes/chatbot-functions.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'init':
        handleInitV2();
        break;
    case 'message':
        handleMessageV2();
        break;
    case 'step':
        handleStepV2();
        break;
    case 'form':
        handleFormV2();
        break;
    default:
        jsonResponseV2(['error' => 'Action inconnue']);
}

/**
 * Initialise une conversation avec le nouveau scénario
 */
function handleInitV2() {
    $conversation = chatbotGetOrCreateConversation();
    $scenario = chatbotGetAdvancedScenario();
    
    // Si nouvelle conversation
    if (isset($conversation['is_new']) && $conversation['is_new']) {
        $firstStep = $scenario['steps'][0];
        
        chatbotSaveMessage($conversation['id'], 'bot', $firstStep['question'], [
            'buttons' => $firstStep['options'] ?? null
        ]);
        
        jsonResponseV2([
            'conversation_id' => $conversation['id'],
            'step' => 1,
            'message' => $firstStep['question'],
            'type' => $firstStep['type'],
            'options' => $firstStep['options'] ?? null,
            'is_new' => true
        ]);
    }
    
    // Reprendre la conversation existante
    $history = chatbotGetHistory($conversation['id']);
    jsonResponseV2([
        'conversation_id' => $conversation['id'],
        'step' => $conversation['current_step'],
        'history' => $history,
        'is_new' => false
    ]);
}

/**
 * Gère les messages utilisateur avec détection multi-intention
 */
function handleMessageV2() {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (!$conversation_id || empty($message)) {
        jsonResponseV2(['error' => 'Données manquantes']);
    }
    
    // Sauvegarder le message
    chatbotSaveMessage($conversation_id, 'user', $message);
    
    // Récupérer contexte
    $conversation = getConversationV2($conversation_id);
    $scenario = chatbotGetAdvancedScenario();
    $context = chatbotGetContext($conversation_id);
    $currentStepId = $conversation['current_step'];
    
    // Compter les interactions sans coordonnées
    $interaction_count = count($context['recent_messages'] ?? []);
    
    // ===== ÉTAPE 1: Détection d'objection ou question =====
    $forced_path = chatbotDeterminePath($conversation_id, $message, $currentStepId, $context);
    
    if ($forced_path && is_array($forced_path)) {
        // Message personnalisé pour forcer les coordonnées
        jsonResponseV2([
            'type' => 'force_coord',
            'message' => $forced_path['message'],
            'step' => $forced_path['next'],
            'options' => [
                ['label' => '📞 Donner mes coordonnées', 'value' => 'ok_coord', 'next' => 50],
                ['label' => '📧 Juste l\'email', 'value' => 'just_email', 'next' => 61]
            ]
        ]);
    } elseif ($forced_path && is_int($forced_path)) {
        // Redirection vers une étape spécifique (objection handler)
        return goToStepV2($conversation_id, $scenario, $forced_path);
    }
    
    // ===== ÉTAPE 2: Détection d'intention =====
    $intention = chatbotDetectIntention($message);
    
    if ($intention) {
        // Si c'est une question informative et qu'on n'a pas encore les coordonnées
        if ($interaction_count > 4 && !hasCoordinates($context)) {
            // Forcer la collecte après 4 interactions
            return forceCoordinatesCollection($conversation_id, $message);
        }
        
        $response = generateSmartResponse($intention, $context);
        
        chatbotSaveMessage($conversation_id, 'bot', $response, [
            'intention' => $intention['intention_key']
        ]);
        
        // Si question FAQ, proposer de continuer ou donner coordonnées
        if (in_array($intention['intention_key'], ['prix', 'modele', 'terrain', 'delai', 'aide'])) {
            jsonResponseV2([
                'type' => 'smart_response',
                'message' => $response,
                'continue_options' => [
                    ['label' => '💰 Obtenir mon devis', 'value' => 'devis', 'next' => 10],
                    ['label' => '📅 RDV conseiller', 'value' => 'rdv', 'next' => 50],
                    ['label' => '❓ Autre question', 'value' => 'question', 'next' => 30]
                ],
                'force_coord_after' => 2
            ]);
        }
        
        jsonResponseV2([
            'type' => 'intention',
            'message' => $response,
            'action' => $intention['action']
        ]);
    }
    
    // ===== ÉTAPE 3: Traiter comme réponse au scénario courant =====
    return processScenarioResponseV2($conversation_id, $message, $scenario, $currentStepId);
}

/**
 * Traite une réponse dans le contexte du scénario
 */
function processScenarioResponseV2($conversation_id, $message, $scenario, $currentStepId) {
    // Trouver l'étape actuelle
    $currentStep = null;
    foreach ($scenario['steps'] as $step) {
        if ($step['id'] == $currentStepId) {
            $currentStep = $step;
            break;
        }
    }
    
    if (!$currentStep) {
        // Si l'étape n'existe pas, aller à la collecte de coordonnées
        return goToStepV2($conversation_id, $scenario, 50);
    }
    
    // Si c'est une étape de collecte de coordonnées
    if ($currentStepId >= 50 && $currentStepId <= 55) {
        return handleCoordStep($conversation_id, $message, $currentStep);
    }
    
    // Essayer de matcher avec les options
    if (isset($currentStep['options'])) {
        $matched = matchUserResponseAdvanced($message, $currentStep['options']);
        
        if ($matched) {
            // Sauvegarder la donnée
            if (isset($currentStep['field'])) {
                chatbotUpdateData($conversation_id, $currentStep['field'], $matched['value']);
            }
            
            // Passer à l'étape suivante
            return goToStepV2($conversation_id, $scenario, $matched['next']);
        }
    }
    
    // Si réponse libre (texte) - accepter et continuer
    if (isset($currentStep['field'])) {
        $value = extractAndNormalizeValue($message, $currentStep['field']);
        chatbotUpdateData($conversation_id, $currentStep['field'], $value);
        
        $nextStep = $currentStep['next'] ?? 50;
        return goToStepV2($conversation_id, $scenario, $nextStep);
    }
    
    // Si rien ne match après plusieurs tentatives
    $context = chatbotGetContext($conversation_id);
    if (count($context['recent_messages']) > 6 && !hasCoordinates($context)) {
        return forceCoordinatesCollection($conversation_id, $message);
    }
    
    // Réponse par défaut
    jsonResponseV2([
        'type' => 'clarification',
        'message' => "Je ne suis pas sûr de comprendre. 😊\n\n**Pour mieux vous aider, pourriez-vous me donner vos coordonnées ?** Un conseiller vous rappellera gratuitement.",
        'options' => [
            ['label' => '📞 Laisser mes coordonnées', 'value' => 'coord', 'next' => 50],
            ['label' => '📧 Juste mon email', 'value' => 'email_only', 'next' => 61]
        ]
    ]);
}

/**
 * Gère une étape de collecte de coordonnées
 */
function handleCoordStep($conversation_id, $message, $step) {
    $field = $step['field'];
    $value = trim($message);
    
    // Validation
    $isValid = true;
    $errorMsg = $step['error_message'] ?? "Cette information ne semble pas valide.";
    
    switch ($field) {
        case 'telephone':
            $isValid = preg_match('/^(0[1-9])(?:[\s.-]?\d{2}){4}$/', $value);
            // Normaliser
            if ($isValid) {
                $value = preg_replace('/[^0-9]/', '', $value);
                $value = substr($value, 0, 2) . ' ' . substr($value, 2, 2) . ' ' . substr($value, 4, 2) . ' ' . substr($value, 6, 2) . ' ' . substr($value, 8, 2);
            }
            break;
            
        case 'email':
            $isValid = filter_var($value, FILTER_VALIDATE_EMAIL);
            break;
            
        case 'prenom':
        case 'nom':
            $isValid = strlen($value) >= 2 && preg_match('/^[\p{L}\s\-\']+$/u', $value);
            break;
    }
    
    if (!$isValid) {
        jsonResponseV2([
            'type' => 'validation_error',
            'message' => $errorMsg,
            'retry' => true,
            'field' => $field
        ]);
    }
    
    // Sauvegarder la valeur validée
    chatbotUpdateData($conversation_id, $field, $value);
    
    // Passer à l'étape suivante
    $scenario = chatbotGetAdvancedScenario();
    $nextStepId = $step['next'] ?? 55;
    
    // Si on arrive à l'étape finale, créer le lead
    if ($nextStepId == 55 || $step['id'] == 54) {
        $conversation = getConversationV2($conversation_id);
        $data = json_decode($conversation['data_collected'], true);
        
        $leadResult = chatbotCreateLead($conversation_id, $data);
        
        return goToStepV2($conversation_id, $scenario, 55, ['lead_id' => $leadResult['lead_id']]);
    }
    
    return goToStepV2($conversation_id, $scenario, $nextStepId);
}

/**
 * Force la collecte des coordonnées après plusieurs échanges
 */
function forceCoordinatesCollection($conversation_id, $lastMessage) {
    $messages = [
        "J'ai bien noté votre question sur '{$lastMessage}'. 🤔\n\n**Pour vous donner une réponse personnalisée et précise, laissez-moi vos coordonnées** et un conseiller expert vous rappellera sous 24h avec tous les détails.",
        "Je vois que vous vous posez des questions pertinentes ! 😊\n\n**Plutôt que de continuer par écrit, puis-je vous demander votre email et téléphone ?** Un conseiller pourra ainsi vous guider pas à pas.",
        "Je préfère être transparent : **vos questions méritent des réponses d'expert**. 📞\n\nDonnez-moi simplement vos coordonnées, un conseiller ORCA vous appellera gratuitement pour tout vous expliquer."
    ];
    
    $randomMessage = $messages[array_rand($messages)];
    
    jsonResponseV2([
        'type' => 'force_coord',
        'message' => $randomMessage,
        'step' => 50,
        'urgent' => true,
        'options' => [
            ['label' => '✅ OK, voici mes coordonnées', 'value' => 'ok', 'next' => 50],
            ['label' => '📧 Juste mon email', 'value' => 'email', 'next' => 61]
        ]
    ]);
}

/**
 * Matching avancé des réponses utilisateur
 */
function matchUserResponseAdvanced($message, $options) {
    $message = strtolower(trim($message));
    
    // 1. Match exact sur la valeur
    foreach ($options as $option) {
        if (strtolower($option['value']) === $message) {
            return $option;
        }
    }
    
    // 2. Match sur le label (sans emojis)
    foreach ($options as $option) {
        $label = preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', '', $option['label']);
        $label = strtolower(trim($label));
        
        similar_text($message, $label, $percent);
        if ($percent > 60) {
            return $option;
        }
        
        if (strpos($label, $message) !== false || strpos($message, $label) !== false) {
            return $option;
        }
    }
    
    // 3. Mots-clés spécifiques
    $keywords_map = [
        'prix' => ['prix', 'budget', 'coûte', 'euros', '€', 'combien'],
        'modeles' => ['modèle', 'maison', 'voir', 'découvrir'],
        'terrain' => ['terrain', 'parcelle', 'trouver'],
        'rdv' => ['rdv', 'rendez-vous', 'conseiller', 'appeler', 'téléphone'],
        'devis' => ['devis', 'estimation', 'calculer']
    ];
    
    foreach ($keywords_map as $type => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($message, $kw) !== false) {
                foreach ($options as $option) {
                    if (strpos(strtolower($option['value']), $type) !== false) {
                        return $option;
                    }
                }
            }
        }
    }
    
    return null;
}

/**
 * Extrait et normalise une valeur selon le champ
 */
function extractAndNormalizeValue($message, $field) {
    $message = trim($message);
    
    switch ($field) {
        case 'departement':
            // Extraire les 2 premiers chiffres
            if (preg_match('/(\d{2})/', $message, $matches)) {
                return $matches[1];
            }
            return $message;
            
        case 'surface':
            if (preg_match('/(\d+)/', $message, $matches)) {
                return $matches[1];
            }
            return $message;
            
        case 'budget':
        case 'budget_max':
            if (preg_match('/(\d[\d\s]*)/', $message, $matches)) {
                $num = intval(preg_replace('/\s/', '', $matches[1]));
                // Normaliser
                if ($num < 150000) return '150000';
                if ($num < 200000) return '175000';
                if ($num < 250000) return '225000';
                return '300000';
            }
            return $message;
            
        default:
            return $message;
    }
}

/**
 * Passe à une étape spécifique
 */
function goToStepV2($conversation_id, $scenario, $stepId, $extra = []) {
    $step = null;
    foreach ($scenario['steps'] as $s) {
        if ($s['id'] == $stepId) {
            $step = $s;
            break;
        }
    }
    
    if (!$step) {
        jsonResponseV2(['error' => 'Étape non trouvée', 'step_id' => $stepId]);
    }
    
    // Mettre à jour l'étape courante
    updateConversationStepV2($conversation_id, $stepId);
    
    // Générer le message avec les variables
    $message = $step['question'];
    
    // Remplacer les variables si présentes
    if (strpos($message, '{{') !== false) {
        $conversation = getConversationV2($conversation_id);
        $data = json_decode($conversation['data_collected'] ?? '{}', true);
        
        // Calculer estimation si nécessaire
        if (strpos($message, '{{prix_') !== false) {
            $estimates = chatbotCalculateEstimate($data);
            foreach ($estimates as $key => $value) {
                $message = str_replace('{{' . $key . '}}', $value, $message);
            }
        }
        
        // Remplacer les données simples
        foreach ($data as $key => $value) {
            $message = str_replace('{{' . $key . '}}', htmlspecialchars($value), $message);
        }
    }
    
    chatbotSaveMessage($conversation_id, 'bot', $message, [
        'buttons' => $step['options'] ?? null
    ]);
    
    $response = [
        'type' => $step['type'] ?? 'step',
        'step' => $stepId,
        'message' => $message,
        'options' => $step['options'] ?? null,
        'field' => $step['field'] ?? null
    ];
    
    // Fusionner avec les données extra
    $response = array_merge($response, $extra);
    
    jsonResponseV2($response);
}

/**
 * Gère la soumission du formulaire final
 */
function handleFormV2() {
    $conversation_id = intval($_POST['conversation_id'] ?? 0);
    $data = json_decode($_POST['data'] ?? '{}', true);
    
    if (!$conversation_id) {
        jsonResponseV2(['error' => 'Conversation manquante']);
    }
    
    // Fusionner avec les données existantes
    $conversation = getConversationV2($conversation_id);
    $existing = json_decode($conversation['data_collected'] ?? '{}', true);
    $allData = array_merge($existing, $data);
    
    // Créer le lead
    $leadResult = chatbotCreateLead($conversation_id, $allData);
    
    // Message de confirmation
    $prenom = $allData['prenom'] ?? '';
    
    jsonResponseV2([
        'type' => 'final',
        'message' => "🎉 **Merci {$prenom} !**\n\nVotre demande a bien été enregistrée.\n\n📞 **Un conseiller ORCA vous contactera sous 24h ouvrées.**\n\nEn attendant, n'hésitez pas à consulter nos modèles sur le site.",
        'lead_id' => $leadResult['lead_id'],
        'lead_quality' => $leadResult['quality'],
        'options' => [
            ['label' => '🏠 Voir les modèles', 'action' => 'redirect:/modeles.php'],
            ['label' => '❌ Fermer', 'action' => 'close']
        ]
    ]);
}

/**
 * Fonctions utilitaires
 */
function jsonResponseV2($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getConversationV2($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateConversationStepV2($id, $step) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE chatbot_conversations SET current_step = ?, last_activity = NOW() WHERE id = ?");
    $stmt->execute([$step, $id]);
}

function handleStepV2() {
    // Wrapper pour compatibilité
    handleMessageV2();
}
