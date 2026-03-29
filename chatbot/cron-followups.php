<?php
/**
 * CRON - Traitement des relances chatbot
 *
 * À exécuter toutes les heures via crontab :
 * 0 * * * * php /var/www/orca/chatbot/cron-followups.php >> /var/log/orca-followups.log 2>&1
 *
 * Ou une fois par jour à 9h :
 * 0 9 * * * php /var/www/orca/chatbot/cron-followups.php >> /var/log/orca-followups.log 2>&1
 */

// Charger la config
require_once __DIR__ . '/../includes/config.php';

echo "[" . date('Y-m-d H:i:s') . "] Début du traitement des followups chatbot\n";

// 1. Planifier les followups pour les conversations abandonnées (score > 0, pas de lead, inactives > 2h)
try {
    $stmt = $pdo->query("SELECT id FROM chatbot_conversations
        WHERE is_active = 1
        AND lead_id IS NULL
        AND completion_score > 0
        AND last_activity < DATE_SUB(NOW(), INTERVAL 2 HOUR)
        AND id NOT IN (SELECT conversation_id FROM chatbot_followups)
        LIMIT 50");

    $abandoned = $stmt->fetchAll();
    $planned = 0;

    foreach ($abandoned as $conv) {
        chatbotScheduleFollowup($conv['id']);
        // Marquer la conversation comme inactive
        $pdo->prepare("UPDATE chatbot_conversations SET is_active = 0 WHERE id = ?")->execute([$conv['id']]);
        $planned++;
    }

    echo "  → {$planned} followup(s) planifié(s)\n";
} catch (Exception $e) {
    echo "  → Erreur planification: " . $e->getMessage() . "\n";
}

// 2. Envoyer les followups en attente
$sent = chatbotProcessFollowups();
echo "  → {$sent} followup(s) envoyé(s)\n";

echo "[" . date('Y-m-d H:i:s') . "] Terminé\n\n";
