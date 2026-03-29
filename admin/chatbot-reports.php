<?php
/**
 * Admin - Rapports Avancés Chatbot
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Chatbot - Rapports Avancés';

// Période d'analyse
$period = $_GET['period'] ?? '30';
$date_start = date('Y-m-d', strtotime("-$period days"));

// Statistiques globales sur la période
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total,
    COUNT(DISTINCT DATE(started_at)) as active_days,
    SUM(CASE WHEN lead_id IS NOT NULL THEN 1 ELSE 0 END) as conversions,
    AVG(completion_score) as avg_score,
    AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_duration
    FROM chatbot_conversations 
    WHERE started_at >= ?");
$stmt->execute([$date_start]);
$stats = $stmt->fetch();

// Entonnoir de conversion (par étape)
$steps_funnel = [];
for ($i = 1; $i <= 7; $i++) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chatbot_conversations 
                          WHERE started_at >= ? AND current_step >= ?");
    $stmt->execute([$date_start, $i]);
    $steps_funnel[$i] = $stmt->fetchColumn();
}

// Heures de pointe
$stmt = $pdo->prepare("SELECT 
    HOUR(started_at) as hour,
    COUNT(*) as count
    FROM chatbot_conversations 
    WHERE started_at >= ?
    GROUP BY HOUR(started_at)
    ORDER BY hour ASC");
$stmt->execute([$date_start]);
$hourly_stats = $stmt->fetchAll();

// Jours de la semaine
$stmt = $pdo->prepare("SELECT 
    DAYOFWEEK(started_at) as day,
    COUNT(*) as count
    FROM chatbot_conversations 
    WHERE started_at >= ?
    GROUP BY DAYOFWEEK(started_at)
    ORDER BY day ASC");
$stmt->execute([$date_start]);
$weekly_stats = $stmt->fetchAll();

// Taux d'abandon par étape
$abandon_by_step = [];
for ($i = 1; $i <= 6; $i++) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM chatbot_conversations 
                          WHERE started_at >= ? AND current_step = ? AND is_active = 0 AND lead_id IS NULL");
    $stmt->execute([$date_start, $i]);
    $abandon_by_step[$i] = $stmt->fetchColumn();
}

// Top pages d'entrée
$stmt = $pdo->prepare("SELECT 
    page_source,
    COUNT(*) as count
    FROM chatbot_conversations 
    WHERE started_at >= ? AND page_source IS NOT NULL
    GROUP BY page_source
    ORDER BY count DESC
    LIMIT 10");
$stmt->execute([$date_start]);
$top_pages = $stmt->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">📊 Rapports Avancés</h1>
    
    <!-- Filtres -->
    <div class="admin-section" style="margin-bottom: 30px;">
        <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500;">Période</label>
                <select name="period" class="form-control" onchange="this.form.submit()">
                    <option value="7" <?php echo $period === '7' ? 'selected' : ''; ?>>7 derniers jours</option>
                    <option value="30" <?php echo $period === '30' ? 'selected' : ''; ?>>30 derniers jours</option>
                    <option value="90" <?php echo $period === '90' ? 'selected' : ''; ?>>3 derniers mois</option>
                </select>
            </div>
            <a href="chatbot-export.php?period=<?php echo $period; ?>" class="btn btn-outline">📥 Exporter CSV</a>
        </form>
    </div>
    
    <!-- KPIs -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;">
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="font-size: 32px; font-weight: 700; color: var(--color-primary);"><?php echo $stats['total'] ?? 0; ?></div>
            <div style="color: var(--color-gray); font-size: 14px;">Conversations (période)</div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="font-size: 32px; font-weight: 700; color: #27ae60;"><?php echo round((($stats['conversions'] ?? 0) / max($stats['total'], 1)) * 100, 1); ?>%</div>
            <div style="color: var(--color-gray); font-size: 14px;">Taux conversion</div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="font-size: 32px; font-weight: 700; color: #e74c3c;"><?php echo round($stats['avg_score'] ?? 0, 1); ?>%</div>
            <div style="color: var(--color-gray); font-size: 14px;">Score moyen</div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="font-size: 32px; font-weight: 700; color: #f39c12;"><?php echo round(($stats['avg_duration'] ?? 0)); ?> min</div>
            <div style="color: var(--color-gray); font-size: 14px;">Durée moyenne</div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Entonnoir de conversion -->
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">🎯 Entonnoir de Conversion</h2>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php 
                $step_names = [
                    1 => 'Démarrage',
                    2 => 'Département',
                    3 => 'Terrain',
                    4 => 'Surface',
                    5 => 'Budget',
                    6 => 'Délai',
                    7 => 'Coordonnées'
                ];
                
                $max_count = max($steps_funnel);
                foreach ($steps_funnel as $step => $count): 
                    $percent = ($count / max($max_count, 1)) * 100;
                    $dropoff = $step > 1 ? round((1 - $count / max($steps_funnel[$step-1], 1)) * 100, 1) : 0;
                ?>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 100px; font-size: 13px;">Étape <?php echo $step; ?></div>
                    <div style="flex: 1; background: #f0f0f0; height: 30px; border-radius: 4px; overflow: hidden; position: relative;">
                        <div style="background: <?php echo $step === 7 ? '#27ae60' : 'var(--color-primary)'; ?>; height: 100%; width: <?php echo $percent; ?>%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 600;">
                            <?php echo $count; ?>
                        </div>
                    </div>
                    <div style="width: 80px; font-size: 12px; color: <?php echo $dropoff > 30 ? '#e74c3c' : '#666'; ?>;">
                        <?php echo $step > 1 ? '-'.$dropoff.'%' : ''; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; font-size: 13px;">
                <strong>💡 Insight :</strong> 
                <?php 
                $max_dropoff = 0;
                $max_dropoff_step = 0;
                foreach ($abandon_by_step as $step => $count) {
                    if ($count > $max_dropoff) {
                        $max_dropoff = $count;
                        $max_dropoff_step = $step;
                    }
                }
                if ($max_dropoff_step > 0) {
                    echo "La plus grande perte se fait à l'étape $max_dropoff_step (" . $step_names[$max_dropoff_step] . "). Envisagez de la simplifier.";
                }
                ?>
            </div>
        </div>
        
        <!-- Heatmap horaire -->
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">🔥 Heatmap Horaire</h2>
            
            <div style="display: grid; grid-template-columns: repeat(12, 1fr); gap: 2px; height: 150px;">
                <?php 
                $hourly_data = array_fill(0, 24, 0);
                foreach ($hourly_stats as $h) {
                    $hourly_data[$h['hour']] = $h['count'];
                }
                $max_hourly = max($hourly_data) ?: 1;
                
                foreach ($hourly_data as $hour => $count): 
                    $intensity = $count / $max_hourly;
                    $color = $intensity > 0.7 ? '#c62828' : ($intensity > 0.4 ? '#f39c12' : ($intensity > 0.1 ? '#27ae60' : '#e0e0e0'));
                ?>
                <div style="background: <?php echo $color; ?>; border-radius: 2px; position: relative;" title="<?php echo $hour; ?>h: <?php echo $count; ?> conversations">
                    <?php if ($count > 0): ?>
                    <span style="position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); font-size: 9px; color: white;"><?php echo $hour; ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="display: flex; gap: 15px; margin-top: 15px; font-size: 12px; justify-content: center;">
                <div style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; background: #e0e0e0; border-radius: 2px;"></div> Faible</div>
                <div style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; background: #27ae60; border-radius: 2px;"></div> Moyen</div>
                <div style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; background: #f39c12; border-radius: 2px;"></div> Fort</div>
                <div style="display: flex; align-items: center; gap: 5px;"><div style="width: 12px; height: 12px; background: #c62828; border-radius: 2px;"></div> Très fort</div>
            </div>
        </div>
    </div>
    
    <!-- Pages d'entrée et jours -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">📄 Top Pages d'Entrée</h2>
            
            <?php if (empty($top_pages)): ?>
            <p style="color: #999;">Aucune donnée</p>
            <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($top_pages as $page): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f8f9fa; border-radius: 6px;">
                    <code style="font-size: 12px;"><?php echo htmlspecialchars(substr($page['page_source'], 0, 40)); ?></code>
                    <span style="background: var(--color-primary); color: white; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php echo $page['count']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="admin-section">
            <h2 style="margin-bottom: 20px; font-size: 18px;">📅 Répartition par Jour</h2>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php 
                $days = ['', 'Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
                $weekly_data = array_fill(1, 7, 0);
                foreach ($weekly_stats as $w) {
                    $weekly_data[$w['day']] = $w['count'];
                }
                $max_weekly = max($weekly_data) ?: 1;
                
                for ($i = 1; $i <= 7; $i++): 
                    $percent = ($weekly_data[$i] / $max_weekly) * 100;
                ?>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 100px;"><?php echo $days[$i]; ?></div>
                    <div style="flex: 1; background: #f0f0f0; height: 25px; border-radius: 4px; overflow: hidden;">
                        <div style="background: var(--color-primary); height: 100%; width: <?php echo $percent; ?>%;"></div>
                    </div>
                    <div style="width: 40px; text-align: right; font-weight: 600;"><?php echo $weekly_data[$i]; ?></div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
