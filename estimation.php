<?php
/**
 * Landing page - Estimation chatbot plein écran
 * URL: /estimation.php
 * Objectif: conversion maximale via Google Ads / Facebook Ads
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
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_description; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background: #f5f7f9; min-height: 100vh; display: flex; flex-direction: column; }

        /* Header minimal */
        .lp-header {
            background: #fff;
            padding: 12px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .lp-logo { font-weight: 700; font-size: 20px; color: #1a5653; }
        .lp-logo span { color: #c41e3a; }
        .lp-phone { color: #1a5653; text-decoration: none; font-weight: 600; font-size: 15px; }
        .lp-phone:hover { text-decoration: underline; }

        /* Zone principale */
        .lp-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px 20px;
        }
        .lp-title {
            text-align: center;
            margin-bottom: 24px;
        }
        .lp-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a5653;
            margin-bottom: 8px;
        }
        .lp-title p {
            font-size: 16px;
            color: #666;
        }

        /* Chatbot inline (grand) */
        .lp-chatbot {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .lp-chat-header {
            background: linear-gradient(135deg, #1a5653, #0f3d3a);
            color: #fff;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .lp-chat-avatar {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .lp-chat-name { font-weight: 700; font-size: 16px; }
        .lp-chat-status { font-size: 12px; opacity: .7; }
        #lp-msgs {
            min-height: 320px;
            max-height: 420px;
            overflow-y: auto;
            padding: 20px;
            background: #f5f7f9;
        }
        #lp-form-area {
            display: none;
            padding: 16px 20px;
            background: #fff;
            border-top: 1px solid #eee;
        }
        #lp-form-area input {
            width: 100%; padding: 10px 12px; margin: 4px 0;
            border: 1px solid #ddd; border-radius: 8px;
            font-size: 14px; outline: none; box-sizing: border-box;
        }
        #lp-form-area input:focus { border-color: #1a5653; }
        #lp-err { display: none; color: #e74c3c; font-size: 12px; margin: 4px 0; }
        #lp-submit-btn {
            width: 100%; padding: 12px; margin-top: 8px;
            background: linear-gradient(135deg, #1a5653, #0f3d3a);
            color: #fff; border: none; border-radius: 8px;
            cursor: pointer; font-size: 15px; font-weight: 700;
        }
        #lp-submit-btn:hover { opacity: .9; }
        .lp-input-area {
            padding: 14px 20px;
            background: #fff;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }
        .lp-input-area input {
            flex: 1; padding: 11px 16px;
            border: 1px solid #ddd; border-radius: 24px;
            font-size: 14px; outline: none;
        }
        .lp-input-area input:focus { border-color: #1a5653; }
        .lp-input-area button {
            padding: 11px 20px; background: #1a5653; color: #fff;
            border: none; border-radius: 24px; cursor: pointer;
            font-size: 14px; font-weight: 700;
        }

        /* Réassurance */
        .lp-trust {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 24px;
            flex-wrap: wrap;
        }
        .lp-trust-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #555;
        }
        .lp-trust-icon { font-size: 18px; }

        .lp-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            flex-wrap: wrap;
        }
        .lp-stat { text-align: center; }
        .lp-stat-num { font-size: 24px; font-weight: 700; color: #1a5653; }
        .lp-stat-label { font-size: 12px; color: #888; }

        /* Messages & boutons - réutilise les mêmes styles que chatbot.js */
        .lp-msg {
            margin: 8px 0; padding: 12px 16px; border-radius: 16px;
            max-width: 88%; font-size: 14px; line-height: 1.6;
            word-wrap: break-word; animation: lpfade .3s ease;
        }
        .lp-msg-bot {
            background: #fff; color: #333; margin-right: auto;
            border: 1px solid #e8e8e8; border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .lp-msg-user {
            background: #1a5653; color: #fff; margin-left: auto;
            border-bottom-right-radius: 4px; max-width: 75%;
        }
        .lp-opts { margin: 8px 0; display: flex; flex-direction: column; gap: 5px; }
        .lp-opt {
            display: block; width: 100%; padding: 10px 14px;
            background: #fff; border: 1.5px solid #1a5653; border-radius: 10px;
            cursor: pointer; text-align: left; font-size: 14px;
            color: #1a5653; font-weight: 500; transition: all .15s;
        }
        .lp-opt:hover { background: #1a5653; color: #fff; }
        .cb-dot {
            width: 7px; height: 7px; background: #bbb; border-radius: 50%;
            display: inline-block; animation: cbdot 1.2s infinite;
        }
        .cb-dot:nth-child(2) { animation-delay: .2s; }
        .cb-dot:nth-child(3) { animation-delay: .4s; }

        @keyframes lpfade { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        @keyframes cbdot { 0%,60%,100% { transform:translateY(0); } 30% { transform:translateY(-8px); } }

        @media (max-width: 600px) {
            .lp-title h1 { font-size: 22px; }
            .lp-main { padding: 15px 10px; }
            .lp-chatbot { border-radius: 12px; }
            #lp-msgs { min-height: 250px; }
            .lp-trust { gap: 15px; }
        }
    </style>
</head>
<body>
    <!-- Header minimal -->
    <header class="lp-header">
        <div class="lp-logo">Maisons <span>ORCA</span></div>
        <a href="tel:<?php echo str_replace(' ', '', $site_config['site_phone'] ?? '0344000000'); ?>" class="lp-phone">
            📞 <?php echo $site_config['site_phone'] ?? '03 44 00 00 00'; ?>
        </a>
    </header>

    <!-- Main -->
    <main class="lp-main">
        <div class="lp-title">
            <h1>🏠 Estimez votre maison en 2 minutes</h1>
            <p>Gratuit, sans engagement, réponse sous 24h</p>
        </div>

        <!-- Chatbot inline -->
        <div class="lp-chatbot">
            <div class="lp-chat-header">
                <div class="lp-chat-avatar">🏠</div>
                <div>
                    <div class="lp-chat-name">Assistant ORCA</div>
                    <div class="lp-chat-status">En ligne — Constructeur depuis 1993</div>
                </div>
            </div>
            <div id="lp-msgs"></div>
            <div id="lp-form-area">
                <div style="font-weight:600;font-size:13px;margin-bottom:10px;color:#1a5653;">📋 Vos coordonnées</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <input id="lf-p" placeholder="Prénom *">
                    <input id="lf-n" placeholder="Nom *">
                </div>
                <input id="lf-e" type="email" placeholder="Email *">
                <input id="lf-t" type="tel" placeholder="Téléphone * (06 12 34 56 78)">
                <div id="lp-err"></div>
                <button id="lp-submit-btn">Recevoir mon estimation gratuite</button>
            </div>
            <div class="lp-input-area" id="lp-input-area">
                <input id="lp-input" type="text" placeholder="Posez votre question..." autocomplete="off">
                <button id="lp-send">Envoyer</button>
            </div>
        </div>

        <!-- Réassurance -->
        <div class="lp-trust">
            <div class="lp-trust-item"><span class="lp-trust-icon">✅</span> Gratuit</div>
            <div class="lp-trust-item"><span class="lp-trust-icon">🔒</span> Sans engagement</div>
            <div class="lp-trust-item"><span class="lp-trust-icon">📞</span> Rappel sous 24h</div>
            <div class="lp-trust-item"><span class="lp-trust-icon">🏠</span> Depuis 1993</div>
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

        // Init
        showTyping();
        post('action=init', function(d) {
            hideTyping();
            if (d.error) { addMsg(d.error, 'bot'); return; }
            chatId = d.conversation_id;
            currentStep = d.step || 1;
            if (d.is_new) {
                addMsg(d.message, 'bot');
                if (d.options) showBtns(d.options);
            } else if (d.history && d.history.length) {
                d.history.forEach(function(m) { addMsg(m.message, m.type); });
                if (d.type === 'form' || d.type === 'results_then_form') showForm();
            }
        });

        // Events
        q('lp-send').onclick = sendText;
        q('lp-input').onkeypress = function(e) { if (e.key === 'Enter') sendText(); };
        q('lp-submit-btn').onclick = submitForm;

        function sendText() {
            var t = q('lp-input').value.trim(); if (!t) return;
            q('lp-input').value = ''; send(t);
        }
        function send(val, label) {
            addMsg(label || val, 'user'); clearBtns(); hideForm(); showTyping();
            post('action=message&conversation_id=' + chatId + '&message=' + encodeURIComponent(val), function(d) {
                hideTyping();
                if (d.error) { addMsg(d.error, 'bot'); return; }
                currentStep = d.step || currentStep;
                if (d.message) addMsg(d.message, 'bot');
                if (d.type === 'form') showForm();
                else if (d.type === 'results_then_form') setTimeout(showForm, 1500);
                else if (d.type === 'final') { hideForm(); hideInput(); if (d.options) showBtns(d.options); }
                else if (d.options) showBtns(d.options);
            });
        }
        function submitForm() {
            var p=q('lf-p').value.trim(), n=q('lf-n').value.trim(), e=q('lf-e').value.trim(), t=q('lf-t').value.trim(), err=q('lp-err');
            var errs = [];
            if (!p||p.length<2) errs.push('Prénom'); if (!n||n.length<2) errs.push('Nom');
            if (!e||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)) errs.push('Email');
            if (!t||!/^0[1-9][\s.-]?(\d{2}[\s.-]?){4}$/.test(t)) errs.push('Téléphone');
            if (errs.length) { err.textContent='Champ(s) invalide(s) : '+errs.join(', '); err.style.display='block'; return; }
            err.style.display='none';
            var btn=q('lp-submit-btn'); btn.disabled=true; btn.textContent='Envoi...';
            addMsg(p+' '+n+' - '+e, 'user'); showTyping();
            post('action=form&conversation_id='+chatId+'&data='+encodeURIComponent(JSON.stringify({prenom:p,nom:n,email:e,telephone:t})), function(d) {
                hideTyping(); btn.disabled=false; btn.textContent='Recevoir mon estimation gratuite';
                if (d.error) { err.textContent=d.error; err.style.display='block'; return; }
                if (d.message) addMsg(d.message, 'bot');
                hideForm(); hideInput(); if (d.options) showBtns(d.options);
            });
        }

        function addMsg(text, type) {
            if (!text) return; var c=q('lp-msgs'), d=document.createElement('div');
            d.className = 'lp-msg '+(type==='bot'?'lp-msg-bot':'lp-msg-user');
            var s=text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            d.innerHTML=s.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>').replace(/→/g,'<span style="color:#1a5653;">→</span>');
            c.appendChild(d); c.scrollTop=c.scrollHeight;
        }
        function showBtns(opts) {
            if (!opts||!opts.length) return; var c=q('lp-msgs'), w=document.createElement('div'); w.className='lp-opts';
            opts.forEach(function(o) {
                var b=document.createElement('button'); b.className='lp-opt'; b.textContent=o.label;
                b.onclick=function() {
                    if (o.action==='close') return;
                    if (o.action==='link'&&o.url) { window.location.href=o.url; return; }
                    send(o.value, o.label);
                };
                w.appendChild(b);
            });
            c.appendChild(w); c.scrollTop=c.scrollHeight;
        }
        function clearBtns() { var a=document.querySelectorAll('.lp-opts'); for(var i=0;i<a.length;i++) a[i].style.display='none'; }
        function showForm() { q('lp-form-area').style.display='block'; q('lp-input-area').style.display='none'; q('lf-p').focus(); }
        function hideForm() { q('lp-form-area').style.display='none'; q('lp-input-area').style.display='flex'; }
        function hideInput() { q('lp-input-area').style.display='none'; q('lp-form-area').style.display='none'; }
        function showTyping() {
            if (q('lp-typ')) return; var c=q('lp-msgs'), d=document.createElement('div'); d.id='lp-typ';
            d.style.cssText='margin:8px 0;padding:12px 16px;background:#fff;border-radius:16px;border-bottom-left-radius:4px;display:inline-flex;gap:5px;border:1px solid #e8e8e8;';
            d.innerHTML='<span class="cb-dot"></span><span class="cb-dot"></span><span class="cb-dot"></span>';
            c.appendChild(d); c.scrollTop=c.scrollHeight;
        }
        function hideTyping() { var e=q('lp-typ'); if (e) e.remove(); }
        function post(body, cb) {
            var x=new XMLHttpRequest(); x.open('POST',apiUrl,true);
            x.setRequestHeader('Content-Type','application/x-www-form-urlencoded'); x.timeout=15000;
            x.onreadystatechange=function(){if(x.readyState===4){if(x.status===200){try{cb(JSON.parse(x.responseText));}catch(e){cb({error:'Erreur serveur'});}}else{cb({error:'Connexion impossible'});}}};
            x.ontimeout=function(){cb({error:'Délai dépassé'});}; x.send(body);
        }
    })();
    </script>
</body>
</html>
