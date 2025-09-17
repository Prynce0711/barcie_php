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

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(50) NOT NULL,
    type VARCHAR(100),
    status VARCHAR(50) DEFAULT 'available'
    
    
);

ALTER TABLE rooms {
ADD COLUMN notes TEXT NULL,
ADD COLUMN image VARCHAR(255) NULL,
MODIFY status ENUM('available','occupied','maintenance') DEFAULT 'available';
};

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT,
    guest_name VARCHAR(100),
    check_in DATE,
    check_out DATE,
    status VARCHAR(50) DEFAULT 'active',
    FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE IF NOT EXISTS facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    capacity INT,
    price DECIMAL(10,2)
);



