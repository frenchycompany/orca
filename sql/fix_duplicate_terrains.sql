-- Supprimer les doublons de terrains (garder seulement 1 par référence)
DELETE t1 FROM terrains t1
INNER JOIN terrains t2
WHERE t1.id > t2.id AND t1.reference = t2.reference;

-- Vérifier
SELECT reference, COUNT(*) as nb FROM terrains GROUP BY reference HAVING nb > 1;
