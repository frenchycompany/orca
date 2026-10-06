<?php
/**
 * Admin - Statistiques de fréquentation
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$page_title = 'Statistiques';

// Période
$days = intval($_GET['days'] ?? 30);
if (!in_array($days, [7, 30, 90], true)) $days = 30;
$since = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));
$prevSince = date('Y-m-d', strtotime("-" . (2 * $days - 1) . " days"));
$prevUntil = date('Y-m-d', strtotime("-{$days} days"));

$tableOk = true;
$kpi = ['views' => 0, 'visitors' => 0, 'leads' => 0, 'mobile' => 0];
$prev = ['views' => 0, 'visitors' => 0, 'leads' => 0];
$perDay = $topPages = $topModeles = $sources = $leadsBySource = [];

try {
    // KPI période courante
    $r = $pdo->prepare("SELECT COUNT(*) v, COUNT(DISTINCT visitor_hash) u, COALESCE(SUM(is_mobile),0) m FROM page_views WHERE day >= ?");
    $r->execute([$since]); $row = $r->fetch();
    $kpi['views'] = (int) $row['v']; $kpi['visitors'] = (int) $row['u']; $kpi['mobile'] = (int) $row['m'];

    // KPI période précédente (comparaison)
    $r = $pdo->prepare("SELECT COUNT(*) v, COUNT(DISTINCT visitor_hash) u FROM page_views WHERE day >= ? AND day < ?");
    $r->execute([$prevSince, $prevUntil]); $row = $r->fetch();
    $prev['views'] = (int) $row['v']; $prev['visitors'] = (int) $row['u'];

    // Par jour
    $r = $pdo->prepare("SELECT day, COUNT(*) v, COUNT(DISTINCT visitor_hash) u FROM page_views WHERE day >= ? GROUP BY day ORDER BY day");
    $r->execute([$since]);
    $byDay = [];
    foreach ($r->fetchAll() as $d) $byDay[$d['day']] = $d;
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $perDay[] = ['day' => $d, 'v' => (int) ($byDay[$d]['v'] ?? 0), 'u' => (int) ($byDay[$d]['u'] ?? 0)];
    }

    // Top pages
    $r = $pdo->prepare("SELECT path, page_type, COUNT(*) v, COUNT(DISTINCT visitor_hash) u FROM page_views WHERE day >= ? GROUP BY path, page_type ORDER BY v DESC LIMIT 10");
    $r->execute([$since]); $topPages = $r->fetchAll();

    // Top modèles (vues + leads sur la période)
    $r = $pdo->prepare("SELECT m.id, m.nom, m.slug, m.prix_afficher,
            COUNT(pv.id) v, COUNT(DISTINCT pv.visitor_hash) u,
            (SELECT COUNT(*) FROM leads l WHERE l.modele_interesse = m.id AND l.created_at >= ?) leads
        FROM modeles m
        LEFT JOIN page_views pv ON pv.modele_id = m.id AND pv.day >= ?
        WHERE m.is_active = 1
        GROUP BY m.id ORDER BY v DESC");
    $r->execute([$since . ' 00:00:00', $since]); $topModeles = $r->fetchAll();

    // Sources
    $r = $pdo->prepare("SELECT referer_host, COUNT(*) v, COUNT(DISTINCT visitor_hash) u FROM page_views WHERE day >= ? GROUP BY referer_host ORDER BY v DESC");
    $r->execute([$since]);
    $agg = [];
    foreach ($r->fetchAll() as $s) {
        $label = trafficSourceLabel($s['referer_host']);
        $agg[$label] = ($agg[$label] ?? 0) + (int) $s['u'];
    }
    arsort($agg); $sources = array_slice($agg, 0, 8, true);
} catch (Throwable $e) {
    $tableOk = false;
}

// Leads (table leads, indépendante de page_views)
try {
    $r = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE created_at >= ?");
    $r->execute([$since . ' 00:00:00']); $kpi['leads'] = (int) $r->fetchColumn();
    $r = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE created_at >= ? AND created_at < ?");
    $r->execute([$prevSince . ' 00:00:00', $prevUntil . ' 00:00:00']); $prev['leads'] = (int) $r->fetchColumn();
    $r = $pdo->prepare("SELECT COALESCE(source,'site-web') src, COUNT(*) n FROM leads WHERE created_at >= ? GROUP BY src ORDER BY n DESC");
    $r->execute([$since . ' 00:00:00']); $leadsBySource = $r->fetchAll();
} catch (Throwable $e) {}

$conversion = $kpi['visitors'] > 0 ? round($kpi['leads'] / $kpi['visitors'] * 100, 1) : 0;
$mobilePct = $kpi['views'] > 0 ? round($kpi['mobile'] / $kpi['views'] * 100) : 0;

function deltaBadge($cur, $prev) {
    if ($prev == 0) return $cur > 0 ? '<span style="color:#27ae60;font-size:12px;">nouveau</span>' : '';
    $d = round(($cur - $prev) / $prev * 100);
    $c = $d >= 0 ? '#27ae60' : '#e74c3c';
    $s = $d >= 0 ? '▲' : '▼';
    return "<span style=\"color:$c;font-size:12px;font-weight:600;\">$s " . abs($d) . "%</span> <span style=\"color:#999;font-size:11px;\">vs période préc.</span>";
}

// Graphique SVG
$maxV = max(1, max(array_column($perDay, 'v') ?: [1]));
$W = 900; $H = 220; $padL = 36; $padB = 28; $padT = 10;
$plotW = $W - $padL - 10; $plotH = $H - $padB - $padT;
$n = count($perDay); $slot = $plotW / max(1, $n); $barW = max(3, $slot * 0.62);
$labelEvery = $days === 7 ? 1 : ($days === 30 ? 5 : 15);

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;">
        <h1 class="admin-title" style="margin:0;">📈 Statistiques</h1>
        <div style="display:flex;gap:6px;">
            <?php foreach ([7 => '7 jours', 30 => '30 jours', 90 => '90 jours'] as $d => $lbl): ?>
            <a href="?days=<?php echo $d; ?>" class="btn <?php echo $days === $d ? 'btn-primary' : 'btn-outline'; ?>" style="font-size:13px;"><?php echo $lbl; ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$tableOk): ?>
    <div class="alert alert-error">
        La table <code>page_views</code> n'existe pas encore. Importez <code>sql/page_views.sql</code> :
        <code>mysql -u orca_user -p orca &lt; /var/www/orca/sql/page_views.sql</code>
    </div>
    <?php endif; ?>

    <!-- KPI -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:20px;">
        <div class="admin-section" style="margin:0;padding:18px 20px;">
            <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.3px;">Pages vues</div>
            <div style="font-size:30px;font-weight:700;color:#1a1a1a;margin:4px 0;"><?php echo number_format($kpi['views'], 0, ',', ' '); ?></div>
            <?php echo deltaBadge($kpi['views'], $prev['views']); ?>
        </div>
        <div class="admin-section" style="margin:0;padding:18px 20px;">
            <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.3px;">Visiteurs</div>
            <div style="font-size:30px;font-weight:700;color:#1a1a1a;margin:4px 0;"><?php echo number_format($kpi['visitors'], 0, ',', ' '); ?></div>
            <?php echo deltaBadge($kpi['visitors'], $prev['visitors']); ?>
        </div>
        <div class="admin-section" style="margin:0;padding:18px 20px;">
            <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.3px;">Leads</div>
            <div style="font-size:30px;font-weight:700;color:var(--color-primary);margin:4px 0;"><?php echo $kpi['leads']; ?></div>
            <?php echo deltaBadge($kpi['leads'], $prev['leads']); ?>
        </div>
        <div class="admin-section" style="margin:0;padding:18px 20px;">
            <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.3px;">Taux de conversion</div>
            <div style="font-size:30px;font-weight:700;color:#27ae60;margin:4px 0;"><?php echo $conversion; ?>%</div>
            <span style="color:#999;font-size:11px;">leads / visiteurs · <?php echo $mobilePct; ?>% mobile</span>
        </div>
    </div>

    <!-- Graphique -->
    <div class="admin-section">
        <h2>Visites par jour</h2>
        <div style="overflow-x:auto;">
        <svg viewBox="0 0 <?php echo $W; ?> <?php echo $H; ?>" width="100%" style="min-width:600px;display:block;font-family:inherit;">
            <?php for ($g = 0; $g <= 4; $g++):
                $y = $padT + $plotH - ($plotH * $g / 4); $val = round($maxV * $g / 4); ?>
            <line x1="<?php echo $padL; ?>" y1="<?php echo $y; ?>" x2="<?php echo $W - 10; ?>" y2="<?php echo $y; ?>" stroke="#eee" stroke-width="1"/>
            <text x="<?php echo $padL - 6; ?>" y="<?php echo $y + 4; ?>" font-size="10" fill="#999" text-anchor="end"><?php echo $val; ?></text>
            <?php endfor; ?>
            <?php foreach ($perDay as $i => $d):
                $x = $padL + $i * $slot + ($slot - $barW) / 2;
                $hV = $plotH * $d['v'] / $maxV; $hU = $plotH * $d['u'] / $maxV;
                $yV = $padT + $plotH - $hV; $yU = $padT + $plotH - $hU;
                $lbl = date('d/m', strtotime($d['day'])); ?>
            <rect x="<?php echo $x; ?>" y="<?php echo $yV; ?>" width="<?php echo $barW; ?>" height="<?php echo $hV; ?>" rx="2" fill="#cfe3e1"><title><?php echo $lbl; ?> — <?php echo $d['v']; ?> pages vues, <?php echo $d['u']; ?> visiteurs</title></rect>
            <rect x="<?php echo $x; ?>" y="<?php echo $yU; ?>" width="<?php echo $barW; ?>" height="<?php echo $hU; ?>" rx="2" fill="#1a5653"><title><?php echo $lbl; ?> — <?php echo $d['u']; ?> visiteurs</title></rect>
            <?php if ($i % $labelEvery === 0 || $i === $n - 1): ?>
            <text x="<?php echo $x + $barW / 2; ?>" y="<?php echo $H - 8; ?>" font-size="10" fill="#777" text-anchor="middle"><?php echo $lbl; ?></text>
            <?php endif; ?>
            <?php endforeach; ?>
        </svg>
        </div>
        <div style="display:flex;gap:18px;font-size:12px;color:#666;margin-top:8px;">
            <span><span style="display:inline-block;width:12px;height:12px;background:#1a5653;border-radius:2px;vertical-align:middle;"></span> Visiteurs uniques</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#cfe3e1;border-radius:2px;vertical-align:middle;"></span> Pages vues</span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- Modèles -->
        <div class="admin-section" style="margin:0;">
            <h2>🏠 Modèles les plus consultés</h2>
            <?php if (empty($topModeles)): ?><p style="color:#999;">Aucune donnée.</p><?php else: ?>
            <?php $maxM = max(1, max(array_column($topModeles, 'v'))); ?>
            <table class="admin-table" style="font-size:13px;">
                <thead><tr><th>Modèle</th><th style="text-align:right;">Vues</th><th style="text-align:right;">Visiteurs</th><th style="text-align:right;">Leads</th></tr></thead>
                <tbody>
                <?php foreach ($topModeles as $m): ?>
                <tr>
                    <td>
                        <a href="modele-edit.php?id=<?php echo $m['id']; ?>" style="font-weight:600;color:#1a1a1a;text-decoration:none;"><?php echo htmlspecialchars($m['nom']); ?></a>
                        <div style="height:4px;background:#f0f0f0;border-radius:2px;margin-top:5px;"><div style="height:4px;width:<?php echo round($m['v'] / $maxM * 100); ?>%;background:#1a5653;border-radius:2px;"></div></div>
                    </td>
                    <td style="text-align:right;font-weight:600;"><?php echo $m['v']; ?></td>
                    <td style="text-align:right;color:#666;"><?php echo $m['u']; ?></td>
                    <td style="text-align:right;"><?php echo $m['leads'] ? '<span class="badge badge-success">' . $m['leads'] . '</span>' : '<span style="color:#ccc;">0</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Sources -->
        <div class="admin-section" style="margin:0;">
            <h2>🔗 Sources de trafic</h2>
            <?php if (empty($sources)): ?><p style="color:#999;">Aucune donnée.</p><?php else: ?>
            <?php $totS = max(1, array_sum($sources)); ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($sources as $label => $u): $pct = round($u / $totS * 100); ?>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                        <span style="font-weight:600;"><?php echo htmlspecialchars($label); ?></span>
                        <span style="color:#666;"><?php echo $u; ?> visiteurs · <?php echo $pct; ?>%</span>
                    </div>
                    <div style="height:8px;background:#f0f0f0;border-radius:4px;"><div style="height:8px;width:<?php echo $pct; ?>%;background:#1a5653;border-radius:4px;"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h2 style="margin-top:26px;">📋 Leads par origine</h2>
            <?php if (empty($leadsBySource)): ?><p style="color:#999;">Aucun lead sur la période.</p><?php else: ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                <?php foreach ($leadsBySource as $ls): ?>
                <span style="padding:6px 12px;background:#f5f7f9;border-radius:20px;font-size:13px;"><strong><?php echo $ls['n']; ?></strong> <?php echo htmlspecialchars($ls['src'] === 'chatbot' ? 'Chatbot' : ($ls['src'] === 'site-web' ? 'Formulaire site' : $ls['src'])); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top pages -->
    <div class="admin-section" style="margin-top:20px;">
        <h2>📄 Pages les plus consultées</h2>
        <?php if (empty($topPages)): ?><p style="color:#999;">Aucune donnée.</p><?php else: ?>
        <table class="admin-table" style="font-size:13px;">
            <thead><tr><th>Page</th><th>Type</th><th style="text-align:right;">Vues</th><th style="text-align:right;">Visiteurs</th></tr></thead>
            <tbody>
            <?php foreach ($topPages as $p): ?>
            <tr>
                <td><code style="font-size:12px;"><?php echo htmlspecialchars($p['path']); ?></code></td>
                <td><span style="font-size:11px;background:#f0f0f0;padding:2px 8px;border-radius:10px;"><?php echo htmlspecialchars($p['page_type']); ?></span></td>
                <td style="text-align:right;font-weight:600;"><?php echo $p['v']; ?></td>
                <td style="text-align:right;color:#666;"><?php echo $p['u']; ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <p style="font-size:12px;color:#999;margin-top:16px;">
        Mesure côté serveur, sans cookie ni script tiers : conforme RGPD sans bannière de consentement. Un visiteur est compté une fois par jour. Les robots et l'administration sont exclus.
    </p>
</div>

<?php include 'includes/admin-footer.php'; ?>
