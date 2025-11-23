-- Modify existing feedback table to support room-specific reviews
ALTER TABLE feedback 
ADD COLUMN IF NOT EXISTS room_id INT NULL AFTER id,
ADD COLUMN IF NOT EXISTS is_anonymous BOOLEAN DEFAULT FALSE AFTER feedback_email,
ADD INDEX IF NOT EXISTS idx_feedback_room_id (room_id),
ADD INDEX IF NOT EXISTS idx_feedback_rating (rating);

-- Add average rating cache columns to items table
ALTER TABLE items 
ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER price,
ADD COLUMN IF NOT EXISTS total_reviews INT DEFAULT 0 AFTER average_rating;

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_items_average_rating ON items(average_rating);
CREATE INDEX IF NOT EXISTS idx_items_total_reviews ON items(total_reviews);

-- Update existing feedback records to have room_id as NULL (general feedback)
UPDATE feedback SET room_id = NULL WHERE room_id IS NULL;
