-- Create the database
CREATE DATABASE IF NOT EXISTS barcie_db;

-- Use the database
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