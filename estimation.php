<?php
/**
 * Landing page - Estimation chatbot plein écran via FrenchyBot
 */
require_once __DIR__ . '/includes/config.php';

$page_title = t('estimation.meta_title', 'Estimez votre maison en 2 minutes - Maisons ORCA');
$page_description = t('estimation.meta_description', 'Obtenez une estimation gratuite et personnalisée pour votre projet de construction. Réponse sous 24h.');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Poppins',sans-serif;background:#f5f7f9;min-height:100vh;display:flex;flex-direction:column}
        .lp-header{background:#fff;padding:12px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(0,0,0,0.06)}
        .lp-logo{font-weight:700;font-size:20px;color:#1a5653}.lp-logo span{color:#c41e3a}
        .lp-phone{color:#1a5653;text-decoration:none;font-weight:600;font-size:15px}
        .lp-main{flex:1;display:flex;flex-direction:column;align-items:center;padding:25px 20px 20px}
        .lp-title{text-align:center;margin-bottom:20px}
        .lp-title h1{font-size:26px;font-weight:700;color:#1a5653;margin-bottom:6px}
        .lp-title p{font-size:15px;color:#666}
        .lp-iframe-wrap{width:100%;max-width:520px;border-radius:16px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,0.12)}
        .lp-iframe-wrap iframe{width:100%;height:550px;border:none;display:block}
        .lp-trust{display:flex;justify-content:center;gap:25px;margin-top:22px;flex-wrap:wrap}
        .lp-trust-item{display:flex;align-items:center;gap:5px;font-size:13px;color:#555}
        .lp-stats{display:flex;justify-content:center;gap:35px;margin-top:18px;padding-top:18px;border-top:1px solid #e0e0e0;flex-wrap:wrap}
        .lp-stat{text-align:center}.lp-stat-num{font-size:22px;font-weight:700;color:#1a5653}.lp-stat-label{font-size:11px;color:#888}
        @media(max-width:600px){.lp-title h1{font-size:20px}.lp-main{padding:12px 8px}.lp-iframe-wrap iframe{height:500px}.lp-trust{gap:12px}}
    </style>
</head>
<body>
    <header class="lp-header">
        <div class="lp-logo"><?php echo te('estimation.logo', 'Maisons'); ?> <span>ORCA</span></div>
        <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" class="lp-phone">
            📞 <?php echo htmlspecialchars($site_config['site_phone'] ?? '03 44 00 00 00'); ?>
        </a>
    </header>

    <main class="lp-main">
        <div class="lp-title">
            <h1><?php echo te('estimation.title', '🏠 Estimez votre maison en 2 minutes'); ?></h1>
            <p><?php echo te('estimation.subtitle', 'Gratuit, sans engagement, réponse sous 24h'); ?></p>
        </div>

        <div class="lp-iframe-wrap">
            <iframe src="https://bot.frenchycompany.fr/api/v1/iframe.php?token=83059f1ffd4adf64a5ef5e9a803dd1d2"
                    title="Assistant ORCA"
                    allow="clipboard-write"></iframe>
        </div>

        <div class="lp-trust">
            <div class="lp-trust-item"><?php echo te('estimation.trust1', '✅ Gratuit'); ?></div>
            <div class="lp-trust-item"><?php echo te('estimation.trust2', '🔒 Sans engagement'); ?></div>
            <div class="lp-trust-item"><?php echo te('estimation.trust3', '📞 Rappel sous 24h'); ?></div>
            <div class="lp-trust-item"><?php echo te('estimation.trust4', '🏠 Depuis 1993'); ?></div>
        </div>
        <div class="lp-stats">
            <div class="lp-stat"><div class="lp-stat-num"><?php echo te('estimation.stat1_num', '30+'); ?></div><div class="lp-stat-label"><?php echo te('estimation.stat1_label', 'ans d\'expérience'); ?></div></div>
            <div class="lp-stat"><div class="lp-stat-num"><?php echo te('estimation.stat2_num', '3000+'); ?></div><div class="lp-stat-label"><?php echo te('estimation.stat2_label', 'maisons construites'); ?></div></div>
            <div class="lp-stat"><div class="lp-stat-num"><?php echo te('estimation.stat3_num', '6'); ?></div><div class="lp-stat-label"><?php echo te('estimation.stat3_label', 'modèles'); ?></div></div>
            <div class="lp-stat"><div class="lp-stat-num"><?php echo te('estimation.stat4_num', '145k€'); ?></div><div class="lp-stat-label"><?php echo te('estimation.stat4_label', 'à partir de'); ?></div></div>
        </div>
    </main>
</body>
</html>
