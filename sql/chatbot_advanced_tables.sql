-- ============================================
-- Tables pour les fonctionnalités avancées du Chatbot
-- ============================================

-- Table A/B Tests
CREATE TABLE IF NOT EXISTS chatbot_ab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    test_type VARCHAR(50) NOT NULL, -- 'welcome_message', 'button_style', 'question_order', 'response_style'
    variant_a_value TEXT NOT NULL,
    variant_b_value TEXT NOT NULL,
    status ENUM('active', 'completed', 'paused') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME NULL,
    INDEX idx_status (status),
    INDEX idx_type (test_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table Relances Automatiques
CREATE TABLE IF NOT EXISTS chatbot_followups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    lead_data JSON,
    followup_date DATETIME NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'sent', 'cancelled', 'converted') DEFAULT 'pending',
    email_subject VARCHAR(255),
    email_content TEXT,
    sent_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_date (status, followup_date),
    INDEX idx_conversation (conversation_id),
    FOREIGN KEY (conversation_id) REFERENCES chatbot_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter colonnes à chatbot_conversations pour A/B testing
ALTER TABLE chatbot_conversations 
    ADD COLUMN IF NOT EXISTS ab_test_id INT NULL AFTER lead_id,
    ADD COLUMN IF NOT EXISTS ab_variant CHAR(1) NULL AFTER ab_test_id,
    ADD COLUMN IF NOT EXISTS referrer VARCHAR(500) NULL AFTER page_source,
    ADD COLUMN IF NOT EXISTS user_agent VARCHAR(500) NULL AFTER referrer,
    ADD INDEX IF NOT EXISTS idx_ab_test (ab_test_id, ab_variant);

-- Table Analytics Temps Réel
CREATE TABLE IF NOT EXISTS chatbot_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    hour INT NOT NULL,
    conversations INT DEFAULT 0,
    messages INT DEFAULT 0,
    leads_generated INT DEFAULT 0,
    avg_score DECIMAL(5,2) DEFAULT 0,
    avg_duration INT DEFAULT 0, -- en minutes
    UNIQUE KEY unique_date_hour (date, hour),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les préférences utilisateur (autocomplete intelligent)
CREATE TABLE IF NOT EXISTS chatbot_user_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern_type VARCHAR(50) NOT NULL, -- 'email_domain', 'common_name', 'dept_city'
    pattern_value VARCHAR(255) NOT NULL,
    frequency INT DEFAULT 1,
    last_used DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pattern (pattern_type, pattern_value),
    INDEX idx_type_freq (pattern_type, frequency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insertions de configuration par défaut
-- ============================================

INSERT INTO config (cle, valeur) VALUES
('chatbot_openai_api_key', ''),
('chatbot_openai_model', 'gpt-4'),
('chatbot_enabled', '1'),
('chatbot_auto_popup', '1'),
('chatbot_popup_delay', '30'),
('chatbot_primary_color', '#1a5653'),
('chatbot_welcome_message', 'Bonjour ! Je suis l\'assistant ORCA. Que souhaitez-vous faire ?'),
('chatbot_offline_message', 'Un conseiller vous répondra dès que possible.'),
('chatbot_email_notifications', '1'),
('chatbot_lead_threshold', '70'),
('chatbot_api_key', MD5(CONCAT('orca_', UNIX_TIMESTAMP()))) -- Clé API par défaut
ON DUPLICATE KEY UPDATE cle = cle;

-- Insertion d'intentions par défaut supplémentaires
INSERT INTO chatbot_intentions (intention, keywords, response, action, priority, is_active) VALUES
('reprendre', 'reprendre,continuer,plus tard,revenu,retour', 'Je reprends où nous en étions ! Quelle étape aviez-vous atteinte ?', 'show_steps', 10, 1),
('comparer', 'comparer,différence,versus,meilleur,choisir entre', 'Je peux vous aider à comparer nos modèles ! Quelles maisons souhaitez-vous comparer ?', 'show_comparison', 10, 1),
('négocier', 'négocier,rabais,remise,promo,réduction,moins cher', 'Nos prix sont compétitifs et transparents. Je vais vous mettre en relation avec un conseiller qui pourra étudier votre projet.', 'create_lead_priority', 10, 1),
('plan', 'plan,croquis,dessin,technique,façade', 'Vous souhaitez voir les plans détaillés ? Je peux vous envoyer nos catalogues par email.', 'send_catalog', 10, 1),
('constructeur_concurrent', 'maisons pierre,maisons france confort,tradi,france configuration,autre constructeur', 'ORCA se différencie par son accompagnement personnalisé et ses maisons sur mesure. Je peux vous montrer nos réalisations !', 'show_realisations', 10, 1),
('credit_refuse', 'crédit refusé,banque refus,prêt refusé,financement impossible', 'Ne vous inquiétez pas ! Nous avons des partenaires financiers qui peuvent vous aider. Un conseiller peut étudier votre situation.', 'create_lead_priority', 10, 1)
ON DUPLICATE KEY UPDATE keywords = VALUES(keywords);
