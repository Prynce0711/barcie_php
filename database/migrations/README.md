# Database Migrations

This folder contains database migration files that update your existing BarCIE database schema safely.

## ✅ What These Migrations Do

These migrations are designed to **update your existing database** without destroying any data:

- ✅ Add missing columns
- ✅ Add missing indexes  
- ✅ Ensure proper data types
- ✅ Hash plain text passwords
- ✅ Create new tables if needed

## 🚀 How to Run

### Option 1: Run All Migrations (Recommended)

Visit in your browser:
```
http://localhost/barcie_php/database/update_database.php
```

This will run all migrations in order and show you the results.

### Option 2: Command Line

```bash
cd C:\xampp\htdocs\barcie_php\database
php update_database.php
```

### Option 3: Individual Migrations

Run specific migrations as needed:
```
http://localhost/barcie_php/database/migrations/001_update_items_table.php
http://localhost/barcie_php/database/migrations/002_update_admins_table.php
```

## 📋 Migration Files

1. **001_update_items_table.php**
   - Adds `images`, `addons`, `room_status` columns
   - Adds indexes for performance
   - Ensures proper ENUM values

2. **002_update_admins_table.php**
   - Adds `full_name`, `role`, `is_active`, `updated_at` columns
   - Hashes plain text passwords
   - Ensures username is unique

3. **003_update_bookings_table.php**
   - Adds `reminder_sent`, `payment_date`, `checked_out_at` columns
   - Adds indexes for common queries
   - Updates ENUM values

4. **004_verify_pencil_bookings_table.php**
   - Verifies pencil_bookings table exists
   - Uses existing migration if table missing

5. **005_update_feedback_table.php**
   - Adds `admin_response`, `responded_by`, `responded_at` columns
   - Enables admin feedback responses

6. **006_verify_news_updates_table.php**
   - Verifies news_updates table exists
   - Uses existing migration if table missing

7. **007_create_users_table.php**
   - Creates users table for future guest accounts
   - Only if table doesn't exist

## 🔒 Safety Features

- **Non-Destructive**: Won't drop tables or delete data
- **Idempotent**: Safe to run multiple times
- **Error Handling**: Shows clear error messages
- **Transaction Safe**: Uses InnoDB engine
- **Validation**: Checks before adding columns/indexes

## 📊 Check Your Database

View current database structure:
```
http://localhost/barcie_php/database/check_structure.php
```

This shows:
- All tables and their columns
- Data types and constraints
- Indexes and foreign keys
- Row counts

## ⚙️ What Gets Updated

### Items Table
- JSON support for multiple images
- Room status tracking
- Rating system fields

### Admins Table  
- Role-based access control
- Password security (BCRYPT)
- Activity tracking

### Bookings Table
- Payment tracking
- Reminder system
- Checkout timestamps

### Feedback Table
- Admin response capability
- Response tracking

## 🎯 Expected Results

After running migrations, you'll see output like:

```
Checking items table structure...
✓ Items table exists
✅ Added column: images
✅ Added column: room_status
✅ Added index: idx_room_status
✅ Items table structure is up to date
```

## ❌ Troubleshooting

### "Column already exists"
This is normal! The script checks before adding columns. If it already exists, it skips it.

### "Access denied"
1. Make sure MySQL is running in XAMPP
2. Check credentials in `database/db_connect.php`
3. Default XAMPP: user=`root`, password=*(empty)*

### "Table doesn't exist"
The script will create missing tables automatically. Just run the update script.

### "Cannot add foreign key"
Some migrations skip foreign keys if parent tables don't exist yet. This is normal and safe.

## 📖 Documentation

Full schema documentation:
```
database/SCHEMA.md
```

Contains:
- Complete table structures
- Column descriptions
- Relationships diagram
- Common queries
- Security notes

## 🔄 Version Control

These migration files are safe to commit to git. They:
- Don't contain sensitive data
- Are reusable across environments
- Don't include database credentials
- Are well-documented

## ⚡ Performance

All migrations add appropriate indexes for:
- Faster lookups
- Better query performance
- Efficient joins
- Sorted results

## 💡 Tips

1. **Always backup** before running migrations (though they're non-destructive)
2. **Run migrations** after pulling code updates
3. **Check output** for any warnings or errors
4. **Verify changes** using check_structure.php
5. **Keep migrations** in numerical order

## 🆘 Need Help?

If you encounter issues:

1. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Check MySQL logs: `C:\xampp\mysql\data\`
3. Review migration output for specific errors
4. Verify XAMPP services are running
5. Check database permissions

---

**Safe to run anytime!** These migrations won't destroy your existing data.

**Last Updated:** December 11, 2025
