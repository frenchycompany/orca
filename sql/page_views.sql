-- ============================================
-- Statistiques de fréquentation (sans cookie, RGPD-friendly)
-- Une ligne par page vue. visitor_hash change chaque jour (pas de suivi inter-jours).
-- ============================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    day DATE NOT NULL,
    path VARCHAR(255) NOT NULL,
    page_type VARCHAR(30) NOT NULL DEFAULT 'autre',
    modele_id INT NULL,
    referer_host VARCHAR(120) NULL,
    visitor_hash CHAR(32) NOT NULL,
    is_mobile TINYINT(1) NOT NULL DEFAULT 0,

    INDEX idx_day (day),
    INDEX idx_day_visitor (day, visitor_hash),
    INDEX idx_page_type (page_type),
    INDEX idx_modele (modele_id),
    INDEX idx_path (path(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
