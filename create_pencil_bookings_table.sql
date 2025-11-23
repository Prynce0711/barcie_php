-- Pencil Bookings Table
-- This table stores draft/tentative reservations with a 2-week confirmation period
-- Separate from main bookings table to manage the pencil booking workflow

CREATE TABLE IF NOT EXISTS pencil_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) UNIQUE NOT NULL,
    room_id INT NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    checkin DATETIME NOT NULL,
    checkout DATETIME NOT NULL,
    occupants INT NOT NULL,
    company VARCHAR(255) DEFAULT NULL,
    company_contact VARCHAR(100) DEFAULT NULL,
    
    -- Discount information
    discount_code VARCHAR(50) DEFAULT NULL,
    discount_proof_path VARCHAR(255) DEFAULT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    
    -- Pricing
    base_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    
    -- Status tracking
    status ENUM('pending', 'approved', 'confirmed', 'cancelled', 'rejected', 'expired') DEFAULT 'pending',
    
    -- Two-week acknowledgment
    terms_acknowledged BOOLEAN DEFAULT FALSE,
    acknowledgment_timestamp DATETIME DEFAULT NULL,
    
    -- Important dates
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL, -- Automatically set to 2 weeks from creation
    confirmed_at DATETIME DEFAULT NULL,
    
    -- Email tracking
    reminder_sent BOOLEAN DEFAULT FALSE,
    reminder_sent_at DATETIME DEFAULT NULL,
    confirmation_email_sent BOOLEAN DEFAULT FALSE,
    
    -- Additional details
    details TEXT DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    
    -- Foreign key
    CONSTRAINT fk_pencil_room FOREIGN KEY (room_id) REFERENCES items(id) ON DELETE RESTRICT,
    
    -- Indexes for better query performance
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_checkin (checkin),
    INDEX idx_expires_at (expires_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add trigger to automatically set expiration date (2 weeks from creation)
DELIMITER $$

CREATE TRIGGER set_pencil_booking_expiration 
BEFORE INSERT ON pencil_bookings
FOR EACH ROW
BEGIN
    IF NEW.expires_at IS NULL THEN
        SET NEW.expires_at = DATE_ADD(NEW.created_at, INTERVAL 14 DAY);
    END IF;
    
    IF NEW.terms_acknowledged = TRUE AND NEW.acknowledgment_timestamp IS NULL THEN
        SET NEW.acknowledgment_timestamp = NOW();
    END IF;
END$$

DELIMITER ;

-- Optional: Create a view for easier querying with room details
CREATE OR REPLACE VIEW pencil_bookings_with_details AS
SELECT 
    pb.*,
    i.name AS room_name,
    i.room_number,
    i.item_type,
    i.capacity AS room_capacity,
    i.price AS room_base_price,
    DATEDIFF(pb.expires_at, NOW()) AS days_until_expiration,
    CASE 
        WHEN pb.status = 'expired' THEN 'Expired'
        WHEN pb.status = 'confirmed' THEN 'Confirmed'
        WHEN pb.status = 'cancelled' THEN 'Cancelled'
        WHEN pb.status = 'rejected' THEN 'Rejected'
        WHEN DATEDIFF(pb.expires_at, NOW()) <= 0 THEN 'Expired'
        WHEN DATEDIFF(pb.expires_at, NOW()) <= 3 THEN 'Expiring Soon'
        ELSE 'Active'
    END AS urgency_status
FROM pencil_bookings pb
LEFT JOIN items i ON pb.room_id = i.id;
