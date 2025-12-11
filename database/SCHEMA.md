# BarCIE Hotel Management System - Database Schema

**Version:** 1.0.0  
**Last Updated:** December 11, 2025  
**XAMPP Path:** `C:\xampp\htdocs\barcie_php`  
**Database:** `barcie_db`

---

## 🚀 Quick Start

### Update Your Database Structure

```
http://localhost/barcie_php/database/update_database.php
```

This script will safely update your database schema without destroying existing data.

### View Current Structure

```
http://localhost/barcie_php/database/check_structure.php
```

---

## 📊 Current Database Tables

Your database currently has **6 tables**:

1. `items` - Rooms and facilities
2. `admins` - Administrator accounts
3. `bookings` - Confirmed bookings
4. `pencil_bookings` - Tentative bookings
5. `feedback` - Customer reviews
6. `news_updates` - News articles

---

## 📋 Table Schemas

### 1. `items` - Rooms & Facilities

Stores all bookable inventory including rooms and facilities.

**Structure:**
```sql
CREATE TABLE `items` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `item_type` ENUM('room', 'facility', 'amenity', 'service') NOT NULL,
  `room_number` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `capacity` INT(11) DEFAULT 0,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `average_rating` DECIMAL(3,2) DEFAULT 0.00,
  `total_reviews` INT(11) DEFAULT 0,
  `image` VARCHAR(255) NULL,
  `images` TEXT NULL COMMENT 'JSON array of images',
  `addons` LONGTEXT NULL COMMENT 'JSON array of add-ons',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `room_status` ENUM('available', 'reserved', 'occupied', 'clean', 'dirty', 'maintenance', 'out_of_order') DEFAULT 'available',
  
  INDEX `idx_name` (`name`),
  INDEX `idx_item_type` (`item_type`),
  INDEX `idx_room_status` (`room_status`),
  INDEX `idx_average_rating` (`average_rating`),
  INDEX `idx_total_reviews` (`total_reviews`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Key Fields:**
- `item_type`: Type of item (room, facility, amenity, service)
- `room_status`: Current availability status
- `images`: JSON array for multiple images
- `addons`: JSON array for add-on services

---

### 2. `admins` - Administrator Accounts

Stores staff and administrator accounts with roles.

**Structure:**
```sql
CREATE TABLE `admins` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `full_name` VARCHAR(255) NULL,
  `role` ENUM('super_admin', 'admin', 'manager', 'staff') DEFAULT 'staff',
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL,
  
  INDEX `idx_username` (`username`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Roles:**
- `super_admin`: Full system access
- `admin`: Administrative access
- `manager`: Management functions
- `staff`: Basic operations

**Security:**
- Passwords hashed with BCRYPT
- Session-based authentication

---

### 3. `bookings` - Confirmed Reservations

Stores all confirmed bookings and reservations.

**Structure:**
```sql
CREATE TABLE `bookings` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `receipt_no` VARCHAR(50) NULL,
  `room_id` INT(11) NULL,
  `type` ENUM('reservation', 'pencil', 'facility') NOT NULL,
  `details` TEXT NOT NULL,
  `status` VARCHAR(50) DEFAULT 'Pending',
  `discount_status` VARCHAR(50) DEFAULT 'none',
  `reminder_sent` TINYINT(1) DEFAULT 0,
  `proof_of_id` VARCHAR(255) NULL,
  `payment_status` VARCHAR(50) DEFAULT 'none',
  `proof_of_payment` VARCHAR(255) NULL,
  `payment_verified_by` INT(11) NULL,
  `payment_verified_at` DATETIME NULL,
  `payment_date` TIMESTAMP NULL,
  `checkin` DATETIME NULL,
  `checkout` DATETIME NULL,
  `checked_out_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_status` (`status`),
  INDEX `idx_room_id` (`room_id`),
  INDEX `idx_checkin` (`checkin`),
  INDEX `idx_checkout` (`checkout`),
  INDEX `idx_discount_status` (`discount_status`),
  INDEX `idx_payment_status` (`payment_status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Status Values:**
- `Pending`: Awaiting confirmation
- `confirmed`: Booking confirmed
- `approved`: Payment verified
- `checked_in`: Guest checked in
- `checked_out`: Guest checked out
- `cancelled`: Booking cancelled

**Payment Status:**
- `none`: No payment submitted
- `pending`: Payment submitted, awaiting verification
- `verified`: Payment confirmed by admin
- `rejected`: Payment rejected

---

### 4. `pencil_bookings` - Tentative Bookings

Stores unconfirmed "pencil" bookings that can be converted to full bookings.

**Structure:**
```sql
CREATE TABLE `pencil_bookings` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `receipt_no` VARCHAR(50) UNIQUE NOT NULL,
  `room_id` INT(11) NOT NULL,
  `guest_name` VARCHAR(255) NOT NULL,
  `contact_number` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `checkin` DATETIME NOT NULL,
  `checkout` DATETIME NOT NULL,
  `occupants` INT(11) DEFAULT 1,
  `company` VARCHAR(255) NULL,
  `company_contact` VARCHAR(50) NULL,
  `discount_code` VARCHAR(50) NULL,
  `discount_proof_path` VARCHAR(255) NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `base_price` DECIMAL(10,2) NOT NULL,
  `total_price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'approved', 'converted', 'expired', 'cancelled') DEFAULT 'pending',
  `terms_acknowledged` TINYINT(1) DEFAULT 0,
  `acknowledgment_timestamp` DATETIME NULL,
  `conversion_token` VARCHAR(255) UNIQUE NULL,
  `token_expires_at` DATETIME NULL,
  `converted_booking_receipt` VARCHAR(50) NULL,
  `details` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_receipt_no` (`receipt_no`),
  INDEX `idx_room_id` (`room_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_status` (`status`),
  INDEX `idx_conversion_token` (`conversion_token`),
  INDEX `idx_token_expires` (`token_expires_at`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Conversion Flow:**
1. Create pencil booking with conversion token
2. Send email with conversion link
3. Guest clicks link to convert to full booking
4. Token expires after set duration
5. Status changes to 'converted'

---

### 5. `feedback` - Customer Reviews

Stores customer feedback and reviews.

**Structure:**
```sql
CREATE TABLE `feedback` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT(11) NULL,
  `rating` INT(11) NOT NULL DEFAULT 5,
  `message` TEXT NULL,
  `feedback_name` VARCHAR(255) NULL,
  `feedback_email` VARCHAR(255) NULL,
  `is_anonymous` TINYINT(1) DEFAULT 0,
  `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `admin_response` TEXT NULL,
  `responded_by` INT(11) NULL,
  `responded_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_room_id` (`room_id`),
  INDEX `idx_rating` (`rating`),
  INDEX `idx_approval_status` (`approval_status`),
  INDEX `idx_created_at` (`created_at`),
  
  CONSTRAINT `chk_rating` CHECK (`rating` >= 1 AND `rating` <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Features:**
- Anonymous or named feedback
- 5-star rating system
- Admin approval workflow
- Admin response capability

---

### 6. `news_updates` - News & Announcements

Stores news articles and system announcements.

**Structure:**
```sql
CREATE TABLE `news_updates` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `image_path` VARCHAR(500) NULL,
  `author` VARCHAR(100) DEFAULT 'Admin',
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'published',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_date` DATE NULL,
  
  INDEX `idx_status` (`status`),
  INDEX `idx_published_date` (`published_date`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Status Values:**
- `draft`: Not yet published
- `published`: Visible to users
- `archived`: Hidden from users

---

## 🔄 Database Relationships

```
┌─────────┐
│  items  │◄─────┐
└─────────┘      │
    ▲            │
    │            │
    │     ┌──────┴─────┐
    │     │  bookings  │
    │     └────────────┘
    │            ▲
    │            │
    │     ┌──────┴──────────┐
    └─────┤ pencil_bookings │
          └─────────────────┘

┌─────────┐
│ admins  │
└─────────┘
    │
    ├─── verifies ────► bookings.payment_verified_by
    │
    └─── responds ────► feedback.responded_by


┌──────────┐
│ feedback │◄──── room_id ────┤ items
└──────────┘
```

---

## 🛠️ Maintenance Scripts

### Update Database Structure
```
http://localhost/barcie_php/database/update_database.php
```

Safely adds missing columns and indexes without destroying data.

### Check Current Structure
```
http://localhost/barcie_php/database/check_structure.php
```

View detailed information about all tables, columns, and indexes.

### Run Specific Migration
```
http://localhost/barcie_php/database/migrations/001_update_items_table.php
```

Run individual migration files as needed.

---

## 📝 Common Queries

### Find Available Rooms
```sql
SELECT * FROM items 
WHERE item_type = 'room' 
AND room_status = 'available' 
ORDER BY price ASC;
```

### Get Recent Bookings
```sql
SELECT * FROM bookings 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

### Update Room Status
```sql
UPDATE items 
SET room_status = 'available' 
WHERE id = ?;
```

### Get Pending Feedbacks
```sql
SELECT * FROM feedback 
WHERE approval_status = 'pending' 
ORDER BY created_at DESC;
```

### Revenue Report
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as bookings,
    status
FROM bookings
WHERE MONTH(created_at) = MONTH(CURDATE())
GROUP BY DATE(created_at), status;
```

---

## 🔒 Security Notes

1. **Password Storage**: All passwords use PHP's `password_hash()` with BCRYPT
2. **SQL Injection**: All queries use prepared statements
3. **XSS Protection**: All output is escaped
4. **CSRF Protection**: Tokens implemented on forms
5. **File Uploads**: Validated and stored securely

---

## 🆘 Troubleshooting

### Cannot Connect to Database
1. Check MySQL is running in XAMPP
2. Verify credentials in `database/db_connect.php`
3. Check database name is `barcie_db`

### Migration Errors
1. Check PHP error logs in `C:\xampp\php\logs\`
2. Check MySQL error logs in `C:\xampp\mysql\data\`
3. Ensure proper file permissions

### Missing Tables
Run the update script:
```
http://localhost/barcie_php/database/update_database.php
```

---

## 📞 Support Files

- **db_connect.php** - Database connection
- **user_auth.php** - Authentication & booking logic
- **migrations/** - Schema update scripts
- **check_structure.php** - Database inspection tool

---

**Last Updated:** December 11, 2025  
**Compatible With:** XAMPP, MySQL 5.7+, PHP 7.4+, MariaDB 10.4+
