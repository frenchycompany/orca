<?php
/**
 * Fonctions du Chatbot ORCA - Version complète avec IA
 */

// Récupérer ou créer une conversation
function chatbotGetOrCreateConversation() {
    global $pdo;
    $sid = session_id();
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $page = $_SERVER['REQUEST_URI'] ?? '';
    
    // Chercher conversation active
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
                          (session_id, ip_address, page_source, started_at, last_activity) 
                          VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute([$sid, $ip, $page]);
    
    return [
        'id' => $pdo->lastInsertId(),
        'current_step' => 1,
        'is_new' => true
    ];
}

// Scénario avancé avec IA
function chatbotGetAdvancedScenario() {
    return [
        'steps' => [
            // Étape 1: Accueil
            [
                'id' => 1,
                'type' => 'buttons',
                'question' => "Bonjour ! 👋\n\nJe suis l'assistant virtuel ORCA. Je peux vous aider à :\n\n• Obtenir un devis personnalisé\n• Découvrir nos modèles\n• Prendre RDV avec un conseiller\n\nQue souhaitez-vous faire ?",
                'options' => [
                    ['label' => '💰 Obtenir un devis', 'value' => 'devis', 'next' => 10],
                    ['label' => '🏠 Voir les modèles', 'value' => 'modeles', 'next' => 20],
                    ['label' => '📅 Prendre RDV', 'value' => 'rdv', 'next' => 50]
                ]
            ],
            
            // Étape 10: Département (parcours devis)
            [
                'id' => 10,
                'type' => 'buttons',
                'question' => 'Dans quel département souhaitez-vous construire ?',
                'field' => 'departement',
                'options' => [
                    ['label' => '60 - Oise', 'value' => '60', 'next' => 11],
                    ['label' => '77 - Seine-et-Marne', 'value' => '77', 'next' => 11],
                    ['label' => '95 - Val-d\'Oise', 'value' => '95', 'next' => 11],
                    ['label' => 'Autre (02, 80, 91, 92, 93, 94)', 'value' => 'autre', 'next' => 11]
                ]
            ],
            
            // Étape 11: Surface
            [
                'id' => 11,
                'type' => 'buttons',
                'question' => 'Quelle surface habitable envisagez-vous ?',
                'field' => 'surface',
                'options' => [
                    ['label' => '70-90 m²', 'value' => '80', 'next' => 12],
                    ['label' => '90-110 m²', 'value' => '100', 'next' => 12],
                    ['label' => '110-130 m²', 'value' => '120', 'next' => 12],
                    ['label' => '130+ m²', 'value' => '150', 'next' => 12]
                ]
            ],
            
            // Étape 12: Terrain
            [
                'id' => 12,
                'type' => 'buttons',
                'question' => 'Avez-vous un terrain ?',
                'field' => 'terrain',
                'options' => [
                    ['label' => '✅ Oui, j\'ai un terrain', 'value' => 'oui', 'next' => 13],
                    ['label' => '❌ Non, je cherche', 'value' => 'non', 'next' => 13],
                    ['label' => '🤔 En cours de recherche', 'value' => 'recherche', 'next' => 13]
                ]
            ],
            
            // Étape 13: Budget
            [
                'id' => 13,
                'type' => 'buttons',
                'question' => 'Quel est votre budget approximatif ?',
                'field' => 'budget',
                'options' => [
                    ['label' => 'Moins de 150k€', 'value' => '150000', 'next' => 50],
                    ['label' => '150k€ - 200k€', 'value' => '175000', 'next' => 50],
                    ['label' => '200k€ - 250k€', 'value' => '225000', 'next' => 50],
                    ['label' => 'Plus de 250k€', 'value' => '300000', 'next' => 50]
                ]
            ],
            
            // Étape 20: Modèles (parcours modèles)
            [
                'id' => 20,
                'type' => 'buttons',
                'question' => 'Quel type de maison vous intéresse ?',
                'field' => 'type_maison',
                'options' => [
                    ['label' => '🏠 Plain-pied', 'value' => 'plain_pied', 'next' => 21],
                    ['label' => '🏡 Avec étage', 'value' => 'etage', 'next' => 21],
                    ['label' => '🏘️ Sur sous-sol', 'value' => 'soussol', 'next' => 21]
                ]
            ],
            
            // Étape 21: Budget modèles
            [
                'id' => 21,
                'type' => 'buttons',
                'question' => 'Votre budget ?',
                'field' => 'budget',
                'options' => [
                    ['label' => '< 150k€', 'value' => '150000', 'next' => 50],
                    ['label' => '150k€ - 200k€', 'value' => '175000', 'next' => 50],
                    ['label' => '> 200k€', 'value' => '250000', 'next' => 50]
                ]
            ],
            
            // Étape 50: Collecte coordonnées - Prénom
            [
                'id' => 50,
                'type' => 'text',
                'question' => '🎯 Parfait ! Pour vous envoyer une estimation personnalisée, j\'ai besoin de vos coordonnées.\n\nQuel est votre prénom ?',
                'field' => 'prenom',
                'next' => 51,
                'error_message' => 'Veuillez entrer un prénom valide (au moins 2 lettres).'
            ],
            
            // Étape 51: Nom
            [
                'id' => 51,
                'type' => 'text',
                'question' => 'Merci ! Et votre nom ?',
                'field' => 'nom',
                'next' => 52,
                'error_message' => 'Veuillez entrer un nom valide.'
            ],
            
            // Étape 52: Email
            [
                'id' => 52,
                'type' => 'email',
                'question' => 'Votre email ?',
                'field' => 'email',
                'next' => 53,
                'error_message' => 'Veuillez entrer un email valide (ex: nom@email.fr).'
            ],
            
            // Étape 53: Téléphone
            [
                'id' => 53,
                'type' => 'tel',
                'question' => 'Enfin, votre numéro de téléphone ?',
                'field' => 'telephone',
                'next' => 55,
                'error_message' => 'Veuillez entrer un numéro valide (ex: 06 12 34 56 78).'
            ],
            
            // Étape 55: Confirmation finale
            [
                'id' => 55,
                'type' => 'final',
                'question' => "🎉 **Merci {{prenom}} !**\n\nVotre demande a bien été enregistrée.\n\n📞 **Un conseiller ORCA vous contactera sous 24h** au {{telephone}}.\n\n💡 *Estimation pour une maison de {{surface}}m² : entre {{prix_min}} et {{prix_max}}*"
            ]
        ]
    ];
}

// Détecter l'intention de l'utilisateur
function chatbotDetectIntention($message) {
    $message = strtolower($message);
    
    $intentions = [
        'prix' => ['prix', 'budget', 'coûte', 'cout', 'euros', '€', 'combien', 'tarif'],
        'modeles' => ['modèle', 'modeles', 'maison', 'voir', 'découvrir', 'catalogue'],
        'terrain' => ['terrain', 'parcelle', 'trouver', 'achat terrain'],
        'rdv' => ['rdv', 'rendez-vous', 'conseiller', 'appeler', 'téléphone', 'contact'],
        'devis' => ['devis', 'estimation', 'calculer', 'simulation'],
        'delai' => ['délai', 'duree', 'temps', 'quand', 'livraison'],
        'aide' => ['aide', 'financement', 'pret', 'ptz', 'aides'],
        'qualite' => ['qualité', 'matériaux', 'garantie', 'constructeur'],
        'localisation' => ['ou', 'situé', 'adresse', 'agence', 'venir']
    ];
    
    foreach ($intentions as $key => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($message, $kw) !== false) {
                return [
                    'intention_key' => $key,
                    'confidence' => 'high'
                ];
            }
        }
    }
    
    return null;
}

// Générer une réponse intelligente
function generateSmartResponse($intention, $context) {
    $responses = [
        'prix' => "💰 **Nos maisons ORCA sont 20-30% moins chères que la concurrence !**\n\nGrâce à nos modèles standardisés et notre expérience depuis 1993, nous maîtrisons les coûts.\n\nPrix indicatifs :\n• 80m² : à partir de 125 000€\n• 100m² : à partir de 155 000€\n• 120m² : à partir de 185 000€\n\n**Pour une estimation précise, laissez-moi vos coordonnées !**",
        
        'modeles' => "🏠 **Nous avons 6 modèles de maisons** de 70 à 130m² :\n\n• Plain-pied ou à étage\n• Traditionnel ou contemporain\n• Avec ou sans sous-sol\n\nTous nos modèles sont personnalisables (façade, aménagements...).\n\n**Voulez-vous voir nos modèles ou recevoir la brochure ?**",
        
        'terrain' => "🌿 **Vous n'avez pas encore de terrain ?** Pas de problème !\n\nNous avons un service gratuit de recherche de terrain dans l'Oise, l'Aisne, la Somme et l'Île-de-France.\n\n**Donnez-moi votre email et je vous enverrai nos offres de terrains disponibles !**",
        
        'rdv' => "📅 **Avec plaisir !**\n\nUn conseiller ORCA peut vous recevoir :\n• À l'agence de Longueil Annel (60)\n• Chez vous (déplacement gratuit)\n• En visio\n\n**Laissez-moi vos coordonnées et nous fixons un RDV sous 48h !**",
        
        'devis' => "📊 **Je peux vous établir un devis personnalisé !**\n\nJ'ai besoin de quelques informations :\n• Votre département\n• La surface souhaitée\n• Votre budget\n• Si vous avez un terrain\n\n**Commençons par votre département ?**",
        
        'delai' => "⏱️ **Délai moyen : 6 à 8 mois** après obtention du permis.\n\nNos délais sont contractuels et respectés. Pas de mauvaise surprise !\n\n**Voulez-vous un planning personnalisé pour votre projet ?**",
        
        'aide' => "💡 **Plusieurs aides existent :**\n\n• **PTZ** (Prêt à Taux Zéro) sans condition de revenus\n• **Eco-PTZ** pour la rénovation\n• **TVA réduite** à 5.5%\n• **Exonération de taxe foncière** possible\n\n**Notre partenaire financier peut vous accompagner. Laissez-moi vos coordonnées !**",
        
        'qualite' => "✅ **Qualité ORCA :**\n\n• Garantie décennale (10 ans)\n• Garantie biennale (2 ans)\n• Dommages-ouvrage obligatoires\n• NF Habitat certification\n• 30 ans d'expérience\n\n**Voulez-vous visiter une maison témoin ?**",
        
        'localisation' => "📍 **Nous sommes à Longueil Annel (60)** :\n\n119 rue Bordier\n60150 Longueil Annel\n\nOuvert du lundi au vendredi, 9h-18h.\n\n**Appelez-nous au 03 44 00 00 00 ou laissez vos coordonnées !**"
    ];
    
    return $responses[$intention['intention_key']] ?? "Je comprends votre question. 😊\n\n**Pour vous donner la meilleure réponse, laissez-moi vos coordonnées et un conseiller vous rappellera sous 24h !**";
}

// Déterminer le chemin forcé
function chatbotDeterminePath($conversation_id, $message, $currentStepId, $context) {
    // Si déjà en collecte de coordonnées, ne pas forcer
    if ($currentStepId >= 50 && $currentStepId <= 55) {
        return null;
    }
    
    $interactionCount = count($context['recent_messages'] ?? []);
    
    // Après 4 interactions sans coordonnées, forcer
    if ($interactionCount > 4 && !hasCoordinates($context)) {
        $messages = [
            "J'ai bien noté votre question. 🤔\n\n**Pour vous donner une réponse personnalisée, laissez-moi vos coordonnées** et un conseiller vous rappellera sous 24h.",
            "Je vois que vous vous posez des questions ! 😊\n\n**Plutôt que de continuer par écrit, puis-je vous demander votre email et téléphone ?**",
            "Je préfère être transparent : **vos questions méritent des réponses d'expert**. 📞\n\nDonnez-moi simplement vos coordonnées, un conseiller ORCA vous appellera gratuitement."
        ];
        
        return [
            'message' => $messages[array_rand($messages)],
            'next' => 50,
            'urgent' => true
        ];
    }
    
    return null;
}

// Obtenir le contexte de conversation
function chatbotGetContext($conversation_id) {
    global $pdo;
    
    // Récupérer données collectées
    $stmt = $pdo->prepare("SELECT data_collected FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$conversation_id]);
    $data = json_decode($stmt->fetchColumn() ?: '{}', true);
    
    // Récupérer messages récents
    $stmt = $pdo->prepare("SELECT type, message, created_at FROM chatbot_messages 
                          WHERE conversation_id = ? ORDER BY id DESC LIMIT 10");
    $stmt->execute([$conversation_id]);
    $messages = $stmt->fetchAll();
    
    return [
        'data' => $data,
        'recent_messages' => $messages,
        'has_coordinates' => !empty($data['email']) || !empty($data['telephone'])
    ];
}

// Vérifier si on a les coordonnées
function hasCoordinates($context) {
    $data = $context['data'] ?? [];
    return !empty($data['email']) || !empty($data['telephone']);
}

// Calculer estimation prix
function chatbotCalculateEstimate($data) {
    $surface = intval($data['surface'] ?? 100);
    $budget = intval($data['budget'] ?? 175000);
    $terrain = $data['terrain'] ?? 'non';
    
    // Prix de base au m²
    $prix_m2 = 1250;
    
    // Ajustements
    if ($surface < 90) $prix_m2 += 100;
    if ($surface > 120) $prix_m2 -= 50;
    
    $prix_base = $surface * $prix_m2;
    
    return [
        'prix_min' => number_format($prix_base * 0.9, 0, ',', ' ') . ' €',
        'prix_max' => number_format($prix_base * 1.1, 0, ',', ' ') . ' €',
        'prix_base' => number_format($prix_base, 0, ',', ' ') . ' €'
    ];
}

// Créer un lead
function chatbotCreateLead($conversation_id, $data) {
    global $pdo;
    
    try {
        $terrainPrevu = in_array(($data['terrain'] ?? 'non'), ['oui', '1', 'true'], true) ? 1 : 0;

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
        
        // Mettre à jour conversation
        $pdo->prepare("UPDATE chatbot_conversations SET lead_id = ?, is_active = 0, ended_at = NOW() WHERE id = ?")
            ->execute([$lead_id, $conversation_id]);
        
        return ['lead_id' => $lead_id, 'quality' => 'hot'];
    } catch (Exception $e) {
        return ['lead_id' => null, 'quality' => 'medium', 'error' => $e->getMessage()];
    }
}

// Sauvegarder un message
function chatbotSaveMessage($cid, $type, $msg, $buttons = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO chatbot_messages 
                          (conversation_id, type, message, buttons, created_at) 
                          VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$cid, $type, $msg, $buttons ? json_encode($buttons) : null]);
}

// Mettre à jour les données collectées
function chatbotUpdateData($cid, $field, $val) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT data_collected FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$cid]);
    $data = json_decode($stmt->fetchColumn() ?: '{}', true);
    $data[$field] = $val;
    $score = min(count($data) * 15, 100);
    $stmt = $pdo->prepare("UPDATE chatbot_conversations SET data_collected = ?, completion_score = ?, last_activity = NOW() WHERE id = ?");
    $stmt->execute([json_encode($data), $score, $cid]);
}

// Récupérer l'historique
function chatbotGetHistory($cid) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT type, message, buttons FROM chatbot_messages WHERE conversation_id = ? ORDER BY id ASC");
    $stmt->execute([$cid]);
    return $stmt->fetchAll();
}

// Scénario simple (fallback)
function chatbotGetScenario($id = 'main') {
    return chatbotGetAdvancedScenario();
}
