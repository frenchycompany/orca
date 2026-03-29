// Chatbot ORCA - Version complète avec IA
(function() {
    'use strict';
    
    var chatId = null;
    var baseUrl = window.chatbotBaseUrl || '';
    var currentStep = 1;
    var isFormMode = false;

    // Créer le widget
    function createWidget() {
        var div = document.createElement('div');
        div.id = 'chatbot-widget';
        div.innerHTML = 
            '<div id="cb-window" style="display:none;position:fixed;bottom:90px;right:20px;width:400px;height:550px;background:white;border-radius:16px;box-shadow:0 12px 50px rgba(0,0,0,0.35);z-index:9999;font-family:Arial,sans-serif;overflow:hidden;transition:all 0.3s;">' +
            '<div style="background:linear-gradient(135deg,#1a5653 0%,#124a47 100%);color:white;padding:18px;display:flex;justify-content:space-between;align-items:center;">' +
            '<div><div style="font-weight:bold;font-size:17px;">Assistant ORCA</div><div style="font-size:12px;opacity:0.85;">En ligne - Réponse sous 24h</div></div>' +
            '<span onclick="window.cbToggle()" style="cursor:pointer;font-size:24px;line-height:1;opacity:0.8;transition:opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">✕</span>' +
            '</div>' +
            '<div id="cb-messages" style="height:370px;overflow-y:auto;padding:18px;background:#f5f7f9;"></div>' +
            '<div style="padding:16px;background:white;border-top:1px solid #e8e8e8;">' +
            '<div id="cb-buttons" style="margin-bottom:12px;max-height:100px;overflow-y:auto;"></div>' +
            '<div id="cb-form" style="display:none;background:#f8f9fa;padding:12px;border-radius:10px;margin-bottom:10px;">' +
            '<input id="cb-prenom" type="text" placeholder="Prénom *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
            '<input id="cb-nom" type="text" placeholder="Nom *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
            '<input id="cb-email" type="email" placeholder="Email *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
            '<input id="cb-tel" type="tel" placeholder="Téléphone *" style="width:100%;padding:10px;margin:4px 0;border:1px solid #ddd;border-radius:8px;box-sizing:border-box;font-size:14px;outline:none;transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'">' +
            '<button onclick="window.cbSubmitForm()" style="width:100%;padding:12px;margin-top:8px;background:#1a5653;color:white;border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:bold;transition:all 0.2s;" onmouseover="this.style.background=\'#124a47\'" onmouseout="this.style.background=\'#1a5653\'">Envoyer mes coordonnées</button>' +
            '</div>' +
            '<div id="cb-textarea" style="display:flex;gap:10px;">' +
            '<input id="cb-input" type="text" placeholder="Écrivez votre message..." style="flex:1;padding:12px 15px;border:1px solid #ddd;border-radius:10px;font-size:14px;outline:none;transition:border-color 0.2s;" onfocus="this.style.borderColor=\'#1a5653\'" onblur="this.style.borderColor=\'#ddd\'" onkeypress="if(event.key===\'Enter\')window.cbSendText()">' +
            '<button onclick="window.cbSendText()" style="padding:12px 20px;background:#1a5653;color:white;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:bold;transition:all 0.2s;" onmouseover="this.style.background=\'#124a47\'" onmouseout="this.style.background=\'#1a5653\'">Envoyer</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div id="cb-fab" onclick="window.cbToggle()" style="position:fixed;bottom:20px;right:20px;width:65px;height:65px;background:linear-gradient(135deg,#1a5653 0%,#124a47 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 6px 20px rgba(26,86,83,0.4);z-index:9999;transition:all 0.3s;" onmouseover="this.style.transform=\'scale(1.1)\';this.style.boxShadow=\'0 8px 25px rgba(26,86,83,0.5)\'" onmouseout="this.style.transform=\'scale(1)\';this.style.boxShadow=\'0 6px 20px rgba(26,86,83,0.4)\'">' +
            '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>' +
            '</div>';
        document.body.appendChild(div);
    }

    // Toggle
    window.cbToggle = function() {
        var win = document.getElementById('cb-window');
        if (win.style.display === 'none') {
            win.style.display = 'block';
            if (!chatId) initChat();
        } else {
            win.style.display = 'none';
        }
    };

    // Init
    function initChat() {
        post('action=init', function(data) {
            if (data.error) {
                addMsg('Erreur: ' + data.error, 'bot');
                return;
            }
            chatId = data.conversation_id;
            currentStep = data.step || 1;
            
            if (data.history && data.history.length > 0) {
                data.history.forEach(function(m) {
                    addMsg(m.message, m.type);
                });
            } else {
                addMsg(data.message, 'bot');
            }
            
            if (data.options) {
                showButtons(data.options);
            } else if (data.continue_options) {
                showButtons(data.continue_options);
            }
        });
    };

    // Envoi bouton
    window.cbSend = function(value, label) {
        addMsg(label || value, 'user');
        clearButtons();
        
        post('action=message&conversation_id=' + chatId + '&message=' + encodeURIComponent(value), function(data) {
            currentStep = data.step;
            addMsg(data.message, 'bot');
            
            if (data.options) {
                hideForm();
                showButtons(data.options);
            } else if (data.continue_options) {
                hideForm();
                showButtons(data.continue_options);
            } else if (data.type === 'force_coord' || data.urgent || data.message.indexOf('coordonnées') !== -1) {
                showForm();
            } else if (data.step >= 50 && data.step < 55) {
                showForm();
            } else {
                hideForm();
            }
        });
    };

    // Envoi texte
    window.cbSendText = function() {
        var input = document.getElementById('cb-input');
        var text = input.value.trim();
        if (!text) return;
        input.value = '';
        window.cbSend(text, text);
    };

    // Submit formulaire
    window.cbSubmitForm = function() {
        var prenom = document.getElementById('cb-prenom').value.trim();
        var nom = document.getElementById('cb-nom').value.trim();
        var email = document.getElementById('cb-email').value.trim();
        var tel = document.getElementById('cb-tel').value.trim();
        
        if (!prenom || !nom || !email || !tel) {
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }
        
        if (!validateEmail(email)) {
            alert('Veuillez entrer un email valide');
            return;
        }
        
        addMsg('✓ Informations envoyées', 'user');
        
        // Envoyer via action=form
        post('action=form&conversation_id=' + chatId + 
             '&data=' + encodeURIComponent(JSON.stringify({
                 prenom: prenom,
                 nom: nom,
                 email: email,
                 telephone: tel
             })), function(data) {
            addMsg(data.message, 'bot');
            hideForm();
            if (data.options) {
                showButtons(data.options);
            }
        });
    };

    // Afficher message
    function addMsg(text, type) {
        var div = document.createElement('div');
        var isBot = type === 'bot';
        div.style.cssText = 'margin:10px 0;padding:14px 18px;border-radius:18px;max-width:85%;font-size:14px;line-height:1.5;word-wrap:break-word;' + 
            (isBot ? 'background:white;border:1px solid #e5e5e5;margin-right:auto;color:#333;border-bottom-left-radius:4px;box-shadow:0 2px 5px rgba(0,0,0,0.05);' : 'background:#1a5653;color:white;margin-left:auto;border-bottom-right-radius:4px;');
        // Sanitize text before inserting as HTML
        var safe = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        div.innerHTML = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
        var container = document.getElementById('cb-messages');
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    // Afficher boutons
    function showButtons(options) {
        var container = document.getElementById('cb-buttons');
        container.innerHTML = '';
        if (!options || !options.length) return;
        
        options.forEach(function(opt) {
            var btn = document.createElement('button');
            btn.innerHTML = opt.label;
            btn.style.cssText = 'display:block;width:100%;padding:12px 15px;margin:6px 0;background:#f5f5f5;border:1px solid #e0e0e0;border-radius:10px;cursor:pointer;text-align:left;font-size:14px;transition:all 0.2s;';
            btn.onmouseenter = function() { this.style.background = '#1a5653'; this.style.color = 'white'; this.style.borderColor = '#1a5653'; };
            btn.onmouseleave = function() { this.style.background = '#f5f5f5'; this.style.color = 'inherit'; this.style.borderColor = '#e0e0e0'; };
            btn.onclick = function() { window.cbSend(opt.value, opt.label); };
            container.appendChild(btn);
        });
    }

    function clearButtons() {
        document.getElementById('cb-buttons').innerHTML = '';
    }

    function showForm() {
        document.getElementById('cb-buttons').style.display = 'none';
        document.getElementById('cb-textarea').style.display = 'none';
        document.getElementById('cb-form').style.display = 'block';
    }

    function hideForm() {
        document.getElementById('cb-form').style.display = 'none';
        document.getElementById('cb-buttons').style.display = 'block';
        document.getElementById('cb-textarea').style.display = 'flex';
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // AJAX
    function post(body, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', baseUrl + 'chatbot/api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        callback(JSON.parse(xhr.responseText));
                    } catch(e) {
                        console.error('Parse error:', e, xhr.responseText);
                    }
                } else {
                    console.error('HTTP error:', xhr.status);
                }
            }
        };
        xhr.send(body);
    }

    // Init
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createWidget);
    } else {
        createWidget();
    }
})();
