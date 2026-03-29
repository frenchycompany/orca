-- ============================================
-- Enrichissement des intentions chatbot
-- Nouvelles réponses pour les questions fréquentes
-- ============================================

SET NAMES utf8mb4;

-- Supprimer les anciennes intentions par défaut pour réinsérer proprement
-- (ne supprime que celles créées par ce script, pas celles ajoutées manuellement via l'admin)
DELETE FROM chatbot_intentions WHERE intention_key IN (
    'prix', 'terrain_info', 'modele', 'contact_info', 'delai', 'aide_financement',
    'salutation', 'remerciement', 'au_revoir', 'devis_demande', 'rdv_demande',
    're2020', 'materiaux', 'garantie_info', 'surface_info', 'personnalisation',
    'visite_maison', 'primo_accedant', 'objection_prix', 'objection_delai',
    'comparaison', 'localisation'
);

INSERT INTO chatbot_intentions (intention_key, keywords, response_text, action, priority, is_active) VALUES

-- Salutations et politesses
('salutation', 'bonjour, salut, hello, bonsoir, coucou, hey',
'Bonjour ! 😊 Bienvenue chez Maisons ORCA. Je suis là pour vous aider dans votre projet de construction. Que souhaitez-vous savoir ?', NULL, 10, 1),

('remerciement', 'merci, super, parfait, génial, top, excellent',
'Avec plaisir ! 😊 N''hésitez pas si vous avez d''autres questions. Je suis là pour vous aider !', NULL, 10, 1),

('au_revoir', 'au revoir, bye, bonne journée, à bientôt, ciao',
'À bientôt ! 👋 N''hésitez pas à revenir si vous avez d''autres questions. Bonne continuation dans votre projet !', 'close', 10, 1),

-- Prix et budget
('prix', 'prix, combien, coûte, cout, tarif, cher, budget, euro, €, moins cher',
'💰 **Nos maisons de 145 000 € à 215 000 €** (hors terrain) :\n\n🏠 Plain-pied :\n• Coquelicot 88m² → 145 000 €\n• Tulipe 95m² → 152 000 €\n• Hibiscus 102m² → 168 000 €\n\n🏡 Avec étage :\n• Orchidée 110m² → 178 000 €\n• Lila 120m² → 185 000 €\n• Magnolia 130m² → 215 000 €\n\n*Nos prix sont 20-30% inférieurs à la concurrence !*', NULL, 9, 1),

-- Terrains
('terrain_info', 'terrain, parcelle, foncier, constructible, lotissement',
'🌿 **Service gratuit de recherche de terrain !**\n\nNous avons des terrains disponibles dans l''Oise (60), la Seine-et-Marne (77), le Val-d''Oise (95), l''Aisne (02) et la Somme (80).\n\nDe 35 000 € à 110 000 € selon la localisation et la surface.\n\n*Voulez-vous voir les terrains disponibles ?*', 'scenario_terrain', 8, 1),

-- Modèles
('modele', 'modèle, modele, catalogue, gamme, maison, plain-pied, étage, plain pied',
'🏠 **6 modèles de maisons de 88 à 130m²** :\n\n• 3 plain-pied (Coquelicot, Tulipe, Hibiscus)\n• 3 à étage (Orchidée, Lila, Magnolia)\n• Styles : traditionnel, contemporain, moderne\n• 3 à 4 chambres\n• Tous personnalisables\n\n*Voulez-vous trouver le modèle idéal pour vous ?*', 'afficher_modeles', 8, 1),

-- Contact
('contact_info', 'adresse, où, situé, agence, horaire, ouvert',
'📍 **Agence principale :**\n119 rue Bordier, 60150 Longueil-Annel\n\n🕐 Ouvert du lundi au vendredi, 9h-18h\n📞 03 44 00 00 00\n\nNous pouvons aussi nous déplacer chez vous gratuitement !', NULL, 7, 1),

-- Délais
('delai', 'délai, delai, durée, duree, temps, quand, livraison, mois, attendre, long',
'⏱️ **Délai : 8 à 12 mois** du permis aux clés :\n\n1. Étude + permis : 2-3 mois\n2. Terrassement + fondations : 1 mois\n3. Gros oeuvre : 3-4 mois\n4. Second oeuvre + finitions : 2-3 mois\n\n**Nos délais sont contractuels et garantis.** Pas de mauvaise surprise !', NULL, 7, 1),

-- Financement
('aide_financement', 'financement, prêt, pret, ptz, crédit, credit, banque, aide, mensualité, taux, emprunt, apport',
'💡 **Aides et financement :**\n\n• **PTZ** : jusqu''à 40% du montant sans intérêts\n• **Prêt Action Logement** : jusqu''à 40 000 €\n• **TVA réduite** à 5,5% (zones ANRU)\n• **Exonération taxe foncière** possible (2 ans)\n\n📊 Exemple : maison à 160 000 € → mensualité ~750 €/mois sur 25 ans\n\nNotre courtier partenaire vous accompagne **gratuitement** !', NULL, 8, 1),

-- Devis
('devis_demande', 'devis, estimation, simulation, calculer, chiffrer, estimer',
'📊 **Je peux vous orienter vers un devis !**\n\nPour une estimation précise, nos conseillers prennent en compte :\n• Votre terrain (surface, pente, accès)\n• Le modèle choisi et vos options\n• Les normes locales (PLU)\n\n*Commençons par choisir votre modèle ?*', 'scenario_devis', 7, 1),

-- RDV
('rdv_demande', 'rendez-vous, rdv, rencontrer, rappeler, appeler, conseiller, commercial',
'📅 **3 façons de nous rencontrer :**\n\n🏢 À l''agence (Longueil-Annel, 60)\n🏠 Chez vous (déplacement gratuit)\n💻 En visioconférence\n\nDisponible du lundi au vendredi, 9h-18h.\n\n*Laissez vos coordonnées et on vous rappelle sous 24h !*', 'transfert_humain', 7, 1),

-- RE2020
('re2020', 're2020, re 2020, norme, réglementation, environnement, thermique, rt2012, isolation, bbc, énergie',
'🌱 **Toutes nos maisons sont RE2020** (depuis 2022) :\n\n• Isolation renforcée (murs, toiture, sol)\n• Double vitrage haute performance\n• VMC double flux\n• Chauffage pompe à chaleur\n• Consommation < 50 kWh/m²/an\n\nRésultat : **facture énergie divisée par 3** vs ancien !', NULL, 6, 1),

-- Matériaux
('materiaux', 'matériaux, matériels, brique, parpaing, bois, béton, construction, qualité, solide',
'🧱 **Nos matériaux :**\n\n• Murs : parpaing + isolation extérieure ou briques Monomur\n• Charpente : traditionnelle ou fermettes\n• Couverture : tuiles ou ardoises selon région\n• Menuiseries : PVC ou alu double vitrage\n• Chauffage : pompe à chaleur air/eau\n\nTout est aux **normes RE2020** avec des matériaux de marques françaises.', NULL, 6, 1),

-- Garanties
('garantie_info', 'garantie, assurance, décennale, biennale, dommage, protection, recours, litige',
'✅ **Vos 5 garanties ORCA :**\n\n1. **Décennale** (10 ans) → structure\n2. **Biennale** (2 ans) → équipements\n3. **Parfait achèvement** (1 an) → finitions\n4. **Dommages-ouvrage** → remboursement rapide\n5. **Livraison prix/délai** → contractuel\n\nConstructeur depuis **1993**, plus de 30 ans d''expérience !', NULL, 6, 1),

-- Personnalisation
('personnalisation', 'personnaliser, modifier, changer, adapter, option, sur mesure, cuisine, salle de bain, aménagement',
'🎨 **Tous nos modèles sont personnalisables :**\n\n• Façade : enduit, briques, bardage, mixte\n• Cuisine : ouverte ou fermée\n• Salle de bain : baignoire, douche italienne\n• Chambres : nombre, taille, dressing\n• Garage : simple, double, carport\n• Terrasse, véranda, piscine...\n\nVotre maison, **vos choix** !', NULL, 6, 1),

-- Visite maison témoin
('visite_maison', 'visite, visiter, voir, témoin, showroom, exposition, maison témoin',
'👀 **Visitez nos réalisations !**\n\nNous pouvons organiser la visite d''une maison ORCA déjà construite dans votre secteur.\n\nRien de mieux que de voir et toucher pour se projeter dans votre futur chez-vous !\n\n*Laissez vos coordonnées pour organiser une visite.*', 'transfert_humain', 6, 1),

-- Primo-accédant
('primo_accedant', 'premier, première, primo, accédant, jamais, première fois, jeune, couple',
'🏡 **Bravo pour votre premier achat !**\n\nVous avez droit à des aides spéciales :\n• **PTZ** : empruntez sans intérêts\n• **Prêt Action Logement** : 40 000 € à 1%\n• **APL accession** : sous conditions\n\nNos conseillers sont spécialisés dans l''accompagnement des primo-accédants. On gère tout de A à Z !', NULL, 7, 1),

-- Objections prix
('objection_prix', 'trop cher, pas les moyens, hors budget, dépasse, impossible, petit budget',
'💪 **On trouve toujours une solution !**\n\n• Notre modèle Coquelicot démarre à **145 000 €**\n• Avec le PTZ, votre mensualité peut descendre sous **700 €/mois**\n• Nos prix sont **20-30% moins chers** que la moyenne\n• Possibilité d''auto-construction partielle\n\nUn conseiller peut étudier **gratuitement** votre capacité d''emprunt !', 'transfert_humain', 8, 1),

-- Objection délai
('objection_delai', 'trop long, pressé, urgent, vite, rapide, rapidement',
'⚡ **On peut accélérer !**\n\nNotre délai standard est 8-12 mois, mais :\n• Si vous avez déjà un terrain → on gagne 2-3 mois\n• Permis express possible dans certaines communes\n• Choix d''options standard = plus rapide\n\nCas le plus rapide : **6 mois** du permis aux clés !', NULL, 6, 1),

-- Comparaison concurrence
('comparaison', 'concurrent, comparaison, mieux, différence, pourquoi vous, autre constructeur, avantage',
'🏆 **Pourquoi choisir ORCA ?**\n\n✅ Constructeur depuis **1993** (30+ ans)\n✅ Prix **20-30% inférieurs** à la concurrence\n✅ Délais **contractuels et garantis**\n✅ Accompagnement **de A à Z** (terrain, financement, construction)\n✅ **RE2020** sur tous les modèles\n✅ Service après-vente réactif\n\nNos clients nous recommandent à 95% !', NULL, 7, 1),

-- Localisation / zone d'intervention
('localisation', 'où construire, secteur, zone, intervention, département, picardie, ile de france, oise, aisne',
'📍 **Notre zone d''intervention :**\n\n• **Oise (60)** - Notre département historique\n• **Aisne (02)** - Sud du département\n• **Somme (80)** - Secteur Amiens\n• **Seine-et-Marne (77)** - Nord du département\n• **Val-d''Oise (95)** - Tout le département\n\nAgence principale : **Longueil-Annel (60)**', NULL, 6, 1);
