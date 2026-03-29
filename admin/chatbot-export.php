<?php
/**
 * Admin - Export des données Chatbot
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$action = $_GET['action'] ?? 'form';
$period = $_GET['period'] ?? '30';
$date_start = date('Y-m-d', strtotime("-$period days"));

// Export CSV
if ($action === 'download') {
    $type = $_GET['type'] ?? 'conversations';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="chatbot_' . $type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    if ($type === 'conversations') {
        fputcsv($output, ['ID', 'Session', 'Démarré', 'Terminé', 'Score', 'Lead ID', 'IP', 'Source']);
        
        $stmt = $pdo->prepare("SELECT * FROM chatbot_conversations WHERE started_at >= ? ORDER BY started_at DESC");
        $stmt->execute([$date_start]);
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['id'],
                $row['session_id'],
                $row['started_at'],
                $row['ended_at'] ?? 'En cours',
                $row['completion_score'],
                $row['lead_id'] ?? 'Non',
                $row['ip_address'],
                $row['page_source'] ?? 'Direct'
            ]);
        }
    } elseif ($type === 'messages') {
        fputcsv($output, ['ID Conversation', 'Type', 'Message', 'Intention', 'Timestamp']);
        
        $stmt = $pdo->prepare("SELECT m.*, c.session_id 
                              FROM chatbot_messages m 
                              JOIN chatbot_conversations c ON m.conversation_id = c.id 
                              WHERE c.started_at >= ? 
                              ORDER BY m.timestamp ASC");
        $stmt->execute([$date_start]);
        
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['conversation_id'],
                $row['type'],
                $row['message'],
                $row['intention_detected'] ?? 'N/A',
                $row['timestamp']
            ]);
        }
    } elseif ($type === 'leads') {
        fputcsv($output, ['ID Lead', 'ID Conversation', 'Nom', 'Téléphone', 'Email', 'Département', 'Budget', 'Délai', 'Score Qualité', 'Date']);
        
        $stmt = $pdo->prepare("SELECT c.lead_id, c.data_collected, c.completion_score, c.started_at 
                              FROM chatbot_conversations c 
                              WHERE c.lead_id IS NOT NULL AND c.started_at >= ?
                              ORDER BY c.started_at DESC");
        $stmt->execute([$date_start]);
        
        while ($row = $stmt->fetch()) {
            $data = json_decode($row['data_collected'], true);
            fputcsv($output, [
                $row['lead_id'],
                $row['lead_id'], // conversation id
                ($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? ''),
                $data['telephone'] ?? '',
                $data['email'] ?? '',
                $data['departement'] ?? '',
                $data['budget'] ?? '',
                $data['delai'] ?? '',
                $row['completion_score'],
                $row['started_at']
            ]);
        }
    }
    
    fclose($output);
    exit;
}

$page_title = 'Chatbot - Export de Données';
include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">📥 Export de Données</h1>
    
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px;">
        <!-- Export Conversations -->
        <div class="admin-section" style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 15px;">💬</div>
            <h2 style="font-size: 18px; margin-bottom: 10px;">Conversations</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                Export complet des conversations avec métriques de complétion
            </p>
            <a href="?action=download&type=conversations&period=<?php echo $period; ?>" class="btn btn-primary">
                📥 Télécharger CSV
            </a>
        </div>
        
        <!-- Export Messages -->
        <div class="admin-section" style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 15px;">📝</div>
            <h2 style="font-size: 18px; margin-bottom: 10px;">Messages</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                Tous les messages échangés avec détection d'intentions
            </p>
            <a href="?action=download&type=messages&period=<?php echo $period; ?>" class="btn btn-primary">
                📥 Télécharger CSV
            </a>
        </div>
        
        <!-- Export Leads -->
        <div class="admin-section" style="text-align: center;">
            <div style="font-size: 48px; margin-bottom: 15px;">🎯</div>
            <h2 style="font-size: 18px; margin-bottom: 10px;">Leads Qualifiés</h2>
            <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                Données des leads générés avec score de qualité
            </p>
            <a href="?action=download&type=leads&period=<?php echo $period; ?>" class="btn btn-primary">
                📥 Télécharger CSV
            </a>
        </div>
    </div>
    
    <!-- API Access -->
    <div class="admin-section" style="margin-top: 30px;">
        <h2 style="margin-bottom: 20px; font-size: 18px;">🔌 Accès API JSON</h2>
        
        <p style="margin-bottom: 20px;">Utilisez ces URLs pour intégrer les données dans vos outils (Power BI, Google Sheets, etc.)</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">API Conversations (JSON)</label>
                <code style="display: block; background: white; padding: 10px; border-radius: 4px; word-break: break-all;">
                    https://<?php echo $_SERVER['HTTP_HOST']; ?>/chatbot/api.php?action=admin_stats&key=VOTRE_CLE_API
                </code>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Endpoint Données Temps Réel</label>
                <code style="display: block; background: white; padding: 10px; border-radius: 4px; word-break: break-all;">
                    https://<?php echo $_SERVER['HTTP_HOST']; ?>/chatbot/api.php?action=live_stats&key=VOTRE_CLE_API
                </code>
            </div>
        </div>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; font-size: 13px;">
            <strong>💡 Astuce :</strong> Pour créer une clé API, ajoutez une entrée <code>chatbot_api_key</code> dans la table config.
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
