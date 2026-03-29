<?php
/**
 * Admin - Paramètres du Chatbot
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Chatbot - Paramètres';
$message = '';

// Sauvegarder les paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configs = [
        'chatbot_openai_api_key' => $_POST['openai_api_key'] ?? '',
        'chatbot_openai_model' => $_POST['openai_model'] ?? 'gpt-4',
        'chatbot_enabled' => isset($_POST['enabled']) ? '1' : '0',
        'chatbot_auto_popup' => isset($_POST['auto_popup']) ? '1' : '0',
        'chatbot_popup_delay' => $_POST['popup_delay'] ?? '30',
        'chatbot_primary_color' => $_POST['primary_color'] ?? '#1a5653',
        'chatbot_welcome_message' => $_POST['welcome_message'] ?? 'Bonjour ! Je suis l\'assistant ORCA. Que souhaitez-vous faire ?',
        'chatbot_offline_message' => $_POST['offline_message'] ?? 'Un conseiller vous répondra dès que possible.',
        'chatbot_email_notifications' => isset($_POST['email_notifications']) ? '1' : '0',
        'chatbot_lead_threshold' => $_POST['lead_threshold'] ?? '70',
        'chatbot_n8n_enabled' => isset($_POST['n8n_enabled']) ? '1' : '0',
        'chatbot_n8n_email' => $_POST['n8n_email'] ?? 'ia@maisons-orca.fr',
        'chatbot_n8n_webhook_url' => $_POST['n8n_webhook_url'] ?? '',
    ];
    
    foreach ($configs as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
        $stmt->execute([$key, $value]);
    }
    
    $message = 'Paramètres sauvegardés avec succès !';
}

// Récupérer les valeurs actuelles
$settings = [];
$stmt = $pdo->query("SELECT cle, valeur FROM config WHERE cle LIKE 'chatbot_%'");
while ($row = $stmt->fetch()) {
    $settings[$row['cle']] = $row['valeur'];
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">⚙️ Paramètres du Chatbot</h1>
    
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form method="POST" class="admin-section">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Colonne gauche -->
            <div>
                <h2 style="margin-bottom: 20px; font-size: 18px;">🤖 Configuration Générale</h2>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="enabled" <?php echo ($settings['chatbot_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <span>Activer le chatbot</span>
                    </label>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="auto_popup" <?php echo ($settings['chatbot_auto_popup'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <span>Popup automatique après délai</span>
                    </label>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Délai popup (secondes)</label>
                    <input type="number" name="popup_delay" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['chatbot_popup_delay'] ?? '30'); ?>" min="5" max="300">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Couleur principale</label>
                    <input type="color" name="primary_color" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['chatbot_primary_color'] ?? '#1a5653'); ?>" style="width: 100px; height: 40px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Message de bienvenue</label>
                    <textarea name="welcome_message" class="form-control" rows="3"><?php echo htmlspecialchars($settings['chatbot_welcome_message'] ?? 'Bonjour ! Je suis l\'assistant ORCA. Que souhaitez-vous faire ?'); ?></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Message hors ligne</label>
                    <textarea name="offline_message" class="form-control" rows="2"><?php echo htmlspecialchars($settings['chatbot_offline_message'] ?? 'Un conseiller vous répondra dès que possible.'); ?></textarea>
                </div>
            </div>
            
            <!-- Colonne droite -->
            <div>
                <h2 style="margin-bottom: 20px; font-size: 18px;">🔮 Intégration n8n / IA</h2>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>🔗 Connexion n8n</strong>
                    <p style="margin: 10px 0 0 0; font-size: 13px;">
                        Envoyez automatiquement les leads à une boîte email dédiée pour traitement n8n.<br>
                        Format: JSON avec headers spéciaux pour faciliter le parsing.
                    </p>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="n8n_enabled" value="1" 
                               <?php echo ($settings['chatbot_n8n_enabled'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span>Activer l'envoi à n8n</span>
                    </label>
                    <small style="color: #666; display: block; margin-top: 5px;">
                        Envoie une copie JSON de chaque lead à l'email dédié
                    </small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Email dédiée n8n</label>
                    <input type="email" name="n8n_email" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['chatbot_n8n_email'] ?? 'ia@maisons-orca.fr'); ?>"
                           placeholder="ia@maisons-orca.fr">
                    <small style="color: #666;">Boîte email que n8n surveillera</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">URL Webhook n8n (optionnel)</label>
                    <input type="url" name="n8n_webhook_url" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['chatbot_n8n_webhook_url'] ?? ''); ?>"
                           placeholder="https://n8n.votre-site.fr/webhook/...">
                    <small style="color: #666;">Pour appel direct HTTP POST (plus rapide que l'email)</small>
                </div>
                
                <hr style="margin: 30px 0;">
                
                <h2 style="margin-bottom: 20px; font-size: 18px;">🤖 Préparation OpenAI (Futur)</h2>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>⚠️ Mode IA désactivé</strong>
                    <p style="margin: 10px 0 0 0; font-size: 13px;">
                        Ces paramètres sont préparés pour une future intégration GPT.<br>
                        Le chatbot fonctionne actuellement en mode "règles" (scénarios).
                    </p>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Clé API OpenAI</label>
                    <input type="password" name="openai_api_key" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['chatbot_openai_api_key'] ?? ''); ?>"
                           placeholder="sk-... (laisser vide pour mode actuel)">
                    <small style="color: #666;">Votre clé API OpenAI (pour futur usage GPT-4)</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Modèle OpenAI</label>
                    <select name="openai_model" class="form-control">
                        <option value="gpt-4" <?php echo ($settings['chatbot_openai_model'] ?? 'gpt-4') === 'gpt-4' ? 'selected' : ''; ?>>GPT-4 (Meilleur)</option>
                        <option value="gpt-3.5-turbo" <?php echo ($settings['chatbot_openai_model'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo (Moins cher)</option>
                    </select>
                    <small style="color: #666;">Sera utilisé quand vous activerez l'IA</small>
                </div>
                
                <hr style="margin: 30px 0;">
                
                <h2 style="margin-bottom: 20px; font-size: 18px;">📧 Notifications</h2>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="email_notifications" <?php echo ($settings['chatbot_email_notifications'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        <span>Recevoir un email à chaque lead</span>
                    </label>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Seuil de qualification (%)</label>
                    <input type="number" name="lead_threshold" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['chatbot_lead_threshold'] ?? '70'); ?>" min="0" max="100">
                    <small style="color: #666;">Score minimum pour considérer un lead comme "chaud"</small>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <button type="submit" class="btn btn-primary btn-lg">💾 Sauvegarder les paramètres</button>
        </div>
    </form>
    
    <!-- État du système -->
    <div class="admin-section" style="margin-top: 30px;">
        <h2 style="margin-bottom: 20px; font-size: 18px;">📊 État du Système</h2>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
            <div style="background: #d4edda; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 10px;">🤖</div>
                <div style="font-weight: 600; color: #155724;">Mode Actuel</div>
                <div style="font-size: 14px; color: #155724;">Scénarios (Règles)</div>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 10px;">🔮</div>
                <div style="font-weight: 600; color: #666;">Mode IA</div>
                <div style="font-size: 14px; color: #666;">
                    <?php echo empty($settings['chatbot_openai_api_key']) ? 'Non configuré' : 'Prêt (désactivé)'; ?>
                </div>
            </div>
            
            <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; margin-bottom: 10px;">📈</div>
                <div style="font-weight: 600; color: #0d47a1;">Version</div>
                <div style="font-size: 14px; color: #0d47a1;">2.0 - Intelligence Locale</div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
