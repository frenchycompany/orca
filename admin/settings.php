<?php
/**
 * Admin - Configuration du site (CSS, couleurs, bannière)
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$page_title = 'Configuration du site';
$success = false;
$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        // Mise à jour des configurations
        $configs = [
            'color_primary' => $_POST['color_primary'] ?? '#1a5653',
            'color_primary_dark' => $_POST['color_primary_dark'] ?? '#124a47',
            'color_primary_light' => $_POST['color_primary_light'] ?? '#2d7a76',
            'color_accent' => $_POST['color_accent'] ?? '#c9a227',
            'color_accent_dark' => $_POST['color_accent_dark'] ?? '#b08d20',
            'banner_overlay_opacity' => $_POST['banner_overlay_opacity'] ?? '0.85',
            'css_version' => time(), // Force le refresh du cache
        ];
        
        // Upload de l'image de bannière
        if (!empty($_FILES['banner_bg_image']['name'])) {
            $upload_dir = '../uploads/config/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_name = 'banner-bg-' . time() . '.' . pathinfo($_FILES['banner_bg_image']['name'], PATHINFO_EXTENSION);
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['banner_bg_image']['tmp_name'], $upload_path)) {
                $configs['banner_bg_image'] = 'uploads/config/' . $file_name;
            }
        } else {
            $configs['banner_bg_image'] = $_POST['current_banner_image'] ?? 'images/banner-default.jpg';
        }
        
        // Sauvegarder dans la base de données
        try {
            foreach ($configs as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO config (cle, valeur) 
                                      VALUES (?, ?) 
                                      ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
                $stmt->execute([$key, $value]);
            }
            
            // Recharger la config
            $stmt = $pdo->query("SELECT cle, valeur FROM config");
            while ($row = $stmt->fetch()) {
                $site_config[$row['cle']] = $row['valeur'];
            }
            
            $success = true;
        } catch (PDOException $e) {
            $error = 'Erreur lors de la sauvegarde : ' . $e->getMessage();
        }
    }
}

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Configuration du site</h1>
    
    <?php if ($success): ?>
    <div class="alert alert-success">
        <span class="alert-icon">✓</span>
        Configuration sauvegardée avec succès ! Le cache CSS a été rafraîchi.
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-error">
        <span class="alert-icon">✗</span>
        <?php echo $error; ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="admin-section">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <h2 style="margin-bottom: 20px; font-size: 18px;">🎨 Couleurs du site</h2>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Couleur principale</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="color_primary" value="<?php echo $site_config['color_primary'] ?? '#1a5653'; ?>" style="width: 60px; height: 40px; border: none; cursor: pointer;">
                    <input type="text" value="<?php echo $site_config['color_primary'] ?? '#1a5653'; ?>" class="form-control" style="width: 120px;" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Couleur principale (foncée)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="color_primary_dark" value="<?php echo $site_config['color_primary_dark'] ?? '#124a47'; ?>" style="width: 60px; height: 40px; border: none; cursor: pointer;">
                    <input type="text" value="<?php echo $site_config['color_primary_dark'] ?? '#124a47'; ?>" class="form-control" style="width: 120px;" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Couleur principale (clair)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="color_primary_light" value="<?php echo $site_config['color_primary_light'] ?? '#2d7a76'; ?>" style="width: 60px; height: 40px; border: none; cursor: pointer;">
                    <input type="text" value="<?php echo $site_config['color_primary_light'] ?? '#2d7a76'; ?>" class="form-control" style="width: 120px;" readonly>
                </div>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Couleur d'accent (boutons, liens)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="color_accent" value="<?php echo $site_config['color_accent'] ?? '#c9a227'; ?>" style="width: 60px; height: 40px; border: none; cursor: pointer;">
                    <input type="text" value="<?php echo $site_config['color_accent'] ?? '#c9a227'; ?>" class="form-control" style="width: 120px;" readonly>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Couleur d'accent (foncée)</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="color" name="color_accent_dark" value="<?php echo $site_config['color_accent_dark'] ?? '#b08d20'; ?>" style="width: 60px; height: 40px; border: none; cursor: pointer;">
                    <input type="text" value="<?php echo $site_config['color_accent_dark'] ?? '#b08d20'; ?>" class="form-control" style="width: 120px;" readonly>
                </div>
            </div>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <h2 style="margin-bottom: 20px; font-size: 18px;">🖼️ Bannière avec image de fond</h2>
        
        <div class="form-group">
            <label class="form-label">Image de fond actuelle</label>
            <?php 
            $current_banner = $site_config['banner_bg_image'] ?? '';
            $banner_path = '../' . $current_banner;
            if ($current_banner && file_exists($banner_path)): 
            ?>
            <div style="margin-bottom: 15px;">
                <img src="../<?php echo $current_banner; ?>?v=<?php echo time(); ?>" alt="Bannière actuelle" style="max-width: 400px; max-height: 150px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            </div>
            <?php else: ?>
            <p style="color: #666; font-style: italic;">Aucune image définie. La bannière utilisera la couleur principale.</p>
            <?php endif; ?>
            <input type="hidden" name="current_banner_image" value="<?php echo $current_banner; ?>">
            
            <label class="form-label">Nouvelle image (laisser vide pour garder l'actuelle)</label>
            <input type="file" name="banner_bg_image" class="form-control" accept="image/*">
            <small style="color: #666; display: block; margin-top: 5px;">Format recommandé : JPG ou PNG, 1920x400 pixels minimum</small>
        </div>
        
        <div class="form-group">
            <label class="form-label">Opacité de l'overlay (transparence)</label>
            <input type="range" name="banner_overlay_opacity" min="0" max="1" step="0.05" value="<?php echo $site_config['banner_overlay_opacity'] ?? '0.85'; ?>" style="width: 100%;" oninput="document.getElementById('opacity-value').textContent = this.value">
            <div style="text-align: center; margin-top: 5px;">
                <span id="opacity-value"><?php echo $site_config['banner_overlay_opacity'] ?? '0.85'; ?></span> 
                <small style="color: #666;">(0 = transparent, 1 = opaque)</small>
            </div>
        </div>
        
        <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="font-size: 14px; margin-bottom: 10px;">📌 Aperçu du rendu :</h3>
            <div style="display: flex; gap: 20px; align-items: center;">
                <?php 
                $preview_bg = ($current_banner && file_exists('../' . $current_banner)) 
                    ? 'url(../' . $current_banner . ')' 
                    : 'none';
                ?>
                <div style="width: 200px; height: 100px; background: <?php echo $preview_bg; ?> center/cover; background-color: <?php echo $site_config['color_primary'] ?? '#1a5653'; ?>; position: relative; border-radius: 8px; overflow: hidden;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: <?php echo $site_config['color_primary'] ?? '#1a5653'; ?>; opacity: <?php echo $site_config['banner_overlay_opacity'] ?? '0.85'; ?>;"></div>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold;">Aperçu</div>
                </div>
                <p style="font-size: 13px; color: #666; flex: 1;">
                    <?php if ($current_banner && file_exists('../' . $current_banner)): ?>
                    L'image de fond sera visible à travers la couleur principale selon l'opacité choisie.
                    <?php else: ?>
                    Aucune image définie. La bannière utilisera uniquement la couleur principale avec l'opacité choisie.
                    <?php endif; ?>
                    <br><br>
                    <strong>Conseil :</strong> Une opacité entre 0.7 et 0.9 permet de voir l'image tout en gardant un bon contraste pour le texte.
                </p>
            </div>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
        
        <h2 style="margin-bottom: 20px; font-size: 18px;">🔄 Cache CSS</h2>
        
        <div class="form-group">
            <label class="form-label">Version actuelle du CSS</label>
            <input type="text" value="<?php echo $site_config['css_version'] ?? '1.0.0'; ?>" class="form-control" style="width: 200px;" readonly>
            <small style="color: #666; display: block; margin-top: 5px;">
                La version est automatiquement incrémentée à chaque sauvegarde pour forcer le rafraîchissement du cache navigateur.
            </small>
        </div>
        
        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary btn-lg">💾 Sauvegarder les modifications</button>
            <a href="../index.php" target="_blank" class="btn btn-outline" style="margin-left: 10px;">👁️ Voir le site</a>
        </div>
    </form>
</div>

<style>
.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}
.form-group {
    flex: 1;
}
.form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
}
.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}
input[type="file"].form-control {
    padding: 8px;
}
input[type="range"] {
    -webkit-appearance: none;
    height: 8px;
    border-radius: 4px;
    background: #ddd;
    outline: none;
}
input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--color-primary, #1a5653);
    cursor: pointer;
}
</style>

<?php include 'includes/admin-footer.php'; ?>
