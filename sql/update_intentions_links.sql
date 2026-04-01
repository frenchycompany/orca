-- Mise à jour des intentions existantes avec des liens vers les pages du site
UPDATE chatbot_intentions SET action = 'link:/engagements.php' WHERE intention_key = 'garantie_info' AND (action IS NULL OR action = '');
UPDATE chatbot_intentions SET action = 'link:/modeles.php' WHERE intention_key = 'modele' AND action = 'afficher_modeles';
UPDATE chatbot_intentions SET action = 'link:/contact.php' WHERE intention_key = 'contact_info' AND (action IS NULL OR action = '');
UPDATE chatbot_intentions SET action = 'link:/faq.php' WHERE intention_key IN ('re2020', 'materiaux', 'isolation', 'chauffage') AND (action IS NULL OR action = '');
UPDATE chatbot_intentions SET action = 'link:/modeles.php' WHERE intention_key = 'personnalisation' AND (action IS NULL OR action = '');
UPDATE chatbot_intentions SET action = 'link:/engagements.php' WHERE intention_key = 'ccmi' AND (action IS NULL OR action = '');
UPDATE chatbot_intentions SET action = 'link:/engagements.php' WHERE intention_key = 'comparaison' AND (action IS NULL OR action = '');
