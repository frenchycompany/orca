-- ============================================
-- Table des textes éditables du site
-- ============================================
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS site_texts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page VARCHAR(50) NOT NULL,
    text_key VARCHAR(100) NOT NULL,
    text_value TEXT NOT NULL,
    text_type ENUM('text','textarea','html') DEFAULT 'text',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_page_key (page, text_key),
    INDEX idx_page (page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
