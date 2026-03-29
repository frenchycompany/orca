<?php
/**
 * Admin - Relances automatiques du Chatbot
 */
require_once '../includes/config.php';
require_once '../includes/chatbot-advanced.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Chatbot - Relances Automatiques';
$message = '';

// Marquer comme envoyé
if (isset($_GET['sent'])) {
    $id = intval($_GET['sent']);
    $pdo->prepare("UPDATE chatbot_followups SET status = 'sent', sent_at = NOW() WHERE id = ?")
        ->execute([$id]);
    header('Location: chatbot-followups.php?msg=sent');
    exit;
}

// Marquer comme converti
if (isset($_GET['converted'])) {
    $id = intval($_GET['converted']);
    $pdo->prepare("UPDATE chatbot_followups SET status = 'converted' WHERE id = ?")
        ->execute([$id]);
    header('Location: chatbot-followups.php?msg=converted');
    exit;
}

// Annuler une relance
if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    $pdo->prepare("UPDATE chatbot_followups SET status = 'cancelled' WHERE id = ?")
        ->execute([$id]);
    header('Location: chatbot-followups.php?msg=cancelled');
    exit;
}

// Traiter le message personnalisé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_custom'])) {
    $followup_id = intval($_POST['followup_id']);
    $subject = $_POST['subject'];
    $content = $_POST['content'];
    
    // Récupérer les données du lead
    $followup = $pdo->prepare("SELECT * FROM chatbot_followups WHERE id = ?")
                    ->execute([$followup_id])->fetch();
    
    if ($followup) {
        $lead_data = json_decode($followup['lead_data'], true);
        $to = $lead_data['email'];
        
        // Envoyer l'email
        $headers = 'From: ORCA <contact@maisons-orca.fr>' . "\r\n";
        $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        
        if (@mail($to, $subject, $content, $headers)) {
            $pdo->prepare("UPDATE chatbot_followups SET status = 'sent', sent_at = NOW(), email_subject = ?, email_content = ? WHERE id = ?")
                ->execute([$subject, $content, $followup_id]);
            $message = 'Email envoyé avec succès !';
        } else {
            $message = 'Erreur lors de l\'envoi de l\'email.';
        }
    }
}

// Messages
if ($_GET['msg'] ?? '' === 'sent') $message = 'Relance marquée comme envoyée.';
if ($_GET['msg'] ?? '' === 'converted') $message = 'Lead marqué comme converti.';
if ($_GET['msg'] ?? '' === 'cancelled') $message = 'Relance annulée.';

// Récupérer les relances
$filter = $_GET['filter'] ?? 'pending';
$where = $filter === 'all' ? '' : "WHERE f.status = '$filter'";

$followups = $pdo->query("SELECT f.*, c.session_id, c.started_at as conversation_date
                         FROM chatbot_followups f
                         JOIN chatbot_conversations c ON f.conversation_id = c.id
                         $where
                         ORDER BY f.followup_date ASC, f.priority DESC")
                 ->fetchAll();

// Statistiques
$stats = $pdo->query("SELECT 
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM chatbot_followups")->fetch();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">📬 Relances Automatiques</h1>
    
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background: #fff3cd; padding: 20px; border-radius: 12px;">
            <div style="font-size: 32px; font-weight: 700;"><?php echo $stats['pending']; ?></div>
            <div style="color: #856404; font-size: 14px;">En attente</div>
        </div>
        <div class="stat-card" style="background: #d4edda; padding: 20px; border-radius: 12px;">
            <div style="font-size: 32px; font-weight: 700; color: #155724;"><?php echo $stats['sent']; ?></div>
            <div style="color: #155724; font-size: 14px;">Envoyées</div>
        </div>
        <div class="stat-card" style="background: #cce5ff; padding: 20px; border-radius: 12px;">
            <div style="font-size: 32px; font-weight: 700; color: #004085;"><?php echo $stats['converted']; ?></div>
            <div style="color: #004085; font-size: 14px;">Converties</div>
        </div>
        <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 12px;">
            <div style="font-size: 32px; font-weight: 700;"><?php echo $stats['cancelled']; ?></div>
            <div style="color: #666; font-size: 14px;">Annulées</div>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="admin-section" style="margin-bottom: 20px;">
        <div style="display: flex; gap: 10px;">
            <a href="?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-outline';">">⏳ En attente</a>
            <a href="?filter=sent" class="btn <?php echo $filter === 'sent' ? 'btn-primary' : 'btn-outline';">">📤 Envoyées</a>
            <a href="?filter=converted" class="btn <?php echo $filter === 'converted' ? 'btn-primary' : 'btn-outline';">">✅ Converties</a>
            <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline';">">Toutes</a>
        </div>
    </div>
    
    <!-- Liste des relances -->
    <div class="admin-section">
        <h2 style="margin-bottom: 20px; font-size: 18px;">Relances <?php echo $filter === 'all' ? '' : '(' . ucfirst($filter) . ')'; ?></h2>
        
        <?php if (empty($followups)): ?>
        <p style="color: #999; text-align: center; padding: 40px;">Aucune relance dans cette catégorie.</p>
        <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <?php foreach ($followups as $f): 
                $data = json_decode($f['lead_data'], true);
                $is_overdue = strtotime($f['followup_date']) < time() && $f['status'] === 'pending';
            ?>
            <div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; background: <?php echo $is_overdue ? '#fff3cd' : 'white'; ?>;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                    <div>
                        <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 5px;">
                            <strong><?php echo htmlspecialchars(($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? '')); ?></strong>
                            <span style="background: <?php echo $f['priority'] === 'high' ? '#dc3545' : ($f['priority'] === 'medium' ? '#ffc107' : '#6c757d'); ?>; 
                                         color: white; padding: 2px 10px; border-radius: 12px; font-size: 11px;">
                                <?php echo ucfirst($f['priority']); ?>
                            </span>
                            <span style="background: #e0e0e0; padding: 2px 10px; border-radius: 12px; font-size: 11px;">
                                <?php echo ucfirst($f['status']); ?>
                            </span>
                        </div>
                        <div style="font-size: 13px; color: #666;">
                            📧 <?php echo htmlspecialchars($data['email'] ?? 'N/A'); ?> • 
                            📞 <?php echo htmlspecialchars($data['telephone'] ?? 'N/A'); ?> • 
                            📍 <?php echo htmlspecialchars($data['departement'] ?? 'N/A'); ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 13px; color: #666;">
                            Relance prévue : <?php echo date('d/m/Y H:i', strtotime($f['followup_date'])); ?>
                        </div>
                        <?php if ($is_overdue): ?>
                        <div style="color: #dc3545; font-size: 12px; font-weight: 600;">⚠️ EN RETARD</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Détails du projet -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px; font-size: 13px;">
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <span>🏠 Surface: <?php echo $data['surface'] ?? 'N/A'; ?> m²</span>
                        <span>💰 Budget: <?php echo $data['budget'] ?? 'N/A'; ?> €</span>
                        <span>🌿 Terrain: <?php echo ($data['terrain'] ?? '') === 'oui' ? 'Oui' : 'Non'; ?></span>
                        <span>📅 Délai: <?php echo $data['delai'] ?? 'N/A'; ?></span>
                    </div>
                </div>
                
                <!-- Actions -->
                <?php if ($f['status'] === 'pending'): ?>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="showEmailForm(<?php echo $f['id']; ?>)">
                        ✉️ Envoyer un email
                    </button>
                    <a href="?sent=<?php echo $f['id']; ?>" class="btn btn-sm" style="background: #6c757d; color: white;">✓ Marquer envoyé</a>
                    <a href="?converted=<?php echo $f['id']; ?>" class="btn btn-sm" style="background: #28a745; color: white;">🎯 Converti</a>
                    <a href="?cancel=<?php echo $f['id']; ?>" class="btn btn-sm btn-danger">Annuler</a>
                </div>
                
                <!-- Formulaire email caché -->
                <div id="email-form-<?php echo $f['id']; ?>" style="display: none; margin-top: 15px; padding: 15px; background: #e3f2fd; border-radius: 8px;">
                    <form method="POST">
                        <input type="hidden" name="followup_id" value="<?php echo $f['id']; ?>">
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Objet</label>
                            <input type="text" name="subject" class="form-control" 
                                   value="Votre projet de construction ORCA - <?php echo htmlspecialchars($data['prenom'] ?? ''); ?>"
                                   required>
                        </div>
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Message</label>
                            <textarea name="content" class="form-control" rows="6" required><?php 
$default_msg = "Bonjour " . ($data['prenom'] ?? '') . ",\n\n";
$default_msg .= "Nous avons bien pris note de votre projet de construction d'une maison de " . ($data['surface'] ?? '') . "m².\n\n";
$default_msg .= "Un conseiller ORCA sera ravi de vous accompagner dans votre démarche.\n\n";
$default_msg .= "Pouvons-nous nous appeler cette semaine pour en discuter ?\n\n";
$default_msg .= "Cordialement,\nL'équipe ORCA\nTél: 03 44 00 00 00";
echo $default_msg;
?></textarea>
                        </div>
                        <button type="submit" name="send_custom" class="btn btn-primary btn-sm">📤 Envoyer</button>
                    </form>
                </div>
                <?php else: ?>
                <div style="font-size: 13px; color: #666;">
                    <?php if ($f['sent_at']): ?>📤 Envoyé le <?php echo date('d/m/Y H:i', strtotime($f['sent_at'])); endif; ?>
                    <?php if ($f['email_subject']): ?><br><strong>Sujet:</strong> <?php echo htmlspecialchars($f['email_subject']); endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showEmailForm(id) {
    const form = document.getElementById('email-form-' + id);
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

<?php include 'includes/admin-footer.php'; ?>
