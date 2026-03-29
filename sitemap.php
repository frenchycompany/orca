<?php
/**
 * Génération automatique du sitemap XML
 */
require_once 'includes/config.php';

// Récupérer les URLs à indexer
$urls = [];

// Page d'accueil
$urls[] = ['url' => url(''), 'priority' => '1.0', 'changefreq' => 'daily'];

// Pages statiques
$pages = ['modeles.php', 'constructeur.php', 'engagements.php', 'contact.php', 'blog.php', 'faq.php'];
foreach ($pages as $page) {
    $urls[] = ['url' => url($page), 'priority' => '0.8', 'changefreq' => 'weekly'];
}

// Modèles
$modeles = getModeles();
foreach ($modeles as $modele) {
    $urls[] = [
        'url' => url('modele.php?slug=' . $modele['slug']),
        'priority' => '0.9',
        'changefreq' => 'monthly'
    ];
}

// Actualités
$actualites = getActualites();
foreach ($actualites as $actu) {
    $urls[] = [
        'url' => url('actualite.php?slug=' . $actu['slug']),
        'priority' => '0.7',
        'changefreq' => 'monthly'
    ];
}

// Générer le XML
header('Content-Type: application/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

foreach ($urls as $item) {
    echo '  <url>' . PHP_EOL;
    echo '    <loc>' . htmlspecialchars($item['url']) . '</loc>' . PHP_EOL;
    echo '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
    echo '    <changefreq>' . $item['changefreq'] . '</changefreq>' . PHP_EOL;
    echo '    <priority>' . $item['priority'] . '</priority>' . PHP_EOL;
    echo '  </url>' . PHP_EOL;
}

echo '</urlset>';
