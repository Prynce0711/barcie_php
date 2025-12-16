# 🔧 Live Server Issues - Fixes Applied

## Issues Identified

Your live server was experiencing these issues while localhost worked perfectly:

1. ❌ **Google Sign-In not working** for reviews
2. ❌ **Payment verification "Submitted" column showing "90s"** instead of proper time
3. ❌ **Recent activities not displaying properly** for pencil and booking
4. ❌ **Time labels inaccurate** in dashboard

## Root Causes

### 1. **Timezone Mismatch**
- **Problem**: Live server PHP and MySQL were using different timezones (likely UTC vs Asia/Manila)
- **Impact**: All timestamp calculations were incorrect, causing:
  - "90s ago" instead of "just now" or proper time display
  - Recent activities showing wrong times
  - Payment dates displaying incorrectly

### 2. **Database Timezone Not Set**
- **Problem**: MySQL session timezone was not explicitly set to +08:00
- **Impact**: Database stored times in different timezone than PHP calculated them

### 3. **Missing Timezone Configuration**
- **Problem**: `api/recent_activities.php` didn't set timezone at the top
- **Impact**: Time calculations used server's default timezone instead of Asia/Manila

## Fixes Applied

### ✅ Fix 1: Added Timezone to `api/recent_activities.php`
```php
// Set timezone first to ensure consistent time calculations
date_default_timezone_set('Asia/Manila');
```

**File**: [api/recent_activities.php](api/recent_activities.php)

### ✅ Fix 2: Set MySQL Session Timezone in `database/db_connect.php`
```php
// Set MySQL timezone to match PHP timezone (Asia/Manila)
$conn->query("SET time_zone = '+08:00'");
```

**File**: [database/db_connect.php](database/db_connect.php)

### ✅ Fix 3: Enhanced `.htaccess` with Timezone Configuration
```apache
# Set PHP timezone for consistent time handling
<IfModule mod_php.c>
    php_value date.timezone "Asia/Manila"
</IfModule>
```

**File**: [.htaccess](.htaccess)

### ✅ Fix 4: Fixed Payment Verification Time Display
```php
// Format date/time with proper timezone handling
$display_date = $payment_date ? date('M j, Y H:i', strtotime($payment_date)) : 'N/A';
```

**File**: [components/dashboard/sections/payment_verification.php](components/dashboard/sections/payment_verification.php)

### ✅ Fix 5: Improved Activity Sorting with Null Handling
```php
// Sort all activities by date (handle null/invalid dates)
usort($activities, function($a, $b) {
    $timeA = strtotime($a['activity_date'] ?? '1970-01-01');
    $timeB = strtotime($b['activity_date'] ?? '1970-01-01');
    if ($timeA === false) $timeA = 0;
    if ($timeB === false) $timeB = 0;
    return $timeB - $timeA;
});
```

## Deployment Steps for Live Server

### Step 1: Upload Files
Upload these modified files to your live server:
- ✅ `api/recent_activities.php`
- ✅ `database/db_connect.php`
- ✅ `components/dashboard/sections/payment_verification.php`
- ✅ `.htaccess`
- ✅ `debug_live_timezone.php` (NEW - for testing)

### Step 2: Set MySQL Timezone Permanently

#### Option A: Via SQL (Recommended)
Connect to MySQL and run:
```sql
SET GLOBAL time_zone = '+08:00';
SET SESSION time_zone = '+08:00';
```

#### Option B: Via my.cnf/my.ini
Add to your MySQL configuration file:
```ini
[mysqld]
default-time-zone = '+08:00'
```

Then restart MySQL service:
```bash
sudo systemctl restart mysql
```

### Step 3: Verify php.ini Timezone (Optional but Recommended)

If you have access to `php.ini`, add/update:
```ini
date.timezone = "Asia/Manila"
```

Then restart web server:
```bash
sudo systemctl restart apache2
# OR
sudo systemctl restart nginx
sudo systemctl restart php-fpm
```

### Step 4: Test Configuration

Visit this diagnostic page on your live server:
```
https://your-domain.com/debug_live_timezone.php
```

This will show:
- ✓ PHP timezone settings
- ✓ MySQL timezone settings
- ✓ Time synchronization between PHP and MySQL
- ✓ Recent activities test
- ✓ Payment verification test

### Step 5: Clear Cache

Clear all caches:
```bash
# Browser cache
- Press Ctrl+F5 (hard refresh)
- Clear cookies for your domain

# PHP opcache (if enabled)
# Add ?opcache_reset=1 to any PHP page
```

## Google Sign-In Issue

The Google Sign-In is already properly configured in the code. If it's still not working on live server:

### 1. Check Google OAuth Settings

Visit: https://console.cloud.google.com/apis/credentials

Ensure your OAuth 2.0 Client has:
- ✅ Authorized JavaScript origins: `https://your-live-domain.com`
- ✅ Authorized redirect URIs: `https://your-live-domain.com`

### 2. Verify Client ID

Check [components/guest/sections/rooms.php](components/guest/sections/rooms.php) line 187:
```html
data-client_id="173306587840-ine8ao88f5a8r5mnjnuc7fa8sdmo110c.apps.googleusercontent.com"
```

Make sure this matches your Google Cloud Console Client ID.

### 3. Browser Console Check

Open browser DevTools (F12) on the feedback form page and check for:
- ❌ `Cross-origin` errors → Add domain to Google OAuth settings
- ❌ `Invalid client` errors → Update client ID
- ❌ `Blocked by CORS` errors → Check .htaccess CORS settings

## Verification Checklist

After deployment, verify these work correctly:

### ✅ Recent Activities
- [ ] Shows "JUST NOW" for activities within 60 seconds
- [ ] Shows "5M AGO" for 5 minutes ago
- [ ] Shows "2H AGO" for 2 hours ago
- [ ] Updates every second without page refresh

### ✅ Payment Verification
- [ ] "Submitted" column shows proper date/time (not "90s")
- [ ] Format: "Dec 16, 2025 14:30"
- [ ] Times are in Philippine timezone

### ✅ Google Sign-In
- [ ] Google sign-in button appears
- [ ] Clicking it opens Google OAuth popup
- [ ] After signing in, user info displays correctly
- [ ] Submit button becomes enabled after sign-in

### ✅ Time Consistency
- [ ] All timestamps across the system use Philippine time
- [ ] No timezone conversion issues
- [ ] Database and PHP times match

## Troubleshooting

### Issue: Still showing wrong times

**Solution**: Check server timezone:
```bash
php -r "echo date_default_timezone_get();"
mysql -e "SELECT @@global.time_zone, @@session.time_zone;"
```

Both should show `Asia/Manila` or `+08:00`.

### Issue: Google Sign-In not loading

**Solution**: Check browser console for errors. Common fixes:
1. Add your domain to Google OAuth authorized origins
2. Make sure HTTPS is enabled (Google requires HTTPS for OAuth)
3. Check if `accounts.google.com/gsi/client` script is blocked

### Issue: Payment verification still shows "90s"

**Solution**: 
1. Clear PHP opcache
2. Restart web server
3. Hard refresh browser (Ctrl+F5)
4. Check `debug_live_timezone.php` for time difference

### Issue: Recent activities not updating

**Solution**:
1. Check [api/recent_activities.php](api/recent_activities.php) is accessible
2. Open browser DevTools → Network tab
3. Look for `/api/recent_activities.php` requests
4. Check for 500/401 errors

## Quick Test Commands

### Test on Live Server:
```bash
# Check PHP timezone
php -r "echo date_default_timezone_get();"

# Check MySQL timezone
mysql -u root -p -e "SELECT @@global.time_zone, @@session.time_zone;" barcie_db

# Test time difference
php debug_live_timezone.php

# Check if .htaccess is working
php -r "phpinfo();" | grep date.timezone
```

## Files Modified

| File | Changes | Purpose |
|------|---------|---------|
| `api/recent_activities.php` | Added timezone setting | Fix time calculations |
| `database/db_connect.php` | Added MySQL timezone | Sync DB with PHP |
| `components/dashboard/sections/payment_verification.php` | Fixed time display | Show correct submission time |
| `.htaccess` | Added PHP timezone directive | Server-wide timezone |
| `debug_live_timezone.php` | NEW file | Diagnostic tool |

## Additional Notes

### Why This Happens

The issue occurs because:

1. **Localhost (XAMPP)**: Usually has timezone set to your local timezone by default
2. **Live Server**: Often uses UTC by default
3. **Result**: 8-hour difference (UTC vs Asia/Manila)

This causes:
- Recent activity showing "90 seconds" is actually from 8 hours ago
- Times appearing incorrect throughout the system
- Database storing times in one timezone, PHP calculating in another

### Prevention

To prevent this in future deployments:

1. ✅ Always set timezone in ALL entry points:
   ```php
   date_default_timezone_set('Asia/Manila');
   ```

2. ✅ Always set MySQL timezone after connection:
   ```php
   $conn->query("SET time_zone = '+08:00'");
   ```

3. ✅ Use consistent date/time functions across codebase

4. ✅ Test on staging server with same configuration as production

---

## Support

If issues persist after applying all fixes:

1. Run `debug_live_timezone.php` and send screenshot
2. Check browser console (F12) for JavaScript errors
3. Check PHP error logs
4. Verify all files were uploaded correctly

**Last Updated**: December 16, 2025
