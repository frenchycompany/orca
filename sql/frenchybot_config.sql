-- Token FrenchyBot centralisé dans la config (remplace les 4 occurrences codées en dur)
INSERT INTO config (cle, valeur, description) VALUES
('frenchybot_token', '83059f1ffd4adf64a5ef5e9a803dd1d2', 'Token du chatbot FrenchyBot pour ce domaine'),
('frenchybot_url', 'https://bot.frenchycompany.fr', 'URL de la plateforme FrenchyBot')
ON DUPLICATE KEY UPDATE valeur = VALUES(valeur);
