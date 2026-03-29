<?php
/**
 * Admin - A/B Testing pour Chatbot
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Chatbot - A/B Testing';
$message = '';

// Créer un test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test'])) {
    $name = $_POST['test_name'];
    $variant_a = $_POST['variant_a'];
    $variant_b = $_POST['variant_b'];
    $test_type = $_POST['test_type'];
    
    $stmt = $pdo->prepare("INSERT INTO chatbot_ab_tests 
        (name, test_type, variant_a_value, variant_b_value, status, created_at) 
        VALUES (?, ?, ?, ?, 'active', NOW())");
    $stmt->execute([$name, $test_type, $variant_a, $variant_b]);
    
    $message = 'Test A/B créé avec succès !';
}

// Arrêter un test
if (isset($_GET['stop'])) {
    $id = intval($_GET['stop']);
    $pdo->prepare("UPDATE chatbot_ab_tests SET status = 'completed', ended_at = NOW() WHERE id = ?")
        ->execute([$id]);
    header('Location: chatbot-abtest.php?msg=stopped');
    exit;
}

// Supprimer un test
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM chatbot_ab_tests WHERE id = ?")->execute([$id]);
    header('Location: chatbot-abtest.php?msg=deleted');
    exit;
}

if ($_GET['msg'] ?? '' === 'stopped') $message = 'Test arrêté.';
if ($_GET['msg'] ?? '' === 'deleted') $message = 'Test supprimé.';

// Récupérer les tests
$tests = $pdo->query("SELECT * FROM chatbot_ab_tests ORDER BY created_at DESC")->fetchAll();

// Stats pour chaque test
function getTestStats($pdo, $test_id) {
    // Variante A
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as views,
        SUM(CASE WHEN lead_id IS NOT NULL THEN 1 ELSE 0 END) as conversions,
        AVG(completion_score) as avg_score
        FROM chatbot_conversations 
        WHERE ab_test_id = ? AND ab_variant = 'A'");
    $stmt->execute([$test_id]);
    $variant_a = $stmt->fetch();
    
    // Variante B
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as views,
        SUM(CASE WHEN lead_id IS NOT NULL THEN 1 ELSE 0 END) as conversions,
        AVG(completion_score) as avg_score
        FROM chatbot_conversations 
        WHERE ab_test_id = ? AND ab_variant = 'B'");
    $stmt->execute([$test_id]);
    $variant_b = $stmt->fetch();
    
    return ['A' => $variant_a, 'B' => $variant_b];
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">🧪 A/B Testing</h1>
    
    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <!-- Nouveau test -->
    <div class="admin-section" style="margin-bottom: 30px;">
        <h2 style="margin-bottom: 20px; font-size: 18px;">➕ Créer un Test</h2>
        
        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nom du test</label>
                    <input type="text" name="test_name" class="form-control" placeholder="Ex: Message de bienvenue" required>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Type</label>
                    <select name="test_type" class="form-control" required>
                        <option value="welcome_message">Message de bienvenue</option>
                        <option value="button_style">Style des boutons</option>
                        <option value="question_order">Ordre des questions</option>
                        <option value="response_style">Style de réponse</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Variante A</label>
                    <input type="text" name="variant_a" class="form-control" placeholder="Valeur A" required>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Variante B</label>
                    <input type="text" name="variant_b" class="form-control" placeholder="Valeur B" required>
                </div>
            </div>
            
            <button type="submit" name="create_test" class="btn btn-primary" style="margin-top: 15px;">
                🚀 Lancer le Test
            </button>
        </form>
    </div>
    
    <!-- Liste des tests -->
    <div class="admin-section">
        <h2 style="margin-bottom: 20px; font-size: 18px;">📊 Tests en Cours / Terminés</h2>
        
        <?php if (empty($tests)): ?>
        <p style="color: #999; text-align: center; padding: 40px;">Aucun test créé pour le moment.</p>
        <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <?php foreach ($tests as $test): 
                $stats = getTestStats($pdo, $test['id']);
                $total_a = $stats['A']['views'] ?? 0;
                $total_b = $stats['B']['views'] ?? 0;
                $conv_a = $stats['A']['conversions'] ?? 0;
                $conv_b = $stats['B']['conversions'] ?? 0;
                $rate_a = $total_a > 0 ? round(($conv_a / $total_a) * 100, 2) : 0;
                $rate_b = $total_b > 0 ? round(($conv_b / $total_b) * 100, 2) : 0;
                $winner = $rate_a > $rate_b ? 'A' : ($rate_b > $rate_a ? 'B' : 'equal');
            ?>
            <div style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; background: <?php echo $test['status'] === 'active' ? '#f0fff4' : 'white'; ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <h3 style="font-size: 16px; margin: 0;"><?php echo htmlspecialchars($test['name']); ?></h3>
                        <span style="font-size: 12px; color: #666;">
                            <?php echo ucfirst($test['test_type']); ?> • 
                            <span style="color: <?php echo $test['status'] === 'active' ? '#27ae60' : '#999'; ?>">
                                <?php echo $test['status'] === 'active' ? '🟢 Actif' : '⚪ Terminé'; ?>
                            </span>
                        </span>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <?php if ($test['status'] === 'active'): ?>
                        <a href="?stop=<?php echo $test['id']; ?>" class="btn btn-sm" style="background: #f39c12; color: white;">⏹ Arrêter</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $test['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce test ?')">🗑</a>
                    </div>
                </div>
                
                <!-- Comparaison -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="background: white; padding: 15px; border-radius: 8px; border: 2px solid <?php echo $winner === 'A' ? '#27ae60' : 'transparent'; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Variante A</span>
                            <?php if ($winner === 'A'): ?><span style="background: #27ae60; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">🏆 Gagnant</span><?php endif; ?>
                        </div>
                        <div style="margin-top: 10px; font-size: 13px; color: #666; margin-bottom: 10px;">
                            "<?php echo htmlspecialchars(substr($test['variant_a_value'], 0, 50)); ?>..."
                        </div>
                        <div style="display: flex; gap: 20px;">
                            <div>
                                <div style="font-size: 24px; font-weight: 700;"><?php echo $total_a; ?></div>
                                <div style="font-size: 11px; color: #666;">Visites</div>
                            </div>
                            <div>
                                <div style="font-size: 24px; font-weight: 700; color: #27ae60;"><?php echo $rate_a; ?>%</div>
                                <div style="font-size: 11px; color: #666;">Conversion</div>
                            </div>
                            <div>
                                <div style="font-size: 24px; font-weight: 700;"><?php echo $conv_a; ?></div>
                                <div style="font-size: 11px; color: #666;">Leads</div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 15px; border-radius: 8px; border: 2px solid <?php echo $winner === 'B' ? '#27ae60' : 'transparent'; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600;">Variante B</span>
                            <?php if ($winner === 'B'): ?><span style="background: #27ae60; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px;">🏆 Gagnant</span><?php endif; ?>
                        </div>
                        <div style="margin-top: 10px; font-size: 13px; color: #666; margin-bottom: 10px;">
                            "<?php echo htmlspecialchars(substr($test['variant_b_value'], 0, 50)); ?>..."
                        </div>
                        <div style="display: flex; gap: 20px;">
                            <div>
                                <div style="font-size: 24px; font-weight: 700;"><?php echo $total_b; ?></div>
                                <div style="font-size: 11px; color: #666;">Visites</div>
                            </div>
                            <div>
                                <div style="font-size: 24px; font-weight: 700; color: #27ae60;"><?php echo $rate_b; ?>%</div>
                                <div style="font-size: 11px; color: #666;">Conversion</div>
                            </div>
                            <div>
                                <div style="font-size: 24px; font-weight: 700;"><?php echo $conv_b; ?></div>
                                <div style="font-size: 11px; color: #666;">Leads</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($total_a >= 30 && $total_b >= 30): ?>
                <div style="margin-top: 15px; padding: 10px; background: #e3f2fd; border-radius: 6px; font-size: 13px;">
                    <?php if (abs($rate_a - $rate_b) < 1): ?>
                    📊 Résultat : Pas de différence significative entre les variantes.
                    <?php elseif ($winner !== 'equal'): ?>
                    📈 Résultat : La variante <?php echo $winner; ?> convertit <strong><?php echo round(abs($rate_a - $rate_b), 1); ?>%</strong> mieux.
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 6px; font-size: 13px;">
                    ⏳ Attendez 30 visites par variante pour des résultats significatifs.
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Info -->
    <div class="admin-section" style="margin-top: 30px;">
        <h2 style="margin-bottom: 15px; font-size: 18px;">💡 Comment ça marche ?</h2>
        <ol style="padding-left: 20px; line-height: 1.8; color: #666;">
            <li>Créez un test avec 2 variantes (ex: 2 messages de bienvenue différents)</li>
            <li>Le chatbot affichera aléatoirement A ou B aux visiteurs (50/50)</li>
            <li>Suivez les conversions en temps réel</li>
            <li>Quand vous avez assez de données, arrêtez le test et gardez le gagnant !</li>
        </ol>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
