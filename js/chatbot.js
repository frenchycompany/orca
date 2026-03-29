/**
 * Chatbot ORCA - Widget conversationnel
 * Objectif : guider le visiteur et obtenir ses coordonnées
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
            '<div id="cb-win" style="display:none;position:fixed;bottom:90px;right:20px;width:380px;max-height:600px;background:#fff;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.25);z-index:10000;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;overflow:hidden;display:none;flex-direction:column;">' +
                '<div style="background:linear-gradient(135deg,#1a5653,#124a47);color:#fff;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">' +
                    '<div><div style="font-weight:700;font-size:16px;">Assistant ORCA</div><div style="font-size:12px;opacity:.8;">En ligne</div></div>' +
                    '<span id="cb-close" style="cursor:pointer;font-size:22px;opacity:.8;padding:4px 8px;">✕</span>' +
                '</div>' +
                '<div id="cb-msgs" style="flex:1;overflow-y:auto;padding:16px;background:#f5f7f9;min-height:300px;max-height:400px;"></div>' +
                '<div id="cb-form-area" style="display:none;padding:16px;background:#fff;border-top:1px solid #eee;">' +
                    '<input id="cf-prenom" placeholder="Prénom *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;">' +
                    '<input id="cf-nom" placeholder="Nom *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;">' +
                    '<input id="cf-email" type="email" placeholder="Email *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;">' +
                    '<input id="cf-tel" type="tel" placeholder="Téléphone *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;">' +
                    '<button id="cf-submit" style="width:100%;padding:12px;margin-top:8px;background:#1a5653;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:700;">Envoyer mes coordonnées</button>' +
                '</div>' +
                '<div id="cb-input-area" style="padding:12px 16px;background:#fff;border-top:1px solid #eee;display:flex;gap:8px;">' +
                    '<input id="cb-input" type="text" placeholder="Votre message..." style="flex:1;padding:10px 14px;border:1px solid #ddd;border-radius:24px;font-size:14px;outline:none;" autocomplete="off">' +
                    '<button id="cb-send" style="padding:10px 18px;background:#1a5653;color:#fff;border:none;border-radius:24px;cursor:pointer;font-size:14px;font-weight:700;">Envoyer</button>' +
                '</div>' +
            '</div>' +
            '<div id="cb-fab" style="position:fixed;bottom:20px;right:20px;width:62px;height:62px;background:linear-gradient(135deg,#1a5653,#124a47);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 4px 15px rgba(26,86,83,0.4);z-index:10000;">' +
                '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>' +
            '</div>';
        document.body.appendChild(div);

        // Event listeners
        document.getElementById('cb-fab').onclick = toggleChat;
        document.getElementById('cb-close').onclick = toggleChat;
        document.getElementById('cb-send').onclick = sendText;
        document.getElementById('cb-input').onkeypress = function(e) {
            if (e.key === 'Enter') sendText();
        };
        document.getElementById('cf-submit').onclick = submitForm;
    }

    // ==========================================
    // Toggle ouverture/fermeture
    // ==========================================
    function toggleChat() {
        var win = document.getElementById('cb-win');
        var isOpen = win.style.display === 'flex';
        win.style.display = isOpen ? 'none' : 'flex';
        if (!isOpen && !chatId) initChat();
    }

    // ==========================================
    // Initialiser la conversation
    // ==========================================
    function initChat() {
        showTyping();
        post('action=init', function(data) {
            hideTyping();
            if (data.error) {
                addMsg(data.error, 'bot');
                return;
            }
            chatId = data.conversation_id;
            currentStep = data.step || 1;

            if (data.is_new) {
                addMsg(data.message, 'bot');
                if (data.options) showOptions(data.options);
            } else if (data.history && data.history.length) {
                var lastButtons = null;
                data.history.forEach(function(m) {
                    addMsg(m.message, m.type);
                    if (m.buttons) {
                        try {
                            var parsed = JSON.parse(m.buttons);
                            if (Array.isArray(parsed)) lastButtons = parsed;
                        } catch(e) {}
                    }
                });
                if (lastButtons) showOptions(lastButtons);
            }
        });
    }

    // ==========================================
    // Envoyer un message texte
    // ==========================================
    function sendText() {
        var input = document.getElementById('cb-input');
        var text = input.value.trim();
        if (!text) return;
        input.value = '';
        sendMessage(text);
    }

    // ==========================================
    // Envoyer un message (texte ou valeur de bouton)
    // ==========================================
    function sendMessage(value, label) {
        addMsg(label || value, 'user');
        clearOptions();
        showTyping();

        post('action=message&conversation_id=' + chatId + '&message=' + encodeURIComponent(value), function(data) {
            hideTyping();

            if (data.error) {
                addMsg('Erreur : ' + data.error, 'bot');
                return;
            }

            currentStep = data.step || currentStep;

            if (data.message) {
                addMsg(data.message, 'bot');
            }

            // Afficher les options ou le formulaire selon le contexte
            if (data.options) {
                showOptions(data.options);
                hideForm();
            } else if (data.type === 'final') {
                hideForm();
                hideInput();
            } else if (data.retry) {
                // Champ invalide, l'utilisateur doit resaisir
                hideForm();
            } else if (currentStep >= 50 && currentStep < 55 && !data.options) {
                // Étape de saisie texte (coordonnées)
                hideForm();
                focusInput();
            }
        });
    }

    // ==========================================
    // Soumettre le formulaire HTML
    // ==========================================
    function submitForm() {
        var prenom = document.getElementById('cf-prenom').value.trim();
        var nom = document.getElementById('cf-nom').value.trim();
        var email = document.getElementById('cf-email').value.trim();
        var tel = document.getElementById('cf-tel').value.trim();

        if (!prenom || !nom || !email || !tel) {
            alert('Veuillez remplir tous les champs.');
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert('Email invalide.');
            return;
        }

        var btn = document.getElementById('cf-submit');
        btn.disabled = true;
        btn.textContent = 'Envoi...';
        showTyping();

        var payload = JSON.stringify({ prenom: prenom, nom: nom, email: email, telephone: tel });
        post('action=form&conversation_id=' + chatId + '&data=' + encodeURIComponent(payload), function(data) {
            hideTyping();
            btn.disabled = false;
            btn.textContent = 'Envoyer mes coordonnées';

            if (data.error) {
                alert('Erreur : ' + data.error);
                return;
            }

            if (data.message) {
                addMsg(data.message, 'bot');
            }
            hideForm();
            hideInput();
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

        div.style.cssText = 'margin:8px 0;padding:12px 16px;border-radius:16px;max-width:85%;font-size:14px;line-height:1.5;word-wrap:break-word;' +
            (isBot
                ? 'background:#fff;color:#333;margin-right:auto;border:1px solid #e8e8e8;border-bottom-left-radius:4px;box-shadow:0 1px 3px rgba(0,0,0,0.06);'
                : 'background:#1a5653;color:#fff;margin-left:auto;border-bottom-right-radius:4px;');

        // Sanitize then format
        var safe = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
        div.innerHTML = safe
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

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
            btn.onmouseenter = function() { this.style.background = '#1a5653'; this.style.color = '#fff'; this.style.borderColor = '#1a5653'; };
            btn.onmouseleave = function() { this.style.background = '#f5f5f5'; this.style.color = 'inherit'; this.style.borderColor = '#ddd'; };
            btn.onclick = function() {
                // Actions spéciales
                if (opt.action === 'close') { toggleChat(); return; }
                if (opt.action === 'link' && opt.url) { window.location.href = opt.url; return; }
                // Navigation normale
                if (opt.next) {
                    sendMessage(opt.value, opt.label);
                }
            };
            wrapper.appendChild(btn);
        });

        container.appendChild(wrapper);
        container.scrollTop = container.scrollHeight;
    }

    function clearOptions() {
        var opts = document.querySelectorAll('.cb-options');
        for (var i = 0; i < opts.length; i++) {
            opts[i].style.display = 'none';
        }
    }

    // ==========================================
    // Formulaire HTML (fallback)
    // ==========================================
    function showForm() {
        document.getElementById('cb-form-area').style.display = 'block';
        document.getElementById('cb-input-area').style.display = 'none';
    }

    function hideForm() {
        document.getElementById('cb-form-area').style.display = 'none';
        document.getElementById('cb-input-area').style.display = 'flex';
    }

    function hideInput() {
        document.getElementById('cb-input-area').style.display = 'none';
        document.getElementById('cb-form-area').style.display = 'none';
    }

    function focusInput() {
        var input = document.getElementById('cb-input');
        setTimeout(function() { input.focus(); }, 100);
    }

    // ==========================================
    // Indicateur de saisie
    // ==========================================
    function showTyping() {
        var container = document.getElementById('cb-msgs');
        var existing = document.getElementById('cb-typing');
        if (existing) return;
        var div = document.createElement('div');
        div.id = 'cb-typing';
        div.style.cssText = 'margin:8px 0;padding:12px 16px;background:#fff;border-radius:16px;border-bottom-left-radius:4px;display:inline-flex;gap:5px;align-self:flex-start;border:1px solid #e8e8e8;';
        div.innerHTML = '<span style="width:8px;height:8px;background:#ccc;border-radius:50%;animation:cbdot 1.2s infinite;display:inline-block;"></span>' +
                        '<span style="width:8px;height:8px;background:#ccc;border-radius:50%;animation:cbdot 1.2s .2s infinite;display:inline-block;"></span>' +
                        '<span style="width:8px;height:8px;background:#ccc;border-radius:50%;animation:cbdot 1.2s .4s infinite;display:inline-block;"></span>';
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function hideTyping() {
        var el = document.getElementById('cb-typing');
        if (el) el.remove();
    }

    // ==========================================
    // AJAX POST
    // ==========================================
    function post(body, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', apiUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.timeout = 15000;
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        callback(JSON.parse(xhr.responseText));
                    } catch(e) {
                        console.error('Chatbot parse error:', e, xhr.responseText);
                        callback({ error: 'Erreur serveur' });
                    }
                } else {
                    console.error('Chatbot HTTP error:', xhr.status, xhr.responseText);
                    callback({ error: 'Erreur de connexion' });
                }
            }
        };
        xhr.ontimeout = function() {
            callback({ error: 'Délai dépassé' });
        };
        xhr.send(body);
    }

    // ==========================================
    // CSS animation pour les points
    // ==========================================
    function injectCSS() {
        var style = document.createElement('style');
        style.textContent = '@keyframes cbdot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-8px)}}' +
            '@media(max-width:480px){#cb-win{left:10px!important;right:10px!important;bottom:80px!important;width:auto!important;max-height:80vh!important;}}';
        document.head.appendChild(style);
    }

    // ==========================================
    // Initialisation
    // ==========================================
    function init() {
        injectCSS();
        createWidget();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
