# BarCIE Guest Portal - Troubleshooting Guide

## Current Issues
- Rooms not loading on Guest.php
- Calendar not displaying events
- API endpoints not responding correctly

## Files Modified

### 1. `/database/user_auth.php`
**Changes Made:**
- ✅ Enabled error display temporarily for debugging
- ✅ Added CORS headers
- ✅ Added `debug_connection` endpoint for diagnostics
- ✅ Enhanced `fetch_items` endpoint with better error handling
- ✅ Changed response format to include success flag: `{ success: true, items: [...], count: X }`

### 2. `/assets/js/guest-bootstrap.js`
**Changes Made:**
- ✅ Updated `loadItems()` to handle new response format
- ✅ Added better error handling for API responses
- ✅ Added support for multiple response formats (backward compatible)

### 3. New Files Created
- ✅ `/debug_live.php` - Comprehensive debug page for live server
- ✅ `/test_api_endpoints.php` - API testing page for local development

## Testing Steps

### Step 1: Upload Changes to Live Server
Upload these modified files:
```
database/user_auth.php
assets/js/guest-bootstrap.js
debug_live.php
```

### Step 2: Run Debug Page
Visit: **https://barcie.safehub-lcup.uk/debug_live.php**

This will check:
- ✓ PHP version
- ✓ Database connection
- ✓ Database tables (items, bookings, feedback)
- ✓ API endpoints functionality
- ✓ Critical files existence
- ✓ PHP extensions

### Step 3: Test Individual Endpoints

#### Test Database Connection
URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=debug_connection`

Expected Response:
```json
{
  "php_version": "8.x.x",
  "mysql_extension": true,
  "db_connected": true,
  "items_count": 11,
  "current_time": "2025-10-17 ..."
}
```

#### Test Fetch Items
URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=fetch_items`

Expected Response:
```json
{
  "success": true,
  "items": [
    {
      "id": 1,
      "name": "Deluxe Room",
      "item_type": "room",
      "capacity": 2,
      "price": "2500",
      ...
    }
  ],
  "count": 11
}
```

#### Test Calendar
URL: `https://barcie.safehub-lcup.uk/database/user_auth.php?action=fetch_guest_availability`

Expected Response:
```json
[
  {
    "title": "Room Name - Occupied (2 days)",
    "start": "2025-10-17",
    "end": "2025-10-19",
    "backgroundColor": "#dc3545",
    ...
  }
]
```

### Step 4: Check Guest.php
Visit: **https://barcie.safehub-lcup.uk/Guest.php**

Open Browser Console (F12) and check for:
- ✓ "Guest: Loading items from user_auth.php..."
- ✓ "Guest: Response status: 200"
- ✓ "Guest: Got wrapped items array: 11"
- ✓ "Guest: Rendering 11 items"

## Common Problems & Solutions

### Problem 1: "Database connection failed"
**Cause:** Database credentials incorrect or MySQL not running
**Solution:** 
- Check `/database/db_connect.php` credentials
- Verify MySQL service is running on live server
- Check if database name exists

### Problem 2: "Items table does not exist"
**Cause:** Database schema not created
**Solution:**
- Import the database schema SQL file
- Run table creation scripts

### Problem 3: "No items found"
**Cause:** Empty items table
**Solution:**
- Insert sample items via admin dashboard
- Import sample data SQL

### Problem 4: "500 Internal Server Error"
**Cause:** PHP syntax error or missing dependencies
**Solution:**
- Check PHP error log on server
- Verify all required PHP extensions installed
- Check file permissions (755 for directories, 644 for files)

### Problem 5: "CORS Policy Error"
**Cause:** Cross-origin request blocked
**Solution:**
- Added CORS headers to `user_auth.php` (already done)
- Verify `.htaccess` allows API requests

## Response Format Changes

### OLD Format (Plain Array)
```javascript
[
  { id: 1, name: "Room 1", ... },
  { id: 2, name: "Room 2", ... }
]
```

### NEW Format (Success Wrapper)
```javascript
{
  "success": true,
  "items": [
    { id: 1, name: "Room 1", ... },
    { id: 2, name: "Room 2", ... }
  ],
  "count": 2
}
```

The JavaScript code now handles **BOTH** formats for backward compatibility.

## After Testing

Once you confirm everything works:

### 1. Disable Debug Mode
In `/database/user_auth.php`, change line 2-4 back to:
```php
<?php
// Disable error display for API endpoints
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

### 2. Remove Debug Files (Optional)
After confirming everything works, you can optionally delete:
- `debug_live.php`
- `test_api_endpoints.php`

## Need Help?

If issues persist after testing:

1. **Check debug_live.php output** - Note any "FAILED" status
2. **Check browser console** - Look for specific error messages
3. **Check server error logs** - PHP errors logged to server logs
4. **Database queries** - Test SQL queries directly in phpMyAdmin

## Quick Fix Checklist

- [ ] Uploaded modified `user_auth.php`
- [ ] Uploaded modified `guest-bootstrap.js`
- [ ] Uploaded `debug_live.php`
- [ ] Visited debug page and checked all tests pass
- [ ] Tested API endpoints individually
- [ ] Visited Guest.php and confirmed rooms load
- [ ] Checked browser console for errors
- [ ] Confirmed calendar displays events

## Contact

If you need further assistance, provide:
- Output from `debug_live.php`
- Browser console errors (F12 → Console tab)
- Server error log entries (if accessible)

---
Generated: October 17, 2025
