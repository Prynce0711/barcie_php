-- Enhance bookings table with comprehensive tracking
-- Add timestamp columns if they don't exist

ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS payment_date TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS checked_out_at TIMESTAMP NULL;

-- Enhance feedback table with name and timestamp
ALTER TABLE feedback
ADD COLUMN IF NOT EXISTS feedback_name VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS feedback_email VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add index for better query performance
CREATE INDEX IF NOT EXISTS idx_bookings_created_at ON bookings(created_at);
CREATE INDEX IF NOT EXISTS idx_bookings_payment_date ON bookings(payment_date);
CREATE INDEX IF NOT EXISTS idx_bookings_status ON bookings(status);
CREATE INDEX IF NOT EXISTS idx_feedback_created_at ON feedback(created_at);
