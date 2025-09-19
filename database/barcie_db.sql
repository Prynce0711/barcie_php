
CREATE DATABASE IF NOT EXISTS barcie_db;
USE barcie_db;

-- Create the admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert an admin user with a plain text password
INSERT INTO admins (username, password)
VALUES ('admin', 'password123'); -- Replace 'password123' with your desired password


CREATE DATABASE IF NOT EXISTS barcie;
USE barcie;

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    checkin DATETIME NOT NULL,
    checkout DATETIME NOT NULL,
    occupants INT NOT NULL,
    company_affiliation VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
