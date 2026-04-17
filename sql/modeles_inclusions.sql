-- Ajout des colonnes inclusions par modèle
ALTER TABLE modeles
    ADD COLUMN inclus_structure TEXT AFTER plan_pdf,
    ADD COLUMN inclus_interieur TEXT AFTER inclus_structure,
    ADD COLUMN inclus_equipements TEXT AFTER inclus_interieur;

-- Remplir avec les valeurs par défaut pour les modèles existants
UPDATE modeles SET
    inclus_structure = 'Fondations superficielles\nMurs en briques ou parpaings\nCharpente traditionnelle\nCouverture tuiles ou ardoises\nMenuiseries PVC ou ALU',
    inclus_interieur = 'Cloisons et plafonds\nCarrelage séjour/cuisine\nParquet ou moquette chambres\nCuisine équipée (meubles + électro)\nSalle de bain complète',
    inclus_equipements = 'Chauffage gaz + eau chaude\nVolets roulants électriques\nPorte de garage sectionnelle\nPortail + interphone\nJardinet clôturé'
WHERE inclus_structure IS NULL;
