<?php
/**
 * Admin - Configuration globale du site
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Configuration du site';

// Récupérer toutes les configs
$configs = [];
$stmt = $pdo->query("SELECT * FROM config ORDER BY id");
while ($row = $stmt->fetch()) {
    $configs[$row['cle']] = $row['valeur'];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name', 'site_slogan', 'site_description', 'site_email', 'site_phone', 'site_address',
        'site_fax', 'site_siret', 'meta_description', 'meta_keywords',
        'facebook_url', 'instagram_url', 'linkedin_url', 'youtube_url',
        'google_analytics', 'google_maps_api',
        'color_primary', 'color_secondary', 'color_dark',
        'footer_text', 'horaires', 'zone_intervention'
    ];
    
    foreach ($fields as $field) {
        $valeur = $_POST[$field] ?? '';
        $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = ?");
        $stmt->execute([$field, $valeur, $valeur]);
    }
    
    // Gestion des uploads d'images
    $upload_dir = '../uploads/config/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Upload logo
    if (!empty($_FILES['logo']['name'])) {
        $logo_name = 'logo.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name);
        $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) VALUES ('logo', ?) ON DUPLICATE KEY UPDATE valeur = ?");
        $stmt->execute([$logo_name, $logo_name]);
    }
    
    // Upload favicon
    if (!empty($_FILES['favicon']['name'])) {
        $favicon_name = 'favicon.' . pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['favicon']['tmp_name'], $upload_dir . $favicon_name);
        $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) VALUES ('favicon', ?) ON DUPLICATE KEY UPDATE valeur = ?");
        $stmt->execute([$favicon_name, $favicon_name]);
    }
    
    // Upload image par défaut
    if (!empty($_FILES['image_default']['name'])) {
        $img_name = 'default.' . pathinfo($_FILES['image_default']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image_default']['tmp_name'], $upload_dir . $img_name);
        $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) VALUES ('image_default', ?) ON DUPLICATE KEY UPDATE valeur = ?");
        $stmt->execute([$img_name, $img_name]);
    }
    
    header('Location: config.php?success=1');
    exit;
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Configuration du site</h1>
    
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">Configuration enregistrée avec succès !</div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        
        <!-- SECTION : Identité du site -->
        <div class="admin-section">
            <h2>🏢 Identité du site</h2>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nom du site</label>
                    <input type="text" name="site_name" class="form-control" value="<?php echo htmlspecialchars($configs['site_name'] ?? 'Maisons ORCA'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Slogan (affiché sur la bannière)</label>
                    <input type="text" name="site_slogan" class="form-control" value="<?php echo htmlspecialchars($configs['site_slogan'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Description accueil (sous le slogan)</label>
                    <textarea name="site_description" class="form-control" rows="2"><?php echo htmlspecialchars($configs['site_description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Logo actuel</label>
                    <?php if (!empty($configs['logo'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../uploads/config/<?php echo $configs['logo']; ?>" alt="Logo" style="max-height: 60px; background: #f5f5f5; padding: 10px; border-radius: 8px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="logo" class="form-control" accept="image/*">
                    <small style="color: var(--color-gray);">Format recommandé : PNG transparent, hauteur 60px</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Favicon</label>
                    <?php if (!empty($configs['favicon'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../uploads/config/<?php echo $configs['favicon']; ?>" alt="Favicon" style="width: 32px; height: 32px;">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="favicon" class="form-control" accept=".ico,.png">
                    <small style="color: var(--color-gray);">Format : .ico ou .png (32x32px)</small>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Image par défaut (pour les articles sans image)</label>
                <?php if (!empty($configs['image_default'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../uploads/config/<?php echo $configs['image_default']; ?>" alt="Default" style="max-height: 100px; border-radius: 8px;">
                </div>
                <?php endif; ?>
                <input type="file" name="image_default" class="form-control" accept="image/*">
            </div>
        </div>
        
        <!-- SECTION : Couleurs -->
        <div class="admin-section">
            <h2>🎨 Couleurs du site</h2>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label">Couleur principale</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color" name="color_primary" value="<?php echo $configs['color_primary'] ?? '#C41E3A'; ?>" style="width: 60px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
                        <input type="text" name="color_primary" class="form-control" value="<?php echo $configs['color_primary'] ?? '#C41E3A'; ?>" style="flex: 1;">
                    </div>
                    <small style="color: var(--color-gray);">Rouge ORCA actuel</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Couleur secondaire</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color" name="color_secondary" value="<?php echo $configs['color_secondary'] ?? '#2C3E50'; ?>" style="width: 60px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
                        <input type="text" name="color_secondary" class="form-control" value="<?php echo $configs['color_secondary'] ?? '#2C3E50'; ?>" style="flex: 1;">
                    </div>
                    <small style="color: var(--color-gray);">Bleu foncé</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Couleur sombre</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color" name="color_dark" value="<?php echo $configs['color_dark'] ?? '#1A1A1A'; ?>" style="width: 60px; height: 40px; border: none; border-radius: 8px; cursor: pointer;">
                        <input type="text" name="color_dark" class="form-control" value="<?php echo $configs['color_dark'] ?? '#1A1A1A'; ?>" style="flex: 1;">
                    </div>
                    <small style="color: var(--color-gray);">Noir/textes</small>
                </div>
            </div>
        </div>
        
        <!-- SECTION : Contact -->
        <div class="admin-section">
            <h2>📞 Informations de contact</h2>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="site_email" class="form-control" value="<?php echo htmlspecialchars($configs['site_email'] ?? 'contact@maisons-orca.fr'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="site_phone" class="form-control" value="<?php echo htmlspecialchars($configs['site_phone'] ?? '03 44 00 00 00'); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Adresse postale</label>
                <textarea name="site_address" class="form-control" rows="2"><?php echo htmlspecialchars($configs['site_address'] ?? "119 rue Bordier\n60150 Longueil Annel"); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Fax</label>
                    <input type="text" name="site_fax" class="form-control" value="<?php echo htmlspecialchars($configs['site_fax'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">SIRET</label>
                    <input type="text" name="site_siret" class="form-control" value="<?php echo htmlspecialchars($configs['site_siret'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Horaires d'ouverture</label>
                <textarea name="horaires" class="form-control" rows="3"><?php echo htmlspecialchars($configs['horaires'] ?? "Lundi au Vendredi : 9h-12h / 14h-18h\nSamedi : 10h-17h sur rendez-vous"); ?></textarea>
            </div>
        </div>
        
        <!-- SECTION : SEO -->
        <div class="admin-section">
            <h2>🔍 SEO & Meta</h2>
            <div class="form-group">
                <label class="form-label">Meta description par défaut</label>
                <textarea name="meta_description" class="form-control" rows="2"><?php echo htmlspecialchars($configs['meta_description'] ?? ''); ?></textarea>
                <small style="color: var(--color-gray);">Description qui apparaît dans Google (160 caractères max)</small>
            </div>
            <div class="form-group">
                <label class="form-label">Mots-clés (séparés par des virgules)</label>
                <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($configs['meta_keywords'] ?? ''); ?>">
            </div>
        </div>
        
        <!-- SECTION : Réseaux sociaux -->
        <div class="admin-section">
            <h2>📱 Réseaux sociaux</h2>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Facebook</label>
                    <input type="url" name="facebook_url" class="form-control" value="<?php echo htmlspecialchars($configs['facebook_url'] ?? ''); ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="form-group">
                    <label class="form-label">Instagram</label>
                    <input type="url" name="instagram_url" class="form-control" value="<?php echo htmlspecialchars($configs['instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/...">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">LinkedIn</label>
                    <input type="url" name="linkedin_url" class="form-control" value="<?php echo htmlspecialchars($configs['linkedin_url'] ?? ''); ?>" placeholder="https://linkedin.com/...">
                </div>
                <div class="form-group">
                    <label class="form-label">YouTube</label>
                    <input type="url" name="youtube_url" class="form-control" value="<?php echo htmlspecialchars($configs['youtube_url'] ?? ''); ?>" placeholder="https://youtube.com/...">
                </div>
            </div>
        </div>
        
        <!-- SECTION : Intégrations -->
        <div class="admin-section">
            <h2>🔧 Intégrations</h2>
            <div class="form-group">
                <label class="form-label">Google Analytics ID (GA4)</label>
                <input type="text" name="google_analytics" class="form-control" value="<?php echo htmlspecialchars($configs['google_analytics'] ?? ''); ?>" placeholder="G-XXXXXXXXXX">
                <small style="color: var(--color-gray);">Format : G-XXXXXXXXXX</small>
            </div>
            <div class="form-group">
                <label class="form-label">Google Maps API Key</label>
                <input type="text" name="google_maps_api" class="form-control" value="<?php echo htmlspecialchars($configs['google_maps_api'] ?? ''); ?>">
            </div>
        </div>
        
        <!-- SECTION : Contenu -->
        <div class="admin-section">
            <h2>📝 Contenu spécifique</h2>
            <div class="form-group">
                <label class="form-label">Zone d'intervention (affichée sur la page d'accueil)</label>
                <textarea name="zone_intervention" class="form-control" rows="3"><?php echo htmlspecialchars($configs['zone_intervention'] ?? "Oise (60), Aisne (02), Somme (80), Seine-et-Marne (77), Val-d'Oise (95), Val-de-Marne (94), Seine-Saint-Denis (93), Essonne (91)"); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Texte du footer</label>
                <textarea name="footer_text" class="form-control" rows="2"><?php echo htmlspecialchars($configs['footer_text'] ?? "Constructeur de maisons individuelles depuis 1993. 6 modèles de qualité à prix maîtrisé."); ?></textarea>
            </div>
        </div>
        
        <div>
            <button type="submit" class="btn btn-primary btn-lg">💾 Enregistrer toute la configuration</button>
        </div>
        
    </form>
</div>

<?php include 'includes/admin-footer.php'; ?>
