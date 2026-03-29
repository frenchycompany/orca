<?php
/**
 * Fonctions du Chatbot ORCA - Version intelligente
 * Scénarios complets pré-établis + recherche BDD
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

    if ($conv) return array_merge($conv, ['is_new' => false]);

    $pdo->prepare("INSERT INTO chatbot_conversations
                  (session_id, ip_address, page_source, current_step, data_collected, started_at, last_activity)
                  VALUES (?, ?, ?, 1, '{}', NOW(), NOW())")->execute([$sid, $ip, $page]);

    return ['id' => $pdo->lastInsertId(), 'current_step' => 1, 'data_collected' => '{}', 'is_new' => true];
}

function chatbotGetConversation($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function chatbotUpdateStep($cid, $step) {
    global $pdo;
    $pdo->prepare("UPDATE chatbot_conversations SET current_step = ?, last_activity = NOW() WHERE id = ?")->execute([$step, $cid]);
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
// SCÉNARIOS COMPLETS PRÉ-ÉTABLIS
// Chaque parcours guide l'utilisateur de A à Z
// et termine TOUJOURS par le formulaire de coordonnées
// ======================================================

function chatbotGetScenario() {
    return [
        // ===================== ACCUEIL =====================
        1 => [
            'type' => 'buttons',
            'message' => "Bonjour ! 👋 Je suis l'assistant ORCA, constructeur de maisons depuis 1993.\n\nComment puis-je vous aider ?",
            'options' => [
                ['label' => '🏠 Je cherche une maison', 'value' => 'go_maison', 'next' => 10],
                ['label' => '🌿 Je cherche un terrain', 'value' => 'go_terrain', 'next' => 20],
                ['label' => '💰 Connaître les prix', 'value' => 'go_prix', 'next' => 30],
                ['label' => '❓ J\'ai une question', 'value' => 'go_question', 'next' => 40]
            ]
        ],

        // ===================== PARCOURS MAISON =====================
        10 => [
            'type' => 'buttons',
            'message' => 'Super ! Quel style de maison vous plaît ?',
            'field' => 'type_maison',
            'options' => [
                ['label' => '🏠 Plain-pied (tout de plain-pied)', 'value' => 'plain-pied', 'next' => 11],
                ['label' => '🏡 Avec étage (plus de surface)', 'value' => '1-etage', 'next' => 11],
                ['label' => '🤷 Je ne sais pas encore', 'value' => 'tous', 'next' => 11]
            ]
        ],
        11 => [
            'type' => 'buttons',
            'message' => 'Combien de chambres vous faut-il ?',
            'field' => 'nb_chambres',
            'options' => [
                ['label' => '2 chambres', 'value' => '2', 'next' => 12],
                ['label' => '3 chambres', 'value' => '3', 'next' => 12],
                ['label' => '4 chambres ou +', 'value' => '4', 'next' => 12]
            ]
        ],
        12 => [
            'type' => 'buttons',
            'message' => 'Quel est votre budget pour la maison (hors terrain) ?',
            'field' => 'budget',
            'options' => [
                ['label' => 'Moins de 155 000 €', 'value' => '155000', 'next' => 'results_maison'],
                ['label' => '155 000 - 185 000 €', 'value' => '185000', 'next' => 'results_maison'],
                ['label' => '185 000 - 220 000 €', 'value' => '220000', 'next' => 'results_maison'],
                ['label' => 'Plus de 220 000 €', 'value' => '250000', 'next' => 'results_maison']
            ]
        ],

        // ===================== PARCOURS TERRAIN =====================
        20 => [
            'type' => 'buttons',
            'message' => 'Dans quel département cherchez-vous un terrain ?',
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
            'message' => 'Budget terrain ?',
            'field' => 'budget_terrain',
            'options' => [
                ['label' => 'Moins de 50 000 €', 'value' => '50000', 'next' => 'results_terrain'],
                ['label' => '50 000 - 80 000 €', 'value' => '80000', 'next' => 'results_terrain'],
                ['label' => '80 000 - 120 000 €', 'value' => '120000', 'next' => 'results_terrain'],
                ['label' => 'Pas de limite', 'value' => '999999', 'next' => 'results_terrain']
            ]
        ],

        // ===================== PARCOURS PRIX =====================
        30 => [
            'type' => 'buttons',
            'message' => "💰 **Nos prix de départ :**\n\n🏠 **Plain-pied :**\n• Le Coquelicot (88m², 3 ch.) → 145 000 €\n• La Tulipe (95m², 3 ch.) → 152 000 €\n• L'Hibiscus (102m², 3 ch.) → 168 000 €\n\n🏡 **Avec étage :**\n• L'Orchidée (110m², 3 ch.) → 178 000 €\n• Le Lila (120m², 4 ch.) → 185 000 €\n• Le Magnolia (130m², 4 ch.) → 215 000 €\n\n*Prix hors terrain, hors options.*\n\nQue souhaitez-vous faire ?",
            'options' => [
                ['label' => '🏠 Choisir un modèle', 'value' => 'go_maison', 'next' => 10],
                ['label' => '🌿 Trouver un terrain', 'value' => 'go_terrain', 'next' => 20],
                ['label' => '📋 Recevoir une estimation', 'value' => 'go_form', 'next' => 50],
                ['label' => '❓ J\'ai une question', 'value' => 'go_question', 'next' => 40]
            ]
        ],

        // ===================== QUESTIONS LIBRES =====================
        40 => [
            'type' => 'text',
            'message' => "Posez-moi votre question ! 😊\n\nJe connais nos maisons, les terrains disponibles, les prix, les délais, le financement...",
        ],

        // ===================== FORMULAIRE =====================
        50 => [
            'type' => 'form',
            'message' => "Pour recevoir votre estimation personnalisée et être recontacté par un conseiller, remplissez ce formulaire :",
        ],

        // ===================== CONFIRMATION =====================
        55 => [
            'type' => 'final',
            'message' => "🎉 **Merci {{prenom}} !**\n\nVotre demande a bien été enregistrée.\n📞 **Un conseiller ORCA vous contactera sous 24h.**",
            'options' => [
                ['label' => '🏠 Voir nos modèles', 'value' => 'voir_modeles', 'action' => 'link', 'url' => '/modeles.php'],
                ['label' => '❌ Fermer', 'value' => 'fermer', 'action' => 'close']
            ]
        ]
    ];
}

// ======================================================
// RECHERCHE MODÈLES EN BDD
// ======================================================

function chatbotSearchModeles($data) {
    global $pdo;

    try {
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
            // Chercher les modèles dans le budget (avec marge de 10%)
            $where[] = '(prix_base IS NOT NULL AND prix_base <= ?)';
            $params[] = intval($budget * 1.1);
        }

        $sql = "SELECT nom, slug, surface_habitable, nb_chambres, nb_etages, style, prix_base, prix_afficher, slogan
                FROM modeles WHERE " . implode(' AND ', $where) . " ORDER BY prix_base ASC LIMIT 6";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        // Si aucun résultat avec le budget, chercher TOUS les modèles correspondant au type/chambres
        if (empty($results) && $budget > 0) {
            $where2 = ['is_active = 1'];
            $params2 = [];
            if ($type && $type !== 'tous') { $where2[] = 'nb_etages = ?'; $params2[] = $type; }
            if ($chambres > 0) { $where2[] = 'nb_chambres >= ?'; $params2[] = $chambres; }

            $sql2 = "SELECT nom, slug, surface_habitable, nb_chambres, nb_etages, style, prix_base, prix_afficher, slogan
                     FROM modeles WHERE " . implode(' AND ', $where2) . " ORDER BY prix_base ASC LIMIT 6";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute($params2);
            $results = $stmt2->fetchAll();
        }

        return $results;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Formater les résultats modèles
 */
function chatbotFormatModeles($modeles, $budget = 0) {
    if (empty($modeles)) {
        return "Nous n'avons pas encore de modèle en base, mais nos conseillers ont plein de solutions pour vous !";
    }

    $inBudget = [];
    $aboveBudget = [];

    foreach ($modeles as $m) {
        $prix = $m['prix_base'] ? intval($m['prix_base']) : 0;
        if ($budget > 0 && $prix > $budget) {
            $aboveBudget[] = $m;
        } else {
            $inBudget[] = $m;
        }
    }

    $text = '';

    if (!empty($inBudget)) {
        $text .= "🏠 **" . count($inBudget) . " modèle(s) dans votre budget :**\n\n";
        foreach ($inBudget as $m) {
            $prix = $m['prix_afficher'] ?: number_format($m['prix_base'], 0, ',', ' ') . ' €';
            $etage = $m['nb_etages'] === 'plain-pied' ? 'Plain-pied' : 'Avec étage';
            $text .= "**{$m['nom']}** — {$m['surface_habitable']}m², {$m['nb_chambres']} ch., {$etage}\n";
            $text .= "→ {$prix}\n\n";
        }
    }

    if (!empty($aboveBudget) && empty($inBudget)) {
        $text .= "Aucun modèle exactement dans ce budget, mais voici les plus proches :\n\n";
        foreach ($aboveBudget as $m) {
            $prix = $m['prix_afficher'] ?: number_format($m['prix_base'], 0, ',', ' ') . ' €';
            $etage = $m['nb_etages'] === 'plain-pied' ? 'Plain-pied' : 'Avec étage';
            $text .= "**{$m['nom']}** — {$m['surface_habitable']}m², {$m['nb_chambres']} ch., {$etage}\n";
            $text .= "→ {$prix}\n\n";
        }
        $text .= "💡 *Nos conseillers peuvent adapter les modèles à votre budget !*\n";
    } elseif (!empty($aboveBudget)) {
        $text .= "Et avec un peu plus de budget :\n\n";
        foreach (array_slice($aboveBudget, 0, 2) as $m) {
            $prix = $m['prix_afficher'] ?: number_format($m['prix_base'], 0, ',', ' ') . ' €';
            $text .= "**{$m['nom']}** — {$m['surface_habitable']}m², {$m['nb_chambres']} ch. → {$prix}\n";
        }
        $text .= "\n";
    }

    return $text;
}

// ======================================================
// RECHERCHE TERRAINS EN BDD
// ======================================================

function chatbotSearchTerrains($data) {
    global $pdo;

    try {
        $where = ['is_available = 1'];
        $params = [];

        $dept = $data['departement'] ?? '';
        if ($dept) { $where[] = 'departement = ?'; $params[] = $dept; }

        $budget = intval($data['budget_terrain'] ?? 0);
        if ($budget > 0 && $budget < 999999) { $where[] = 'prix <= ?'; $params[] = $budget; }

        $sql = "SELECT reference, ville, code_postal, departement, surface, prix, est_viabilise, proximite
                FROM terrains WHERE " . implode(' AND ', $where) . " ORDER BY prix ASC LIMIT 5";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function chatbotFormatTerrains($terrains) {
    if (empty($terrains)) {
        return "Aucun terrain disponible pour ces critères actuellement.\n\n💡 **Nos conseillers recherchent en permanence de nouveaux terrains.** Laissez vos coordonnées et on vous préviendra dès qu'on a quelque chose !";
    }
    $text = "🌿 **" . count($terrains) . " terrain(s) disponible(s) :**\n\n";
    foreach ($terrains as $t) {
        $prix = number_format($t['prix'], 0, ',', ' ') . ' €';
        $viab = $t['est_viabilise'] ? '✅ Viabilisé' : '⚠️ À viabiliser';
        $text .= "**{$t['ville']}** ({$t['departement']}) — {$t['surface']}m² — {$prix}\n";
        $text .= "{$viab}";
        if ($t['proximite']) $text .= " | 📍 {$t['proximite']}";
        $text .= "\n\n";
    }
    return $text;
}

// ======================================================
// DÉTECTION D'INTENTION (BDD puis fallback)
// ======================================================

function chatbotDetectIntention($message) {
    global $pdo;
    $msg = mb_strtolower(trim($message));

    // 1. Chercher dans chatbot_intentions (BDD)
    try {
        $stmt = $pdo->query("SELECT * FROM chatbot_intentions WHERE is_active = 1 ORDER BY priority DESC");
        foreach ($stmt->fetchAll() as $intent) {
            $keywords = array_map('trim', explode(',', mb_strtolower($intent['keywords'])));
            foreach ($keywords as $kw) {
                if ($kw !== '' && mb_strpos($msg, $kw) !== false) {
                    return [
                        'key' => $intent['intention_key'],
                        'response' => $intent['response_text'],
                        'action' => $intent['action'] ?? null
                    ];
                }
            }
        }
    } catch (Exception $e) {}

    // 2. Fallback hardcodé
    $fallback = [
        'prix' => [
            'kw' => ['prix', 'coût', 'cout', 'combien', 'tarif', 'cher', '€', 'euro'],
            'text' => "💰 **Nos maisons démarrent à 145 000 € (88m², plain-pied).**\n\nGamme complète de 145 000 € à 215 000 € selon la surface et le nombre de chambres.\n\n*Prix hors terrain, hors options. Consultez la grille complète !*"
        ],
        'delai' => [
            'kw' => ['délai', 'delai', 'durée', 'duree', 'temps', 'quand', 'livraison', 'mois'],
            'text' => "⏱️ **Délai moyen : 8 à 12 mois** (permis + construction).\n\n• Étude et permis : 2-3 mois\n• Gros oeuvre : 4-5 mois\n• Second oeuvre + finitions : 2-3 mois\n\n**Nos délais sont contractuels et garantis.**"
        ],
        'financement' => [
            'kw' => ['financement', 'prêt', 'pret', 'ptz', 'crédit', 'credit', 'banque', 'aide', 'mensualité'],
            'text' => "💡 **Aides disponibles :**\n\n• **PTZ** : Prêt à Taux Zéro (sous conditions)\n• **Prêt Action Logement** : jusqu'à 40 000 €\n• **TVA réduite** dans certaines zones\n\nNotre partenaire bancaire vous accompagne gratuitement !"
        ],
        'rdv' => [
            'kw' => ['rendez-vous', 'rdv', 'rencontrer', 'agence', 'visite', 'appeler'],
            'text' => "📅 **Prenons rendez-vous !**\n\n• À l'agence de Longueil-Annel (60)\n• Chez vous (déplacement gratuit)\n• En visioconférence\n\nOuvert du lundi au vendredi, 9h-18h."
        ],
        'garantie' => [
            'kw' => ['garantie', 'qualité', 'norme', 'assurance', 'décennale', 're2020'],
            'text' => "✅ **Garanties ORCA :**\n\n• Garantie décennale (10 ans)\n• Garantie biennale (2 ans)\n• Assurance dommages-ouvrage\n• Norme RE2020\n• Constructeur depuis 1993"
        ],
    ];

    foreach ($fallback as $key => $data) {
        foreach ($data['kw'] as $kw) {
            if (mb_strpos($msg, $kw) !== false) {
                return ['key' => $key, 'response' => $data['text'], 'action' => null];
            }
        }
    }

    return null;
}

// ======================================================
// VALIDATION & NORMALISATION
// ======================================================

function chatbotValidateInput($value, $type) {
    $value = trim($value);
    switch ($type) {
        case 'name':  return mb_strlen($value) >= 2 && preg_match('/^[\p{L}\s\-\']+$/u', $value);
        case 'email': return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        case 'phone': return preg_match('/^(0[1-9])[\s.-]?(\d{2}[\s.-]?){4}$/', $value);
        default: return true;
    }
}

function chatbotNormalizePhone($phone) {
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 10) {
        return implode(' ', str_split($digits, 2));
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
            $data['nom'] ?? '', $data['prenom'] ?? '', $data['email'] ?? '',
            $data['telephone'] ?? '', $data['departement'] ?? '',
            $data['surface'] ?? '', $data['budget'] ?? '',
            $terrainPrevu, $pageSource, $ip
        ]);

        $lead_id = $pdo->lastInsertId();
        $pdo->prepare("UPDATE chatbot_conversations SET lead_id = ?, is_active = 0, ended_at = NOW() WHERE id = ?")
            ->execute([$lead_id, $conversation_id]);

        return ['lead_id' => $lead_id, 'success' => true];
    } catch (Exception $e) {
        error_log('Chatbot lead error: ' . $e->getMessage());
        return ['lead_id' => null, 'success' => false, 'error' => $e->getMessage()];
    }
}
