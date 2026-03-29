<?php
/**
 * Admin - Dashboard
 */
require_once '../includes/config.php';

// Vérifier si connecté
if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

// Stats
$stats = [
    'total_leads' => $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
    'new_leads' => $pdo->query("SELECT COUNT(*) FROM leads WHERE is_treated = 0")->fetchColumn(),
    'total_modeles' => $pdo->query("SELECT COUNT(*) FROM modeles WHERE is_active = 1")->fetchColumn(),
    'total_temoignages' => $pdo->query("SELECT COUNT(*) FROM temoignages WHERE is_active = 1")->fetchColumn()
];

// Derniers leads
$recent_leads = $pdo->query("SELECT l.*, m.nom as modele_nom FROM leads l LEFT JOIN modeles m ON l.modele_interesse = m.id ORDER BY l.created_at DESC LIMIT 5")->fetchAll();

include 'includes/admin-header.php';
?>

<div class="admin-content">
    <h1 class="admin-title">Tableau de bord</h1>
    
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #E3F2FD; color: #1565C0;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['total_leads']; ?></div>
                <div class="stat-label">Total leads</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #FFF3E0; color: #E65100;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['new_leads']; ?></div>
                <div class="stat-label">Nouveaux leads</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #E8F5E9; color: #2E7D32;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['total_modeles']; ?></div>
                <div class="stat-label">Modèles actifs</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #F3E5F5; color: #7B1FA2;">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['total_temoignages']; ?></div>
                <div class="stat-label">Témoignages</div>
            </div>
        </div>
    </div>
    
    <!-- Derniers leads -->
    <div class="admin-section">
        <div class="section-header">
            <h2>Derniers leads</h2>
            <a href="leads.php" class="btn btn-sm btn-primary">Voir tous</a>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Type</th>
                    <th>Modèle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_leads as $lead): ?>
                <tr class="<?php echo $lead['is_treated'] ? '' : 'unread'; ?>">
                    <td><?php echo date('d/m/Y H:i', strtotime($lead['created_at'])); ?></td>
                    <td><strong><?php echo clean($lead['prenom'] . ' ' . $lead['nom']); ?></strong></td>
                    <td><?php echo $lead['telephone']; ?></td>
                    <td><?php echo ucfirst($lead['type_demande']); ?></td>
                    <td><?php echo $lead['modele_nom'] ?? '-'; ?></td>
                    <td>
                        <?php if ($lead['is_treated']): ?>
                        <span class="badge badge-success">Traité</span>
                        <?php else: ?>
                        <span class="badge badge-warning">Nouveau</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="lead-view.php?id=<?php echo $lead['id']; ?>" class="btn btn-sm btn-outline">Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Accès rapide -->
    <div class="admin-section">
        <h2>Accès rapide</h2>
        <div class="quick-links">
            <a href="leads.php" class="quick-link">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Gérer les leads
            </a>
            <a href="modeles.php" class="quick-link">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Gérer les modèles
            </a>
            <a href="temoignages.php" class="quick-link">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Gérer les témoignages
            </a>
            <a href="actualites.php" class="quick-link">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                Gérer les actualités
            </a>
            <a href="config.php" class="quick-link">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configuration
            </a>
        </div>
    </div>
</div>

<?php include 'includes/admin-footer.php'; ?>
