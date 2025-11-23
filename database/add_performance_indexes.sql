-- =====================================================
-- Database Performance Optimization - Add Indexes
-- BarCIE Hotel Management System
-- =====================================================
-- Run this script to add indexes for improved query performance
-- This will speed up lookups on frequently queried columns

-- =====================================================
-- BOOKINGS TABLE INDEXES
-- =====================================================

-- Index on status column (frequently used in WHERE clauses and JOINs)
CREATE INDEX IF NOT EXISTS idx_status ON bookings (status);

-- Index on check-in date (used for date range queries and availability checks)
CREATE INDEX IF NOT EXISTS idx_checkin ON bookings (checkin);

-- Index on check-out date (used for date range queries)
CREATE INDEX IF NOT EXISTS idx_checkout ON bookings (checkout);

-- Index on room_id (used in JOINs with items table)
CREATE INDEX IF NOT EXISTS idx_room_id ON bookings (room_id);

-- Index on created_at for sorting recent bookings
CREATE INDEX IF NOT EXISTS idx_created_at ON bookings (created_at);

-- Composite index for date range and status queries (most common query pattern)
CREATE INDEX IF NOT EXISTS idx_room_date_status ON bookings (room_id, checkin, checkout, status);

-- Index on payment_status for filtering paid/unpaid bookings
CREATE INDEX IF NOT EXISTS idx_payment_status ON bookings (payment_status);

-- Index on discount_status for discount management
CREATE INDEX IF NOT EXISTS idx_discount_status ON bookings (discount_status);

-- =====================================================
-- PENCIL_BOOKINGS TABLE INDEXES
-- =====================================================

-- Index on status
CREATE INDEX IF NOT EXISTS idx_status ON pencil_bookings (status);

-- Index on check-in date
CREATE INDEX IF NOT EXISTS idx_checkin ON pencil_bookings (checkin);

-- Index on check-out date
CREATE INDEX IF NOT EXISTS idx_checkout ON pencil_bookings (checkout);

-- Index on room_id
CREATE INDEX IF NOT EXISTS idx_room_id ON pencil_bookings (room_id);

-- Index on expires_at for finding expiring bookings
CREATE INDEX IF NOT EXISTS idx_expires_at ON pencil_bookings (expires_at);

-- Index on created_at
CREATE INDEX IF NOT EXISTS idx_created_at ON pencil_bookings (created_at);

-- Composite index for date range and status queries
CREATE INDEX IF NOT EXISTS idx_room_date_status ON pencil_bookings (room_id, checkin, checkout, status);

-- =====================================================
-- ITEMS TABLE INDEXES (Rooms & Facilities)
-- =====================================================

-- Index on item_type for filtering rooms vs facilities
CREATE INDEX IF NOT EXISTS idx_item_type ON items (item_type);

-- Index on room_status for availability checks
CREATE INDEX IF NOT EXISTS idx_room_status ON items (room_status);

-- Index on name for searching
CREATE INDEX IF NOT EXISTS idx_name ON items (name);

-- Composite index for filtering available rooms/facilities by type
CREATE INDEX IF NOT EXISTS idx_type_status ON items (item_type, room_status);

-- =====================================================
-- ADMINS TABLE INDEXES
-- =====================================================

-- Index on username for admin login
CREATE INDEX IF NOT EXISTS idx_username ON admins (username);

-- =====================================================
-- FEEDBACK TABLE INDEXES
-- =====================================================

-- Index on created_at for sorting feedback by date
CREATE INDEX IF NOT EXISTS idx_created_at ON feedback (created_at);

-- =====================================================
-- NEWS TABLE INDEXES (if exists)
-- =====================================================

-- Index on created_at for sorting news
CREATE INDEX IF NOT EXISTS idx_created_at ON news_updates (created_at);

-- Index on status for filtering published news
CREATE INDEX IF NOT EXISTS idx_status ON news_updates (status);

-- =====================================================
-- VERIFY INDEXES CREATED
-- =====================================================
-- Run these queries to verify indexes were created successfully:

-- SHOW INDEXES FROM bookings;
-- SHOW INDEXES FROM pencil_bookings;
-- SHOW INDEXES FROM items;
-- SHOW INDEXES FROM admins;
-- SHOW INDEXES FROM feedback;

-- =====================================================
-- PERFORMANCE NOTES
-- =====================================================
-- 1. Indexes speed up SELECT queries but slightly slow down INSERT/UPDATE
-- 2. For small tables (< 1000 rows), indexes may not show significant improvement
-- 3. Composite indexes are used when queries filter by multiple columns together
-- 4. Monitor query performance with EXPLAIN before and after adding indexes
-- 5. Remove unused indexes if they don't improve query performance

-- Example: Check query execution plan
-- EXPLAIN SELECT * FROM bookings WHERE room_id = 1 AND status = 'approved' AND checkin >= '2025-01-01';

-- =====================================================
-- MAINTENANCE COMMANDS
-- =====================================================
-- Optimize tables after adding indexes:
-- OPTIMIZE TABLE bookings;
-- OPTIMIZE TABLE pencil_bookings;
-- OPTIMIZE TABLE items;

-- Analyze tables to update statistics:
-- ANALYZE TABLE bookings;
-- ANALYZE TABLE pencil_bookings;
-- ANALYZE TABLE items;

-- =====================================================
-- ROLLBACK (Remove indexes if needed)
-- =====================================================
/*
-- To remove indexes if needed:
ALTER TABLE bookings DROP INDEX idx_status;
ALTER TABLE bookings DROP INDEX idx_checkin;
ALTER TABLE bookings DROP INDEX idx_checkout;
ALTER TABLE bookings DROP INDEX idx_room_id;
ALTER TABLE bookings DROP INDEX idx_created_at;
ALTER TABLE bookings DROP INDEX idx_room_date_status;
ALTER TABLE bookings DROP INDEX idx_payment_status;
ALTER TABLE bookings DROP INDEX idx_discount_status;

ALTER TABLE pencil_bookings DROP INDEX idx_status;
ALTER TABLE pencil_bookings DROP INDEX idx_checkin;
ALTER TABLE pencil_bookings DROP INDEX idx_checkout;
ALTER TABLE pencil_bookings DROP INDEX idx_room_id;
ALTER TABLE pencil_bookings DROP INDEX idx_expires_at;
ALTER TABLE pencil_bookings DROP INDEX idx_created_at;
ALTER TABLE pencil_bookings DROP INDEX idx_room_date_status;

ALTER TABLE items DROP INDEX idx_item_type;
ALTER TABLE items DROP INDEX idx_room_status;
ALTER TABLE items DROP INDEX idx_name;
ALTER TABLE items DROP INDEX idx_type_status;

ALTER TABLE admins DROP INDEX idx_username;

ALTER TABLE feedback DROP INDEX idx_created_at;
ALTER TABLE feedback DROP INDEX idx_email;

ALTER TABLE news_updates DROP INDEX idx_created_at;
ALTER TABLE news_updates DROP INDEX idx_status;
*/
