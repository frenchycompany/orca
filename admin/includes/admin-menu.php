<nav class="admin-nav">
    <ul>
        <li><a href="dashboard.php" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">🏠 Tableau de bord</a></li>
        <li><a href="leads.php" class="<?php echo $current_page === 'leads' ? 'active' : ''; ?>">👥 Leads</a></li>
        <li><a href="modeles.php" class="<?php echo $current_page === 'modeles' ? 'active' : ''; ?>">🏠 Modèles</a></li>
        <li><a href="temoignages.php" class="<?php echo $current_page === 'temoignages' ? 'active' : ''; ?>">⭐ Témoignages</a></li>
        <li><a href="chantiers.php" class="<?php echo $current_page === 'chantiers' ? 'active' : ''; ?>">🚧 Chantiers</a></li>
        <li><a href="actualites.php" class="<?php echo $current_page === 'actualites' ? 'active' : ''; ?>">📰 Actualités</a></li>
        <li><a href="faq.php" class="<?php echo $current_page === 'faq' ? 'active' : ''; ?>">❓ FAQ</a></li>
        <li><a href="pages.php" class="<?php echo $current_page === 'pages' ? 'active' : ''; ?>">📄 Pages</a></li>
        <li><a href="config.php" class="<?php echo $current_page === 'config' ? 'active' : ''; ?>">⚙️ Configuration</a></li>
        
        <!-- Menu Chatbot avec sous-menus -->
        <li class="has-submenu">
            <a href="chatbot.php" class="<?php echo in_array($current_page, ['chatbot', 'chatbot-learn', 'chatbot-reports', 'chatbot-abtest', 'chatbot-export', 'chatbot-settings']) ? 'active' : ''; ?>">
                🤖 Chatbot <?php if (in_array($current_page, ['chatbot', 'chatbot-learn', 'chatbot-reports', 'chatbot-abtest', 'chatbot-export', 'chatbot-settings'])) echo '▼'; ?>
            </a>
            <?php if (in_array($current_page, ['chatbot', 'chatbot-learn', 'chatbot-reports', 'chatbot-abtest', 'chatbot-export', 'chatbot-settings', 'chatbot-followups'])): ?>
            <ul class="submenu" style="display: block; background: rgba(0,0,0,0.2); margin-top: 5px; padding-left: 20px;">
                <li><a href="chatbot.php" class="<?php echo $current_page === 'chatbot' ? 'active' : ''; ?>">💬 Conversations</a></li>
                <li><a href="chatbot-learn.php" class="<?php echo $current_page === 'chatbot-learn' ? 'active' : ''; ?>">🧠 Apprendre</a></li>
                <li><a href="chatbot-reports.php" class="<?php echo $current_page === 'chatbot-reports' ? 'active' : ''; ?>">📊 Rapports</a></li>
                <li><a href="chatbot-abtest.php" class="<?php echo $current_page === 'chatbot-abtest' ? 'active' : ''; ?>">🧪 A/B Testing</a></li>
                <li><a href="chatbot-followups.php" class="<?php echo $current_page === 'chatbot-followups' ? 'active' : ''; ?>">📬 Relances</a></li>
                <li><a href="chatbot-export.php" class="<?php echo $current_page === 'chatbot-export' ? 'active' : ''; ?>">📥 Export</a></li>
                <li><a href="chatbot-settings.php" class="<?php echo $current_page === 'chatbot-settings' ? 'active' : ''; ?>">⚙️ Paramètres</a></li>
            </ul>
            <?php endif; ?>
        </li>
    </ul>
    
    <a href="logout.php" class="btn btn-outline" style="margin-top: 30px;">🚪 Déconnexion</a>
</nav>

<style>
.admin-nav .has-submenu {
    position: relative;
}
.admin-nav .submenu {
    display: none;
}
.admin-nav .has-submenu:hover .submenu {
    display: block;
}
.admin-nav .submenu li a {
    font-size: 14px;
    padding: 8px 15px;
}
</style>
