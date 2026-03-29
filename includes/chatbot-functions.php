<?php
/**
 * Fonctions du Chatbot ORCA - Version intelligente
 * Recherche dans les modèles, terrains et intentions depuis la BDD
 */

// ======================================================
// CONVERSATION
// ======================================================

function chatbotGetOrCreateConversation() {
    global $pdo;
    $sid = session_id();
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $page = $_SERVER['HTTP_REFERER'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations
                          WHERE session_id = ? AND is_active = 1
                          AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                          ORDER BY id DESC LIMIT 1");
    $stmt->execute([$sid]);
    $conv = $stmt->fetch();

    if ($conv) {
        return array_merge($conv, ['is_new' => false]);
    }

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

function chatbotGetConversation($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function chatbotUpdateStep($cid, $step) {
    global $pdo;
    $pdo->prepare("UPDATE chatbot_conversations SET current_step = ?, last_activity = NOW() WHERE id = ?")
        ->execute([$step, $cid]);
}

function chatbotUpdateData($cid, $field, $val) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT data_collected FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$cid]);
    $data = json_decode($stmt->fetchColumn() ?: '{}', true) ?: [];
    $data[$field] = $val;
    $score = min(count($data) * 12, 100);
    $pdo->prepare("UPDATE chatbot_conversations SET data_collected = ?, completion_score = ?, last_activity = NOW() WHERE id = ?")
        ->execute([json_encode($data, JSON_UNESCAPED_UNICODE), $score, $cid]);
}

function chatbotSaveMessage($cid, $type, $msg, $extra = null) {
    global $pdo;
    $buttons = $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null;
    $pdo->prepare("INSERT INTO chatbot_messages (conversation_id, type, message, buttons, created_at) VALUES (?, ?, ?, ?, NOW())")
        ->execute([$cid, $type, $msg, $buttons]);
}

function chatbotGetHistory($cid) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT type, message, buttons FROM chatbot_messages WHERE conversation_id = ? ORDER BY id ASC");
    $stmt->execute([$cid]);
    return $stmt->fetchAll();
}

function chatbotCountUserMessages($cid) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chatbot_messages WHERE conversation_id = ? AND type = 'user'");
    $stmt->execute([$cid]);
    return (int) $stmt->fetchColumn();
}

// ======================================================
// SCÉNARIO (étapes guidées par boutons)
// ======================================================

function chatbotGetScenario() {
    return [
        1 => [
            'type' => 'buttons',
            'message' => "Bonjour ! 👋 Je suis l'assistant ORCA.\n\nJe peux vous aider à trouver la maison idéale et le terrain parfait. Que recherchez-vous ?",
            'options' => [
                ['label' => '🏠 Je cherche une maison', 'value' => 'maison', 'next' => 10],
                ['label' => '🌿 Je cherche un terrain', 'value' => 'terrain', 'next' => 20],
                ['label' => '💰 Je veux un devis', 'value' => 'devis', 'next' => 30],
                ['label' => '❓ J\'ai une question', 'value' => 'question', 'next' => 40]
            ]
        ],

        // --- Parcours MAISON ---
        10 => [
            'type' => 'buttons',
            'message' => 'Quel type de maison vous intéresse ?',
            'field' => 'type_maison',
            'options' => [
                ['label' => '🏠 Plain-pied', 'value' => 'plain-pied', 'next' => 11],
                ['label' => '🏡 Avec étage', 'value' => '1-etage', 'next' => 11],
                ['label' => '🤷 Pas de préférence', 'value' => 'tous', 'next' => 11]
            ]
        ],
        11 => [
            'type' => 'buttons',
            'message' => 'Combien de chambres minimum ?',
            'field' => 'nb_chambres',
            'options' => [
                ['label' => '2 chambres', 'value' => '2', 'next' => 12],
                ['label' => '3 chambres', 'value' => '3', 'next' => 12],
                ['label' => '4+ chambres', 'value' => '4', 'next' => 12]
            ]
        ],
        12 => [
            'type' => 'buttons',
            'message' => 'Votre budget maison (hors terrain) ?',
            'field' => 'budget',
            'options' => [
                ['label' => 'Moins de 150 000 €', 'value' => '150000', 'next' => 'search_modeles'],
                ['label' => '150 000 - 200 000 €', 'value' => '200000', 'next' => 'search_modeles'],
                ['label' => 'Plus de 200 000 €', 'value' => '300000', 'next' => 'search_modeles']
            ]
        ],

        // --- Parcours TERRAIN ---
        20 => [
            'type' => 'buttons',
            'message' => 'Dans quel département cherchez-vous ?',
            'field' => 'departement',
            'options' => [
                ['label' => '60 - Oise', 'value' => '60', 'next' => 21],
                ['label' => '77 - Seine-et-Marne', 'value' => '77', 'next' => 21],
                ['label' => '95 - Val-d\'Oise', 'value' => '95', 'next' => 21],
                ['label' => '02 - Aisne', 'value' => '02', 'next' => 21],
                ['label' => '80 - Somme', 'value' => '80', 'next' => 21]
            ]
        ],
        21 => [
            'type' => 'buttons',
            'message' => 'Votre budget terrain ?',
            'field' => 'budget_terrain',
            'options' => [
                ['label' => 'Moins de 50 000 €', 'value' => '50000', 'next' => 'search_terrains'],
                ['label' => '50 000 - 80 000 €', 'value' => '80000', 'next' => 'search_terrains'],
                ['label' => '80 000 - 100 000 €', 'value' => '100000', 'next' => 'search_terrains'],
                ['label' => 'Plus de 100 000 €', 'value' => '150000', 'next' => 'search_terrains']
            ]
        ],

        // --- Parcours DEVIS ---
        30 => [
            'type' => 'buttons',
            'message' => 'Dans quel département souhaitez-vous construire ?',
            'field' => 'departement',
            'options' => [
                ['label' => '60 - Oise', 'value' => '60', 'next' => 31],
                ['label' => '77 - Seine-et-Marne', 'value' => '77', 'next' => 31],
                ['label' => '95 - Val-d\'Oise', 'value' => '95', 'next' => 31],
                ['label' => 'Autre', 'value' => 'autre', 'next' => 31]
            ]
        ],
        31 => [
            'type' => 'buttons',
            'message' => 'Surface souhaitée ?',
            'field' => 'surface',
            'options' => [
                ['label' => '70 - 90 m²', 'value' => '80', 'next' => 32],
                ['label' => '90 - 110 m²', 'value' => '100', 'next' => 32],
                ['label' => '110 - 130 m²', 'value' => '120', 'next' => 32],
                ['label' => '130+ m²', 'value' => '150', 'next' => 32]
            ]
        ],
        32 => [
            'type' => 'buttons',
            'message' => 'Avez-vous un terrain ?',
            'field' => 'terrain',
            'options' => [
                ['label' => '✅ Oui', 'value' => 'oui', 'next' => 33],
                ['label' => '❌ Non', 'value' => 'non', 'next' => 33],
                ['label' => '🔍 En recherche', 'value' => 'recherche', 'next' => 33]
            ]
        ],
        33 => [
            'type' => 'buttons',
            'message' => 'Budget global (maison + terrain) ?',
            'field' => 'budget',
            'options' => [
                ['label' => 'Moins de 200 000 €', 'value' => '200000', 'next' => 50],
                ['label' => '200 000 - 300 000 €', 'value' => '300000', 'next' => 50],
                ['label' => 'Plus de 300 000 €', 'value' => '400000', 'next' => 50]
            ]
        ],

        // --- Mode question libre ---
        40 => [
            'type' => 'text',
            'message' => "Posez-moi votre question ! 😊\n\nJe connais nos modèles de maisons, les terrains disponibles, les prix, les délais, les aides au financement...",
        ],

        // --- Formulaire coordonnées ---
        50 => [
            'type' => 'form',
            'message' => "Pour recevoir votre estimation détaillée et être recontacté par un conseiller, remplissez ce formulaire :",
        ],
        55 => [
            'type' => 'final',
            'message' => "🎉 **Merci {{prenom}} !**\n\nVotre demande a bien été enregistrée.\n📞 **Un conseiller ORCA vous contactera sous 24h.**",
            'options' => [
                ['label' => '🏠 Voir nos modèles', 'value' => 'modeles', 'action' => 'link', 'url' => '/modeles.php'],
                ['label' => '❌ Fermer', 'value' => 'close', 'action' => 'close']
            ]
        ]
    ];
}

// ======================================================
// RECHERCHE INTELLIGENTE EN BDD
// ======================================================

/**
 * Chercher des modèles de maisons selon les critères
 */
function chatbotSearchModeles($data) {
    global $pdo;

    $where = ['is_active = 1'];
    $params = [];

    $type = $data['type_maison'] ?? '';
    if ($type && $type !== 'tous') {
        $where[] = 'nb_etages = ?';
        $params[] = $type;
    }

    $chambres = intval($data['nb_chambres'] ?? 0);
    if ($chambres > 0) {
        $where[] = 'nb_chambres >= ?';
        $params[] = $chambres;
    }

    $budget = intval($data['budget'] ?? 0);
    if ($budget > 0) {
        $where[] = 'prix_base <= ?';
        $params[] = $budget;
    }

    $sql = "SELECT nom, slug, surface_habitable, nb_chambres, nb_etages, style, prix_base, prix_afficher, slogan
            FROM modeles WHERE " . implode(' AND ', $where) . " ORDER BY prix_base ASC LIMIT 4";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Chercher des terrains selon les critères
 */
function chatbotSearchTerrains($data) {
    global $pdo;

    $where = ['is_available = 1'];
    $params = [];

    $dept = $data['departement'] ?? '';
    if ($dept) {
        $where[] = 'departement = ?';
        $params[] = $dept;
    }

    $budget = intval($data['budget_terrain'] ?? 0);
    if ($budget > 0) {
        $where[] = 'prix <= ?';
        $params[] = $budget;
    }

    $sql = "SELECT reference, ville, code_postal, departement, surface, prix, est_viabilise, description, proximite
            FROM terrains WHERE " . implode(' AND ', $where) . " ORDER BY prix ASC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Formater les résultats modèles en texte
 */
function chatbotFormatModeles($modeles) {
    if (empty($modeles)) {
        return "Nous n'avons pas trouvé de modèle correspondant exactement à vos critères, mais nos conseillers peuvent adapter n'importe quel modèle à votre projet !";
    }
    $text = "🏠 **J'ai trouvé " . count($modeles) . " modèle(s) pour vous :**\n\n";
    foreach ($modeles as $m) {
        $prix = $m['prix_afficher'] ?: number_format($m['prix_base'], 0, ',', ' ') . ' €';
        $etage = $m['nb_etages'] === 'plain-pied' ? 'Plain-pied' : 'Avec étage';
        $text .= "**{$m['nom']}** - {$m['surface_habitable']}m², {$m['nb_chambres']} ch., {$etage}\n";
        $text .= "→ À partir de {$prix}\n\n";
    }
    return $text;
}

/**
 * Formater les résultats terrains en texte
 */
function chatbotFormatTerrains($terrains) {
    if (empty($terrains)) {
        return "Aucun terrain disponible pour ces critères pour le moment. Nos conseillers recherchent en permanence de nouvelles parcelles !";
    }
    $text = "🌿 **J'ai trouvé " . count($terrains) . " terrain(s) disponible(s) :**\n\n";
    foreach ($terrains as $t) {
        $prix = number_format($t['prix'], 0, ',', ' ') . ' €';
        $viab = $t['est_viabilise'] ? '✅ Viabilisé' : '⚠️ À viabiliser';
        $text .= "**{$t['ville']}** ({$t['departement']}) - {$t['surface']}m²\n";
        $text .= "→ {$prix} - {$viab}\n";
        if ($t['proximite']) {
            $text .= "📍 {$t['proximite']}\n";
        }
        $text .= "\n";
    }
    return $text;
}

// ======================================================
// DÉTECTION D'INTENTION DEPUIS LA BDD
// ======================================================

/**
 * Chercher une intention dans la table chatbot_intentions
 * Puis fallback sur les intentions hardcodées
 */
function chatbotDetectIntention($message) {
    global $pdo;
    $msg = mb_strtolower(trim($message));

    // 1. Chercher dans chatbot_intentions (BDD) - priorité
    try {
        $stmt = $pdo->query("SELECT * FROM chatbot_intentions WHERE is_active = 1 ORDER BY priority DESC");
        $intentions = $stmt->fetchAll();

        foreach ($intentions as $intent) {
            $keywords = array_map('trim', explode(',', mb_strtolower($intent['keywords'])));
            foreach ($keywords as $kw) {
                if ($kw !== '' && mb_strpos($msg, $kw) !== false) {
                    return [
                        'key' => $intent['intention_key'],
                        'response' => $intent['response_text'],
                        'action' => $intent['action'] ?? null,
                        'source' => 'db'
                    ];
                }
            }
        }
    } catch (Exception $e) {
        // Table pas encore créée, on continue avec le fallback
    }

    // 2. Détection par recherche de données (mots-clés implicites)
    // Si le message parle de maison/modèle → chercher dans les modèles
    $modelKeywords = ['maison', 'modèle', 'modele', 'plain-pied', 'plain pied', 'étage', 'etage', 'chambre'];
    foreach ($modelKeywords as $kw) {
        if (mb_strpos($msg, $kw) !== false) {
            return ['key' => 'search_maison', 'response' => null, 'action' => 'search_modeles', 'source' => 'auto'];
        }
    }

    // Si le message parle de terrain → chercher dans les terrains
    $terrainKeywords = ['terrain', 'parcelle', 'foncier', 'constructible'];
    foreach ($terrainKeywords as $kw) {
        if (mb_strpos($msg, $kw) !== false) {
            return ['key' => 'search_terrain', 'response' => null, 'action' => 'search_terrains', 'source' => 'auto'];
        }
    }

    // 3. Fallback hardcodé pour les questions fréquentes
    $fallback = [
        'prix' => ['prix', 'coût', 'cout', 'combien', 'tarif', 'cher', '€', 'euro'],
        'delai' => ['délai', 'delai', 'durée', 'duree', 'temps', 'quand', 'livraison', 'construction'],
        'financement' => ['financement', 'prêt', 'pret', 'ptz', 'crédit', 'credit', 'banque', 'aide', 'mensualité'],
        'rdv' => ['rendez-vous', 'rdv', 'rencontrer', 'agence', 'visite', 'appeler', 'téléphone'],
        'garantie' => ['garantie', 'qualité', 'norme', 'assurance', 'décennale', 're2020'],
    ];

    $responses = [
        'prix' => "💰 **Nos maisons démarrent à partir de 125 000 € pour 80m².**\n\nLe prix varie selon le modèle, la surface et les options. Nos 6 modèles couvrent de 88 à 130m².\n\n**Voulez-vous que je cherche les modèles dans votre budget ?**",
        'delai' => "⏱️ **Délai moyen : 8 à 12 mois** du permis de construire à la remise des clés.\n\n• Étude + permis : 2-3 mois\n• Construction : 6-8 mois\n• Finitions : 1 mois\n\n**Nos délais sont contractuels et garantis !**",
        'financement' => "💡 **Aides disponibles pour votre projet :**\n\n• **PTZ** : Prêt à Taux Zéro (sous conditions)\n• **Prêt Action Logement** : jusqu'à 40 000€\n• **TVA réduite** dans certaines zones\n\nNotre partenaire bancaire vous accompagne gratuitement !",
        'rdv' => "📅 **Prenons rendez-vous !**\n\nNous pouvons vous recevoir :\n• À l'agence de Longueil-Annel (60)\n• Chez vous (déplacement gratuit)\n• En visioconférence\n\nOuvert du lundi au vendredi, 9h-18h.",
        'garantie' => "✅ **Vos garanties ORCA :**\n\n• Garantie décennale (10 ans)\n• Garantie biennale (2 ans)\n• Assurance dommages-ouvrage\n• Constructeur depuis 1993\n• Norme RE2020\n\nPlus de 30 ans de savoir-faire !",
    ];

    foreach ($fallback as $key => $keywords) {
        foreach ($keywords as $kw) {
            if (mb_strpos($msg, $kw) !== false) {
                return ['key' => $key, 'response' => $responses[$key], 'action' => null, 'source' => 'fallback'];
            }
        }
    }

    return null;
}

/**
 * Extraire des critères de recherche depuis un message libre
 * Ex: "maison 3 chambres 180000€" → ['nb_chambres' => 3, 'budget' => 180000]
 */
function chatbotExtractCriteria($message) {
    $criteria = [];

    // Extraire nombre de chambres
    if (preg_match('/(\d+)\s*(?:chambre|ch\b|pièce)/i', $message, $m)) {
        $criteria['nb_chambres'] = intval($m[1]);
    }

    // Extraire budget/prix
    if (preg_match('/(\d[\d\s]*)\s*(?:€|euro|000)/i', $message, $m)) {
        $num = intval(preg_replace('/\s/', '', $m[1]));
        if ($num < 1000) $num *= 1000; // "180" → 180000
        $criteria['budget'] = $num;
    }

    // Extraire surface
    if (preg_match('/(\d+)\s*m[²2]/i', $message, $m)) {
        $criteria['surface'] = intval($m[1]);
    }

    // Extraire département
    if (preg_match('/\b(60|77|95|02|80|91|92|93|94)\b/', $message, $m)) {
        $criteria['departement'] = $m[1];
    }

    // Type de maison
    if (preg_match('/plain[\s-]?pied/i', $message)) {
        $criteria['type_maison'] = 'plain-pied';
    } elseif (preg_match('/étage|etage/i', $message)) {
        $criteria['type_maison'] = '1-etage';
    }

    return $criteria;
}

// ======================================================
// VALIDATION & NORMALISATION
// ======================================================

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

function chatbotNormalizePhone($phone) {
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 10) {
        return substr($digits, 0, 2) . ' ' . substr($digits, 2, 2) . ' ' .
               substr($digits, 4, 2) . ' ' . substr($digits, 6, 2) . ' ' . substr($digits, 8, 2);
    }
    return $phone;
}

// ======================================================
// CRÉATION DE LEAD
// ======================================================

function chatbotCreateLead($conversation_id, $data) {
    global $pdo;

    try {
        $terrainPrevu = in_array(($data['terrain'] ?? ''), ['oui', '1', 'true'], true) ? 1 : 0;
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $pageSource = $_SERVER['HTTP_REFERER'] ?? 'chatbot';

        $stmt = $pdo->prepare("INSERT INTO leads
            (nom, prenom, email, telephone, departement, surface_souhaitee, budget_estime,
             terrain_prevu, type_demande, source, page_source, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'devis', 'chatbot', ?, ?, NOW())");

        $stmt->execute([
            $data['nom'] ?? '',
            $data['prenom'] ?? '',
            $data['email'] ?? '',
            $data['telephone'] ?? '',
            $data['departement'] ?? '',
            $data['surface'] ?? ($data['surface_souhaitee'] ?? ''),
            $data['budget'] ?? ($data['budget_estime'] ?? ''),
            $terrainPrevu,
            $pageSource,
            $ip
        ]);

        $lead_id = $pdo->lastInsertId();

        $pdo->prepare("UPDATE chatbot_conversations SET lead_id = ?, is_active = 0, ended_at = NOW() WHERE id = ?")
            ->execute([$lead_id, $conversation_id]);

        return ['lead_id' => $lead_id, 'success' => true];
    } catch (Exception $e) {
        error_log('Chatbot lead creation error: ' . $e->getMessage());
        return ['lead_id' => null, 'success' => false, 'error' => $e->getMessage()];
    }
}
