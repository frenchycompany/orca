<?php
/**
 * Admin - View Lead
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    redirect('leads.php');
}

// Récupérer le lead
$stmt = $pdo->prepare("SELECT l.*, m.nom as modele_nom FROM leads l LEFT JOIN modeles m ON l.modele_interesse = m.id WHERE l.id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
    redirect('leads.php');
}

// Marquer comme traité si demandé
if (isset($_GET['action']) && $_GET['action'] == 'treat') {
    $pdo->prepare("UPDATE leads SET is_treated = 1, treated_at = NOW(), assigned_to = ? WHERE id = ?")->execute([$_SESSION['admin_id'], $id]);
    setFlashMessage('success', 'Lead marqué comme traité');
    redirect('lead-view.php?id=' . $id);
}

$page_title = 'Détail du lead';

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 class="admin-title">Détail du lead</h1>
        <div>
            <a href="leads.php" class="btn btn-outline">← Retour à la liste</a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Informations principales -->
        <div class="admin-section">
            <div class="section-header">
                <h2>Informations</h2>
                <?php if (!$lead['is_treated']): ?>
                <a href="?id=<?php echo $id; ?>&action=treat" class="btn btn-success">Marquer comme traité</a>
                <?php endif; ?>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Nom</label>
                    <p style="font-weight: 600; font-size: 18px;"><?php echo clean($lead['civilite'] . ' ' . $lead['prenom'] . ' ' . $lead['nom']); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Type de demande</label>
                    <p style="font-weight: 600;"><?php echo ucfirst($lead['type_demande']); ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Email</label>
                    <p><a href="mailto:<?php echo $lead['email']; ?>"><?php echo $lead['email']; ?></a></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Téléphone</label>
                    <p><a href="tel:<?php echo str_replace(' ', '', $lead['telephone']); ?>"><?php echo $lead['telephone']; ?></a></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Localisation</label>
                    <p><?php echo $lead['ville'] ? clean($lead['ville'] . ' (' . $lead['code_postal'] . ')') : '-'; ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Modèle intéressé</label>
                    <p><?php echo $lead['modele_nom'] ?? 'Non spécifié'; ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Terrain</label>
                    <p><?php echo $lead['terrain_prevu'] ? 'Oui' : 'Non, recherche en cours'; ?></p>
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Délai souhaité</label>
                    <p><?php 
                        $delais = [
                            'immediatement' => 'Immédiatement',
                            '3-mois' => 'Dans 3 mois',
                            '6-mois' => 'Dans 6 mois',
                            '1-an' => 'Dans 1 an',
                            'plus' => 'Plus tard'
                        ];
                        echo $delais[$lead['delai_souhaite']] ?? $lead['delai_souhaite'];
                    ?></p>
                </div>
            </div>
            
            <?php if ($lead['commentaire']): ?>
            <div style="margin-top: 30px;">
                <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Message</label>
                <div style="background: var(--color-gray-lighter); padding: 15px; border-radius: 8px; margin-top: 10px;">
                    <?php echo nl2br(clean($lead['commentaire'])); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Informations secondaires -->
        <div>
            <div class="admin-section">
                <h3>Statut</h3>
                <p style="margin: 15px 0;">
                    <?php if ($lead['is_treated']): ?>
                    <span class="badge badge-success">Traité</span>
                    <?php else: ?>
                    <span class="badge badge-warning">Nouveau</span>
                    <?php endif; ?>
                </p>
                
                <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase;">Reçu le</label>
                <p><?php echo date('d/m/Y à H:i', strtotime($lead['created_at'])); ?></p>
                
                <?php if ($lead['is_treated']): ?>
                <label style="font-size: 12px; color: var(--color-gray); text-transform: uppercase; margin-top: 15px; display: block;">Traité le</label>
                <p><?php echo $lead['treated_at'] ? date('d/m/Y à H:i', strtotime($lead['treated_at'])) : '-'; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="admin-section">
                <h3>Source</h3>
                <p><strong>Page source :</strong> <?php echo $lead['page_source'] ?? '-'; ?></p>
                <p style="margin-top: 10px;"><strong>IP :</strong> <?php echo $lead['ip_address'] ?? '-'; ?></p>
            </div>
            
            <div class="admin-section">
                <h3>Actions rapides</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="mailto:<?php echo $lead['email']; ?>" class="btn btn-primary">Envoyer un email</a>
                    <a href="tel:<?php echo str_replace(' ', '', $lead['telephone']); ?>" class="btn btn-outline">Appeler</a>
                    <?php if (!$lead['is_treated']): ?>
                    <a href="?id=<?php echo $id; ?>&action=treat" class="btn btn-success">Marquer traité</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
