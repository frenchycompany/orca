-- Installation Chatbot ORCA
-- Exécuter ce fichier sur la base de données

-- Table des conversations
CREATE TABLE IF NOT EXISTS chatbot_conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(64) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    page_source VARCHAR(255),
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ended_at TIMESTAMP NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    scenario_id VARCHAR(50) DEFAULT 'qualification_complete',
    current_step INT DEFAULT 1,
    completion_score INT DEFAULT 0,
    data_collected JSON NULL,
    lead_id INT NULL,
    INDEX idx_session (session_id),
    INDEX idx_active (is_active),
    INDEX idx_lead (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages
CREATE TABLE IF NOT EXISTS chatbot_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    type ENUM('bot', 'user', 'system') NOT NULL,
    message TEXT NOT NULL,
    intention_detected VARCHAR(50) NULL,
    step_id INT NULL,
    buttons JSON NULL,
    data_collected JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES chatbot_conversations(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des intentions (configurable)
CREATE TABLE IF NOT EXISTS chatbot_intentions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    intention_key VARCHAR(50) NOT NULL UNIQUE,
    keywords TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    response_text TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    action VARCHAR(50) NULL,
    priority INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion intentions par defaut (sans emojis pour compatibilite)
INSERT INTO chatbot_intentions (intention_key, keywords, response_text, action, priority) VALUES
('prix', 'prix,combien,tarif,coute,cout,euros,budget,cher', 
 'Nos maisons demarrent a 149 000 euros. Souhaitez-vous un devis personnalise ?', 
 'scenario_devis', 10),

('terrain', 'terrain,parcelle,trouver terrain,pas de terrain,chercher terrain', 
 'Nous pouvons vous accompagner dans la recherche de terrain ! Dans quel departement cherchez-vous ?', 
 'scenario_terrain', 9),

('modeles', 'modele,maison,maisons,coquelicot,tulipe,hibiscus,catalogue', 
 'Nous avons 6 modeles de maison. Lequel vous interesse ?', 
 'afficher_modeles', 8),

('contact', 'telephone,tel,appeler,joindre,contact,conseiller,humain,personne', 
 'Je peux vous mettre en relation avec un conseiller. Voulez-vous etre rappele ?', 
 'transfert_humain', 7),

('delai', 'delai,quand,temps,duree,construire,livraison,rapidement', 
 'Le delai moyen est de 6-8 mois. Quand souhaitez-vous demarrer ?', 
 'scenario_delai', 6),

('aide', 'aide,help,commandes,menu,options', 
 'Je peux vous aider a : Acheter une maison, Trouver un terrain, Obtenir un devis, ou Etre rappele', 
 'menu_principal', 10),

('recommencer', 'recommencer,reset,restart,nouveau,again', 
 'Pas de probleme, recommencons ! Que souhaitez-vous faire ?', 
 'reset_conversation', 10),

('au_revoir', 'au revoir,bye,quitte,fermer,salut', 
 'Merci de votre visite ! N hesitez pas a revenir si vous avez d autres questions. Bonne journee !', 
 'close', 5);
