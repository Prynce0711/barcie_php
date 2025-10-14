-- Update database structure for proper room-booking relationship
-- Run this SQL in phpMyAdmin or MySQL command line

-- Add room_status field to items table
ALTER TABLE items ADD COLUMN IF NOT EXISTS room_status ENUM('available', 'reserved', 'occupied', 'clean', 'dirty', 'maintenance', 'out_of_order') DEFAULT 'available';

-- Add room_id field to bookings table to create proper relationship
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS room_id INT NULL;

-- Add foreign key constraint (optional but recommended)
-- ALTER TABLE bookings ADD CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES items(id);

-- Update existing bookings to link with rooms based on details field (one-time migration)
-- This tries to match booking details with room names
UPDATE bookings b 
SET room_id = (
    SELECT i.id 
    FROM items i 
    WHERE i.item_type IN ('room', 'facility') 
    AND b.details LIKE CONCAT('%', i.name, '%')
    LIMIT 1
)
WHERE b.room_id IS NULL;

-- Set room status based on current bookings
UPDATE items i 
SET room_status = CASE 
    WHEN EXISTS (
        SELECT 1 FROM bookings b 
        WHERE b.room_id = i.id 
        AND b.status = 'checked_in' 
        AND CURDATE() BETWEEN DATE(b.checkin) AND DATE(b.checkout)
    ) THEN 'occupied'
    WHEN EXISTS (
        SELECT 1 FROM bookings b 
        WHERE b.room_id = i.id 
        AND b.status IN ('approved', 'confirmed') 
        AND DATE(b.checkin) >= CURDATE()
    ) THEN 'reserved'
    ELSE 'available'
END
WHERE i.item_type IN ('room', 'facility');