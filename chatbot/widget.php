<?php
/**
 * Widget Chatbot ORCA
 * À inclure dans le footer du site
 */
?>
<!-- Chatbot Widget -->
<div id="chatbot-widget" class="chatbot-widget">
    <!-- Bouton toggle -->
    <button id="chatbot-toggle" class="chatbot-toggle" aria-label="Ouvrir le chat">
        <span class="chatbot-toggle-icon">💬</span>
        <span class="chatbot-toggle-close">✕</span>
        <span class="chatbot-notification" style="display: none;"></span>
    </button>
    
    <!-- Container du chat -->
    <div id="chatbot-container" class="chatbot-container">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="chatbot-avatar">🤖</div>
            <div class="chatbot-info">
                <div class="chatbot-name">Assistant ORCA</div>
                <div class="chatbot-status">En ligne</div>
            </div>
            <button id="chatbot-minimize" class="chatbot-minimize" aria-label="Minimiser">−</button>
        </div>
        
        <!-- Messages -->
        <div id="chatbot-messages" class="chatbot-messages">
            <!-- Messages injectés ici -->
        </div>
        
        <!-- Input -->
        <div class="chatbot-input-area">
            <form id="chatbot-form" class="chatbot-form">
                <input 
                    type="text" 
                    id="chatbot-input" 
                    class="chatbot-input" 
                    placeholder="Écrivez votre message..."
                    autocomplete="off"
                >
                <button type="submit" class="chatbot-send" aria-label="Envoyer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Widget Chatbot */
.chatbot-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: var(--font-primary, 'Poppins', sans-serif);
}

/* Bouton toggle */
.chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--color-primary, #1a5653);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: all 0.3s ease;
    position: relative;
}

.chatbot-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.4);
}

.chatbot-toggle.active {
    background: #333;
}

.chatbot-toggle.active .chatbot-toggle-icon {
    display: none;
}

.chatbot-toggle:not(.active) .chatbot-toggle-close {
    display: none;
}

.chatbot-notification {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    background: #e74c3c;
    border-radius: 50%;
    border: 2px solid white;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* Container */
.chatbot-container {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 550px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(20px) scale(0.95);
    transition: all 0.3s ease;
}

.chatbot-widget.open .chatbot-container {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

/* Header */
.chatbot-header {
    background: var(--color-primary, #1a5653);
    color: white;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.chatbot-info {
    flex: 1;
}

.chatbot-name {
    font-weight: 600;
    font-size: 16px;
}

.chatbot-status {
    font-size: 12px;
    opacity: 0.8;
}

.chatbot-minimize {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
}

/* Messages */
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #f8f9fa;
}

/* Bulles de message */
.chatbot-message {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    animation: messageAppear 0.3s ease;
}

@keyframes messageAppear {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chatbot-message.bot {
    background: white;
    color: #333;
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.chatbot-message.user {
    background: var(--color-primary, #1a5653);
    color: white;
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

.chatbot-message.system {
    background: transparent;
    color: #666;
    font-size: 12px;
    text-align: center;
    align-self: center;
    max-width: 100%;
}

/* Boutons options */
.chatbot-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 12px;
}

.chatbot-option {
    background: white;
    border: 2px solid var(--color-primary, #1a5653);
    color: var(--color-primary, #1a5653);
    padding: 10px 16px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-align: left;
    transition: all 0.2s;
}

.chatbot-option:hover {
    background: var(--color-primary, #1a5653);
    color: white;
}

/* Formulaire */
.chatbot-form-fields {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 12px;
}

.chatbot-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.chatbot-field label {
    font-size: 12px;
    font-weight: 500;
    color: #555;
}

.chatbot-field input {
    padding: 10px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
}

.chatbot-field input:focus {
    outline: none;
    border-color: var(--color-primary, #1a5653);
}

.chatbot-submit-form {
    background: var(--color-primary, #1a5653);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 8px;
}

.chatbot-submit-form:hover {
    opacity: 0.9;
}

/* Input area */
.chatbot-input-area {
    padding: 16px 20px;
    background: white;
    border-top: 1px solid #eee;
}

.chatbot-form {
    display: flex;
    gap: 12px;
}

.chatbot-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 24px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
}

.chatbot-input:focus {
    border-color: var(--color-primary, #1a5653);
}

.chatbot-send {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--color-primary, #1a5653);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.chatbot-send:hover {
    transform: scale(1.05);
}

/* Typing indicator */
.chatbot-typing {
    display: flex;
    gap: 4px;
    padding: 12px 16px;
    background: white;
    border-radius: 18px;
    border-bottom-left-radius: 4px;
    align-self: flex-start;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.chatbot-typing span {
    width: 8px;
    height: 8px;
    background: #ccc;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.chatbot-typing span:nth-child(2) { animation-delay: 0.2s; }
.chatbot-typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
}

/* Responsive */
@media (max-width: 480px) {
    .chatbot-widget {
        bottom: 10px;
        right: 10px;
    }
    
    .chatbot-container {
        position: fixed;
        bottom: 0;
        right: 0;
        left: 0;
        width: 100%;
        height: calc(100vh - 80px);
        border-radius: 16px 16px 0 0;
    }
    
    .chatbot-toggle {
        width: 56px;
        height: 56px;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    // Configuration
    const CHATBOT_API_URL = '<?php echo url('chatbot/api.php'); ?>';
    let conversationId = null;
    let currentStep = 1;
    let isTyping = false;
    
    // Éléments DOM
    const widget = document.getElementById('chatbot-widget');
    const toggle = document.getElementById('chatbot-toggle');
    const container = document.getElementById('chatbot-container');
    const messagesContainer = document.getElementById('chatbot-messages');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const minimize = document.getElementById('chatbot-minimize');
    
    // Toggle ouverture/fermeture
    toggle.addEventListener('click', () => {
        widget.classList.toggle('open');
        toggle.classList.toggle('active');
        
        if (widget.classList.contains('open') && !conversationId) {
            initConversation();
        }
        
        // Cacher notification
        document.querySelector('.chatbot-notification').style.display = 'none';
    });
    
    // Minimiser
    minimize.addEventListener('click', () => {
        widget.classList.remove('open');
        toggle.classList.remove('active');
    });
    
    // Initialiser conversation
    async function initConversation() {
        showTyping();
        
        try {
            const response = await fetch(CHATBOT_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=init'
            });
            
            const data = await response.json();
            hideTyping();
            
            if (data.conversation_id) {
                conversationId = data.conversation_id;
                currentStep = data.step;
                
                if (data.is_new) {
                    addMessage('bot', data.message, data.options);
                } else if (data.history) {
                    // Restaurer historique
                    data.history.forEach(msg => {
                        addMessage(msg.type, msg.message, JSON.parse(msg.buttons || 'null'));
                    });
                }
            }
        } catch (error) {
            hideTyping();
            addMessage('bot', 'Désolé, une erreur est survenue. Veuillez réessayer.');
        }
    }
    
    // Envoyer message
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const message = input.value.trim();
        if (!message || isTyping) return;
        
        addMessage('user', message);
        input.value = '';
        
        showTyping();
        
        try {
            const response = await fetch(CHATBOT_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=message&conversation_id=${conversationId}&message=${encodeURIComponent(message)}`
            });
            
            const data = await response.json();
            hideTyping();
            
            if (data.redirect) {
                window.location.href = data.url;
                return;
            }
            
            if (data.reset) {
                conversationId = null;
                messagesContainer.innerHTML = '';
                initConversation();
                return;
            }
            
            if (data.message) {
                currentStep = data.step || currentStep;
                addMessage('bot', data.message, data.options || data.buttons, data.fields);
            }
        } catch (error) {
            hideTyping();
            addMessage('bot', 'Désolé, je n\'ai pas compris. Pouvez-vous reformuler ?');
        }
    });
    
    // Gérer clic sur option
    messagesContainer.addEventListener('click', async (e) => {
        const option = e.target.closest('.chatbot-option');
        if (!option) return;
        
        const value = option.dataset.value;
        const label = option.textContent;
        const step = option.dataset.step;
        
        addMessage('user', label);
        
        showTyping();
        
        try {
            const response = await fetch(CHATBOT_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=step&conversation_id=${conversationId}&step_id=${step}&value=${encodeURIComponent(value)}&label=${encodeURIComponent(label)}`
            });
            
            const data = await response.json();
            hideTyping();
            
            if (data.redirect) {
                window.location.href = data.url;
                return;
            }
            
            if (data.message) {
                currentStep = data.step || currentStep;
                addMessage('bot', data.message, data.options || data.buttons, data.fields);
            }
        } catch (error) {
            hideTyping();
        }
    });
    
    // Gérer soumission formulaire (clic sur bouton)
    messagesContainer.addEventListener('click', async (e) => {
        const submitBtn = e.target.closest('.chatbot-submit-form');
        if (!submitBtn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // Trouver le formulaire parent
        const form = submitBtn.closest('.chatbot-form-fields');
        if (!form) return;
        
        // Récupérer les données du formulaire
        const inputs = form.querySelectorAll('input');
        const data = {};
        let isValid = true;
        
        inputs.forEach(input => {
            if (input.required && !input.value.trim()) {
                isValid = false;
                input.style.borderColor = '#e74c3c';
            } else {
                input.style.borderColor = '';
                data[input.name] = input.value.trim();
            }
        });
        
        if (!isValid) {
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        
        // Désactiver le bouton pendant l'envoi
        submitBtn.disabled = true;
        submitBtn.textContent = 'Envoi...';
        
        showTyping();
        
        try {
            const payload = `action=form&conversation_id=${conversationId}&data=${encodeURIComponent(JSON.stringify(data))}`;
            console.log('Envoi payload:', payload);
            
            const response = await fetch(CHATBOT_API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: payload
            });
            
            const result = await response.json();
            console.log('Réponse:', result);
            hideTyping();
            
            if (result.error) {
                alert('Erreur: ' + result.error);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Envoyer';
                return;
            }
            
            if (result.message) {
                addMessage('bot', result.message, result.buttons);
            }
        } catch (error) {
            hideTyping();
            submitBtn.disabled = false;
            submitBtn.textContent = 'Envoyer';
            console.error('Erreur:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        }
    });
    
    // Ajouter message
    function addMessage(type, text, options = null, fields = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message ${type}`;
        
        // Convertir \n en <br>
        text = text.replace(/\n/g, '<br>');
        messageDiv.innerHTML = text;
        
        // Ajouter options si présentes
        if (options && options.length > 0) {
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'chatbot-options';
            
            options.forEach(opt => {
                const btn = document.createElement('button');
                btn.className = 'chatbot-option';
                btn.textContent = opt.label;
                btn.dataset.value = opt.value;
                btn.dataset.step = currentStep;
                optionsDiv.appendChild(btn);
            });
            
            messageDiv.appendChild(optionsDiv);
        }
        
        // Ajouter formulaire si présent
        if (fields && fields.length > 0) {
            const formDiv = document.createElement('form');
            formDiv.className = 'chatbot-form-fields';
            
            fields.forEach(field => {
                const fieldDiv = document.createElement('div');
                fieldDiv.className = 'chatbot-field';
                
                const label = document.createElement('label');
                label.textContent = field.label;
                
                const input = document.createElement('input');
                input.type = field.type;
                input.name = field.name;
                input.placeholder = field.placeholder || '';
                if (field.required) input.required = true;
                
                fieldDiv.appendChild(label);
                fieldDiv.appendChild(input);
                formDiv.appendChild(fieldDiv);
            });
            
            const submit = document.createElement('button');
            submit.type = 'submit';
            submit.className = 'chatbot-submit-form';
            submit.textContent = 'Envoyer';
            formDiv.appendChild(submit);
            
            messageDiv.appendChild(formDiv);
        }
        
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
    
    // Show typing indicator
    function showTyping() {
        isTyping = true;
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-typing';
        typingDiv.id = 'chatbot-typing';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }
    
    // Hide typing indicator
    function hideTyping() {
        isTyping = false;
        const typing = document.getElementById('chatbot-typing');
        if (typing) typing.remove();
    }
    
    // Scroll to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Afficher notification après 30s
    setTimeout(() => {
        if (!widget.classList.contains('open')) {
            document.querySelector('.chatbot-notification').style.display = 'block';
        }
    }, 30000);
    
})();
</script>
