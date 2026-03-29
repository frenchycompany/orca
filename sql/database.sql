-- ============================================================
-- BASE DE DONNÉES ORCA - Site Vitrine Modernisé
-- Database: orca
-- Encoding: UTF8
-- ============================================================

CREATE DATABASE IF NOT EXISTS orca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE orca;

-- ============================================================
-- TABLE: Configuration du site
-- ============================================================
CREATE TABLE config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cle VARCHAR(100) NOT NULL UNIQUE,
    valeur TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO config (cle, valeur, description) VALUES
('site_name', 'Les Maisons ORCA', 'Nom du site'),
('site_slogan', 'Votre maison neuve à petit prix, sans compromis', 'Slogan'),
('site_email', 'contact@maisons-orca.fr', 'Email de contact'),
('site_phone', '03 44 00 00 00', 'Téléphone'),
('site_fax', '', 'Fax'),
('site_address', '119 rue Bordier, 60150 Longueil Annel', 'Adresse'),
('site_siret', '', 'SIRET'),
('meta_description', 'Constructeur de maisons individuelles depuis 1993. 6 modèles de qualité à prix maîtrisé en Picardie et Île-de-France.', 'Meta description'),
('meta_keywords', 'constructeur maison, maison pas chère, maison individuelle, ORCA', 'Mots-clés'),
('google_analytics', '', 'Code GA4'),
('google_maps_api', '', 'Clé API Google Maps'),
('facebook_url', 'https://facebook.com/maisonsorca', 'Facebook'),
('instagram_url', '', 'Instagram'),
('linkedin_url', '', 'LinkedIn'),
('youtube_url', '', 'YouTube'),
('color_primary', '#C41E3A', 'Couleur principale'),
('color_secondary', '#2C3E50', 'Couleur secondaire'),
('color_dark', '#1A1A1A', 'Couleur sombre'),
('logo', '', 'Logo du site'),
('favicon', '', 'Favicon'),
('image_default', '', 'Image par défaut'),
('footer_text', 'Constructeur de maisons individuelles depuis 1993. 6 modèles de qualité à prix maîtrisé.', 'Texte footer'),
('horaires', 'Lundi au Vendredi : 9h-12h / 14h-18h\nSamedi : 10h-17h sur rendez-vous', 'Horaires d\'ouverture'),
('zone_intervention', 'Oise (60), Aisne (02), Somme (80), Seine-et-Marne (77), Val-d\'Oise (95), Val-de-Marne (94), Seine-Saint-Denis (93), Essonne (91)', 'Zone d\'intervention'),
('maintenance_mode', '0', 'Mode maintenance');

-- ============================================================
-- TABLE: Utilisateurs (Admin)
-- ============================================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    role ENUM('admin', 'editor') DEFAULT 'editor',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin par défaut (mot de passe: orca2024) - À CHANGER IMMÉDIATEMENT
INSERT INTO users (username, email, password_hash, nom, prenom, role) VALUES
('admin', 'admin@maisons-orca.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'ORCA', 'admin');

-- ============================================================
-- TABLE: Modèles de maisons
-- ============================================================
CREATE TABLE modeles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    slogan VARCHAR(255),
    description TEXT,
    points_forts TEXT,
    surface_habitable DECIMAL(6,2),
    surface_terrain_min DECIMAL(6,2),
    nb_chambres INT,
    nb_salles_bain INT,
    nb_etages ENUM('plain-pied', '1-etage', '2-etages') DEFAULT 'plain-pied',
    style ENUM('traditionnel', 'contemporain', 'moderne') DEFAULT 'traditionnel',
    prix_base DECIMAL(10,2),
    prix_afficher VARCHAR(50),
    chambre_rdc BOOLEAN DEFAULT FALSE,
    garage BOOLEAN DEFAULT TRUE,
    terrasse BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_promo BOOLEAN DEFAULT FALSE,
    ordre_affichage INT DEFAULT 0,
    image_principale VARCHAR(255),
    images_galerie JSON,
    plan_pdf VARCHAR(255),
    meta_title VARCHAR(150),
    meta_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO modeles (nom, slug, slogan, description, points_forts, surface_habitable, surface_terrain_min, nb_chambres, nb_salles_bain, nb_etages, style, prix_afficher, chambre_rdc, garage, ordre_affichage) VALUES
('Le Coquelicot', 'le-coquelicot', 'La maison lumineuse et économique', 
'Le modèle Coquelicot allie simplicité et fonctionnalité. Avec ses larges baies vitrées, elle offre une luminosité exceptionnelle tout en maîtrisant votre budget.', 
'Luminosité exceptionnelle\nSalon séjour traversant\nCuisine ouverte moderne\nChambre parentale avec dressing\nGarage intégré',
88.50, 350, 3, 1, 'plain-pied', 'traditionnel', 'À partir de 145 000 €', TRUE, TRUE, 1),

('La Tulipe', 'la-tulipe', 'L\'élégance au meilleur prix',
'La Tulipe séduit par son architecture harmonieuse. Ce modèle optimise chaque mètre carré pour vous offrir un espace de vie agréable et convivial.',
'Architecture harmonieuse\nDouble séjour avec cheminée\n3 chambres spacieuses\nSalle de bain familiale\nCellier attenant',
95.00, 400, 3, 1, 'plain-pied', 'contemporain', 'À partir de 152 000 €', TRUE, TRUE, 2),

('L\'Hibiscus', 'l-hibiscus', 'La contemporaine accessible',
'L\'Hibiscus révolutionne l\'accès à la maison moderne. Lignes épurées, grandes ouvertures et espaces optimisés pour une maison d\'aujourd\'hui.',
'Design contemporain épuré\nBaies coulissantes XXL\nSuite parentale moderne\nToiture terrasse possible\nRT2012 / RE2020',
102.00, 450, 3, 2, 'plain-pied', 'moderne', 'À partir de 168 000 €', TRUE, TRUE, 3),

('Le Lila', 'le-lila', 'L\'espace pour toute la famille',
'Avec ses 4 chambres, le Lila accueille les familles nombreuses. Son étage bien pensé offre à chacun son espace privé tout en préservant les espaces de vie communs.',
'4 chambres dont suite parentale\nBureau à l\'étage\nDouble séjour 40m²\nGarage double possible\nCombles aménageables',
120.00, 500, 4, 2, '1-etage', 'traditionnel', 'À partir de 185 000 €', FALSE, TRUE, 4),

('L\'Orchidée', 'l-orchidee', 'Le raffinement à étage',
'L\'Orchidée conjugue l\'élégance traditionnelle et les prestations haut de gamme. Une maison de caractère qui se distingue par ses finitions soignées.',
'Architecture de caractère\nHall d\'entrée avec escalier\nCuisine séparée possible\n3 chambres + dressing\nSalle d\'eau à l\'étage',
110.00, 450, 3, 2, '1-etage', 'contemporain', 'À partir de 178 000 €', FALSE, TRUE, 5),

('Le Magnolia', 'le-magnolia', 'La maison XXL accessible',
'Le Magnolia est notre modèle premium accessible. Grande surface, prestations de qualité et possibilités d\'extension pour une maison évolutive.',
'Surface généreuse 130m²\n4/5 chambres possibles\nPièce de vie 50m²\nGarage double standard\nExtension modulaire',
130.00, 600, 4, 2, '1-etage', 'moderne', 'À partir de 215 000 €', TRUE, TRUE, 6);

-- ============================================================
-- TABLE: Témoignages clients
-- ============================================================
CREATE TABLE temoignages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    ville VARCHAR(100),
    departement VARCHAR(10),
    modele_id INT,
    note INT DEFAULT 5,
    temoignage TEXT NOT NULL,
    photo VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (modele_id) REFERENCES modeles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO temoignages (nom, prenom, ville, departement, modele_id, note, temoignage, is_featured) VALUES
('MARTIN', 'Sophie et Jean', 'Compiègne', '60', 1, 5, 'Nous avons choisi le modèle Coquelicot pour notre première maison. L\'accompagnement ORCA a été impeccable du début à la fin. Notre maison est livrée dans les temps et conforme à nos attentes. Prix respecté, pas de surprise !', TRUE),
('DUBOIS', 'Marie', 'Creil', '60', 2, 5, 'Maison Tulipe livrée en 4 mois. Je suis ravie de la qualité des finitions et de la luminosité. Le rapport qualité-prix est imbattable. Je recommande ORCA les yeux fermés.', TRUE),
('BERNARD', 'Pierre et Claire', 'Noyon', '60', 3, 5, 'L\'Hibiscus correspond parfaitement à notre recherche d\'une maison moderne sans exploser notre budget. L\'équipe a été à l\'écoute et professionnelle.', TRUE),
('PETIT', 'Laurent', 'Longueil-Annel', '60', 1, 4, 'Bonne expérience globale. Quelques petits retouches à faire après livraison mais le SAV est réactif. Le prix proposé est vraiment compétitif.', FALSE),
('ROBERT', 'Isabelle', 'Méru', '60', 4, 5, 'Nous avions besoin de 4 chambres pour nos 3 enfants. Le Lila est parfait ! Chacun a son espace, la maison est bien insonorisée. Merci ORCA !', TRUE),
('RICHARD', 'Michel', 'Crépy-en-Valois', '60', 5, 5, 'Après avoir comparé 5 constructeurs, nous avons choisi ORCA pour leur transparence tarifaire. Pas de surprise, tout est clair dès le départ. La maison est superbe.', FALSE);

-- ============================================================
-- TABLE: Actualités / Blog
-- ============================================================
CREATE TABLE actualites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    extrait TEXT,
    contenu LONGTEXT,
    image VARCHAR(255),
    categorie ENUM('actualite', 'conseil', 'temoignage', 'promo') DEFAULT 'actualite',
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    nb_vues INT DEFAULT 0,
    published_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO actualites (titre, slug, extrait, contenu, categorie, is_featured, published_at) VALUES
('RE2020 : quels impacts sur votre future maison ?', 're2020-impacts-future-maison', 
'Découvrez comment la nouvelle réglementation environnementale RE2020 influence la construction de maisons neuves et les économies d\'énergie.',
'<h2>La RE2020, c\'est quoi ?</h2><p>La réglementation environnementale RE2020 remplace la RT2012 depuis le 1er janvier 2022...</p><h2>Impact sur votre maison</h2><p>Les nouvelles constructions doivent désormais...</p>',
'conseil', TRUE, NOW()),

('Nos maisons sont désormais 100% RE2020', 'maisons-100-re2020',
'Toutes nos maisons ORCA répondent aux exigences de la RE2020. Mieux pour la planète, meilleur pour votre portefeuille !',
'<p>Nous avons adapté l\'ensemble de notre gamme...</p>',
'actualite', TRUE, NOW()),

('5 conseils pour choisir son terrain', '5-conseils-choisir-terrain',
'Le terrain est l\'élément clé de votre projet. Voici nos conseils pour faire le bon choix.',
'<h2>1. Vérifiez le PLU</h2><p>Le Plan Local d\'Urbanisme...</p>',
'conseil', FALSE, NOW());

-- ============================================================
-- TABLE: Leads / Contacts
-- ============================================================
CREATE TABLE leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_demande ENUM('devis', 'rappel', 'info', 'brochure', 'visite') DEFAULT 'devis',
    civilite ENUM('M', 'Mme', 'Mmlle') DEFAULT 'M',
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    adresse VARCHAR(255),
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    departement VARCHAR(10),
    modele_interesse INT,
    surface_souhaitee VARCHAR(50),
    budget_estime VARCHAR(50),
    terrain_prevu BOOLEAN DEFAULT FALSE,
    delai_souhaite ENUM('immediatement', '3-mois', '6-mois', '1-an', 'plus') DEFAULT '6-mois',
    commentaire TEXT,
    source VARCHAR(50) DEFAULT 'site-web',
    page_source VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_treated BOOLEAN DEFAULT FALSE,
    notes_internes TEXT,
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    treated_at DATETIME,
    FOREIGN KEY (modele_interesse) REFERENCES modeles(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: Pages (contenu éditable)
-- ============================================================
CREATE TABLE pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(150) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    template VARCHAR(50) DEFAULT 'standard',
    contenu LONGTEXT,
    meta_title VARCHAR(150),
    meta_description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    in_menu BOOLEAN DEFAULT TRUE,
    menu_order INT DEFAULT 0,
    parent_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES pages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pages (titre, slug, template, meta_title, meta_description, menu_order) VALUES
('Accueil', 'accueil', 'home', 'Constructeur maison pas chère Picardie Île-de-France | ORCA', 'Constructeur de maisons individuelles depuis 1993. 6 modèles de qualité à prix maîtrisé. Devis gratuit !', 0),
('Le constructeur', 'le-constructeur', 'standard', 'Qui sommes-nous ? | Maisons ORCA', 'Découvrez ORCA, constructeur de maisons individuelles depuis 1993. Notre expertise à votre service.', 1),
('Nos modèles', 'nos-modeles', 'modeles', 'Nos 6 modèles de maisons | Maisons ORCA', 'Découvrez nos 6 modèles de maisons individuelles. Plain-pied ou étage, traditionnel ou contemporain.', 2),
('Nos engagements', 'nos-engagements', 'standard', 'Nos garanties et engagements | Maisons ORCA', 'RE2020, garanties décennales, accompagnement personnalisé. Découvrez nos engagements.', 3),
('Contact', 'contact', 'contact', 'Contactez-nous | Maisons ORCA', 'Demandez un devis gratuit ou demandez à être rappelé. Notre équipe vous répond sous 24h.', 4),
('Mentions légales', 'mentions-legales', 'standard', 'Mentions légales | Maisons ORCA', NULL, 99),
('Politique de confidentialité', 'politique-confidentialite', 'standard', 'Politique de confidentialité | Maisons ORCA', NULL, 99);

-- ============================================================
-- TABLE: Agences
-- ============================================================
CREATE TABLE agences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    adresse VARCHAR(255),
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    telephone VARCHAR(20),
    email VARCHAR(150),
    horaires TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_active BOOLEAN DEFAULT TRUE,
    ordre_affichage INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO agences (nom, adresse, code_postal, ville, telephone, email, horaires) VALUES
('Siège Social - Longueil-Annel', '119 rue Bordier', '60150', 'Longueil-Annel', '03 44 00 00 00', 'contact@maisons-orca.fr', 'Lundi au Vendredi : 9h-12h / 14h-18h\nSamedi : 10h-17h sur rendez-vous'),
('Agence de Compiègne', '15 rue des Jardins', '60200', 'Compiègne', '03 44 00 00 01', 'compiegne@maisons-orca.fr', 'Lundi au Vendredi : 9h-12h / 14h-18h'),
('Agence de Meaux', '8 place du Marché', '77100', 'Meaux', '01 64 00 00 00', 'meaux@maisons-orca.fr', 'Lundi au Vendredi : 9h-12h / 14h-18h');

-- ============================================================
-- TABLE: FAQ
-- ============================================================
CREATE TABLE faq (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    reponse TEXT NOT NULL,
    categorie VARCHAR(50) DEFAULT 'general',
    ordre_affichage INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO faq (question, reponse, categorie, ordre_affichage) VALUES
('Pourquoi les prix ORCA sont-ils si bas ?', 'Nous optimisons nos processus et proposons des modèles standardisés. Pas de personnalisation possible = pas de surcoût. C\'est notre engagement prix maîtrisé.', 'prix', 1),
('Puis-je modifier un modèle ORCA ?', 'Non, nos modèles sont figés pour garantir le meilleur prix. Vous choisissez parmi nos 6 modèles disponibles, sans possibilité de modification. C\'est le secret de nos tarifs compétitifs.', 'modeles', 2),
('Quelles sont les garanties ORCA ?', 'Nous vous offrez : la garantie décennale (10 ans), la garantie biennale (2 ans), la garantie dommages-ouvrage et l\'assurance responsabilité civile.', 'garanties', 3),
('Combien de temps pour construire ma maison ?', 'Délai moyen de 4 à 6 mois après obtention du permis de construire. Nous vous accompagnons à chaque étape.', 'delai', 4),
('Puis-je visiter une maison ORCA ?', 'Oui ! Nous avons des maisons témoins ouvertes sur rendez-vous. Contactez-nous pour programmer votre visite.', 'visite', 5),
('La RE2020 est-elle incluse ?', 'Oui, toutes nos maisons sont conformes RE2020 sans surcoût. Vous bénéficiez des dernières normes énergétiques.', 'normes', 6);

-- ============================================================
-- TABLE: Newsletter
-- ============================================================
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL UNIQUE,
    nom VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INDEXES
-- ============================================================
CREATE INDEX idx_modeles_active ON modeles(is_active);
CREATE INDEX idx_modeles_ordre ON modeles(ordre_affichage);
CREATE INDEX idx_leads_email ON leads(email);
CREATE INDEX idx_leads_treated ON leads(is_treated);
CREATE INDEX idx_leads_created ON leads(created_at);
CREATE INDEX idx_actualites_published ON actualites(published_at);
CREATE INDEX idx_temoignages_featured ON temoignages(is_featured);

-- ============================================================
-- VUES
-- ============================================================
CREATE VIEW stats_leads_mois AS
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as mois,
    COUNT(*) as nb_leads,
    SUM(CASE WHEN is_treated = 1 THEN 1 ELSE 0 END) as nb_traites
FROM leads
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY mois DESC;
