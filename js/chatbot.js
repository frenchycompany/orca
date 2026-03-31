/**
 * Chatbot ORCA - Widget intelligent
 * Recherche maisons + terrains → Formulaire capture lead
 */
(function() {
    'use strict';

    var chatId = null;
    var currentStep = 1;
    var apiUrl = (window.chatbotBaseUrl || '') + 'chatbot/api.php';

    // ==========================================
    // Widget HTML
    // ==========================================
    function createWidget() {
        var el = document.createElement('div');
        el.id = 'orca-cb';
        el.innerHTML =
            // Fenêtre chat
            '<div id="cb-win" style="display:none;position:fixed;bottom:90px;right:20px;width:400px;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.25);z-index:10000;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;overflow:hidden;flex-direction:column;">' +
                // Header
                '<div style="background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;padding:16px 20px;display:flex;align-items:center;gap:12px;">' +
                    '<div style="width:38px;height:38px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">🏠</div>' +
                    '<div style="flex:1;"><div style="font-weight:700;font-size:15px;">Assistant ORCA</div><div style="font-size:11px;opacity:.7;">Constructeur depuis 1993</div></div>' +
                    '<span id="cb-close" style="cursor:pointer;font-size:20px;opacity:.7;padding:4px 8px;transition:opacity .2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.7">✕</span>' +
                '</div>' +
                // Messages
                '<div id="cb-msgs" style="overflow-y:auto;padding:16px;background:#f5f7f9;min-height:200px;max-height:380px;"></div>' +
                // Formulaire coordonnées
                '<div id="cb-form" style="display:none;padding:14px 16px;background:#fff;border-top:1px solid #eee;">' +
                    '<div style="font-weight:600;font-size:13px;margin-bottom:10px;color:#1a5653;">📋 Vos coordonnées</div>' +
                    '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">' +
                        '<input id="cf-p" placeholder="Prénom *" style="padding:9px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;outline:none;">' +
                        '<input id="cf-n" placeholder="Nom *" style="padding:9px 10px;border:1px solid #ddd;border-radius:6px;font-size:13px;outline:none;">' +
                    '</div>' +
                    '<input id="cf-e" type="email" placeholder="Email *" style="width:100%;padding:9px 10px;margin:6px 0;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;font-size:13px;outline:none;">' +
                    '<input id="cf-t" type="tel" placeholder="Téléphone * (06 12 34 56 78)" style="width:100%;padding:9px 10px;margin:0 0 6px;border:1px solid #ddd;border-radius:6px;box-sizing:border-box;font-size:13px;outline:none;">' +
                    '<div id="cf-err" style="display:none;color:#e74c3c;font-size:12px;margin-bottom:6px;"></div>' +
                    '<button id="cf-btn" style="width:100%;padding:11px;background:linear-gradient(135deg,#1a5653,#0f3d3a);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;font-weight:700;transition:opacity .2s;" onmouseover="this.style.opacity=.9" onmouseout="this.style.opacity=1">Recevoir mon estimation gratuite</button>' +
                '</div>' +
                // Zone saisie texte
                '<div id="cb-inp" style="padding:12px 16px;background:#fff;border-top:1px solid #eee;display:flex;gap:8px;">' +
                    '<input id="cb-txt" type="text" placeholder="Posez votre question..." style="flex:1;padding:10px 14px;border:1px solid #ddd;border-radius:24px;font-size:13px;outline:none;" autocomplete="off">' +
                    '<button id="cb-go" style="padding:10px 16px;background:#1a5653;color:#fff;border:none;border-radius:24px;cursor:pointer;font-size:13px;font-weight:700;">Envoyer</button>' +
                '</div>' +
            '</div>' +
            // Bouton flottant
            '<div id="cb-fab" style="position:fixed;bottom:20px;right:20px;width:62px;height:62px;background:linear-gradient(135deg,#1a5653,#0f3d3a);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 15px rgba(26,86,83,0.4);z-index:10000;transition:transform .2s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'">' +
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>' +
            '</div>' +
            // Bulle d\'accroche (apparait après 8s)
            '<div id="cb-bubble" style="display:none;position:fixed;bottom:90px;right:20px;background:#fff;padding:12px 16px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.15);z-index:10000;max-width:260px;font-size:13px;cursor:pointer;animation:cbfade .4s ease;" onclick="document.getElementById(\'cb-bubble\').style.display=\'none\';toggleChat();">' +
                '<div style="font-weight:600;color:#1a5653;">🏠 Besoin d\'aide ?</div>' +
                '<div style="color:#555;margin-top:4px;">Je peux vous trouver la maison et le terrain idéal !</div>' +
                '<span onclick="event.stopPropagation();document.getElementById(\'cb-bubble\').style.display=\'none\';" style="position:absolute;top:6px;right:10px;cursor:pointer;color:#999;font-size:16px;">×</span>' +
            '</div>';
        document.body.appendChild(el);

        // Events
        q('cb-fab').onclick = toggleChat;
        q('cb-close').onclick = toggleChat;
        q('cb-go').onclick = sendText;
        q('cb-txt').onkeypress = function(e) { if (e.key === 'Enter') sendText(); };
        q('cf-btn').onclick = submitForm;

        // Bulle contextuelle après 6 secondes
        setTimeout(function() {
            if (q('cb-win').style.display !== 'flex') {
                // Adapter le message selon la page
                var path = window.location.pathname.toLowerCase();
                var bubbleTitle = q('cb-bubble').querySelector('div:first-child');
                var bubbleText = q('cb-bubble').querySelector('div:nth-child(2)');
                if (path.indexOf('modele') !== -1) {
                    bubbleTitle.textContent = '🏠 Ce modèle vous plaît ?';
                    bubbleText.textContent = 'Je peux vous donner un prix personnalisé !';
                } else if (path.indexOf('terrain') !== -1) {
                    bubbleTitle.textContent = '🌿 Vous cherchez un terrain ?';
                    bubbleText.textContent = 'J\'ai des parcelles disponibles dans votre secteur !';
                } else if (path.indexOf('contact') !== -1) {
                    bubbleTitle.textContent = '📞 Une question rapide ?';
                    bubbleText.textContent = 'Je peux vous répondre tout de suite !';
                } else if (path.indexOf('engagements') !== -1 || path.indexOf('constructeur') !== -1) {
                    bubbleTitle.textContent = '✅ Des questions sur nos garanties ?';
                    bubbleText.textContent = 'Je vous explique tout !';
                }
                q('cb-bubble').style.display = 'block';
            }
        }, 6000);
    }

    function q(id) { return document.getElementById(id); }

    // ==========================================
    // Toggle
    // ==========================================
    function toggleChat() {
        var win = q('cb-win');
        var open = win.style.display === 'flex';
        win.style.display = open ? 'none' : 'flex';
        q('cb-bubble').style.display = 'none';
        if (!open && !chatId) initChat();
        // Émettre un événement pour la bannière/exit intent
        if (!open) window.dispatchEvent(new Event('chatbot-opened'));
    }
    // Exposer globalement pour la bannière CTA et exit intent
    window.cbToggleChat = toggleChat;

    // ==========================================
    // Init
    // ==========================================
    function initChat() {
        showTyping();
        post('action=init', function(d) {
            hideTyping();
            if (d.disabled) { return; } // Chatbot désactivé
            if (d.error) { addMsg(d.error, 'bot'); return; }
            chatId = d.conversation_id;
            currentStep = d.step || 1;

            // Appliquer la config serveur (popup auto, délai, couleur)
            if (d.config) {
                if (d.config.auto_popup) {
                    var delay = (d.config.popup_delay || 20) * 1000;
                    setTimeout(function() {
                        if (q('cb-win').style.display !== 'flex') {
                            toggleChat(); // Ouvre le chat automatiquement
                        }
                    }, delay);
                }
            }

            if (d.is_new) {
                addMsg(d.message, 'bot');
                if (d.options) showBtns(d.options);
            } else if (d.history && d.history.length) {
                d.history.forEach(function(m) { addMsg(m.message, m.type); });
                if (d.type === 'form' || d.type === 'results_then_form') showForm();
            }
        });
    }

    // ==========================================
    // Envoyer texte
    // ==========================================
    function sendText() {
        var inp = q('cb-txt');
        var txt = inp.value.trim();
        if (!txt) return;
        inp.value = '';
        send(txt);
    }

    function send(val, label) {
        addMsg(label || val, 'user');
        clearBtns();
        hideForm();
        showTyping();

        post('action=message&conversation_id=' + chatId + '&message=' + encodeURIComponent(val), function(d) {
            hideTyping();
            if (d.error) { addMsg(d.error, 'bot'); return; }
            currentStep = d.step || currentStep;
            if (d.message) addMsg(d.message, 'bot');

            if (d.type === 'form') {
                showForm();
            } else if (d.type === 'results_then_form') {
                // Afficher résultats puis formulaire après 1.5s
                setTimeout(showForm, 1500);
            } else if (d.type === 'final') {
                hideForm(); hideInput();
                if (d.options) showBtns(d.options);
            } else if (d.options) {
                showBtns(d.options);
            }
        });
    }

    // ==========================================
    // Formulaire coordonnées
    // ==========================================
    function submitForm() {
        var p = q('cf-p').value.trim(), n = q('cf-n').value.trim();
        var e = q('cf-e').value.trim(), t = q('cf-t').value.trim();
        var err = q('cf-err');

        var errs = [];
        if (!p || p.length < 2) errs.push('Prénom');
        if (!n || n.length < 2) errs.push('Nom');
        if (!e || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)) errs.push('Email');
        if (!t || !/^0[1-9][\s.-]?(\d{2}[\s.-]?){4}$/.test(t)) errs.push('Téléphone');

        if (errs.length) {
            err.textContent = 'Champ(s) invalide(s) : ' + errs.join(', ');
            err.style.display = 'block';
            return;
        }
        err.style.display = 'none';

        var btn = q('cf-btn');
        btn.disabled = true; btn.textContent = 'Envoi...';

        addMsg(p + ' ' + n + ' - ' + e, 'user');
        showTyping();

        var payload = JSON.stringify({ prenom: p, nom: n, email: e, telephone: t });
        post('action=form&conversation_id=' + chatId + '&data=' + encodeURIComponent(payload), function(d) {
            hideTyping();
            btn.disabled = false; btn.textContent = 'Recevoir mon estimation gratuite';

            if (d.error) { err.textContent = d.error; err.style.display = 'block'; return; }
            if (d.message) addMsg(d.message, 'bot');

            hideForm(); hideInput();
            if (d.options) showBtns(d.options);
        });
    }

    // ==========================================
    // Messages
    // ==========================================
    function addMsg(text, type) {
        if (!text) return;
        var c = q('cb-msgs');
        var d = document.createElement('div');
        var bot = (type === 'bot');

        d.style.cssText = 'margin:8px 0;padding:12px 16px;border-radius:16px;max-width:88%;font-size:13px;line-height:1.6;word-wrap:break-word;animation:cbfade .3s ease;' +
            (bot ? 'background:#fff;color:#333;margin-right:auto;border:1px solid #e8e8e8;border-bottom-left-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.05);'
                 : 'background:#1a5653;color:#fff;margin-left:auto;border-bottom-right-radius:4px;max-width:75%;');

        var safe = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        d.innerHTML = safe.replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>').replace(/→/g,'<span style="color:#1a5653;">→</span>');

        c.appendChild(d);
        c.scrollTop = c.scrollHeight;
    }

    // ==========================================
    // Boutons
    // ==========================================
    function showBtns(opts) {
        if (!opts || !opts.length) return;
        var c = q('cb-msgs');
        var w = document.createElement('div');
        w.className = 'cb-opts';
        w.style.cssText = 'margin:8px 0;display:flex;flex-direction:column;gap:5px;';

        opts.forEach(function(o) {
            var b = document.createElement('button');
            b.textContent = o.label;
            b.style.cssText = 'display:block;width:100%;padding:10px 14px;background:#fff;border:1.5px solid #1a5653;border-radius:10px;cursor:pointer;text-align:left;font-size:13px;color:#1a5653;font-weight:500;transition:all .15s;';
            b.onmouseenter = function() { this.style.background='#1a5653'; this.style.color='#fff'; };
            b.onmouseleave = function() { this.style.background='#fff'; this.style.color='#1a5653'; };
            b.onclick = function() {
                if (o.action === 'close') { toggleChat(); return; }
                if (o.action === 'link' && o.url) { window.location.href = o.url; return; }
                send(o.value, o.label);
            };
            w.appendChild(b);
        });
        c.appendChild(w);
        c.scrollTop = c.scrollHeight;
    }

    function clearBtns() {
        var all = document.querySelectorAll('.cb-opts');
        for (var i = 0; i < all.length; i++) all[i].style.display = 'none';
    }

    // ==========================================
    // Form / Input visibility
    // ==========================================
    function showForm() {
        q('cb-form').style.display = 'block';
        q('cb-inp').style.display = 'none';
        setTimeout(function() { q('cf-p').focus(); }, 200);
    }
    function hideForm() {
        q('cb-form').style.display = 'none';
        q('cb-inp').style.display = 'flex';
    }
    function hideInput() {
        q('cb-inp').style.display = 'none';
        q('cb-form').style.display = 'none';
    }

    // ==========================================
    // Typing
    // ==========================================
    function showTyping() {
        if (q('cb-typ')) return;
        var c = q('cb-msgs');
        var d = document.createElement('div');
        d.id = 'cb-typ';
        d.style.cssText = 'margin:8px 0;padding:12px 16px;background:#fff;border-radius:16px;border-bottom-left-radius:4px;display:inline-flex;gap:5px;border:1px solid #e8e8e8;';
        d.innerHTML = '<span class="cb-dot"></span><span class="cb-dot"></span><span class="cb-dot"></span>';
        c.appendChild(d);
        c.scrollTop = c.scrollHeight;
    }
    function hideTyping() { var e = q('cb-typ'); if (e) e.remove(); }

    // ==========================================
    // AJAX
    // ==========================================
    function post(body, cb) {
        var x = new XMLHttpRequest();
        x.open('POST', apiUrl, true);
        x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        x.timeout = 15000;
        x.onreadystatechange = function() {
            if (x.readyState === 4) {
                if (x.status === 200) {
                    try { cb(JSON.parse(x.responseText)); }
                    catch(e) { cb({error:'Erreur serveur, réessayez.'}); }
                } else { cb({error:'Connexion impossible.'}); }
            }
        };
        x.ontimeout = function() { cb({error:'Délai dépassé.'}); };
        x.send(body);
    }

    // ==========================================
    // CSS
    // ==========================================
    function injectCSS() {
        var s = document.createElement('style');
        s.textContent =
            '@keyframes cbfade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}' +
            '@keyframes cbdot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-8px)}}' +
            '.cb-dot{width:7px;height:7px;background:#bbb;border-radius:50%;display:inline-block;animation:cbdot 1.2s infinite}' +
            '.cb-dot:nth-child(2){animation-delay:.2s}.cb-dot:nth-child(3){animation-delay:.4s}' +
            '#cb-form input:focus{border-color:#1a5653!important}' +
            '@media(max-width:480px){#cb-win{left:8px!important;right:8px!important;bottom:80px!important;width:auto!important;}#cb-bubble{display:none!important;}}';
        document.head.appendChild(s);
    }

    // ==========================================
    // Init
    // ==========================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { injectCSS(); createWidget(); });
    } else {
        injectCSS(); createWidget();
    }
})();
