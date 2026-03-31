<?php
/**
 * Admin - Apprendre d'une conversation
 * Permet d'ajouter une nouvelle intention basée sur une conversation existante
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$conv_id = intval($_GET['conv'] ?? 0);
if (!$conv_id) {
    header('Location: chatbot.php');
    exit;
}

// Récupérer la conversation
$stmt = $pdo->prepare("SELECT * FROM chatbot_conversations WHERE id = ?");
$stmt->execute([$conv_id]);
$conversation = $stmt->fetch();

if (!$conversation) {
    header('Location: chatbot.php');
    exit;
}

// Récupérer les messages utilisateur vraiment non reconnus
// Exclut les réponses aux boutons de scénario et la navigation
$stmt = $pdo->prepare("SELECT * FROM chatbot_messages
                       WHERE conversation_id = ? AND type = 'user'
                       AND (intention_detected IS NULL OR intention_detected = '')
                       AND LENGTH(TRIM(message)) > 3
                       AND LOWER(TRIM(message)) NOT REGEXP '^[0-9]+$'
                       AND LOWER(TRIM(message)) NOT IN ('go_maison','go_terrain','go_prix','go_question','go_form','autre','coord','fermer','oui','non',
                           'plain-pied','1-etage','tous','devis','modeles','rdv','maison','terrain','question',
                           '60','77','95','02','80','155000','185000','220000','250000','50000','80000','120000','999999')
                       ORDER BY created_at ASC");
$stmt->execute([$conv_id]);
$unrecognized_messages = $stmt->fetchAll();

// Récupérer tous les messages pour contexte
$stmt = $pdo->prepare("SELECT * FROM chatbot_messages 
                       WHERE conversation_id = ? 
                       ORDER BY created_at ASC");
$stmt->execute([$conv_id]);
$all_messages = $stmt->fetchAll();

$message = '';
$error = '';

// Traitement du formulaire d'apprentissage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $intention_key = strtolower(trim($_POST['intention_key'] ?? ''));
    $keywords = trim($_POST['keywords'] ?? '');
    $response_text = trim($_POST['response_text'] ?? '');
    $action = trim($_POST['action'] ?? '');
    $priority = intval($_POST['priority'] ?? 5);
    
    if (empty($intention_key) || empty($keywords) || empty($response_text)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO chatbot_intentions 
                (intention_key, keywords, response_text, action, priority) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$intention_key, $keywords, $response_text, $action, $priority]);
            
            // Rediriger vers la page de la conversation avec message de succès
            header('Location: chatbot-view.php?id=' . $conv_id . '&msg=learned');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Cette intention existe déjà. Modifiez-la dans la section "Apprentissage".';
            } else {
                $error = 'Erreur : ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Apprendre de la conversation #' . $conv_id;

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">🧠 Apprendre de cette conversation</h1>
        <a href="chatbot.php" class="btn btn-outline">← Retour aux conversations</a>
    </div>
    
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Colonne gauche : La conversation -->
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">📜 Conversation #<?php echo $conv_id; ?></h2>
            
            <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; max-height: 600px; overflow-y: auto;">
                <?php foreach ($all_messages as $msg): ?>
                <div style="margin-bottom: 15px; <?php echo $msg['type'] === 'user' ? 'text-align: right;' : 'text-align: left;'; ?>">
                    <div style="display: inline-block; max-width: 80%; padding: 12px 16px; border-radius: 16px;
                        <?php 
                        if ($msg['type'] === 'user') {
                            echo 'background: var(--color-primary); color: white; border-bottom-right-radius: 4px;';
                        } else {
                            echo 'background: white; color: #333; border-bottom-left-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);';
                        }
                        ?>">
                        <div style="font-size: 14px;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        
                        <?php if ($msg['type'] === 'user' && empty($msg['intention_detected'])): ?>
                        <div style="font-size: 11px; margin-top: 5px; opacity: 0.8;">
                            ⚠️ Non reconnu
                        </div>
                        <?php elseif ($msg['type'] === 'user' && $msg['intention_detected']): ?>
                        <div style="font-size: 11px; margin-top: 5px; opacity: 0.8;">
                            ✅ Détecté: <?php echo htmlspecialchars($msg['intention_detected']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($unrecognized_messages)): ?>
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
                <strong>⚠️ Messages non reconnus détectés :</strong>
                <ul style="margin-top: 10px; padding-left: 20px;">
                    <?php foreach ($unrecognized_messages as $umsg): ?>
                    <li>"<?php echo htmlspecialchars(substr($umsg['message'], 0, 50)); ?>..."</li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin-top: 10px; font-size: 13px;">
                    Ces messages n'ont pas déclenché de réponse intelligente. 
                    Utilisez le formulaire ci-contre pour apprendre au chatbot comment répondre.
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Colonne droite : Formulaire d'apprentissage -->
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">➕ Enseigner au chatbot</h2>
            
            <form method="POST">
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>💡 Comment ça marche ?</strong>
                    <ol style="margin-top: 10px; padding-left: 20px; font-size: 13px;">
                        <li>Identifiez un message que le chatbot n'a pas compris</li>
                        <li>Définissez les mots-clés qui auraient dû le déclencher</li>
                        <li>Écrivez la réponse qu'il aurait dû donner</li>
                        <li>Le chatbot apprend et réagira mieux la prochaine fois !</li>
                    </ol>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nom de l'intention (sans espace)</label>
                    <input type="text" name="intention_key" class="form-control" required
                        placeholder="ex: qualite, garantie, finition..."
                        value="<?php echo isset($_POST['intention_key']) ? htmlspecialchars($_POST['intention_key']) : ''; ?>">
                    <small style="color: #666;">Identifiant unique, exemple: "qualite", "thermique", "soundproofing"</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Mots-clés déclencheurs (séparés par des virgules)</label>
                    <textarea name="keywords" class="form-control" rows="3" required
                        placeholder="qualite, materiaux, finition, solide, durable, garantie..."><?php echo isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : ''; ?></textarea>
                    <small style="color: #666;">Plus il y a de mots-clés, plus la détection est précise</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Réponse que le chatbot doit donner</label>
                    <textarea name="response_text" class="form-control" rows="4" required
                        placeholder="Nous utilisons des materiaux de haute qualite avec garantie decennale..."><?php echo isset($_POST['response_text']) ? htmlspecialchars($_POST['response_text']) : ''; ?></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Action (optionnel)</label>
                    <select name="action" class="form-control">
                        <option value="">Aucune action spéciale</option>
                        <option value="scenario_devis">Lancer scénario devis</option>
                        <option value="scenario_terrain">Lancer scénario terrain</option>
                        <option value="transfert_humain">Transférer à un humain</option>
                        <option value="afficher_modeles">Afficher les modèles</option>
                        <option value="redirect:/contact.php">Rediriger vers contact</option>
                        <option value="close">Fermer le chat</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Priorité (1-10)</label>
                    <input type="number" name="priority" class="form-control" min="1" max="10" value="<?php echo isset($_POST['priority']) ? intval($_POST['priority']) : 5; ?>">
                    <small style="color: #666;">10 = priorité maximale (détecté en premier)</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                    🎓 Apprendre au chatbot
                </button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="chatbot-intentions.php" class="btn btn-outline">
                    Voir toutes les intentions
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
