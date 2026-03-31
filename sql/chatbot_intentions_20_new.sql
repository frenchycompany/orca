-- ============================================
-- 20 nouvelles intentions chatbot ORCA
-- Couvre : parcours client, objections, technique, lifestyle
-- ============================================

SET NAMES utf8mb4;

INSERT IGNORE INTO chatbot_intentions (intention_key, keywords, response_text, action, priority, is_active) VALUES

-- ===== PARCOURS CLIENT =====

('etapes_projet', 'étapes, etapes, comment ça marche, comment ca marche, processus, déroulement, parcours, procedure',
'📋 **Les 7 étapes de votre projet ORCA :**\n\n1. 🤝 Premier contact + étude de faisabilité\n2. 📐 Choix du modèle et personnalisation\n3. 🏦 Montage financier (avec notre courtier)\n4. 📋 Dépôt du permis de construire\n5. 🔑 Signature du contrat CCMI\n6. 🏗️ Construction (6-8 mois)\n7. 🎉 Remise des clés !\n\n**On vous accompagne à chaque étape.**', NULL, 7, 1),

('ccmi', 'ccmi, contrat, contrat construction, loi, juridique, protégé, protection, légal',
'📜 **Le Contrat CCMI vous protège :**\n\n• Prix **ferme et définitif** (pas de surprise)\n• Délai de livraison **garanti par contrat**\n• Pénalités de retard à la charge du constructeur\n• Garantie de **remboursement** en cas de défaillance\n• Assurance **dommages-ouvrage** obligatoire\n\nC''est le contrat le plus protecteur en France pour faire construire. Chez ORCA, c''est notre engagement depuis 1993.', NULL, 7, 1),

('apport', 'apport, apport personnel, économies, epargne, sans apport, zero apport, combien apport',
'💶 **L''apport personnel :**\n\n• Idéalement **10% du projet** (frais de notaire + garanties)\n• Mais construction **possible avec peu ou pas d''apport** !\n• Le PTZ compte comme apport pour les banques\n• Notre courtier partenaire optimise votre dossier\n\n📊 Exemple : projet à 200 000 € → apport idéal ~20 000 €\nMais des dossiers à 5 000 € d''apport passent régulièrement !', NULL, 7, 1),

('mensualite', 'mensualité, mensualite, par mois, remboursement, combien par mois, loyer, rembourser',
'📊 **Exemples de mensualités** (taux ~3.5%, 25 ans) :\n\n• Maison 145 000 € → **~720 €/mois**\n• Maison 175 000 € → **~870 €/mois**\n• Maison 200 000 € → **~995 €/mois**\n• Maison + terrain 250 000 € → **~1 245 €/mois**\n\n💡 Souvent **moins cher qu''un loyer** pour une surface 2x plus grande !\n\nNotre courtier peut affiner ces chiffres gratuitement.', NULL, 8, 1),

('frais_notaire', 'notaire, frais notaire, frais annexes, frais supplémentaires, taxe, taxes, coût total, cout total',
'💼 **Frais annexes à prévoir :**\n\n• **Frais de notaire** : ~2-3% pour le neuf (vs 7-8% dans l''ancien !)\n• **Raccordements** (eau, élec, télécom) : 2 000 - 5 000 €\n• **Taxe d''aménagement** : variable selon commune\n• **Assurance dommages-ouvrage** : incluse chez ORCA\n• **Étude de sol** : ~1 500 € (obligatoire loi Élan)\n\n✅ **Bonne nouvelle** : en neuf, les frais de notaire sont 3x moins élevés que dans l''ancien !', NULL, 6, 1),

('taxe_fonciere', 'taxe foncière, taxe fonciere, impôt, impot, exonération, exoneration',
'🏛️ **Taxe foncière et exonérations :**\n\n• Construction neuve = **exonération de 2 ans** de taxe foncière\n• À déclarer en mairie dans les 90 jours après achèvement\n• Après exonération : variable selon commune (500-1 500 €/an)\n\n💡 C''est un avantage majeur du neuf par rapport à l''ancien !', NULL, 5, 1),

-- ===== TECHNIQUE =====

('chauffage', 'chauffage, pompe à chaleur, pac, chaudière, chaudiere, radiateur, plancher chauffant, gaz, électrique, electrique',
'🌡️ **Nos solutions de chauffage RE2020 :**\n\n• **Pompe à chaleur air/eau** (standard sur tous nos modèles)\n• Plancher chauffant basse température possible\n• Ballon thermodynamique pour l''eau chaude\n• Consommation très basse (~50 kWh/m²/an)\n\n💡 Fini le gaz ! La PAC divise par 3 votre facture énergie. Coût annuel estimé : **400-600 €/an** pour une maison de 100m².', NULL, 6, 1),

('isolation', 'isolation, isoler, déperdition, froid, chaud, été, hiver, thermique, phonique, bruit',
'🧱 **Isolation performante RE2020 :**\n\n• Murs : isolation extérieure ou ITI renforcée (R>4.5)\n• Toiture : 30-35 cm de laine minérale (R>8)\n• Sol : isolation sous dalle (R>3.7)\n• Fenêtres : double vitrage 4/16/4 argon\n• VMC double flux (récupère 90% de la chaleur)\n\n❄️ Confort hiver ET été garanti. Nos maisons restent fraîches en été sans climatisation.', NULL, 6, 1),

('piscine', 'piscine, jacuzzi, spa, bassin, nager',
'🏊 **Piscine et aménagements extérieurs :**\n\nNous ne posons pas les piscines directement, mais :\n• Nous **préparons le terrain** (accès, raccordements)\n• Nous **dimensionnons la parcelle** en conséquence\n• Nous travaillons avec des **piscinistes partenaires**\n\n💡 Astuce : intégrez le coût de la piscine dans votre plan de financement global, c''est plus avantageux !', NULL, 5, 1),

('garage', 'garage, voiture, stationnement, carport, abri',
'🚗 **Options garage :**\n\n• **Garage intégré** : inclus sur la plupart des modèles\n• **Garage double** : option sur les modèles à étage\n• **Carport** : alternative économique et esthétique\n• **Garage indépendant** : possible selon le PLU\n\nTous nos garages incluent : porte sectionnelle motorisée, éclairage, prise électrique.', NULL, 5, 1),

('jardin', 'jardin, extérieur, terrasse, clôture, cloture, aménagement, amenagement, pelouse',
'🌳 **Aménagements extérieurs inclus :**\n\n✅ Terrasse arrière dallée\n✅ Clôture sur limites de propriété\n✅ Portail + portillon + interphone\n✅ Allée d''accès carrossable\n\n**En option** : pelouse, plantation, arrosage automatique, éclairage extérieur, store banne...\n\nNos paysagistes partenaires peuvent chiffrer l''ensemble.', NULL, 5, 1),

-- ===== LIFESTYLE / OBJECTIONS =====

('ancien_vs_neuf', 'ancien, rénover, renovation, neuf vs ancien, pourquoi neuf, avantage neuf, acheter ancien',
'🆚 **Neuf vs Ancien, le match :**\n\n| | Neuf ORCA | Ancien |\n|---|---|---|\n| Frais notaire | **2-3%** | 7-8% |\n| Chauffage | **400€/an** | 1 500€/an |\n| Travaux | **0€** (10 ans) | 15-30k€ |\n| Taxe foncière | **Exo 2 ans** | Plein tarif |\n| Garanties | **10 ans** | Aucune |\n| Aux normes | **RE2020** | Souvent non |\n\n💡 Sur 10 ans, le neuf revient **30 à 50 000 € moins cher** que l''ancien !', NULL, 7, 1),

('voisinage', 'voisin, voisinage, lotissement, copropriété, copropriete, nuisance, tranquillité, tranquillite',
'🏘️ **Vivre en lotissement ORCA :**\n\n• Pas de copropriété = **vous êtes chez vous**\n• Cahier des charges léger (harmonie des façades)\n• Espaces verts communs entretenus\n• Quartiers calmes et résidentiels\n• Voisinage familial\n\n💡 Nos lotissements sont pensés pour la **qualité de vie** : pas de vis-à-vis, parcelles généreuses, espaces verts.', NULL, 5, 1),

('revente', 'revendre, revente, valeur, investissement, patrimoine, plus-value, placement',
'📈 **Votre maison ORCA comme investissement :**\n\n• L''immobilier neuf prend de la valeur (+2-3%/an en moyenne)\n• Normes RE2020 = **très recherché** à la revente\n• Pas de travaux pendant 10+ ans\n• DPE classe A ou B garanti (les passoires thermiques perdent 15-20%)\n\n💡 Une maison ORCA est un **patrimoine solide** qui se valorise dans le temps.', NULL, 6, 1),

('famille_nombreuse', 'famille, enfants, enfant, grands, place, espace, grande famille, 4 chambres, 5 chambres',
'👨‍👩‍👧‍👦 **Pour les familles, on a ce qu''il faut :**\n\n• **Le Lila** : 120m², 4 chambres, double séjour 40m²\n• **Le Magnolia** : 130m², 4-5 chambres possibles\n• Les deux avec **garage double** possible\n• Combles aménageables pour encore plus d''espace\n\n💡 On peut ajouter une chambre en option sur presque tous nos modèles !', 'afficher_modeles', 6, 1),

('handicap', 'handicap, pmr, accessibilité, accessibilite, plain pied, mobilité, mobilite, senior, retraite, âgé',
'♿ **Accessibilité et confort :**\n\n• Tous nos **plain-pied** sont adaptables PMR\n• Douche italienne de plain-pied possible\n• Portes élargies (90cm) en option\n• Seuils de porte arasés\n• Commandes à hauteur accessible\n\nLe plain-pied est aussi idéal pour **préparer sa retraite** : pas d''escalier, tout sur un niveau.', NULL, 5, 1),

('objection_terrain', 'pas de terrain, pas trouvé terrain, difficile terrain, chercher terrain, galère terrain, impossible terrain',
'🌿 **On vous aide à trouver votre terrain !**\n\n• Service de **recherche gratuit** dans nos départements\n• Réseau de partenaires fonciers\n• Terrains en lotissement ou diffus\n• On vérifie la constructibilité pour vous\n\n📍 Actuellement des terrains dispo dans l''Oise, l''Aisne, la Somme, la Seine-et-Marne et le Val-d''Oise.\n\n*Voulez-vous voir les terrains disponibles ?*', 'scenario_terrain', 7, 1),

('objection_engagement', 'engagement, engager, obliger, obligation, peur, hésiter, hesiter, pas sûr, réfléchir, reflechir',
'😊 **Aucun engagement à ce stade !**\n\n• L''estimation est **100% gratuite**\n• Le premier RDV est **sans obligation**\n• Vous ne signez rien avant d''être prêt\n• Délai de réflexion légal de **10 jours** après signature\n\nNotre rôle est de vous **informer et accompagner**, pas de vous forcer. Prenez votre temps !', NULL, 8, 1),

('deja_terrain', 'j ai un terrain, j''ai un terrain, déjà un terrain, deja un terrain, mon terrain, propre terrain, possède terrain',
'🎉 **Super, vous avez déjà un terrain !**\n\nC''est un vrai avantage :\n• On peut démarrer **plus vite** (pas de recherche foncière)\n• On adapte le modèle **à votre parcelle** (orientation, pente, accès)\n• Étude de sol et faisabilité **offertes**\n\n📐 Envoyez-nous les références cadastrales et on vous fait une proposition sous 48h !', 'transfert_humain', 7, 1),

('eco_responsable', 'écologique, ecologique, environnement, vert, durable, carbone, empreinte, bio, naturel, bois',
'🌱 **Notre engagement environnemental :**\n\n• Toutes nos maisons sont **RE2020** (la norme la plus exigeante)\n• Matériaux **bas carbone** privilégiés\n• Pompe à chaleur = **énergie renouvelable**\n• Isolation performante = **très faible consommation**\n• Gestion des déchets de chantier responsable\n\n💡 Une maison ORCA consomme **3x moins d''énergie** qu''une maison de 2000. Bon pour la planète ET pour votre portefeuille.', NULL, 6, 1);
