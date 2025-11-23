-- Add approval_status column to feedback table
-- This allows admin to approve/reject feedback before it appears publicly

ALTER TABLE feedback 
ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER is_anonymous,
ADD INDEX idx_approval_status (approval_status),
ADD INDEX idx_room_approved (room_id, approval_status);

-- Update existing feedback to approved (for backwards compatibility)
UPDATE feedback SET approval_status = 'approved' WHERE approval_status IS NULL OR approval_status = 'pending';
