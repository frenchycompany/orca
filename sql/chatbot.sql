-- Tables pour le Chatbot ORCA
-- À exécuter sur la base de données

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
    lead_id INT NULL,
    INDEX idx_session (session_id),
    INDEX idx_active (is_active),
    INDEX idx_lead (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des intentions (configurable)
CREATE TABLE IF NOT EXISTS chatbot_intentions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    intention_key VARCHAR(50) NOT NULL UNIQUE,
    keywords TEXT NOT NULL,
    response_text TEXT NOT NULL,
    action VARCHAR(50) NULL,
    priority INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion intentions par défaut
INSERT INTO chatbot_intentions (intention_key, keywords, response_text, action, priority) VALUES
('prix', 'prix,combien,tarif,coûte,cout,euros,€,budget,chère,cher,moins cher', 
 'Nos maisons démarrent à 149 000€. Souhaitez-vous un devis personnalisé ?', 
 'scenario_devis', 10),

('terrain', 'terrain,parcelle,trouver terrain,pas de terrain,chercher terrain', 
 'Nous pouvons vous accompagner dans la recherche de terrain ! Dans quel département cherchez-vous ?', 
 'scenario_terrain', 9),

('modeles', 'modèle,modeles,maison,maisons,coquelicot,tulipe,hibiscus,catalogue', 
 'Nous avons 6 modèles de maison. Lequel vous intéresse ?', 
 'afficher_modeles', 8),

('contact', 'téléphone,tel,appeler,joindre,contact,conseiller,humain,personne', 
 'Je peux vous mettre en relation avec un conseiller. Voulez-vous être rappelé ?', 
 'transfert_humain', 7),

('delai', 'délai,delai,quand,temps,durée,construire,livraison,rapidement', 
 'Le délai moyen est de 6-8 mois. Quand souhaitez-vous démarrer ?', 
 'scenario_delai', 6),

('aide', 'aide,help,commandes,que peux-tu faire,menu,options', 
 'Je peux vous aider à :\n🏠 Acheter une maison\n🌿 Trouver un terrain\n💰 Obtenir un devis\n📞 Etre rappelé', 
 'menu_principal', 10),

('recommencer', 'recommencer,reset,restart,nouveau,again', 
 'Pas de problème, recommençons ! Que souhaitez-vous faire ?', 
 'reset_conversation', 10);
