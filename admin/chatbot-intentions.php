<?php
/**
 * Admin - Gestion des intentions du Chatbot
 * Permet d'apprendre de nouvelles choses au chatbot
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Chatbot - Apprentissage';
$message = '';
$error = '';

// Ajouter/Modifier une intention
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $intention_key = strtolower(trim($_POST['intention_key'] ?? ''));
    $keywords = trim($_POST['keywords'] ?? '');
    $response_text = trim($_POST['response_text'] ?? '');
    $action = trim($_POST['action'] ?? '');
    $priority = intval($_POST['priority'] ?? 5);
    
    if (empty($intention_key) || empty($keywords) || empty($response_text)) {
        $error = 'Tous les champs sont obligatoires.';
    } else {
        try {
            if ($id) {
                // Modifier
                $stmt = $pdo->prepare("UPDATE chatbot_intentions 
                    SET intention_key = ?, keywords = ?, response_text = ?, action = ?, priority = ?
                    WHERE id = ?");
                $stmt->execute([$intention_key, $keywords, $response_text, $action, $priority, $id]);
                $message = 'Intention modifiée avec succès !';
            } else {
                // Ajouter
                $stmt = $pdo->prepare("INSERT INTO chatbot_intentions 
                    (intention_key, keywords, response_text, action, priority) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$intention_key, $keywords, $response_text, $action, $priority]);
                $message = 'Nouvelle intention ajoutée ! Le chatbot a appris ! 🎉';
            }
        } catch (PDOException $e) {
            $error = 'Erreur : ' . $e->getMessage();
        }
    }
}

// Supprimer une intention
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM chatbot_intentions WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: chatbot-intentions.php?deleted=1');
    exit;
}

// Activer/Désactiver
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $pdo->prepare("UPDATE chatbot_intentions SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: chatbot-intentions.php');
    exit;
}

// Récupérer toutes les intentions
$stmt = $pdo->query("SELECT * FROM chatbot_intentions ORDER BY priority DESC, intention_key ASC");
$intentions = $stmt->fetchAll();

// Récupérer une intention pour édition
$edit_intention = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM chatbot_intentions WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $edit_intention = $stmt->fetch();
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">🎓 Apprendre au Chatbot</h1>
    
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Intention supprimée.</div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Formulaire d'ajout/modification -->
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">
                <?php echo $edit_intention ? '✏️ Modifier' : '➕ Ajouter une connaissance'; ?>
            </h2>
            
            <form method="POST">
                <?php if ($edit_intention): ?>
                <input type="hidden" name="id" value="<?php echo $edit_intention['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nom de l'intention (sans espace)</label>
                    <input type="text" name="intention_key" class="form-control" 
                        value="<?php echo htmlspecialchars($edit_intention['intention_key'] ?? ''); ?>"
                        placeholder="ex: prix, terrain, contact..."
                        required>
                    <small style="color: #666;">Identifiant unique, exemple: "prix", "terrain", "modele"</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Mots-clés (séparés par des virgules)</label>
                    <textarea name="keywords" class="form-control" rows="3" required
                        placeholder="prix, combien, coute, euros, budget..."><?php echo htmlspecialchars($edit_intention['keywords'] ?? ''); ?></textarea>
                    <small style="color: #666;">Plus il y a de mots-clés, plus la détection est précise</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Réponse du chatbot</label>
                    <textarea name="response_text" class="form-control" rows="4" required
                        placeholder="Nos maisons démarrent à 149 000 euros..."><?php echo htmlspecialchars($edit_intention['response_text'] ?? ''); ?></textarea>
                    <small style="color: #666;">Ce que le chatbot répondra quand il détecte ces mots</small>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Action (optionnel)</label>
                    <select name="action" class="form-control">
                        <option value="">Aucune action spéciale</option>
                        <option value="scenario_devis" <?php echo ($edit_intention['action'] ?? '') === 'scenario_devis' ? 'selected' : ''; ?>>Lancer scénario devis</option>
                        <option value="scenario_terrain" <?php echo ($edit_intention['action'] ?? '') === 'scenario_terrain' ? 'selected' : ''; ?>>Lancer scénario terrain</option>
                        <option value="transfert_humain" <?php echo ($edit_intention['action'] ?? '') === 'transfert_humain' ? 'selected' : ''; ?>>Transférer à un humain</option>
                        <option value="afficher_modeles" <?php echo ($edit_intention['action'] ?? '') === 'afficher_modeles' ? 'selected' : ''; ?>>Afficher les modèles</option>
                        <option value="redirect:/contact.php" <?php echo ($edit_intention['action'] ?? '') === 'redirect:/contact.php' ? 'selected' : ''; ?>>Rediriger vers contact</option>
                        <option value="close" <?php echo ($edit_intention['action'] ?? '') === 'close' ? 'selected' : ''; ?>>Fermer le chat</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Priorité (1-10)</label>
                    <input type="number" name="priority" class="form-control" min="1" max="10" 
                        value="<?php echo $edit_intention['priority'] ?? 5; ?>">
                    <small style="color: #666;">10 = priorité maximale (détecté en premier)</small>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <?php echo $edit_intention ? '💾 Enregistrer' : '➕ Ajouter cette connaissance'; ?>
                </button>
                
                <?php if ($edit_intention): ?>
                <a href="chatbot-intentions.php" class="btn btn-outline" style="margin-left: 10px;">Annuler</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Liste des intentions -->
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">📚 Connaissances actuelles</h2>
            
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Intention</th>
                            <th>Mots-clés</th>
                            <th>Réponse</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($intentions as $int): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($int['intention_key']); ?></strong></td>
                            <td>
                                <small style="color: #666;">
                                    <?php 
                                    $words = explode(',', $int['keywords']);
                                    echo htmlspecialchars(implode(', ', array_slice($words, 0, 3)));
                                    if (count($words) > 3) echo '...';
                                    ?>
                                </small>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars(substr($int['response_text'], 0, 50)) . '...'; ?></small>
                            </td>
                            <td><?php echo $int['priority']; ?></td>
                            <td>
                                <?php if ($int['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                <span class="badge badge-error">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?toggle=<?php echo $int['id']; ?>" class="btn btn-sm btn-outline" title="Activer/Désactiver">
                                    <?php echo $int['is_active'] ? 'Désactiver' : 'Activer'; ?>
                                </a>
                                <a href="?edit=<?php echo $int['id']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                                <a href="?delete=<?php echo $int['id']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Supprimer cette intention ?')">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($intentions)): ?>
            <p style="color: #999; text-align: center; padding: 40px;">
                Aucune intention définie. Ajoutez votre première connaissance !
            </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Guide rapide -->
    <div class="admin-section" style="margin-top: 30px;">
        <h2 style="margin-bottom: 20px; font-size: 18px;">💡 Guide d'apprentissage</h2>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
            <div>
                <h3 style="font-size: 16px; margin-bottom: 10px;">📝 Comment ça marche ?</h3>
                <ol style="padding-left: 20px; line-height: 1.8;">
                    <li>L'utilisateur envoie un message</li>
                    <li>Le chatbot analyse les mots-clés</li>
                    <li>Si des mots-clés correspondent → Réponse associée</li>
                    <li>Sinon → Continue le scénario de qualification</li>
                </ol>
            </div>
            
            <div>
                <h3 style="font-size: 16px; margin-bottom: 10px;">🎯 Exemples d'intentions</h3>
                <ul style="padding-left: 20px; line-height: 1.8;">
                    <li><strong>prix</strong> : "combien", "budget", "euros"</li>
                    <li><strong>terrain</strong> : "parcelle", "trouver terrain"</li>
                    <li><strong>contact</strong> : "téléphone", "conseiller", "humain"</li>
                    <li><strong>delai</strong> : "quand", "temps", "livraison"</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-radius: 8px;">
            <strong>💡 Astuce :</strong> Plus vous ajoutez de mots-clés synonymes, plus la détection sera précise !
            Exemple : pour "prix", ajoutez : "prix, combien, coute, cout, tarif, budget, euros, €, cher"
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
