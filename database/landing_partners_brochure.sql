-- Landing Partners + Brochure schema and seed data

CREATE TABLE IF NOT EXISTS landing_partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('event_stylist', 'catering') NOT NULL,
    name VARCHAR(255) NOT NULL,
    facebook_url VARCHAR(500) DEFAULT NULL,
    phones TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_partner_category_name (category, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS landing_brochures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    download_name VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_brochure_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO landing_partners (category, name, facebook_url, phones, image_path, sort_order, is_active)
VALUES
('event_stylist', 'House of Brides', 'https://www.facebook.com/hobevents', '0915 865 8973', 'public/images/Caterings/house of brides.jpg', 1, 1),
('catering', 'ALMA SAN PEDRO CATERING & SERVICES', 'https://www.facebook.com/almasanpedroscateringservices/', '(044) 791 3820', 'public/images/Caterings/alma San Pedro.jpg', 1, 1),
('catering', 'TAVERNA BY BNT', 'https://www.facebook.com/TevernabyBNT', '(044) 896 1387\n(044) 791 1188\n0905 357 1188', 'public/images/Caterings/taverna.jpg', 2, 1),
('catering', 'LEONY''S EVENT STYLING AND CATERING SERVICES', 'https://www.facebook.com/p/Leonys-Event-styling-and-catering-service-100063709154603/', '(044) 794 2392', 'public/images/Caterings/Leonys.jpg', 3, 1),
('catering', 'MOMMY LOU''S & NELXYS FOOD HUB', 'https://www.facebook.com/mommylous2/', '(044) 896 4601\n0915 881 8476', 'public/images/Caterings/mommy.jpg', 4, 1),
('catering', 'FAMILY AFFAIR EVENT SPECIALIST (FAES)', 'https://www.facebook.com/fa', '0991 410 9805', 'public/images/Caterings/family Affair.jpg', 5, 1),
('catering', 'Flavors and Catering', 'https://www.facebook.com/FlavorsAndSpicesCatering/about/?_rdr', '0917 867 1111', 'public/images/Caterings/flavor and catering.jpg', 6, 1)
ON DUPLICATE KEY UPDATE
    facebook_url = VALUES(facebook_url),
    phones = VALUES(phones),
    image_path = VALUES(image_path),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);

INSERT INTO landing_brochures (title, image_path, download_name, sort_order, is_active)
VALUES
('Brochure 1', 'public/images/brochure/brochure 1.png', 'BarCIE-Brochure-Page-1.png', 1, 1),
('Brochure 2', 'public/images/brochure/brochure 2.png', 'BarCIE-Brochure-Page-2.png', 2, 1)
ON DUPLICATE KEY UPDATE
    image_path = VALUES(image_path),
    download_name = VALUES(download_name),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active);
