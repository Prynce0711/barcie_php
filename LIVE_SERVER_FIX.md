# 🚀 Live Server Deployment Fix - Step by Step

## Problem
API endpoints returning HTML instead of JSON with error:
```
Unexpected token '<', "<!DOCTYPE html>..." is not valid JSON
```

## Files to Upload

Upload these **3 files** to your live server:

1. ✅ `database/user_auth.php` - Fixed API handler with error catching
2. ✅ `debug_live.php` - Diagnostic page
3. ✅ `api_test.php` - Simple API test (no database needed)

## Step-by-Step Testing

### Step 1: Test Basic PHP
📍 URL: `https://barcie.safehub-lcup.uk/api_test.php`

**Expected Response:**
```json
{
  "success": true,
  "message": "PHP is working correctly",
  "php_version": "8.x.x",
  "extensions": {
    "mysqli": true,
    "json": true
  },
  "database": {
    "status": "connected"
  }
}
```

**If you see HTML error:**
- Check PHP error logs on server
- PHP version might be too old (need 7.4+)
- Missing PHP extensions

### Step 2: Test Ping Endpoint (No Database)
📍 URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=ping`

**Expected Response:**
```json
{
  "success": true,
  "message": "API is responding",
  "php_version": "8.x.x"
}
```

**If this fails:**
- File upload failed or wrong location
- PHP syntax error in user_auth.php
- Server blocking .php files

### Step 3: Test Database Connection
📍 URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=debug_connection`

**Expected Response:**
```json
{
  "success": true,
  "php_version": "8.x.x",
  "mysqli_extension": true,
  "db_connected": true,
  "items_count": 11,
  "bookings_count": 5
}
```

**If db_connected is false:**
- Check `database/db_connect.php` on live server
- Verify database credentials
- Check MySQL service is running
- Check database name exists

### Step 4: Test Fetch Items
📍 URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=fetch_items`

**Expected Response:**
```json
{
  "success": true,
  "items": [
    {
      "id": "1",
      "name": "Deluxe Room",
      "item_type": "room",
      "capacity": "2",
      "price": "2500.00"
    }
  ],
  "count": 11
}
```

**If items array is empty:**
- Database has no items
- Need to add rooms/facilities via admin dashboard

### Step 5: Test Calendar
📍 URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=fetch_guest_availability`

**Expected Response:**
```json
[
  {
    "title": "Room Name - Occupied (2 days)",
    "start": "2025-10-17",
    "end": "2025-10-19"
  }
]
```

### Step 6: Test Guest Portal
📍 URL: `https://barcie.safehub-lcup.uk/Guest.php`

**Check Browser Console (F12):**
```
Guest: Loading items from user_auth.php...
Guest: Response status: 200
Guest: Got wrapped items array: 11
Guest: Rendering 11 items
```

**If still seeing errors:**
- Clear browser cache (Ctrl + Shift + Delete)
- Hard refresh (Ctrl + F5)
- Check browser console for specific error

## Common Live Server Issues

### Issue 1: "Vendor directory not found"
**Solution:**
```bash
cd /path/to/barcie_php
composer install
```

### Issue 2: "db_connect.php not found"
**Solution:**
```bash
# Check file exists on server
ls -la database/db_connect.php

# If missing, create it with correct credentials
nano database/db_connect.php
```

### Issue 3: "Database connection failed"
**Problem:** Wrong credentials in `db_connect.php`

**Solution:**
```php
<?php
// In database/db_connect.php
$servername = "localhost";  // Usually "localhost" on live servers
$username = "your_db_user";  // Your cPanel/database username
$password = "your_db_password";  // Your database password
$dbname = "your_db_name";  // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

### Issue 4: "Permission denied"
**Solution:**
```bash
# Fix file permissions on live server
chmod 644 database/user_auth.php
chmod 644 database/db_connect.php
chmod 755 database/
```

### Issue 5: "Headers already sent"
**Fixed!** - Our new code clears output buffers before sending JSON

### Issue 6: PHP Version Too Old
**Check:**
```bash
php -v
```

**Requirement:** PHP 7.4 or higher

**Solution:** Upgrade PHP via cPanel or contact hosting provider

## Error Response Examples

### Good Response (Working)
```json
{
  "success": true,
  "items": [...]
}
```

### Database Error (Fixable)
```json
{
  "success": false,
  "error": "Database connection failed",
  "message": "Access denied for user 'xxx'@'localhost'",
  "debug_info": {
    "mysqli_extension": true,
    "file_exists": true
  }
}
```

### Fatal Error (Needs Investigation)
```json
{
  "success": false,
  "error": "Fatal Error",
  "message": "Call to undefined function...",
  "file": "user_auth.php",
  "line": 123
}
```

## Debug Checklist

- [ ] Uploaded `api_test.php` to live server root
- [ ] Accessed api_test.php in browser - saw JSON response
- [ ] Verified PHP version is 7.4+
- [ ] Verified mysqli extension is loaded
- [ ] Uploaded updated `database/user_auth.php`
- [ ] Tested ping endpoint - got JSON response
- [ ] Tested debug_connection endpoint
- [ ] Verified database credentials in db_connect.php
- [ ] Tested fetch_items endpoint - got items array
- [ ] Tested Guest.php - rooms loaded correctly
- [ ] Checked browser console - no errors

## If All Else Fails

### Enable PHP Error Logging
Add to `database/user_auth.php` top (temporarily):
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
```

Then check `database/php_errors.log` for detailed errors.

### Contact Information
If you're still stuck, provide:
1. Output from `api_test.php`
2. Output from `debug_connection` endpoint  
3. PHP error log contents
4. PHP version from cPanel
5. Database credentials (username only, no password!)

---
**Last Updated:** October 17, 2025
