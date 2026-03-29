/**
 * Chatbot ORCA - Widget conversationnel
 * Questionnaire → Formulaire de coordonnées → Lead admin
 */
(function() {
    'use strict';

    var chatId = null;
    var currentStep = 1;
    var apiUrl = (window.chatbotBaseUrl || '') + 'chatbot/api.php';

    // ==========================================
    // Créer le widget HTML
    // ==========================================
    function createWidget() {
        var div = document.createElement('div');
        div.id = 'orca-chatbot';
        div.innerHTML =
            '<div id="cb-win" style="display:none;position:fixed;bottom:90px;right:20px;width:380px;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.25);z-index:10000;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;overflow:hidden;flex-direction:column;">' +
                '<div style="background:linear-gradient(135deg,#1a5653,#124a47);color:#fff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">' +
                    '<div><div style="font-weight:700;font-size:16px;">Assistant ORCA</div><div style="font-size:12px;opacity:.8;">En ligne</div></div>' +
                    '<span id="cb-close" style="cursor:pointer;font-size:22px;opacity:.8;padding:4px 8px;">✕</span>' +
                '</div>' +
                '<div id="cb-msgs" style="overflow-y:auto;padding:16px;background:#f5f7f9;min-height:200px;max-height:350px;"></div>' +
                // Formulaire de coordonnées (caché par défaut)
                '<div id="cb-form-area" style="display:none;padding:16px;background:#fff;border-top:1px solid #eee;">' +
                    '<div style="font-weight:600;font-size:14px;margin-bottom:12px;color:#1a5653;">Vos coordonnées</div>' +
                    '<input id="cf-prenom" placeholder="Prénom *" style="width:100%;padding:10px 12px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
                    '<input id="cf-nom" placeholder="Nom *" style="width:100%;padding:10px 12px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
                    '<input id="cf-email" type="email" placeholder="Email *" style="width:100%;padding:10px 12px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
                    '<input id="cf-tel" type="tel" placeholder="Téléphone * (ex: 06 12 34 56 78)" style="width:100%;padding:10px 12px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
                    '<div id="cf-error" style="display:none;color:#e74c3c;font-size:13px;margin:6px 0;"></div>' +
                    '<button id="cf-submit" style="width:100%;padding:12px;margin-top:8px;background:#1a5653;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:700;transition:background .2s;" onmouseover="this.style.background=\'#124a47\'" onmouseout="this.style.background=\'#1a5653\'">Envoyer mes coordonnées</button>' +
                '</div>' +
                // Zone de saisie texte
                '<div id="cb-input-area" style="padding:12px 16px;background:#fff;border-top:1px solid #eee;display:flex;gap:8px;">' +
                    '<input id="cb-input" type="text" placeholder="Votre message..." style="flex:1;padding:10px 14px;border:1px solid #ddd;border-radius:24px;font-size:14px;outline:none;" autocomplete="off">' +
                    '<button id="cb-send" style="padding:10px 18px;background:#1a5653;color:#fff;border:none;border-radius:24px;cursor:pointer;font-size:14px;font-weight:700;">Envoyer</button>' +
                '</div>' +
            '</div>' +
            // Bouton flottant
            '<div id="cb-fab" style="position:fixed;bottom:20px;right:20px;width:62px;height:62px;background:linear-gradient(135deg,#1a5653,#124a47);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 15px rgba(26,86,83,0.4);z-index:10000;transition:transform .2s;" onmouseover="this.style.transform=\'scale(1.1)\'" onmouseout="this.style.transform=\'scale(1)\'">' +
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>' +
            '</div>';
        document.body.appendChild(div);

        // Events
        document.getElementById('cb-fab').onclick = toggleChat;
        document.getElementById('cb-close').onclick = toggleChat;
        document.getElementById('cb-send').onclick = sendText;
        document.getElementById('cb-input').onkeypress = function(e) {
            if (e.key === 'Enter') sendText();
        };
        document.getElementById('cf-submit').onclick = submitForm;
    }

    // ==========================================
    // Toggle chat
    // ==========================================
    function toggleChat() {
        var win = document.getElementById('cb-win');
        var isOpen = win.style.display === 'flex';
        win.style.display = isOpen ? 'none' : 'flex';
        if (!isOpen && !chatId) initChat();
    }

    // ==========================================
    // Init conversation
    // ==========================================
    function initChat() {
        showTyping();
        post('action=init', function(data) {
            hideTyping();
            if (data.error) { addMsg(data.error, 'bot'); return; }

            chatId = data.conversation_id;
            currentStep = data.step || 1;

            if (data.is_new) {
                addMsg(data.message, 'bot');
                if (data.options) showOptions(data.options);
            } else if (data.history && data.history.length) {
                data.history.forEach(function(m) {
                    addMsg(m.message, m.type);
                });
                // Si on était sur le formulaire, le réafficher
                if (data.type === 'form') {
                    showFormArea();
                }
            }
        });
    }

    // ==========================================
    // Envoyer texte libre
    // ==========================================
    function sendText() {
        var input = document.getElementById('cb-input');
        var text = input.value.trim();
        if (!text) return;
        input.value = '';
        sendMessage(text);
    }

    // ==========================================
    // Envoyer un message (texte ou bouton)
    // ==========================================
    function sendMessage(value, label) {
        addMsg(label || value, 'user');
        clearOptions();
        showTyping();

        post('action=message&conversation_id=' + chatId + '&message=' + encodeURIComponent(value), function(data) {
            hideTyping();
            if (data.error) { addMsg('Erreur : ' + data.error, 'bot'); return; }

            currentStep = data.step || currentStep;

            if (data.message) addMsg(data.message, 'bot');

            // Selon le type de réponse
            if (data.type === 'form') {
                // Afficher le formulaire de coordonnées
                showFormArea();
            } else if (data.type === 'final') {
                // Conversation terminée
                hideFormArea();
                hideInputArea();
                if (data.options) showOptions(data.options);
            } else if (data.options) {
                hideFormArea();
                showOptions(data.options);
            }
        });
    }

    // ==========================================
    // Soumettre le formulaire de coordonnées
    // ==========================================
    function submitForm() {
        var prenom = document.getElementById('cf-prenom').value.trim();
        var nom = document.getElementById('cf-nom').value.trim();
        var email = document.getElementById('cf-email').value.trim();
        var tel = document.getElementById('cf-tel').value.trim();
        var errorDiv = document.getElementById('cf-error');

        // Validation côté client
        var errors = [];
        if (!prenom || prenom.length < 2) errors.push('Prénom requis (min. 2 lettres)');
        if (!nom || nom.length < 2) errors.push('Nom requis (min. 2 lettres)');
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Email invalide');
        if (!tel || !/^0[1-9][\s.-]?(\d{2}[\s.-]?){4}$/.test(tel)) errors.push('Téléphone invalide (ex: 06 12 34 56 78)');

        if (errors.length) {
            errorDiv.textContent = errors.join('. ');
            errorDiv.style.display = 'block';
            return;
        }
        errorDiv.style.display = 'none';

        var btn = document.getElementById('cf-submit');
        btn.disabled = true;
        btn.textContent = 'Envoi en cours...';

        addMsg('Coordonnées envoyées : ' + prenom + ' ' + nom, 'user');
        showTyping();

        var payload = JSON.stringify({
            prenom: prenom,
            nom: nom,
            email: email,
            telephone: tel
        });

        post('action=form&conversation_id=' + chatId + '&data=' + encodeURIComponent(payload), function(data) {
            hideTyping();
            btn.disabled = false;
            btn.textContent = 'Envoyer mes coordonnées';

            if (data.error) {
                errorDiv.textContent = data.error;
                errorDiv.style.display = 'block';
                return;
            }

            if (data.message) addMsg(data.message, 'bot');

            // Masquer le formulaire et la zone de saisie
            hideFormArea();
            hideInputArea();

            // Afficher les boutons finaux (voir modèles / fermer)
            if (data.options) showOptions(data.options);
        });
    }

    // ==========================================
    // Affichage des messages
    // ==========================================
    function addMsg(text, type) {
        if (!text) return;
        var container = document.getElementById('cb-msgs');
        var div = document.createElement('div');
        var isBot = (type === 'bot');

        div.style.cssText = 'margin:8px 0;padding:12px 16px;border-radius:16px;max-width:85%;font-size:14px;line-height:1.5;word-wrap:break-word;animation:cbfade .3s ease;' +
            (isBot
                ? 'background:#fff;color:#333;margin-right:auto;border:1px solid #e8e8e8;border-bottom-left-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.06);'
                : 'background:#1a5653;color:#fff;margin-left:auto;border-bottom-right-radius:4px;');

        // Sanitize puis formater markdown basique
        var safe = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        div.innerHTML = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');

        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    // ==========================================
    // Boutons d'options
    // ==========================================
    function showOptions(options) {
        if (!options || !options.length) return;
        var container = document.getElementById('cb-msgs');
        var wrapper = document.createElement('div');
        wrapper.className = 'cb-options';
        wrapper.style.cssText = 'margin:8px 0;display:flex;flex-direction:column;gap:6px;';

        options.forEach(function(opt) {
            var btn = document.createElement('button');
            btn.textContent = opt.label;
            btn.style.cssText = 'display:block;width:100%;padding:10px 14px;background:#f5f5f5;border:1px solid #ddd;border-radius:10px;cursor:pointer;text-align:left;font-size:14px;transition:all .2s;';
            btn.onmouseenter = function() { this.style.background='#1a5653'; this.style.color='#fff'; this.style.borderColor='#1a5653'; };
            btn.onmouseleave = function() { this.style.background='#f5f5f5'; this.style.color='inherit'; this.style.borderColor='#ddd'; };
            btn.onclick = function() {
                if (opt.action === 'close') { toggleChat(); return; }
                if (opt.action === 'link' && opt.url) { window.location.href = opt.url; return; }
                if (opt.next) sendMessage(opt.value, opt.label);
            };
            wrapper.appendChild(btn);
        });

        container.appendChild(wrapper);
        container.scrollTop = container.scrollHeight;
    }

    function clearOptions() {
        var opts = document.querySelectorAll('.cb-options');
        for (var i = 0; i < opts.length; i++) opts[i].style.display = 'none';
    }

    // ==========================================
    // Formulaire / zones de saisie
    // ==========================================
    function showFormArea() {
        document.getElementById('cb-form-area').style.display = 'block';
        document.getElementById('cb-input-area').style.display = 'none';
        document.getElementById('cf-prenom').focus();
    }

    function hideFormArea() {
        document.getElementById('cb-form-area').style.display = 'none';
        document.getElementById('cb-input-area').style.display = 'flex';
    }

    function hideInputArea() {
        document.getElementById('cb-input-area').style.display = 'none';
        document.getElementById('cb-form-area').style.display = 'none';
    }

    // ==========================================
    // Indicateur de frappe
    // ==========================================
    function showTyping() {
        if (document.getElementById('cb-typing')) return;
        var container = document.getElementById('cb-msgs');
        var div = document.createElement('div');
        div.id = 'cb-typing';
        div.style.cssText = 'margin:8px 0;padding:12px 16px;background:#fff;border-radius:16px;border-bottom-left-radius:4px;display:inline-flex;gap:5px;border:1px solid #e8e8e8;';
        div.innerHTML = '<span class="cb-dot"></span><span class="cb-dot"></span><span class="cb-dot"></span>';
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function hideTyping() {
        var el = document.getElementById('cb-typing');
        if (el) el.remove();
    }

    // ==========================================
    // AJAX
    // ==========================================
    function post(body, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', apiUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.timeout = 15000;
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try { callback(JSON.parse(xhr.responseText)); }
                    catch(e) { console.error('Chatbot parse error:', e); callback({error:'Erreur serveur'}); }
                } else {
                    console.error('Chatbot HTTP error:', xhr.status);
                    callback({error:'Erreur de connexion au serveur'});
                }
            }
        };
        xhr.ontimeout = function() { callback({error:'Délai dépassé'}); };
        xhr.send(body);
    }

    // ==========================================
    // CSS
    // ==========================================
    function injectCSS() {
        var style = document.createElement('style');
        style.textContent =
            '@keyframes cbfade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}' +
            '@keyframes cbdot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-8px)}}' +
            '.cb-dot{width:8px;height:8px;background:#ccc;border-radius:50%;display:inline-block;animation:cbdot 1.2s infinite}' +
            '.cb-dot:nth-child(2){animation-delay:.2s}' +
            '.cb-dot:nth-child(3){animation-delay:.4s}' +
            '@media(max-width:480px){#cb-win{left:10px!important;right:10px!important;bottom:80px!important;width:auto!important;}}';
        document.head.appendChild(style);
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
