<?php
/**
 * Landing page - Estimation chatbot conversationnel
 * URL: /estimation.php — Objectif: conversion maximale
 */
require_once __DIR__ . '/includes/config.php';

$page_title = 'Estimez votre maison en 2 minutes - Maisons ORCA';
$page_description = 'Obtenez une estimation gratuite et personnalisée pour votre projet de construction. Réponse sous 24h.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_description; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Poppins',sans-serif;background:#f5f7f9;min-height:100vh;display:flex;flex-direction:column}
        .lp-header{background:#fff;padding:12px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(0,0,0,0.06)}
        .lp-logo{font-weight:700;font-size:20px;color:#1a5653}.lp-logo span{color:#c41e3a}
        .lp-phone{color:#1a5653;text-decoration:none;font-weight:600;font-size:15px}
        .lp-main{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:25px 20px 20px}
        .lp-title{text-align:center;margin-bottom:20px}
        .lp-title h1{font-size:26px;font-weight:700;color:#1a5653;margin-bottom:6px}
        .lp-title p{font-size:15px;color:#666}
        .lp-chatbot{width:100%;max-width:520px;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.12);overflow:hidden;display:flex;flex-direction:column}
        .lp-chat-header{background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;padding:16px 22px;display:flex;align-items:center;gap:14px}
        .lp-chat-avatar{width:42px;height:42px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px}
        #lp-msgs{min-height:300px;max-height:420px;overflow-y:auto;padding:18px;background:#f5f7f9}
        .lp-inp{padding:12px 18px;background:#fff;border-top:1px solid #eee;display:flex;gap:8px}
        .lp-inp input{flex:1;padding:10px 14px;border:1px solid #ddd;border-radius:24px;font-size:13px;outline:none}
        .lp-inp input:focus{border-color:#1a5653}
        .lp-inp button{padding:10px 18px;background:#1a5653;color:#fff;border:none;border-radius:24px;cursor:pointer;font-size:13px;font-weight:700}
        .lp-msg{margin:6px 0;padding:10px 14px;border-radius:16px;max-width:88%;font-size:13.5px;line-height:1.55;word-wrap:break-word;animation:lpfade .3s ease}
        .lp-bot{background:#fff;color:#333;margin-right:auto;border:1px solid #e8e8e8;border-bottom-left-radius:4px;box-shadow:0 1px 2px rgba(0,0,0,0.04)}
        .lp-user{background:#1a5653;color:#fff;margin-left:auto;border-bottom-right-radius:4px;max-width:75%}
        .lp-chips{margin:8px 0;display:flex;flex-wrap:wrap;gap:6px;animation:lpfade .3s ease}
        .lp-chip{padding:8px 14px;background:#fff;border:1.5px solid #1a5653;border-radius:20px;cursor:pointer;font-size:12.5px;color:#1a5653;font-weight:500;transition:all .15s;white-space:nowrap}
        .lp-chip:hover{background:#1a5653;color:#fff}
        .cb-dot{width:6px;height:6px;background:#bbb;border-radius:50%;display:inline-block;animation:cbdot 1.2s infinite}
        .cb-dot:nth-child(2){animation-delay:.2s}.cb-dot:nth-child(3){animation-delay:.4s}
        .lp-trust{display:flex;justify-content:center;gap:25px;margin-top:22px;flex-wrap:wrap}
        .lp-trust-item{display:flex;align-items:center;gap:5px;font-size:13px;color:#555}
        .lp-stats{display:flex;justify-content:center;gap:35px;margin-top:18px;padding-top:18px;border-top:1px solid #e0e0e0;flex-wrap:wrap}
        .lp-stat{text-align:center}.lp-stat-num{font-size:22px;font-weight:700;color:#1a5653}.lp-stat-label{font-size:11px;color:#888}
        @keyframes lpfade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}
        @keyframes cbdot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-6px)}}
        @media(max-width:600px){.lp-title h1{font-size:20px}.lp-main{padding:12px 8px}#lp-msgs{min-height:240px}.lp-trust{gap:12px}}
    </style>
</head>
<body>
    <header class="lp-header">
        <div class="lp-logo">Maisons <span>ORCA</span></div>
        <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" class="lp-phone">
            📞 <?php echo $site_config['site_phone'] ?? '03 44 00 00 00'; ?>
        </a>
    </header>

    <main class="lp-main">
        <div class="lp-title">
            <h1>🏠 Estimez votre maison en 2 minutes</h1>
            <p>Gratuit, sans engagement, réponse sous 24h</p>
        </div>

        <div class="lp-chatbot">
            <div class="lp-chat-header">
                <div class="lp-chat-avatar">🏠</div>
                <div>
                    <div style="font-weight:700;font-size:15px;">Assistant ORCA</div>
                    <div style="font-size:11px;opacity:.7;">En ligne — Constructeur depuis 1993</div>
                </div>
            </div>
            <div id="lp-msgs"></div>
            <div class="lp-inp" id="lp-inp">
                <input id="lp-input" type="text" placeholder="Tapez votre message..." autocomplete="off">
                <button id="lp-send">↑</button>
            </div>
        </div>

        <div class="lp-trust">
            <div class="lp-trust-item">✅ Gratuit</div>
            <div class="lp-trust-item">🔒 Sans engagement</div>
            <div class="lp-trust-item">📞 Rappel sous 24h</div>
            <div class="lp-trust-item">🏠 Depuis 1993</div>
        </div>
        <div class="lp-stats">
            <div class="lp-stat"><div class="lp-stat-num">30+</div><div class="lp-stat-label">ans d'expérience</div></div>
            <div class="lp-stat"><div class="lp-stat-num">3000+</div><div class="lp-stat-label">maisons construites</div></div>
            <div class="lp-stat"><div class="lp-stat-num">6</div><div class="lp-stat-label">modèles</div></div>
            <div class="lp-stat"><div class="lp-stat-num">145k€</div><div class="lp-stat-label">à partir de</div></div>
        </div>
    </main>

    <script>
    (function() {
        var chatId = null, currentStep = 1;
        var apiUrl = '<?php echo url("chatbot/api.php"); ?>';
        function q(id) { return document.getElementById(id); }

        // Init immédiate
        showTyping();
        post('action=init', function(d) {
            hideTyping();
            if (d.error) { addMsg(d.error, 'bot'); return; }
            chatId = d.conversation_id;
            currentStep = d.step || 1;
            if (d.is_new) {
                addMsg(d.message, 'bot');
                if (d.options) showChips(d.options);
            } else if (d.history && d.history.length) {
                d.history.forEach(function(m) { addMsg(m.message, m.type); });
            }
        });

        q('lp-send').onclick = sendText;
        q('lp-input').onkeypress = function(e) { if (e.key === 'Enter') sendText(); };

        function sendText() {
            var t = q('lp-input').value.trim(); if (!t) return;
            q('lp-input').value = ''; send(t);
        }

        function send(val, label) {
            addMsg(label || val, 'user');
            clearChips();
            showTyping();

            post('action=message&conversation_id=' + chatId + '&message=' + encodeURIComponent(val), function(d) {
                hideTyping();
                if (d.error) { addMsg(d.error, 'bot'); return; }
                currentStep = d.step || currentStep;
                if (d.message) addMsg(d.message, 'bot');

                if (d.type === 'final') {
                    hideInput();
                    if (d.options) showChips(d.options);
                } else if (d.type === 'results_then_form') {
                    showChips([
                        {label: '👍 Ça m\'intéresse', value: 'coord', next: 50},
                        {label: '🔄 Autres critères', value: 'autre', next: 40},
                        {label: '❓ Question', value: 'go_question', next: 40}
                    ]);
                } else if (d.options) {
                    showChips(d.options);
                }

                // Adapter le placeholder
                if (d.field) {
                    var ph = {prenom:'Votre prénom...', nom:'Votre nom...', email:'Votre email...', telephone:'06 12 34 56 78...'};
                    q('lp-input').placeholder = ph[d.field] || 'Tapez votre message...';
                    q('lp-input').focus();
                } else {
                    q('lp-input').placeholder = 'Tapez votre message...';
                }
            });
        }

        function addMsg(text, type) {
            if (!text) return;
            var c = q('lp-msgs'), d = document.createElement('div');
            d.className = 'lp-msg ' + (type === 'bot' ? 'lp-bot' : 'lp-user');
            var s = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            d.innerHTML = s.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>').replace(/→/g,'<span style="color:#1a5653;">→</span>');
            c.appendChild(d); c.scrollTop = c.scrollHeight;
        }

        function showChips(opts) {
            if (!opts || !opts.length) return;
            var c = q('lp-msgs'), w = document.createElement('div');
            w.className = 'lp-chips';
            opts.forEach(function(o) {
                var b = document.createElement('button');
                b.className = 'lp-chip';
                b.textContent = o.label;
                b.onclick = function() {
                    if (o.action === 'close') return;
                    if (o.action === 'link' && o.url) { window.location.href = o.url; return; }
                    send(o.value, o.label);
                };
                w.appendChild(b);
            });
            c.appendChild(w); c.scrollTop = c.scrollHeight;
        }

        function clearChips() {
            var all = document.querySelectorAll('.lp-chips');
            for (var i = 0; i < all.length; i++) all[i].style.display = 'none';
        }

        function hideInput() { q('lp-inp').style.display = 'none'; }

        function showTyping() {
            if (q('lp-typ')) return;
            var c = q('lp-msgs'), d = document.createElement('div'); d.id = 'lp-typ';
            d.style.cssText = 'margin:6px 0;padding:10px 14px;background:#fff;border-radius:16px;border-bottom-left-radius:4px;display:inline-flex;gap:5px;border:1px solid #e8e8e8;';
            d.innerHTML = '<span class="cb-dot"></span><span class="cb-dot"></span><span class="cb-dot"></span>';
            c.appendChild(d); c.scrollTop = c.scrollHeight;
        }
        function hideTyping() { var e = q('lp-typ'); if (e) e.remove(); }

        function post(body, cb) {
            var x = new XMLHttpRequest(); x.open('POST', apiUrl, true);
            x.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); x.timeout = 15000;
            x.onreadystatechange = function() { if (x.readyState === 4) { if (x.status === 200) { try { cb(JSON.parse(x.responseText)); } catch(e) { cb({error:'Erreur serveur'}); } } else { cb({error:'Connexion impossible'}); } } };
            x.ontimeout = function() { cb({error:'Délai dépassé'}); };
            x.send(body);
        }
    })();
    </script>
</body>
</html>
