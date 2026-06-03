<?php
/**
 * Header template
 */
require_once __DIR__ . '/config.php';

// Récupérer la page courante
$current_page = basename($_SERVER['PHP_SELF'], '.php');
if ($current_page == 'index') $current_page = 'accueil';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title><?php echo isset($page_title) ? $page_title : ($site_config['site_name'] ?? 'Maisons ORCA'); ?></title>
    <meta name="description" content="<?php echo isset($page_description) ? $page_description : ($site_config['meta_description'] ?? ''); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : ($site_config['site_name'] ?? 'Maisons ORCA'); ?>">
    <meta property="og:description" content="<?php echo isset($page_description) ? $page_description : ($site_config['meta_description'] ?? ''); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="<?php echo url('images/og-image.jpg'); ?>">
    
    <!-- Favicon -->
    <?php $favicon = !empty($site_config['favicon']) ? $site_config['favicon'] : 'favicon.ico'; ?>
    <link rel="icon" type="image/x-icon" href="<?php echo url('uploads/config/' . $favicon); ?>?v=<?php echo $site_config['css_version'] ?? '1'; ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles avec versioning pour éviter le cache -->
    <?php 
    $css_version = $site_config['css_version'] ?? '1.0.0';
    ?>
    <link rel="stylesheet" href="<?php echo url('css/style.css?v=' . $css_version); ?>">
    
    <?php if (isset($page_css)): ?>
    <link rel="stylesheet" href="<?php echo url('css/' . $page_css . '?v=' . $css_version); ?>">
    <?php endif; ?>
    
    <!-- CSS Configurable depuis l'admin -->
    <?php
    $banner_image = $site_config['banner_bg_image'] ?? 'images/banner-default.jpg';
    $banner_exists = file_exists(__DIR__ . '/../' . $banner_image);
    ?>
    <style>
        :root {
            /* Couleurs principales */
            --color-primary: <?php echo $site_config['color_primary'] ?? '#1a5653'; ?>;
            --color-primary-dark: <?php echo $site_config['color_primary_dark'] ?? '#124a47'; ?>;
            --color-primary-light: <?php echo $site_config['color_primary_light'] ?? '#2d7a76'; ?>;
            
            /* Couleurs secondaires */
            --color-accent: <?php echo $site_config['color_accent'] ?? '#c9a227'; ?>;
            --color-accent-dark: <?php echo $site_config['color_accent_dark'] ?? '#b08d20'; ?>;
            
            /* Bannière avec image de fond */
            --banner-overlay-opacity: <?php echo $site_config['banner_overlay_opacity'] ?? '0.85'; ?>;
        }
        
        /* Bannière configurable */
        .page-header {
            position: relative;
            background-color: var(--color-primary);
            <?php if ($banner_exists): ?>
            background-image: url('<?php echo url($banner_image); ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--color-primary);
            opacity: var(--banner-overlay-opacity);
            z-index: 0;
        }
        
        .page-header .container {
            position: relative;
            z-index: 1;
        }
    </style>
    
    <!-- Google Analytics -->
    <?php if (!empty($site_config['google_analytics'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $site_config['google_analytics']; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $site_config['google_analytics']; ?>');
    </script>
    <?php endif; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="<?php echo url(); ?>" class="logo">
                    <?php if (!empty($site_config['logo'])): ?>
                    <img src="<?php echo url('uploads/config/' . $site_config['logo']); ?>?v=<?php echo $site_config['css_version'] ?? '1'; ?>" alt="<?php echo $site_config['site_name'] ?? 'Maisons ORCA'; ?>" style="max-height: 50px; width: auto; display: block;">
                    <?php else: ?>
                    <div class="logo-text">Maisons <span>ORCA</span></div>
                    <?php endif; ?>
                </a>
                
                <nav class="nav">
                    <ul class="nav-list">
                        <li><a href="<?php echo url('index.php'); ?>" class="nav-link <?php echo $current_page == 'accueil' ? 'active' : ''; ?>">Accueil</a></li>
                        <li><a href="<?php echo url('constructeur.php'); ?>" class="nav-link <?php echo $current_page == 'constructeur' ? 'active' : ''; ?>">Le constructeur</a></li>
                        <li><a href="<?php echo url('modeles.php'); ?>" class="nav-link <?php echo $current_page == 'modeles' || $current_page == 'modele' ? 'active' : ''; ?>">Nos modèles</a></li>
                        <li><a href="<?php echo url('engagements.php'); ?>" class="nav-link <?php echo $current_page == 'engagements' ? 'active' : ''; ?>">Nos engagements</a></li>
                        <?php
                        // Pages CMS dynamiques marquées in_menu
                        try {
                            $menu_pages = $pdo->query("SELECT titre, slug, template FROM pages WHERE is_active = 1 AND in_menu = 1 ORDER BY menu_order ASC, titre ASC")->fetchAll();
                            foreach ($menu_pages as $mp):
                                $mp_slug = $mp['slug'];
                                // Si le slug est un fichier PHP qui existe, lier directement vers ce fichier
                                $mp_file = $mp_slug;
                                if (strpos($mp_file, '.php') === false) $mp_file .= '.php';
                                if (file_exists(__DIR__ . '/../' . $mp_file)) {
                                    $mp_url = url($mp_file);
                                } else {
                                    $mp_url = url('page.php?slug=' . $mp_slug);
                                }
                                $mp_active = ($current_page === pathinfo($mp_slug, PATHINFO_FILENAME)) ? 'active' : '';
                                if (isset($_GET['slug']) && $_GET['slug'] === $mp_slug) $mp_active = 'active';
                        ?>
                        <li><a href="<?php echo $mp_url; ?>" class="nav-link <?php echo $mp_active; ?>"><?php echo htmlspecialchars($mp['titre']); ?></a></li>
                        <?php
                            endforeach;
                        } catch (Exception $e) {}
                        ?>
                        <li><a href="<?php echo url('contact.php'); ?>" class="nav-link <?php echo $current_page == 'contact' ? 'active' : ''; ?>">Contact</a></li>
                    </ul>
                    
                    <div class="nav-cta">
                        <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" class="phone-link">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <?php echo $site_config['site_phone'] ?? '03 44 00 00 00'; ?>
                        </a>
                        <a href="<?php echo url('contact.php'); ?>" class="btn btn-primary btn-sm">Demander un devis</a>
                    </div>
                </nav>
                
                <button class="menu-toggle" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <main>
