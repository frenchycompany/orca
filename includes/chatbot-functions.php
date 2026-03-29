<?php
/**
 * Fonctions du Chatbot ORCA
 * Objectif : guider le visiteur et obtenir ses coordonnées
 */

/**
 * Récupérer ou créer une conversation
 */
function chatbotGetOrCreateConversation() {
    global $pdo;
    $sid = session_id();
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $page = $_SERVER['HTTP_REFERER'] ?? '';

    // Chercher conversation active (moins de 30 min)
    $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations
                          WHERE session_id = ? AND is_active = 1
                          AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                          ORDER BY id DESC LIMIT 1");
    $stmt->execute([$sid]);
    $conv = $stmt->fetch();

    if ($conv) {
        return array_merge($conv, ['is_new' => false]);
    }

    // Créer nouvelle conversation
    $stmt = $pdo->prepare("INSERT INTO chatbot_conversations
                          (session_id, ip_address, page_source, current_step, data_collected, started_at, last_activity)
                          VALUES (?, ?, ?, 1, '{}', NOW(), NOW())");
    $stmt->execute([$sid, $ip, $page]);

    return [
        'id' => $pdo->lastInsertId(),
        'current_step' => 1,
        'data_collected' => '{}',
        'is_new' => true
    ];
}

/**
 * Scénario conversationnel
 * Étapes 1-13 : qualification (département, surface, terrain, budget)
 * Étapes 50-55 : collecte coordonnées (prénom, nom, email, téléphone, confirmation)
 */
function chatbotGetScenario() {
    return [
        1 => [
            'type' => 'buttons',
            'message' => "Bonjour ! 👋\n\nJe suis l'assistant ORCA. Comment puis-je vous aider ?",
            'options' => [
                ['label' => '💰 Obtenir un devis', 'value' => 'devis', 'next' => 10],
                ['label' => '🏠 Voir les modèles', 'value' => 'modeles', 'next' => 20],
                ['label' => '📅 Prendre rendez-vous', 'value' => 'rdv', 'next' => 50]
            ]
        ],
        10 => [
            'type' => 'buttons',
            'message' => 'Dans quel département souhaitez-vous construire ?',
            'field' => 'departement',
            'options' => [
                ['label' => '60 - Oise', 'value' => '60', 'next' => 11],
                ['label' => '77 - Seine-et-Marne', 'value' => '77', 'next' => 11],
                ['label' => '95 - Val-d\'Oise', 'value' => '95', 'next' => 11],
                ['label' => 'Autre département', 'value' => 'autre', 'next' => 11]
            ]
        ],
        11 => [
            'type' => 'buttons',
            'message' => 'Quelle surface habitable envisagez-vous ?',
            'field' => 'surface',
            'options' => [
                ['label' => '70 - 90 m²', 'value' => '80', 'next' => 12],
                ['label' => '90 - 110 m²', 'value' => '100', 'next' => 12],
                ['label' => '110 - 130 m²', 'value' => '120', 'next' => 12],
                ['label' => '130+ m²', 'value' => '150', 'next' => 12]
            ]
        ],
        12 => [
            'type' => 'buttons',
            'message' => 'Avez-vous déjà un terrain ?',
            'field' => 'terrain',
            'options' => [
                ['label' => '✅ Oui', 'value' => 'oui', 'next' => 13],
                ['label' => '❌ Non, je cherche', 'value' => 'non', 'next' => 13],
                ['label' => '🔍 En cours de recherche', 'value' => 'recherche', 'next' => 13]
            ]
        ],
        13 => [
            'type' => 'buttons',
            'message' => 'Quel est votre budget approximatif ?',
            'field' => 'budget',
            'options' => [
                ['label' => 'Moins de 150 000 €', 'value' => '150000', 'next' => 50],
                ['label' => '150 000 - 200 000 €', 'value' => '175000', 'next' => 50],
                ['label' => '200 000 - 250 000 €', 'value' => '225000', 'next' => 50],
                ['label' => 'Plus de 250 000 €', 'value' => '300000', 'next' => 50]
            ]
        ],
        20 => [
            'type' => 'buttons',
            'message' => 'Quel type de maison vous intéresse ?',
            'field' => 'type_maison',
            'options' => [
                ['label' => '🏠 Plain-pied', 'value' => 'plain_pied', 'next' => 21],
                ['label' => '🏡 Avec étage', 'value' => 'etage', 'next' => 21],
                ['label' => '🏘️ Sur sous-sol', 'value' => 'soussol', 'next' => 21]
            ]
        ],
        21 => [
            'type' => 'buttons',
            'message' => 'Quel est votre budget ?',
            'field' => 'budget',
            'options' => [
                ['label' => 'Moins de 150 000 €', 'value' => '150000', 'next' => 50],
                ['label' => '150 000 - 200 000 €', 'value' => '175000', 'next' => 50],
                ['label' => 'Plus de 200 000 €', 'value' => '250000', 'next' => 50]
            ]
        ],
        // Collecte coordonnées
        50 => [
            'type' => 'text',
            'message' => "Parfait ! Pour vous envoyer une estimation personnalisée, j'ai besoin de quelques infos.\n\nQuel est votre **prénom** ?",
            'field' => 'prenom',
            'next' => 51,
            'validation' => 'name',
            'error' => 'Veuillez entrer un prénom valide (au moins 2 lettres).'
        ],
        51 => [
            'type' => 'text',
            'message' => 'Merci ! Et votre **nom** ?',
            'field' => 'nom',
            'next' => 52,
            'validation' => 'name',
            'error' => 'Veuillez entrer un nom valide.'
        ],
        52 => [
            'type' => 'text',
            'message' => 'Votre **email** ?',
            'field' => 'email',
            'next' => 53,
            'validation' => 'email',
            'error' => 'Veuillez entrer un email valide (ex: nom@email.fr).'
        ],
        53 => [
            'type' => 'text',
            'message' => 'Et votre **numéro de téléphone** ?',
            'field' => 'telephone',
            'next' => 55,
            'validation' => 'phone',
            'error' => 'Veuillez entrer un numéro valide (ex: 06 12 34 56 78).'
        ],
        55 => [
            'type' => 'final',
            'message' => "🎉 **Merci {{prenom}} !**\n\nVotre demande a bien été enregistrée.\n\n📞 **Un conseiller ORCA vous contactera sous 24h.**\n\nEn attendant, découvrez nos modèles sur le site !",
            'options' => [
                ['label' => '🏠 Voir les modèles', 'value' => 'modeles', 'action' => 'link', 'url' => '/modeles.php'],
                ['label' => '❌ Fermer le chat', 'value' => 'close', 'action' => 'close']
            ]
        ]
    ];
}

/**
 * Détection d'intention dans un message libre
 */
function chatbotDetectIntention($message) {
    $msg = mb_strtolower(trim($message));

    $intentions = [
        'prix' => [
            'keywords' => ['prix', 'coût', 'cout', 'combien', 'tarif', 'euros', '€', 'cher'],
            'response' => "💰 **Nos prix démarrent à 125 000 € pour 80m².**\n\nPour une estimation précise adaptée à votre projet, laissez-moi vos coordonnées et un conseiller vous rappellera."
        ],
        'modeles' => [
            'keywords' => ['modèle', 'modele', 'maison', 'catalogue', 'gamme', 'voir'],
            'response' => "🏠 **Nous avons 6 modèles de 70 à 130m²** : plain-pied, étage ou sous-sol.\n\nPour recevoir la brochure complète, laissez-moi vos coordonnées !"
        ],
        'terrain' => [
            'keywords' => ['terrain', 'parcelle', 'foncier'],
            'response' => "🌿 **Nous proposons un service gratuit de recherche de terrain** dans l'Oise, l'Aisne, la Somme et l'Île-de-France.\n\nPour recevoir nos offres de terrains, laissez-moi vos coordonnées !"
        ],
        'delai' => [
            'keywords' => ['délai', 'delai', 'durée', 'duree', 'temps', 'quand', 'livraison'],
            'response' => "⏱️ **Délai moyen : 6 à 8 mois** après obtention du permis de construire.\n\nPour un planning personnalisé, laissez-moi vos coordonnées !"
        ],
        'financement' => [
            'keywords' => ['financement', 'prêt', 'pret', 'ptz', 'crédit', 'credit', 'banque', 'aide'],
            'response' => "💡 **Plusieurs aides existent** : PTZ, Eco-PTZ, TVA réduite...\n\nNotre partenaire financier peut vous accompagner. Laissez-moi vos coordonnées !"
        ],
        'rdv' => [
            'keywords' => ['rendez-vous', 'rdv', 'rencontrer', 'agence', 'visite', 'appeler'],
            'response' => "📅 **Avec plaisir !** Nous pouvons vous recevoir à l'agence, chez vous ou en visio.\n\nLaissez-moi vos coordonnées pour fixer un RDV sous 48h !"
        ]
    ];

    foreach ($intentions as $key => $intent) {
        foreach ($intent['keywords'] as $kw) {
            if (mb_strpos($msg, $kw) !== false) {
                return [
                    'key' => $key,
                    'response' => $intent['response']
                ];
            }
        }
    }

    return null;
}

/**
 * Valider une entrée utilisateur selon le type
 */
function chatbotValidateInput($value, $type) {
    $value = trim($value);
    switch ($type) {
        case 'name':
            return mb_strlen($value) >= 2 && preg_match('/^[\p{L}\s\-\']+$/u', $value);
        case 'email':
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        case 'phone':
            return preg_match('/^(0[1-9])[\s.-]?(\d{2}[\s.-]?){4}$/', $value);
        default:
            return true;
    }
}

/**
 * Normaliser un numéro de téléphone → "06 12 34 56 78"
 */
function chatbotNormalizePhone($phone) {
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 10) {
        return substr($digits, 0, 2) . ' ' . substr($digits, 2, 2) . ' ' .
               substr($digits, 4, 2) . ' ' . substr($digits, 6, 2) . ' ' . substr($digits, 8, 2);
    }
    return $phone;
}

/**
 * Sauvegarder un message en BDD
 */
function chatbotSaveMessage($conversation_id, $type, $message, $extra = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO chatbot_messages
                          (conversation_id, type, message, buttons, created_at)
                          VALUES (?, ?, ?, ?, NOW())");
    $buttons = $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null;
    $stmt->execute([$conversation_id, $type, $message, $buttons]);
}

/**
 * Mettre à jour les données collectées
 */
function chatbotUpdateData($conversation_id, $field, $value) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT data_collected FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$conversation_id]);
    $data = json_decode($stmt->fetchColumn() ?: '{}', true) ?: [];
    $data[$field] = $value;
    $score = min(count($data) * 15, 100);
    $pdo->prepare("UPDATE chatbot_conversations SET data_collected = ?, completion_score = ?, last_activity = NOW() WHERE id = ?")
        ->execute([json_encode($data, JSON_UNESCAPED_UNICODE), $score, $conversation_id]);
}

/**
 * Mettre à jour l'étape courante
 */
function chatbotUpdateStep($conversation_id, $step) {
    global $pdo;
    $pdo->prepare("UPDATE chatbot_conversations SET current_step = ?, last_activity = NOW() WHERE id = ?")
        ->execute([$step, $conversation_id]);
}

/**
 * Récupérer une conversation par ID
 */
function chatbotGetConversation($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Récupérer l'historique des messages
 */
function chatbotGetHistory($conversation_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT type, message, buttons FROM chatbot_messages
                          WHERE conversation_id = ? ORDER BY id ASC");
    $stmt->execute([$conversation_id]);
    return $stmt->fetchAll();
}

/**
 * Compter les messages utilisateur
 */
function chatbotCountUserMessages($conversation_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chatbot_messages WHERE conversation_id = ? AND type = 'user'");
    $stmt->execute([$conversation_id]);
    return (int) $stmt->fetchColumn();
}

/**
 * Créer un lead en BDD
 */
function chatbotCreateLead($conversation_id, $data) {
    global $pdo;

    try {
        $terrainPrevu = in_array(($data['terrain'] ?? ''), ['oui', '1', 'true'], true) ? 1 : 0;

        $stmt = $pdo->prepare("INSERT INTO leads
            (nom, prenom, email, telephone, departement, surface_souhaitee, budget_estime, terrain_prevu, source, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'chatbot', NOW())");

        $stmt->execute([
            $data['nom'] ?? '',
            $data['prenom'] ?? '',
            $data['email'] ?? '',
            $data['telephone'] ?? '',
            $data['departement'] ?? '',
            $data['surface'] ?? '',
            $data['budget'] ?? '',
            $terrainPrevu
        ]);

        $lead_id = $pdo->lastInsertId();

        // Fermer la conversation
        $pdo->prepare("UPDATE chatbot_conversations SET lead_id = ?, is_active = 0, ended_at = NOW() WHERE id = ?")
            ->execute([$lead_id, $conversation_id]);

        return ['lead_id' => $lead_id, 'success' => true];
    } catch (Exception $e) {
        error_log('Chatbot lead creation error: ' . $e->getMessage());
        return ['lead_id' => null, 'success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Calculer une estimation de prix
 */
function chatbotCalculateEstimate($data) {
    $surface = intval($data['surface'] ?? 100);
    $prix_m2 = 1250;
    if ($surface < 90) $prix_m2 += 100;
    if ($surface > 120) $prix_m2 -= 50;
    $prix_base = $surface * $prix_m2;

    return [
        'prix_min' => number_format($prix_base * 0.9, 0, ',', ' ') . ' €',
        'prix_max' => number_format($prix_base * 1.1, 0, ',', ' ') . ' €'
    ];
}
