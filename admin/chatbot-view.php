<?php
/**
 * Admin - Vue d'une conversation chatbot
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: chatbot.php');
    exit;
}

// Récupérer la conversation
$stmt = $pdo->prepare("SELECT c.*, l.nom, l.prenom, l.email, l.telephone, l.id as lead_id_full
                       FROM chatbot_conversations c
                       LEFT JOIN leads l ON c.lead_id = l.id
                       WHERE c.id = ?");
$stmt->execute([$id]);
$conversation = $stmt->fetch();

if (!$conversation) {
    header('Location: chatbot.php');
    exit;
}

// Récupérer les messages
$stmt = $pdo->prepare("SELECT * FROM chatbot_messages 
                       WHERE conversation_id = ? 
                       ORDER BY created_at ASC");
$stmt->execute([$id]);
$messages = $stmt->fetchAll();

$page_title = 'Conversation #' . $id;

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Conversation #<?php echo $id; ?></h1>
        <div>
            <a href="chatbot-learn.php?conv=<?php echo $id; ?>" class="btn btn-primary" style="margin-right: 10px;">
                🧠 Apprendre de cette conversation
            </a>
            <a href="chatbot.php?delete=<?php echo $id; ?>" class="btn btn-danger" onclick="return confirm('Supprimer cette conversation ?')">
                🗑️ Supprimer
            </a>
            <a href="chatbot.php" class="btn btn-outline" style="margin-left: 10px;">← Retour</a>
        </div>
    </div>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'learned'): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        ✅ Le chatbot a appris ! Une nouvelle intention a été ajoutée à partir de cette conversation.
    </div>
    <?php endif; ?>
    
    <!-- Infos conversation -->
    <div class="admin-section" style="margin-bottom: 30px;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
            <div>
                <div style="font-size: 12px; color: #999; text-transform: uppercase;">Démarrée</div>
                <div style="font-weight: 600;"><?php echo date('d/m/Y H:i', strtotime($conversation['started_at'])); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #999; text-transform: uppercase;">Dernière activité</div>
                <div style="font-weight: 600;"><?php echo date('d/m/Y H:i', strtotime($conversation['last_activity'])); ?></div>
            </div>
            <div>
                <div style="font-size: 12px; color: #999; text-transform: uppercase;">Progression</div>
                <div style="font-weight: 600;"><?php echo $conversation['completion_score']; ?>%</div>
            </div>
            <div>
                <div style="font-size: 12px; color: #999; text-transform: uppercase;">IP</div>
                <div style="font-weight: 600;"><?php echo htmlspecialchars($conversation['ip_address']); ?></div>
            </div>
        </div>
        
        <?php if ($conversation['lead_id']): ?>
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
            <div style="font-size: 12px; color: #999; text-transform: uppercase; margin-bottom: 10px;">Lead généré</div>
            <div style="display: flex; gap: 20px; align-items: center;">
                <div>
                    <strong><?php echo htmlspecialchars(($conversation['prenom'] ?? '') . ' ' . ($conversation['nom'] ?? '')); ?></strong>
                </div>
                <div>📞 <?php echo htmlspecialchars($conversation['telephone']); ?></div>
                <div>✉️ <?php echo htmlspecialchars($conversation['email']); ?></div>
                <a href="leads.php?id=<?php echo $conversation['lead_id']; ?>" class="btn btn-sm btn-primary">Voir le lead</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Messages -->
    <div class="admin-section">
        <h2 style="margin-bottom: 20px; font-size: 18px;">Transcript</h2>
        
        <div style="display: flex; flex-direction: column; gap: 16px; max-height: 600px; overflow-y: auto; padding: 10px;">
            <?php foreach ($messages as $msg): ?>
            <div style="display: flex; <?php echo $msg['type'] === 'user' ? 'justify-content: flex-end' : 'justify-content: flex-start'; ?>;">
                <div style="max-width: 70%; padding: 12px 16px; border-radius: 16px;
                    <?php 
                    if ($msg['type'] === 'bot') {
                        echo 'background: #f0f0f0; color: #333; border-bottom-left-radius: 4px;';
                    } elseif ($msg['type'] === 'user') {
                        echo 'background: var(--color-primary); color: white; border-bottom-right-radius: 4px;';
                    } else {
                        echo 'background: #fff3cd; color: #856404; font-size: 12px;';
                    }
                    ?>">
                    <div style="font-size: 14px; line-height: 1.5;">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    
                    <?php if ($msg['intention_detected']): ?>
                    <div style="margin-top: 8px; font-size: 11px; opacity: 0.7;">
                        Intention: <?php echo htmlspecialchars($msg['intention_detected']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 4px; font-size: 11px; opacity: 0.6; text-align: right;">
                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Données collectées -->
    <?php if ($conversation['data_collected']): 
        $data = json_decode($conversation['data_collected'], true);
        if (!empty($data)):
    ?>
    <div class="admin-section" style="margin-top: 30px;">
        <h2 style="margin-bottom: 20px; font-size: 18px;">Données collectées</h2>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <?php foreach ($data as $key => $value): ?>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <div style="font-size: 11px; color: #999; text-transform: uppercase;"><?php echo htmlspecialchars($key); ?></div>
                <div style="font-weight: 600; margin-top: 5px;"><?php echo htmlspecialchars($value); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; endif; ?>
</div>

<?php include 'includes/admin-footer.php'; ?>
