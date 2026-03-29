-- ============================================
-- ORCA - Table Terrains disponibles
-- ============================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS terrains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(20) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    code_postal VARCHAR(10) NOT NULL,
    departement VARCHAR(10) NOT NULL,
    surface INT NOT NULL COMMENT 'Surface en m²',
    prix DECIMAL(10,2) NOT NULL,
    est_viabilise BOOLEAN DEFAULT FALSE,
    type_terrain ENUM('plat', 'en_pente', 'boise', 'constructible') DEFAULT 'plat',
    description TEXT,
    proximite TEXT COMMENT 'Commodités à proximité',
    is_available BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_departement (departement),
    INDEX idx_available (is_available),
    INDEX idx_prix (prix),
    INDEX idx_surface (surface)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Données d'exemple
INSERT INTO terrains (reference, ville, code_postal, departement, surface, prix, est_viabilise, type_terrain, description, proximite, is_available, is_featured) VALUES
('T-60-001', 'Compiègne', '60200', '60', 450, 65000.00, 1, 'plat', 'Beau terrain plat dans un lotissement calme, prêt à bâtir.', 'Écoles, commerces à 5 min, gare à 10 min', 1, 1),
('T-60-002', 'Senlis', '60300', '60', 620, 89000.00, 1, 'plat', 'Grand terrain viabilisé en centre-ville, proche toutes commodités.', 'Centre-ville, écoles, médecin, supermarché', 1, 1),
('T-60-003', 'Longueil-Annel', '60150', '60', 380, 45000.00, 0, 'plat', 'Terrain à viabiliser dans quartier résidentiel.', 'Proche canal, commerces à 3 min', 1, 0),
('T-60-004', 'Noyon', '60400', '60', 550, 52000.00, 1, 'plat', 'Terrain viabilisé dans lotissement récent avec vue dégagée.', 'Écoles, gare TER, commerces', 1, 0),
('T-77-001', 'Meaux', '77100', '77', 500, 95000.00, 1, 'plat', 'Terrain constructible dans secteur pavillonnaire recherché.', 'RER, commerces, hôpital', 1, 1),
('T-77-002', 'Coulommiers', '77120', '77', 700, 78000.00, 1, 'plat', 'Vaste terrain plat idéal pour maison avec jardin.', 'Centre-ville à 5 min, écoles', 1, 0),
('T-95-001', 'Cergy', '95000', '95', 400, 110000.00, 1, 'plat', 'Terrain rare en zone urbaine, toutes commodités.', 'RER A, université, commerces', 1, 1),
('T-95-002', 'L\'Isle-Adam', '95290', '95', 580, 98000.00, 1, 'boise', 'Terrain partiellement boisé dans cadre verdoyant.', 'Forêt, rivière, commerces à 5 min', 1, 0),
('T-02-001', 'Laon', '02000', '02', 800, 35000.00, 0, 'plat', 'Grand terrain avec belle exposition sud.', 'Centre-ville, gare, écoles', 1, 0),
('T-80-001', 'Amiens', '80000', '80', 480, 72000.00, 1, 'plat', 'Terrain viabilisé dans nouveau quartier résidentiel.', 'Tramway, commerces, université', 1, 0);
