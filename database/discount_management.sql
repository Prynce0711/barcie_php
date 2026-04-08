-- Discount management schema and seed data

CREATE TABLE IF NOT EXISTS discount_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(80) NOT NULL UNIQUE,
    label VARCHAR(150) NOT NULL,
    description TEXT NULL,
    percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
    accepted_id_types TEXT NULL,
    keywords TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO discount_rules (code, label, description, percentage, accepted_id_types, keywords, is_active)
VALUES
('pwd_senior', 'PWD / Senior Citizen', 'Upload a valid PWD or Senior Citizen ID.', 20.00, '["pwd_id","senior_id","national_id","school_id","alumni_id","personnel_id"]', '["pwd","senior citizen","senior","osca","disability"]', 1),
('lcuppersonnel', 'LCUP Personnel', 'Upload LCUP personnel/faculty/staff identification.', 10.00, '["national_id","drivers_license","passport","umid","school_id","alumni_id","personnel_id"]', '["lcup","la consolacion","personnel","faculty","staff","employee"]', 1),
('lcupstudent', 'LCUP Student/Alumni', 'Upload a valid LCUP student ID or alumni proof.', 7.00, '["national_id","passport","postal_id","voters_id","school_id","alumni_id","personnel_id"]', '["lcup","student","alumni","course","year","batch"]', 1)
ON DUPLICATE KEY UPDATE
  label = VALUES(label),
  description = VALUES(description),
  percentage = VALUES(percentage),
  accepted_id_types = VALUES(accepted_id_types),
  keywords = VALUES(keywords),
  is_active = VALUES(is_active);
