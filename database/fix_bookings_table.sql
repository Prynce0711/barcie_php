-- Database migration to ensure bookings table has all required columns
-- Run this in phpMyAdmin or MySQL command line

-- Ensure bookings table exists with proper structure (no user_id needed)
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) NULL,
    room_id INT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'reservation',
    details TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    checkin DATETIME NULL,
    checkout DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add missing columns if they don't exist
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS receipt_no VARCHAR(50) NULL AFTER id;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS room_id INT NULL AFTER receipt_no;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS type VARCHAR(50) NOT NULL DEFAULT 'reservation' AFTER room_id;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS details TEXT AFTER type;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS status VARCHAR(50) DEFAULT 'pending' AFTER details;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS checkin DATETIME NULL AFTER status;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS checkout DATETIME NULL AFTER checkin;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER checkout;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Remove user_id column if it exists (handle foreign key constraints first)
-- First, drop any foreign key constraints that reference user_id   
ALTER TABLE bookings DROP FOREIGN KEY IF EXISTS fk_bookings_user;
ALTER TABLE bookings DROP FOREIGN KEY IF EXISTS bookings_ibfk_1;
ALTER TABLE bookings DROP INDEX IF EXISTS fk_bookings_user;
ALTER TABLE bookings DROP INDEX IF EXISTS user_id;
-- Now drop the user_id column
ALTER TABLE bookings DROP COLUMN IF EXISTS user_id;

-- Show the final table structure
DESCRIBE bookings;