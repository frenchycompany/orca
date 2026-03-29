<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ORCA - Accueil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #1a5653; color: white; padding: 20px; }
        .content { padding: 40px; max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORCA - Constructeur de maisons</h1>
    </div>
    <div class="content">
        <h2>Page d'accueil avec Chatbot</h2>
        <p>Cliquez sur CHAT en bas à droite.</p>
    </div>
    
    <!-- Chatbot -->
    <script>window.chatbotBaseUrl = '';</script>
    <script src="js/chatbot.js"></script>
</body>
</html>
