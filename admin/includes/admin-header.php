<?php
/**
 * Admin Header
 */
if (!isset($_SESSION['admin_logged_in'])) {
    redirect('index.php');
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin ORCA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primary: #C41E3A;
            --color-primary-dark: #9A1830;
            --color-dark: #1A1A1A;
            --color-gray: #7F7F7F;
            --color-gray-light: #E8E8E8;
            --color-gray-lighter: #F5F5F5;
            --color-white: #FFFFFF;
            --color-success: #27AE60;
            --color-warning: #F39C12;
            --color-error: #E74C3C;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--color-gray-lighter);
            min-height: 100vh;
        }
        
        /* Admin Layout */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .admin-sidebar {
            width: 260px;
            background: var(--color-dark);
            color: var(--color-white);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .admin-logo {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .admin-logo a {
            color: var(--color-white);
            text-decoration: none;
            font-size: 20px;
            font-weight: 700;
        }
        
        .admin-logo a span {
            color: var(--color-primary);
        }
        
        .admin-nav {
            padding: 20px 0;
        }
        
        .admin-nav ul {
            list-style: none;
        }
        
        .admin-nav li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .admin-nav li a:hover,
        .admin-nav li a.active {
            color: var(--color-white);
            background: rgba(255,255,255,0.05);
            border-left: 3px solid var(--color-primary);
        }
        
        .admin-nav li a svg {
            width: 20px;
            height: 20px;
        }
        
        .admin-nav-section {
            padding: 10px 20px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255,255,255,0.4);
            margin-top: 10px;
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 260px;
        }
        
        .admin-header {
            background: var(--color-white);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .admin-header-title {
            font-size: 20px;
            font-weight: 600;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-user-name {
            font-weight: 500;
        }
        
        .btn-logout {
            padding: 8px 16px;
            background: var(--color-gray-lighter);
            color: var(--color-dark);
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-logout:hover {
            background: var(--color-gray-light);
        }
        
        /* Content */
        .admin-content {
            padding: 30px;
        }
        
        .admin-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--color-white);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--color-gray);
        }
        
        /* Sections */
        .admin-section {
            background: var(--color-white);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            font-size: 18px;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .btn-primary {
            background: var(--color-primary);
            color: var(--color-white);
        }
        
        .btn-primary:hover {
            background: var(--color-primary-dark);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--color-dark);
            border: 1px solid var(--color-gray-light);
        }
        
        .btn-outline:hover {
            background: var(--color-gray-lighter);
        }
        
        .btn-success {
            background: var(--color-success);
            color: var(--color-white);
        }
        
        .btn-danger {
            background: var(--color-error);
            color: var(--color-white);
        }
        
        .btn-danger:hover {
            background: #d9363e;
        }
        
        .btn-icon {
            padding: 8px;
            border-radius: 6px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .btn-icon:hover {
            transform: scale(1.05);
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-icon {
            font-size: 18px;
            font-weight: bold;
        }
        
        /* Table */
        .actions-cell {
            white-space: nowrap;
            width: 1%;
        }
        
        .actions-cell .btn-icon + .btn-icon {
            margin-left: 8px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--color-gray-light);
        }
        
        .admin-table th {
            font-weight: 600;
            color: var(--color-gray);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .admin-table tr:hover {
            background: var(--color-gray-lighter);
        }
        
        .admin-table tr.unread {
            background: #FFF8E1;
        }
        
        .admin-table tr.unread td {
            font-weight: 500;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background: #D4EDDA;
            color: #155724;
        }
        
        .badge-warning {
            background: #FFF3CD;
            color: #856404;
        }
        
        .badge-error {
            background: #F8D7DA;
            color: #721C24;
        }
        
        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .quick-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: var(--color-gray-lighter);
            border-radius: 10px;
            text-decoration: none;
            color: var(--color-dark);
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .quick-link:hover {
            background: var(--color-gray-light);
        }
        
        .quick-link svg {
            width: 24px;
            height: 24px;
            color: var(--color-primary);
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #D4EDDA;
            color: #155724;
        }
        
        .alert-error {
            background: #F8D7DA;
            color: #721C24;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-links {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <a href="dashboard.php">Maisons <span>ORCA</span></a>
            </div>
            
            <nav class="admin-nav">
                <ul>
                    <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Tableau de bord
                    </a></li>
                    <li><a href="leads.php" class="<?php echo $current_page == 'leads' || $current_page == 'lead-view' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Leads
                    </a></li>
                    <li><a href="chatbot.php" class="<?php echo $current_page == 'chatbot' || $current_page == 'chatbot-view' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        🤖 Chatbot Stats
                    </a></li>
                    <li><a href="chatbot-intentions.php" class="<?php echo $current_page == 'chatbot-intentions' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        🎓 Apprentissage
                    </a></li>
                    
                    <li class="admin-nav-section">Contenu</li>
                    <li><a href="textes.php" class="<?php echo $current_page == 'textes' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Textes du site
                    </a></li>

                    <li><a href="modeles.php" class="<?php echo $current_page == 'modeles' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Modèles
                    </a></li>
                    <li><a href="terrains.php" class="<?php echo $current_page == 'terrains' || $current_page == 'terrain-edit' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Terrains
                    </a></li>
                    <li><a href="temoignages.php" class="<?php echo $current_page == 'temoignages' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Témoignages
                    </a></li>
                    <li><a href="actualites.php" class="<?php echo $current_page == 'actualites' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        Actualités
                    </a></li>
                    <li><a href="faq.php" class="<?php echo $current_page == 'faq' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        FAQ
                    </a></li>
                    <li><a href="pages.php" class="<?php echo $current_page == 'pages' || $current_page == 'page-edit' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Pages CMS
                    </a></li>
                    
                    <li class="admin-nav-section">Configuration</li>
                    
                    <li><a href="logs.php" class="<?php echo $current_page == 'logs' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Logs
                    </a></li>
                    <li><a href="backup.php" class="<?php echo $current_page == 'backup' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        Backup BDD
                    </a></li>
                    <li><a href="cache.php" class="<?php echo $current_page == 'cache' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Cache
                    </a></li>
                    <li><a href="newsletter.php" class="<?php echo $current_page == 'newsletter' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Newsletter
                    </a></li>
                    <li><a href="settings.php" class="<?php echo $current_page == 'settings' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        🎨 Apparence
                    </a></li>
                    <li><a href="config.php" class="<?php echo $current_page == 'config' ? 'active' : ''; ?>">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Paramètres
                    </a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="admin-main">
            <header class="admin-header">
                <h1 class="admin-header-title"><?php echo isset($page_title) ? $page_title : 'Administration'; ?></h1>
                <div class="admin-user">
                    <span class="admin-user-name"><?php echo $_SESSION['admin_username']; ?></span>
                    <a href="logout.php" class="btn-logout">Déconnexion</a>
                </div>
            </header>
            
            <?php displayFlashMessage(); ?>
